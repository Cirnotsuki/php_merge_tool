import fs from 'fs';
import path from 'path';
import { uuidv4 } from '@ka-libs/crypto/uuidv4';
import PHPParser from 'php-parser';
import { randomPrefix } from '../utils/randomPrefix';
import { BuildContext, AstNode, AnyAstNode } from '../types';
import { RESERVED } from '../config/constans';
import { isKind } from '../utils/typeGard';
import * as utils from '../utils/utils';
import * as logger from '../utils/logger';
import { Runtime } from '../config/runtime';

const globalVariableRecord = new Map<string, Map<string, Set<AstNode>>>();

export default async function (buildContext: BuildContext) {
	const variableMap = buildContext.variables;
	const PREFIX = () => {
		return randomPrefix();
	};
	const ROOT_DIR = buildContext.distDir;

	const parser = new PHPParser.Engine({
		parser: { php7: true, extractDoc: false },
		ast: { withPositions: true },
	});
	function generateName(name: string) {
		if (variableMap.has(name)) {
			return variableMap.get(name);
		}

		const uuid = uuidv4(true);
		const newName = PREFIX() + uuid.slice(-4);

		variableMap.set(name, newName);

		return newName;
	}

	function getRecord(node: AstNode<PHPParser.Variable>) {
		if (!globalVariableRecord.has(Runtime.currentFile)) {
			globalVariableRecord.set(Runtime.currentFile, new Map());
		}

		const varriableMap = globalVariableRecord.get(Runtime.currentFile)!;

		if (!varriableMap.has(node.name)) {
			varriableMap.set(node.name, new Set());
		}

		return varriableMap.get(node.name)!;
	}

	function setRecord(node: AstNode<PHPParser.Variable>) {
		if (isKind(node, 'variable')) {
			const record = getRecord(node);
			record.add(node);
		}
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

	function isRenamableVariable(node: AstNode) {
		if (!isKind(node, 'variable')) return false;
		if (typeof node.name !== 'string') return false;
		if (RESERVED.has(node.name)) return false;

		const parent = node.parent;
		if (!parent) return false;
		if (['global'].includes(parent.kind)) {
			const record = getRecord(node);
			record.size > 0 ? console.log(Array.from(record)) : console.log('global: ', node.name);
			return false;
		}

		setRecord(node);
		return true;
	}

	function walk(node: AstNode | null, callback: { (node: AstNode): void }) {
		if (!node) {
			return;
		}

		callback(node);

		if (Array.isArray(node)) {
			for (const child of node) {
				walk(child, callback);
			}

			return;
		}

		if (typeof node !== 'object') {
			return;
		}

		const keys = Object.keys(node);

		for (const key of keys) {
			if (key === 'loc' || key === 'parent') {
				continue;
			}

			walk((node as any)[key], callback);
		}
	}

	function applyReplacements(
		source: string,
		replacements: { start: number; end: number; value: string }[],
	) {
		replacements.sort((a: { start: number }, b: { start: number }) => b.start - a.start);
		for (const r of replacements) {
			source = source.slice(0, r.start) + r.value + source.slice(r.end);
		}
		return source;
	}

	await utils.fileIterator(await utils.scanPHPFile(ROOT_DIR), async (file) => {
		let source = fs.readFileSync(file, 'utf-8');
		let ast = null;
		try {
			ast = parser.parseCode(source, file) as AstNode;
		} catch (err) {
			logger.error('❌️ Parse Error:', file, `${err}`);
			return;
		}

		attachParent(ast);

		const visitedOffsets = new Set();
		const replacements: { start: any; end: any; value: any }[] = [];
		let scopeCounter = 0;

		walk(ast, (node: AstNode) => {
			if (
				!isKind(node, 'function') &&
				!isKind(node, 'method') &&
				!isKind(node, 'closure') &&
				!isKind(node, 'arrowfunc')
			) {
				return;
			}

			scopeCounter++;
			const scopeId = scopeCounter;

			walk(node.body, (child: AstNode) => {
				if (!isRenamableVariable(child)) return;
				if (!child.loc) return;
				if (child.loc.start.offset === undefined) return;
				if (child.loc.end.offset === undefined) return;

				const pos = `${child.loc.start.offset}:${child.loc.end.offset}`;
				if (visitedOffsets.has(pos)) return; // 已处理过，跳过
				visitedOffsets.add(pos);

				replacements.push({
					start: child.loc.start.offset,
					end: child.loc.end.offset,
					value: generateName(child.name + ''), // 如果你已经决定全局统一变量名，可以只用 name
				});
			});
		});

		source = applyReplacements(source, replacements);
		fs.writeFileSync(file, source, 'utf8');
	});

	logger.log(`✅️ 变量混淆完成，共 ${variableMap.size} 个变量`);
	return buildContext;
}
