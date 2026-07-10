import fs from 'fs';
import fsPromises from 'fs/promises';
import path from 'path';
import { mkdirp } from 'mkdirp';
import { BuildContext } from '../types';
import * as logger from '../utils/logger';
import * as utils from '../utils/utils';
import { Runtime } from '../config/runtime';
import { REQUIRE_TYPES } from '../config/constans';

// ========================================
// PHP Merge Compiler
// ========================================

export default async function (buildContext: BuildContext) {
	// ========================================
	// Context
	// ========================================

	logger.log('>> MERGE Entry File', Runtime.entryFile);

	// ========================================
	// Config
	// ========================================

	// ========================================

	const processedFiles = new Set();

	const fileCache = new Map();

	// ========================================
	// Utils
	// ========================================

	function readFileCached(filePath: string) {
		if (fileCache.has(filePath)) {
			return fileCache.get(filePath);
		}

		let content = fs.readFileSync(filePath, 'utf8');

		/**
		 * Remove BOM
		 */
		content = content.replace(/^\uFEFF/, '');

		fileCache.set(filePath, content);

		return content;
	}

	// ========================================
	// PHP Inline Comment Strip
	// ========================================

	function stripPhpComment(line: string | any[]) {
		let result = '';

		let inString = false;

		let stringChar = '';

		for (let i = 0; i < line.length; i++) {
			const char = line[i];

			const next = line[i + 1];

			/**
			 * String Start
			 */
			if (!inString) {
				if (char === '"' || char === "'") {
					inString = true;

					stringChar = char;

					result += char;

					continue;
				}

				/**
				 * //
				 */
				if (char === '/' && next === '/') {
					break;
				}

				/**
				 * #
				 */
				if (char === '#') {
					break;
				}

				result += char;

				continue;
			}

			result += char;

			/**
			 * String End
			 */
			if (char === stringChar && line[i - 1] !== '\\') {
				inString = false;
			}
		}

		return result;
	}

	// ========================================
	// Parse Variables
	// ========================================

	function parseVariables(lines: any, currentDir: string) {
		const variables: {
			[key: string]: string;
		} = {};

		for (const rawLine of lines) {
			const line = stripPhpComment(rawLine).trim();

			/**
			 * Skip
			 */
			if (!line.startsWith('$')) {
				continue;
			}

			const match = line.match(/^\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*(.+?)\s*;$/);

			if (!match) {
				continue;
			}

			const [, name, value] = match;

			let resolved = value;

			/**
			 * __DIR__
			 */
			resolved = resolved.replace(/__DIR__/g, `'${currentDir}'`);

			/**
			 * get_stylesheet_directory()
			 */
			resolved = resolved.replace(/get_stylesheet_directory\(\)/g, `'${Runtime.sourceDir}'`);

			variables[name] = resolved;

			// logger.log(`📦 Variable: $${name}`);
		}

		return variables;
	}

	// ========================================
	// Extract Require
	// ========================================

	function extractRequire(line: any) {
		const cleanLine = stripPhpComment(line);
		let inString = false;

		let stringChar = '';

		for (let i = 0; i < cleanLine.length; i++) {
			const char = cleanLine[i];

			/**
			 * Outside String
			 */
			if (!inString) {
				/**
				 * Enter String
				 */
				if (char === '"' || char === "'") {
					inString = true;

					stringChar = char;

					continue;
				}

				/**
				 * require
				 */
				for (const type of REQUIRE_TYPES) {
					const next = cleanLine[i + type.length];

					if (cleanLine.startsWith(type, i) && (next === undefined || /\s|\(/.test(next))) {
						if (new RegExp(type + '\\s+dirname\\(').test(cleanLine)) continue;

						return parseRequireExpression(cleanLine, i, type);
					}
				}

				continue;
			}

			/**
			 * Leave String
			 */
			if (char === stringChar && cleanLine[i - 1] !== '\\') {
				inString = false;
			}
		}

		return null;
	}

	// ========================================
	// Parse Require Expression
	// ========================================

	function parseRequireExpression(line: string | any[], start: number, type: string | any[]) {
		let i = start + type.length;

		/**
		 * Skip Space
		 */
		while (/\s/.test(line[i])) {
			i++;
		}

		let expression = '';

		let inString = false;

		let stringChar = '';

		let depth = 0;

		/**
		 * (
		 */
		if (line[i] === '(') {
			depth = 1;

			i++;
		}

		for (; i < line.length; i++) {
			const char = line[i];

			/**
			 * Outside String
			 */
			if (!inString) {
				if (char === '"' || char === "'") {
					inString = true;

					stringChar = char;

					expression += char;

					continue;
				}

				/**
				 * (
				 */
				if (char === '(') {
					depth++;
				} else if (char === ')') {
					/**
					 * )
					 */
					depth--;

					if (depth <= 0) {
						break;
					}
				} else if (char === ';' && depth === 0) {
					/**
					 * ;
					 */
					break;
				}

				expression += char;

				continue;
			}

			expression += char;

			/**
			 * End String
			 */
			if (char === stringChar && line[i - 1] !== '\\') {
				inString = false;
			}
		}

		return {
			type,
			expression: expression.trim(),
		};
	}

	// ========================================
	// Resolve Require Path
	// ========================================

	function resolveRequirePath(
		expression: string,
		currentDir: string,
		variables: { [s: string]: string },
	) {
		let expr = expression;

		/**
		 * __DIR__
		 */
		expr = expr.replace(/__DIR__/g, `'${currentDir}'`);

		/**
		 * get_stylesheet_directory()
		 */
		expr = expr.replace(/get_stylesheet_directory\(\)/g, `'${Runtime.sourceDir}'`);

		/**
		 * Variables
		 */
		const sortedVariables = Object.entries(variables).sort((a, b) => b[0].length - a[0].length);

		for (const [name, value] of sortedVariables) {
			expr = expr.replace(new RegExp(`\\$${name}\\b`, 'g'), value);
		}

		/**
		 * Extract String
		 */
		const segments = [];

		const regex = /['"]([^'"]+)['"]/g;

		let match;

		while ((match = regex.exec(expr)) !== null) {
			segments.push(match[1]);
		}

		if (!segments.length) {
			return null;
		}

		let finalPath = segments.join('');

		/**
		 * Relative
		 */
		if (!path.isAbsolute(finalPath)) {
			finalPath = path.resolve(currentDir, finalPath);
		}

		return path.normalize(finalPath);
	}

	// ========================================
	// Process PHP File
	// ========================================

	async function processPhpFile(filePath: string): Promise<{
		content: string;
		status: 'skipped' | 'excluded' | 'notFound' | 'fulfill';
	}> {
		filePath = path.normalize(filePath);

		/**
		 * Skip
		 */
		if (processedFiles.has(filePath)) {
			logger.log(`⏭️ Already Processed: ${filePath}`);

			return {
				content: '',
				status: 'skipped',
			};
		}

		/**
		 * Excluded
		 */
		if (utils.isExcluded(filePath)) {
			return {
				content: '',
				status: 'excluded',
			};
		}

		/**
		 * Exists
		 */
		if (!(await utils.exists(filePath))) {
			logger.warn(`⚠️ File Not Found: ${filePath}`);

			return {
				content: '',
				status: 'notFound',
			};
		}

		processedFiles.add(filePath);

		logger.log(`📄 Processing: ${path.relative(Runtime.sourceDir, filePath)}`);

		const currentDir = path.dirname(filePath);

		const content = await readFileCached(filePath);

		const lines = content.split(/\r?\n/);

		/**
		 * Variables
		 */
		const variables = parseVariables(lines, currentDir);

		const result = [];

		for (const line of lines) {
			const requireInfo = extractRequire(line);

			/**
			 * Normal Line
			 */
			if (!requireInfo) {
				result.push(line);

				continue;
			}

			logger.log(`🔗 Dependency: ${requireInfo.expression}\n`);

			/**
			 * Resolve
			 */
			const dependencyPath = resolveRequirePath(requireInfo.expression, currentDir, variables);

			/**
			 * Exists
			 */
			if (dependencyPath && (await utils.exists(dependencyPath))) {
				const child = await processPhpFile(dependencyPath);

				/**
				 * Valid
				 */
				if (child.status === 'fulfill' && child.content) {
					/**
					 * Remove PHP Tag
					 */
					const cleanContent = child.content

						.replace(/^\s*<\?php(\s|[\r\n])*/i, '')

						.replace(/\s*\?>\s*$/i, '');

					result.push(
						`// ===== Merge From: ${path.relative(Runtime.sourceDir, dependencyPath)} =====`,
					);

					result.push(cleanContent);

					result.push('// ===== Merge End =====');

					continue;
				}
			}

			/**
			 * Keep Original
			 */
			result.push(line);
		}

		return {
			content: result.join('\n'),
			status: 'fulfill',
		};
	}

	// ========================================
	// Copy Remaining Files
	// ========================================

	async function copyRemainingFiles(dir: string) {
		const files = await utils.scanDirectory(dir);

		await utils.fileIterator(files, async (file) => {
			/**
			 * Skip Merged
			 */
			if (processedFiles.has(file)) {
				return;
			}

			const relativePath = path.relative(Runtime.sourceDir, file);

			const outputPath = path.join(Runtime.distDir, relativePath);

			/**
			 * Ensure Dir
			 */
			await mkdirp(path.dirname(outputPath));

			/**
			 * Copy
			 */
			await fsPromises.copyFile(file, outputPath);

			logger.log(`📄 复制文件: ${relativePath}`);
		});
	}

	// ========================================
	// Main
	// ========================================

	try {
		const entryPath = path.resolve(Runtime.sourceDir, Runtime.entryFile);

		/**
		 * Root Exists
		 */
		if (!utils.exists(Runtime.sourceDir)) {
			throw new Error(`Project Directory Not Found: ${Runtime.sourceDir}`);
		}

		/**
		 * Entry Exists
		 */
		if (!utils.exists(entryPath)) {
			throw new Error(`Entry File Not Found: ${entryPath}`);
		}

		/**
		 * Clean Dist
		 */
		if (utils.exists(Runtime.distDir)) {
			fs.rmSync(Runtime.distDir, {
				recursive: true,
				force: true,
			});
		}

		/**
		 * Create Dist
		 */
		mkdirp.sync(Runtime.distDir);

		logger.log(`🚀 开始合并 PHP 文件: ${entryPath}\n`);

		/**
		 * Merge
		 */
		const { content } = await processPhpFile(entryPath);

		const outputEntry = path.join(Runtime.distDir, Runtime.entryFile);

		/**
		 * Save
		 */
		fs.writeFileSync(outputEntry, content, 'utf8');

		logger.log('\n📦 复制未处理文件...\n');

		/**
		 * Copy Assets
		 */
		await copyRemainingFiles(Runtime.sourceDir);

		logger.log('\n🎉 合并完成');

		logger.log(`📄 合并文件数: ${processedFiles.size}`);

		return buildContext;
	} catch (err) {
		logger.error(err);
	}
}
