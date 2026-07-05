import fs from 'fs';
import path from 'path';
import zlib from 'zlib';
import PHPParser from 'php-parser';
import {
	uuidv4,
	encrypt,
	base64ToArrayBuffer,
	decrypt,
	rsaDecrypt,
	arrayBufferToBase64,
} from '@ka-libs/crypto';
import { BuildContext, AstNode, AnyAstNode, AstReplacement, AstNodeMap } from '../types';
import {
	CONST_PREFIX,
	EXCLUDE_STRING,
	PRIVATE_KEY,
	PUBLIC_KEY,
	RANDOM_NUMBER_SIZE,
	STRING_OPT,
} from '../config/constans';
import * as logger from '../utils/logger';
import * as utils from '../utils/utils';
import { isKind } from '../utils/typeGard';
import { Runtime } from '../config/runtime';

// ========================================
// AST String Pool Compiler
// ========================================

export default async function (buildContext: BuildContext) {
	const POOL_NAME = CONST_PREFIX + uuidv4().slice(-6);
	buildContext.pool = POOL_NAME;

	const { ENABLE_STRING_POOL, ENABLE_POOL_COMPRESS, MIN_STRING_LENGTH } = STRING_OPT;

	const phpFiles: string[] = [];
	const stringMap = buildContext.strings;
	const stringIdSet = new Set<number>();
	const extenalNode = new Set<AstNode<AnyAstNode>>();
	const runtimeFunctionName = CONST_PREFIX + uuidv4().slice(-6);
	buildContext.runtime.stringPoolFunction = runtimeFunctionName;

	const parser = new PHPParser.Engine({
		parser: { php7: true, extractDoc: false },
		ast: { withPositions: true },
	});

	// ========================================
	// Utils
	// ========================================

	function shouldSkipString(str: string): boolean {
		if (!str || str.length < MIN_STRING_LENGTH) return true;
		if (str.includes('<?php') || str.includes('<?=') || str.includes('?>')) return true;
		if (str.includes(POOL_NAME)) return true;
		if (EXCLUDE_STRING.includes(str)) return true;
		return false;
	}

	/**
	 * 判断节点是否处于“编译期上下文”（不应被替换）
	 * 包括：常量定义、类属性默认值、函数/闭包参数默认值、Attribute、Enum Case
	 */
	function isCompileTimeContext(node: AstNode): boolean {
		let current: AstNode | undefined | null = node.parent;

		while (current) {
			// 1. 常量定义: const X = '...'
			if (isKind(current, 'constant') && isKind(current.parent, 'constantstatement')) return true;
			// 类常量: public const X = '...'
			if (isKind(current, 'classconstant')) return true;

			// 2. 类属性默认值: public $x = '...'
			if (isKind(current, 'property') && isKind(current.parent, 'propertystatement')) return true;

			// 3. 函数/方法/闭包/箭头函数的参数默认值
			if (isKind(current, 'parameter')) return true;

			// 4. Attribute: #[Route('...')]
			if (isKind(current, 'attribute') || isKind(current, 'attrgroup')) return true;

			// 5. Enum Case: case X = '...'
			if (isKind(current, 'enumcase')) return true;

			// 6. Static variable default: static $x = '...'
			if (isKind(current, 'staticvariable')) return true;

			current = current.parent;
		}

		return false;
	}

	/**
	 * 从 AST 节点递归提取纯字符串值
	 * - 普通字符串字面量：直接返回
	 * - Encapsed / Heredoc / Nowdoc：递归拼接所有子片段
	 * - 包含变量插值或无法识别的节点：返回 null（不参与字符串池）
	 */
	function extractStringValue(node: AstNode | null | undefined): string | null {
		if (!node || !isKind(node, 'string')) return null;
		// 1. 普通字符串字面量
		return node.value ?? '';
	}

	/**
	 * 获取某个 part 在 encapsed.raw 字符串中的真实片段内容
	 * @param encapsedRaw encapsed 节点本身的 raw 字符串（例如：`<<<HTML\n内容\nHTML;`）
	 * @param part 需要提取内容的 part 节点
	 * @param encapsedStartOffset encapsed 节点在源码中的起始偏移量 (encapsed.loc.start.offset)
	 */
	function getPartContentInEncapsedRaw(
		encapsedRaw: string,
		part: AstNode,
		encapsedStartOffset: number,
	): string {
		if (!part.loc?.start?.offset || !part.loc?.end?.offset) {
			return '';
		}

		// 1. 计算 part 相对于 encapsedRaw 起始位置的偏移量
		const relativeStart = part.loc.start.offset - encapsedStartOffset;
		const relativeEnd = part.loc.end.offset - encapsedStartOffset;

		// 2. 在 encapsedRaw 中精准截取该片段
		return encapsedRaw.slice(relativeStart, relativeEnd);
	}

	function extractEncapsed(
		node: AstNode<AstNodeMap['encapsed']>,
		callback: (child: { isString: boolean; raw: string }) => void,
	) {
		// 2. Encapsed / Heredoc / Nowdoc 等容器节点
		//    通过 isKind 收窄后，node.value 才是安全的数组类型
		for (const child of node.value) {
			const value = getPartContentInEncapsedRaw(node.raw, child, node.loc!.start.offset);
			callback({
				isString: isKind(child.expression, 'string'),
				raw: value,
			});
		}
	}

	function nodeInEncapsed(node: AstNode<AnyAstNode>) {
		let parent: AstNode<AnyAstNode> | null | undefined = node;
		while (parent) {
			if (isKind(parent, 'encapsed')) {
				return true;
			}
			parent = parent.parent;
		}
		return false;
	}

	// ========================================
	// Scan & Parse
	// ========================================

	function attachParent(node: AstNode<AnyAstNode>, parent?: AstNode<AnyAstNode> | null) {
		if (!node || typeof node !== 'object') return;
		node.parent = parent ?? null;
		for (const key in node) {
			if (key === 'parent' || key === 'loc') continue;
			const value = (node as any)[key];
			if (Array.isArray(value))
				value.forEach((child: AstNode<AnyAstNode>, index) => {
					child.index = index;
					child.isFirstChild = index === 0;
					child.isLastChild = index === value.length - 1;
					attachParent(child, node);
				});
			else if (value && typeof value === 'object' && value.kind) attachParent(value, node);
		}
	}

	function walk(node: AstNode | null, callback: (n: AstNode) => void) {
		if (!node || typeof node !== 'object') return;
		callback(node);
		for (const key in node) {
			if (key === 'parent' || key === 'loc') continue;
			const value = (node as any)[key];
			if (Array.isArray(value)) value.forEach((v: any) => walk(v, callback));
			else if (value && typeof value === 'object' && value.kind) walk(value, callback);
		}
	}

	// ========================================
	// Collect Strings (Phase 1)
	// ========================================
	function getRandomID() {
		let stringId = utils.randomNumber(RANDOM_NUMBER_SIZE);
		while (stringIdSet.has(stringId)) {
			stringId = utils.randomNumber(RANDOM_NUMBER_SIZE);
		}
		return stringId;
	}
	function collectStrings(ast: AstNode) {
		function handleEncapsed(node: AstNode<AstNodeMap['encapsed']>) {
			extractEncapsed(node, ({ isString, raw }) => {
				if (isString) {
					if (raw.length < MIN_STRING_LENGTH) return;
					if (stringMap.has(raw)) return;
					stringMap.set(raw, getRandomID());
				}
			});
		}

		function handleString(node: AstNode<AstNodeMap['string']>) {
			if (nodeInEncapsed(node)) return;

			const value = extractStringValue(node);
			if (value === null) return;
			if (shouldSkipString(value)) return;
			if (stringMap.has(value)) return;

			stringMap.set(value, getRandomID());
		}

		walk(ast, (node) => {
			if (!isKind(node, 'string') && !isKind(node, 'encapsed')) return;
			if (isCompileTimeContext(node)) return;

			if (isKind(node, 'encapsed')) {
				handleEncapsed(node);
			}

			if (isKind(node, 'string')) {
				handleString(node);
			}
		});
	}

	// ========================================
	// Replace Strings (Phase 2)
	// ========================================

	function collectReplacements(ast: AstNode): AstReplacement[] {
		const replacements: AstReplacement[] = [];
		const visited = new Set<string>();

		function handleEncapsed(node: AstNode<AstNodeMap['encapsed']>) {
			const result: string[] = [];
			if (!node.loc?.start?.offset || !node.loc?.end?.offset) return;

			extractEncapsed(node, ({ isString, raw }) => {
				if (isString) {
					if (raw.length < MIN_STRING_LENGTH) {
						console.log({ raw });
						result.push(`'${raw}'`);
					} else {
						const id = stringMap.get(raw)!;
						result.push(`${runtimeFunctionName}(${id})`);
					}
				} else {
					result.push(raw.replace(/^{/, '').replace(/}$/, ''));
				}
			});

			if (result.length === 0) return;
			const key = `${node.loc.start.offset}:${node.loc.end.offset}`;
			if (visited.has(key)) return;
			visited.add(key);

			replacements.push({
				start: node.loc.start.offset,
				end: node.loc.end.offset,
				value: result.join('.'),
			});
		}

		function handleString(node: AstNode<AstNodeMap['string']>) {
			if (nodeInEncapsed(node)) return;

			const value = extractStringValue(node);
			if (value === null) return;
			if (!stringMap.has(value)) return;
			if (!node.loc?.start?.offset || !node.loc?.end?.offset) return;

			const key = `${node.loc.start.offset}:${node.loc.end.offset}`;
			if (visited.has(key)) return;
			visited.add(key);

			const id = stringMap.get(value)!;

			replacements.push({
				start: node.loc.start.offset,
				end: node.loc.end.offset,
				value: `${runtimeFunctionName}(${id})`,
			});
		}

		walk(ast, (node) => {
			if (!isKind(node, 'string') && !isKind(node, 'encapsed')) return;
			if (isCompileTimeContext(node)) return;
			if (!node.loc?.start?.offset || !node.loc?.end?.offset) return;

			if (isKind(node, 'encapsed')) {
				handleEncapsed(node);
			}

			if (isKind(node, 'string')) {
				handleString(node);
			}
		});

		replacements.sort((a, b) => b.start - a.start);
		return replacements;
	}

	function applyReplacements(source: string, replacements: AstReplacement[]): string {
		for (const r of replacements) {
			// normal string
			source = source.slice(0, r.start) + r.value + source.slice(r.end);
		}

		return source;
	}

	// ========================================
	// Runtime Generation
	// ========================================

	async function generateRuntimeCode(): Promise<string> {
		const pool: Record<number, string> = {};
		for (const [value, id] of stringMap) {
			pool[id] = value;
		}

		const json = JSON.stringify(pool);
		let runtimePoolCode: string;

		if (ENABLE_POOL_COMPRESS) {
			const result = await encrypt(json, PUBLIC_KEY);
			const valid = base64ToArrayBuffer(result!.valid);
			const data = base64ToArrayBuffer(result!.data);

			const combined = new Uint8Array(256 + data.byteLength);
			combined.set(new Uint8Array(valid), 0);
			combined.set(new Uint8Array(data), 256);

			const filePath = path.resolve(Runtime.distDir, './data.bin');
			fs.writeFileSync(filePath, combined);

			runtimePoolCode = `
if (!defined('${POOL_NAME}')) {
    define('${POOL_NAME}', d(__DIR__));
}`;
		} else {
			runtimePoolCode = `
if (!defined('${POOL_NAME}')) {
    define('${POOL_NAME}', '${json.replace(/'/g, "\\'")}');
}`;
		}

		return `
/* KA_RUNTIME_START */
/* ========================================
* KA String Pool Runtime
* ======================================== */
${runtimePoolCode}
if (!function_exists('${runtimeFunctionName}')) {
    function ${runtimeFunctionName}($id) {
        static $pool = null;
        if ($pool === null) {
            $pool = json_decode(${POOL_NAME}, true);
        }
        return $pool[$id] ?? '';
    }
}
/* ======================================== */
/* KA_RUNTIME_END */
`;
	}

	function findInsertIndex(lines: string[]): number {
		for (let i = 0; i < lines.length; i++) {
			if (lines[i].includes('/* KA_PHP_START */')) return i + 1;
			if (lines[i].includes("if (!defined('ABSPATH'))")) {
				for (let j = i; j < lines.length; j++) {
					if (lines[j].trim() === '}') return j + 1;
				}
			}
		}
		return 1;
	}

	async function injectRuntime() {
		logger.log('⚡ 注入 Runtime');
		if (!Runtime.entryFile) {
			logger.log('⚠️ 未找到入口文件，跳过 Runtime 注入');
			return;
		}
		const entryPath = path.resolve(Runtime.distDir, Runtime.entryFile); // 根据实际入口调整

		let content = fs.readFileSync(entryPath, 'utf8');
		if (content.includes('KA_RUNTIME_START')) return;

		const runtime = await generateRuntimeCode();
		const lines = content.split(/\r?\n/);
		const insertIndex = findInsertIndex(lines);
		lines.splice(insertIndex, 0, '', runtime, '');
		fs.writeFileSync(entryPath, lines.join('\n'), 'utf8');
	}

	// ========================================
	// Main
	// ========================================

	try {
		console.log('🚀 开始 AST String Pool Build...\n');

		phpFiles.push(...(await utils.scanPHPFile(buildContext.distDir)));
		console.log(`📦 共扫描 ${phpFiles.length} 个 PHP 文件\n`);

		// Phase 1: 收集所有可替换字符串
		for (const file of phpFiles) {
			logger.log(`🔍 收集字符串: ${path.relative(buildContext.distDir, file)}`);
			const source = fs.readFileSync(file, 'utf8');
			if (source.includes('KA_RUNTIME_START')) continue;

			try {
				const ast = parser.parseCode(source, file) as AstNode;
				attachParent(ast);
				collectStrings(ast);
			} catch (err) {
				console.error(`❌ 解析失败: ${file}`, err);
			}
		}

		console.log(`📦 收集到 ${stringMap.size} 个字符串\n`);

		if (ENABLE_STRING_POOL && stringMap.size > 0) {
			// Phase 2: 替换字符串
			for (const file of phpFiles) {
				logger.log(`🔄 替换字符串: ${path.relative(buildContext.distDir, file)}`);
				const source = fs.readFileSync(file, 'utf8');
				if (source.includes('KA_RUNTIME_START')) continue;

				try {
					const ast = parser.parseCode(source, file) as AstNode;
					attachParent(ast);
					const replacements = collectReplacements(ast);
					if (replacements.length > 0) {
						const newSource = applyReplacements(source, replacements);
						fs.writeFileSync(file, newSource, 'utf8');
					}
				} catch (err) {
					console.error(`❌ 替换失败: ${file}`, err);
				}
			}

			// Phase 3: 注入 Runtime
			await injectRuntime();
		}

		console.log('\n🎉 AST String Pool 完成');
		console.log(`⚡ Runtime Function: ${runtimeFunctionName}`);
		console.log(`📊 字符串池大小: ${stringMap.size}`);

		return buildContext;
	} catch (err) {
		console.error('❌ 执行失败:', err);
		throw err;
	}
}
