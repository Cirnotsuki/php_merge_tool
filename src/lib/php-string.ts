import fs from 'fs';
import path from 'path';
import crypto from 'crypto';
import zlib from 'zlib';
import { uuidv4 } from '@ka-libs/crypto/uuidv4';
import { BuildContext } from '../types';
import { CONST_PREFIX, STRING_OPT } from '../config/constans';
import { Runtime } from '../config/runtime';
import * as logger from '../utils/logger';
import * as utils from '../utils/utils';

// ========================================
// String Pool Compiler
// ========================================

export default async function (buildContext: BuildContext) {
	const POOL_NAME = CONST_PREFIX + uuidv4().slice(-6);
	buildContext.pool = POOL_NAME;

	const { ENABLE_STRING_POOL, ENABLE_POOL_COMPRESS, MIN_STRING_LENGTH, MAX_STRING_LENGTH } =
		STRING_OPT;

	// ========================================

	// ========================================

	const phpFiles: string[] = [];

	const stringMap = buildContext.strings;

	const constantMap = buildContext.constants;

	/**
	 * Runtime Function Name
	 */
	const runtimeFunctionName = 'KA_' + uuidv4().slice(-6);

	buildContext.runtime.stringPoolFunction = runtimeFunctionName;

	// ========================================
	// Logger
	// ========================================

	// ========================================
	// Utils
	// ========================================

	function shouldSkipString(str: string) {
		if (!str) {
			return true;
		}
		/**
		 * PHP Template
		 */
		if (
			str.includes('<?php') ||
			str.includes('<?= ') ||
			str.includes('<?=') ||
			str.includes('?>')
		) {
			return true;
		}

		/**
		 * 常量名
		 */
		// if (constantMap.has(str)) {
		//   return true;
		// }

		/**
		 * 太短
		 */
		if (str.length < MIN_STRING_LENGTH) {
			return true;
		}

		/**
		 * 太长
		 */
		// if (str.length > MAX_STRING_LENGTH) {
		//   return true;
		// }

		/**
		 * URL
		 */
		// if (str.startsWith("http://") || str.startsWith("https://")) {
		//   return true;
		// }

		/**
		 * HTML
		 */
		// if (str.includes("<") || str.includes(">")) {
		//   return true;
		// }

		/**
		 * SQL
		 */
		// if (/\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b/i.test(str)) {
		//   return true;
		// }

		/**
		 * WP Hook
		 */
		// if (/^[a-z0-9_\-\/]+$/.test(str)) {
		//   return true;
		// }

		/**
		 * 纯常量
		 */
		// if (/^[A-Z0-9_]+$/.test(str)) {
		//   return true;
		// }

		/**
		 * Runtime 自身
		 */
		if (str.includes(POOL_NAME)) {
			return true;
		}

		return false;
	}

	/**
	 * 检测是否为 Compile-Time Context
	 */
	function shouldSkipByContext(content: string, matchIndex: number) {
		const before = content.substring(Math.max(0, matchIndex - 300), matchIndex);

		return (
			/**
			 * const A = 'xxx'
			 */
			/\bconst\s+[A-Z0-9_]+\s*=\s*$/i.test(before) ||
			/**
			 * public const A =
			 */
			/\b(public|protected|private)\s+\$[A-Z0-9_]+\s*=\s*$/i.test(before) ||
			/**
			 * enum case A = 'xxx'
			 */
			/\bcase\s+[A-Z0-9_]+\s*=\s*$/i.test(before) ||
			/**
			 * function test($a = 'xxx')
			 */
			/function\s+[a-zA-Z0-9_]+\s*\([^)]*=\s*$/i.test(before) ||
			/**
			 * fn($a = 'xxx')
			 */
			/fn\s*\([^)]*=\s*$/i.test(before) ||
			/**
			 * attribute:
			 * #[Route('xxx')]
			 */
			/#\[[^\]]*$/i.test(before)
		);
	}

	/**
	 * 查找 Runtime 插入位置
	 */
	function findInsertIndex(lines: string | any[]) {
		for (let i = 0; i < lines.length; i++) {
			if (lines[i].includes("if (!defined('ABSPATH'))")) {
				for (let j = i; j < lines.length; j++) {
					if (lines[j].trim() === '}') {
						return j + 1;
					}
				}
			}
		}

		return 1;
	}

	// ========================================
	// Scan Directory
	// ========================================

	async function scanDirectory(dir: string) {
		const entries = fs.readdirSync(dir, {
			withFileTypes: true,
		});

		for (const entry of entries) {
			const fullPath = path.join(dir, entry.name);

			if (utils.isExcluded(fullPath)) {
				continue;
			}

			if (entry.isDirectory()) {
				scanDirectory(fullPath);

				continue;
			}

			if (utils.isPhpFile(fullPath)) {
				phpFiles.push(fullPath);
			}
		}
	}

	function isClassPropertyLine(line: string) {
		return /^\s*(public|protected|private|static)\s+\$/i.test(line) || /^\s*const\s+/i.test(line);
	}

	function isInArrayContext(line: string | any[], index: number) {
		const before = line.slice(0, index);

		// 最近是否出现 [
		const lastOpen = before.lastIndexOf('[');

		if (lastOpen === -1) return false;

		const afterOpen = before.slice(lastOpen);

		// 简单判断是否是 callable array
		return afterOpen.includes(',');
	}

	function isClassRefContext(line: string, index: number) {
		const before = line.slice(0, index);

		return /::\s*class/.test(before);
	}

	// ========================================
	// Collect Strings
	// ========================================

	async function collectStrings() {
		const stringRegex = /'((?:\\.|[^'\\])*)'/g;

		let stringId = stringMap.size;

		for (const file of phpFiles) {
			logger.log(`🔍 扫描字符串: ${path.relative(Runtime.distDir, file)}`);

			const content = fs.readFileSync(file, 'utf8');

			if (content.includes('KA_RUNTIME_START')) {
				continue;
			}

			const lines = content.split('\n');

			let offset = 0;

			// =========================
			// 状态机：class / function
			// =========================
			let inClass = false;
			let braceDepth = 0;

			for (let i = 0; i < lines.length; i++) {
				const line = lines[i];

				// =========================
				// class 进入判断
				// =========================
				if (!inClass && /\bclass\s+\w+/i.test(line)) {
					inClass = true;
				}

				// =========================
				// brace depth（用于 class 生命周期）
				// =========================
				for (const ch of line) {
					if (ch === '{') braceDepth++;
					if (ch === '}') braceDepth--;

					if (inClass && braceDepth === 0) {
						inClass = false;
					}
				}

				// =========================
				// 扫描字符串（只在当前行）
				// =========================
				stringRegex.lastIndex = 0;

				let match;
				while ((match = stringRegex.exec(line)) !== null) {
					const value = match[1];
					const matchIndex = offset + match.index;

					/**
					 * runtime context skip
					 */
					if (shouldSkipByContext(content, matchIndex)) {
						continue;
					}

					/**
					 * 🚀 关键新增：排除 callable / array context
					 */
					if (isInArrayContext(line, match.index) || isClassRefContext(line, match.index)) {
						continue;
					}

					/**
					 * class 属性 / const 排除（关键）
					 */
					if (inClass && isClassPropertyLine(line)) {
						continue;
					}

					/**
					 * 自定义过滤
					 */
					if (shouldSkipString(value)) {
						continue;
					}

					/**
					 * 去重
					 */
					if (stringMap.has(value)) {
						continue;
					}

					stringMap.set(value, stringId++);
				}

				offset += line.length + 1;
			}
		}
	}

	// ========================================
	// Replace Strings
	// ========================================

	const classParams = {
		status: 0,
		inString: false,
		stringQuote: null, // 当前字符串类型
		escape: false, // 是否处于转义状态
		symbols: new Array<string>(),
	};

	function handleClassParams(str: string | any[]) {
		for (let i = 0; i < str.length; i++) {
			const ch = str[i];

			// =========================
			// 1. 转义处理（优先级最高）
			// =========================
			if (classParams.escape) {
				classParams.escape = false;
				continue;
			}

			if (ch === '\\') {
				classParams.escape = true;
				continue;
			}

			// =========================
			// 2. 字符串处理
			// =========================
			if (classParams.inString) {
				// 只有同类型引号才退出
				if (ch === classParams.stringQuote) {
					classParams.inString = false;
					classParams.stringQuote = null;
				}
				continue; // 字符串内全部忽略
			}

			// 进入字符串
			if (ch === "'" || ch === '"' || ch === '`') {
				classParams.inString = true;
				classParams.stringQuote = ch;
				continue;
			}

			// =========================
			// 3. 括号栈处理（仅非字符串）
			// =========================
			const last = classParams.symbols.at(-1);

			if (ch === '(') {
				classParams.symbols.push('(');
				continue;
			}

			if (ch === ')' && last === '(') {
				classParams.symbols.pop();
				continue;
			}

			if (ch === '[') {
				classParams.symbols.push('[');
				continue;
			}

			if (ch === ']' && last === '[') {
				classParams.symbols.pop();
				continue;
			}
		}

		return classParams;
	}

	async function replaceStrings() {
		const stringRegex = /'((?:\\.|[^'\\])*)'/g;

		for (const file of phpFiles) {
			logger.log(`🔄 替换字符串: ${path.relative(Runtime.distDir, file)}`);

			const content = fs.readFileSync(file, 'utf8');

			if (content.includes('KA_RUNTIME_START')) {
				continue;
			}

			const lines = content.split('\n');

			let result = [];
			let offset = 0;

			for (let i = 0; i < lines.length; i++) {
				const line = lines[i];

				if (classParams.status === 1) {
					handleClassParams(line);
					if (classParams.symbols.length === 0) {
						classParams.status = 0;
					}
				}

				if (/(private|public|protected)(\sstatic\s)?.*?=/.test(line) || classParams.status === 1) {
					if (!/\sfunction\s\w+/.test(line)) {
						console.log('findClassProperty: ', line);
						classParams.status = 1;
						handleClassParams(line);
					}
				}

				stringRegex.lastIndex = 0;

				let match;
				let newLine = '';
				let lastIndex = 0;

				while ((match = stringRegex.exec(line)) !== null) {
					const value = match[1];

					const matchIndex = match.index;

					/**
					 * 1. context skip（用行级更稳定）
					 */
					if (shouldSkipByContext(content, offset + matchIndex)) {
						continue;
					}

					/**
					 * 2. class / array / callable skip（关键）
					 */
					if (
						isInArrayContext(line, matchIndex) ||
						isClassRefContext(line, matchIndex) ||
						isClassPropertyLine(line)
					) {
						continue;
					}

					/**
					 * 3. 未收录
					 */
					if (!stringMap.has(value)) {
						continue;
					}

					if (classParams.symbols.length > 0) {
						continue;
					}

					const id = stringMap.get(value);

					// 拼接 replace 前文本
					newLine += line.slice(lastIndex, matchIndex);

					// 替换
					newLine += `${runtimeFunctionName}(${id})`;

					lastIndex = matchIndex + match[0].length;
				}

				// 尾部拼接
				newLine += line.slice(lastIndex);

				result.push(newLine);
				offset += line.length + 1;
			}

			fs.writeFileSync(file, result.join('\n'), 'utf8');
		}
	}

	// ========================================
	// Generate Runtime
	// ========================================

	function generateRuntimeCode() {
		const pool: { [key: number]: string } = {};

		for (const [value, id] of stringMap) {
			pool[id] = value;
		}

		const json = JSON.stringify(pool);

		// eslint-disable-next-line no-useless-assignment
		let runtimePoolCode = '';

		if (ENABLE_POOL_COMPRESS) {
			const compressed = zlib.gzipSync(json).toString('base64');

			runtimePoolCode = `
if (!defined('${POOL_NAME}')) {
    define('${POOL_NAME}', gzdecode(base64_decode('${compressed}')));
}
`;
		} else {
			runtimePoolCode = `
if (!defined('${POOL_NAME}')) {
    define('${POOL_NAME}', '${json.replace(/'/g, "\\'")}');
}
`;
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

	// ========================================
	// Inject Runtime
	// ========================================

	async function injectRuntime() {
		logger.log('⚡ 注入 Runtime');

		let content = await fs.readFileSync(path.resolve(Runtime.distDir, Runtime.entryFile), 'utf8');

		/**
		 * 防止重复注入
		 */
		if (content.includes('KA_RUNTIME_START')) {
			return;
		}

		const runtime = generateRuntimeCode();

		const lines = content.split(/\r?\n/);

		const insertIndex = findInsertIndex(lines);

		lines.splice(insertIndex, 0, '', runtime, '');

		content = lines.join('\n');
		await fs.writeFileSync(path.resolve(Runtime.distDir, Runtime.entryFile), content, 'utf8');
	}

	// ========================================
	// Main
	// ========================================

	try {
		console.log('🚀 开始 String Pool Build...\n');

		await scanDirectory(Runtime.distDir);

		console.log(`📦 共扫描 ${phpFiles.length} 个 PHP 文件\n`);

		/**
		 * 收集字符串
		 */
		await collectStrings();

		console.log(`📦 收集到 ${stringMap.size} 个字符串\n`);

		if (ENABLE_STRING_POOL) {
			/**
			 * 替换字符串
			 */
			await replaceStrings();

			/**
			 * 注入 Runtime
			 */
			await injectRuntime();
		}

		console.log('\n🎉 String Pool 完成');

		console.log(`⚡ Runtime Function: ${runtimeFunctionName}`);

		return buildContext;
	} catch (err) {
		console.error('❌ 执行失败:', err);
	}
}
