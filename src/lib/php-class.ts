import { uuidv4 } from '@ka-libs/crypto';
import { Runtime } from '../core/runtime';
import { Ast } from '../core/ast';
import { BuildClass, BuildContext } from '../core/buildOption';
import { RecordVariable, RecordFunction, RecordNode } from '../core/recordNode';
import { isKind } from '../utils/typeGard';
import { fileIterator, scanPHPFile } from '../utils/utils';
import { CONST_PREFIX } from '../config/constans';
import { AstNode } from '../types';
import PhpParser from 'php-parser';
import { randomPrefix } from '../utils/randomPrefix';

export default async function (buildContext: BuildContext) {
	const ROOT_DIR = buildContext.distDir;
	const classes = buildContext.classes;

	function generateClassName() {
		const hash = uuidv4(true);
		return CONST_PREFIX + hash.slice(-6);
	}

	function generateName() {
		const hash = uuidv4(true);
		return randomPrefix().slice(1, 3) + hash.slice(-6);
	}

	function findBuildClass(name: string | AstNode) {
		if (!name) return;

		if (typeof name === 'string') {
			return classes.get(name);
		}

		return classes.get(name.name);
	}

	function findSelfBuildClass(node: AstNode) {
		let parent = node.parent;

		while (parent) {
			if (isKind(parent, 'class')) {
				break;
			}
			parent = parent.parent;
		}

		if (parent) {
			try {
				return findBuildClass(parent.name);
			} catch (error) {
				console.error(error);
				parent.trace();
			}
		}
	}

	await fileIterator(await scanPHPFile(ROOT_DIR), async (file) => {
		if (!Runtime.currentFile.includes('test.php')) return;

		const ast = Ast.create(file);
		console.log(ast);

		// 记录所有类
		ast.walk((node) => {
			if (!isKind(node, 'identifier') && !isKind(node, 'variable')) return;
			if (isKind(node.parent, 'class')) {
				if (!node.name) return;

				const classRecord = new RecordNode(node, generateClassName());
				classes.set(node.name, new BuildClass(classRecord));
				return;
			}

			if (isKind(node.parent, 'method')) {
				const buildClass = findSelfBuildClass(node);
				if (!buildClass) return;

				const methodRecord = new RecordFunction(node, generateName());
				buildClass.methods.set(node.name, methodRecord);
				return;
			}

			if (isKind(node.parent, 'property')) {
				const buildClass = findSelfBuildClass(node);
				if (!buildClass) return;

				const propertyRecord = new RecordVariable(node, generateName());
				buildClass.properties.set(node.name, propertyRecord);
				return;
			}
		});

		// 当一个类extends了一个未定义的类时，删除记录该类
		ast.walk((node) => {
			if (!isKind(node, 'identifier') && !isKind(node, 'variable')) return;
			if (isKind(node.parent, 'class')) {
				if (node.parent.extends && !classes.has(node.parent.extends.name)) {
					classes.delete(node.name);
				}
			}
		});

		// 追踪类引用
		ast.walk((node) => {
			if (isKind(node, 'new')) {
				// node.trace();
			}
		});

		// ast.applyReplacements();
	});

	// 记录替换
	await fileIterator(await scanPHPFile(ROOT_DIR), async (file) => {
		if (!Runtime.currentFile.includes('test.php')) return;

		const ast = Ast.create(file);

		ast.walk((node) => {
			if (isKind(node, 'name')) {
				const buildClass = findBuildClass(node.name);
				if (!buildClass) return;

				ast.recordReplacement(node, buildClass.name.replace);
				return;
			}

			// 处理变量
			if (!isKind(node, 'identifier') && !isKind(node, 'variable')) return;
			if (node.record && node.record instanceof RecordVariable) {
				console.log(node.record.type);
			}
			if (isKind(node.parent, 'class')) {
				const buildClass = findBuildClass(node.name);
				if (!buildClass) return;

				ast.recordReplacement(node, buildClass.name.replace);
			}

			if (isKind(node.parent, 'method')) {
				const buildClass = findSelfBuildClass(node);
				if (!buildClass) return;

				const record = buildClass.methods.get(node.name);
				if (!record) return;

				ast.recordReplacement(node, record.replace);

				return;
			}

			if (isKind(node.parent, 'property')) {
				const buildClass = findSelfBuildClass(node);
				if (!buildClass) return;

				const record = buildClass.properties.get(node.name);
				if (!record) return;

				ast.recordReplacement(node, '$' + record.replace);
				return;
			}

			if (!Runtime.currentFile.includes('test.php')) return;

			if (isKind(node.parent, 'staticlookup')) {
				const name = node.parent.what;

				let buildClass;
				if (isKind(name, 'name')) {
					buildClass = findBuildClass(name);
				}

				if (isKind(name, 'selfreference')) {
					buildClass = findSelfBuildClass(name);
				}

				if (!buildClass) return;

				const record = buildClass.properties.get(node.name);
				if (!record) return;

				ast.recordReplacement(node, '$' + record.replace);
				return;
			}

			if (isKind(node.parent, 'propertylookup')) {
				// node.trace();
			}
		});
	});
}
