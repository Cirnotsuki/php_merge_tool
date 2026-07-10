import fs from 'fs';
import path from 'path';
import { uuidv4 } from '@ka-libs/crypto/uuidv4';
import PHPParser from 'php-parser';
import { randomPrefix } from '../utils/randomPrefix';
import { BuildContext, AstNode, AnyAstNode } from '../types';
import { RESERVED } from '../config/constans';
import { isKind } from '../utils/typeGard';
import * as logger from '../utils/logger';
// 扩展 BuildContext，增加函数名映射
// interface BuildContext {
//   variables: Map<string, string>;
//   functions: Map<string, string>; // 新增
//   distDir: string;
// }

export default async function (buildContext: BuildContext) {
	const functionMap = buildContext.functions; // 假设你在 buildContext 中初始化了它
	const PREFIX = () => randomPrefix();
	const ROOT_DIR = buildContext.distDir;

	const parser = new PHPParser.Engine({
		parser: { php7: true, extractDoc: false },
		ast: { withPositions: true },
	});

	const phpFiles: string[] = [];

	async function scan(dir: string) {
		const entries = fs.readdirSync(dir, { withFileTypes: true });
		for (const entry of entries) {
			const full = path.join(dir, entry.name);
			if (entry.isDirectory()) scan(full);
			if (full.endsWith('.php')) phpFiles.push(full);
		}
	}

	// 生成混淆函数名，如果已存在则直接返回
	function generateFunctionName(name: string) {
		if (functionMap.has(name)) {
			return functionMap.get(name);
		}

		const uuid = uuidv4(true);
		const newName = PREFIX() + uuid.slice(-4);
		functionMap.set(name, newName);
		return newName;
	}

	function attachParent(node: AstNode<PHPParser.Node>, parent?: AstNode<AnyAstNode> | null) {
		if (!node || typeof node !== 'object') return;
		node.parent = parent;
		for (const key in node) {
			if (key === 'parent' || key === 'loc') continue;
			const value = (node as any)[key];
			if (Array.isArray(value)) value.forEach((v) => attachParent(v, node));
			else attachParent(value, node);
		}
	}

	function walk(node: AstNode | null, callback: { (node: AstNode): void }) {
		if (!node) return;
		callback(node);
		if (Array.isArray(node)) {
			for (const child of node) walk(child, callback);
			return;
		}
		if (typeof node !== 'object') return;
		const keys = Object.keys(node);
		for (const key of keys) {
			if (key === 'loc' || key === 'parent') continue;
			walk((node as any)[key], callback);
		}
	}

	function applyReplacements(source: string, replacements: any[]) {
		replacements.sort((a: { start: number }, b: { start: number }) => b.start - a.start);
		for (const r of replacements) {
			source = source.slice(0, r.start) + r.value + source.slice(r.end);
		}
		return source;
	}

	await scan(ROOT_DIR);

	for (const file of phpFiles) {
		let source = fs.readFileSync(file, 'utf-8');
		let ast = null;
		try {
			ast = parser.parseCode(source, file) as AstNode;
		} catch (err) {
			logger.error('Parse Error:', file, `${err}`);
			continue;
		}

		attachParent(ast);

		const visitedOffsets = new Set<string>();
		const replacements: { start: number; end: number; value: string }[] = [];

		walk(ast, (node: AstNode) => {
			// 1. 处理函数/方法/闭包的【定义】
			if (
				isKind(node, 'function') ||
				isKind(node, 'method') ||
				isKind(node, 'closure') ||
				isKind(node, 'arrowfunc')
			) {
				// 只有命名函数/方法才需要混淆定义，匿名函数不需要
				if (node.name && typeof node.name === 'string') {
					// 排除魔术方法
					if (node.name.startsWith('__')) return;
					if (!node.loc) return;

					const newName = generateFunctionName(node.name) || '';
					// 替换定义处的函数名
					// 注意：php-parser 的 loc 通常精准覆盖函数名
					const pos = `${node.loc.start.offset}:${node.loc.end.offset}`;
					if (!visitedOffsets.has(pos)) {
						visitedOffsets.add(pos);
						replacements.push({
							start: node.loc.start.offset,
							end: node.loc.end.offset,
							value: newName,
						});
					}
				}
			}

			// 2. 处理函数/方法的【调用】
			if (isKind(node, 'call')) {
				// node.what 是被调用的对象，如果是普通函数调用，what 通常是 Identifier 或 Name
				if (isKind(node.what, 'name') || isKind(node.what, 'identifier')) {
					const funcName = node.what.name;
					if (typeof funcName !== 'string') return;

					// 排除 PHP 内置函数、保留字
					if (RESERVED.has(funcName)) return;

					// 如果这个函数名在我们的映射表里（说明它被定义过且需要混淆），则替换调用处
					if (functionMap.has(funcName)) {
						if (!node.what.loc) return;
						const newName = functionMap.get(funcName)!;
						const pos = `${node.what.loc.start.offset}:${node.what.loc.end.offset}`;
						if (!visitedOffsets.has(pos)) {
							visitedOffsets.add(pos);
							replacements.push({
								start: node.what.loc.start.offset,
								end: node.what.loc.end.offset,
								value: newName,
							});
						}
					}
				}
			}
		});

		source = applyReplacements(source, replacements);
		fs.writeFileSync(file, source, 'utf8');
	}

	logger.log(`函数名混淆完成，共 ${functionMap.size} 个函数`);
	return buildContext;
}
