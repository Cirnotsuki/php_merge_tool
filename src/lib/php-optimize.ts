import fs from 'fs';
import path from 'path';
import { BuildContext } from '../types';
import * as logger from '../utils/logger';
import * as utils from '../utils/utils';
import { Runtime } from '../config/runtime';
import { EXCLUDES, TARGET_EXTENSION } from '../config/constans';

// ========================================
// PHP Optimize Compiler
// ========================================

export default async function (buildContext: BuildContext) {
	// ========================================
	// Context
	// ========================================

	// ========================================
	// Config
	// ========================================

	// ========================================
	// Utils
	// ========================================

	// ========================================
	// Strip PHP Comments
	// ========================================

	function stripPhpComments(content: string) {
		let result = '';

		let inString = false;

		let stringChar = '';

		let inLineComment = false;

		let inBlockComment = false;

		for (let i = 0; i < content.length; i++) {
			const char = content[i];

			const next = content[i + 1];

			const prev = content[i - 1];

			// ====================================
			// Block Comment
			// ====================================

			if (inBlockComment) {
				if (char === '*' && next === '/') {
					inBlockComment = false;

					i++;
				}

				continue;
			}

			// ====================================
			// Line Comment
			// ====================================

			if (inLineComment) {
				if (char === '\n') {
					inLineComment = false;

					result += '\n';
				}

				continue;
			}

			// ====================================
			// String
			// ====================================

			if (inString) {
				result += char;

				if (char === stringChar && prev !== '\\') {
					inString = false;
				}

				continue;
			}

			// ====================================
			// String Start
			// ====================================

			if (char === '"' || char === "'") {
				inString = true;

				stringChar = char;

				result += char;

				continue;
			}

			// ====================================
			// #
			// ====================================

			if (char === '#') {
				inLineComment = true;

				continue;
			}

			// ====================================
			// //
			// ====================================

			if (char === '/' && next === '/') {
				/**
				 * Avoid URL
				 */
				const recent = result.slice(-10);

				if (recent.endsWith('http:') || recent.endsWith('https:')) {
					result += char;

					continue;
				}

				inLineComment = true;

				i++;

				continue;
			}

			// ====================================
			// /*
			// ====================================

			if (char === '/' && next === '*') {
				/**
				 * Preserve License
				 */
				const ahead = content.substring(i, i + 15);

				if (ahead.includes('@license')) {
					result += char;

					continue;
				}

				inBlockComment = true;

				i++;

				continue;
			}

			result += char;
		}

		return cleanupEmptyLines(result);
	}

	// ========================================
	// Cleanup Empty Lines
	// ========================================

	// ========================================
	// Cleanup Empty Lines
	// ========================================

	function cleanupEmptyLines(content: string) {
		const lines = content.split(/\r?\n/);

		const cleaned = [];

		for (const line of lines) {
			/**
			 * 去除行尾空格
			 */
			const trimmedRight = line.replace(/\s+$/g, '');

			/**
			 * 去除首尾后为空
			 */
			if (!trimmedRight.trim()) {
				continue;
			}

			cleaned.push(trimmedRight);
		}

		/**
		 * 压缩连续空格
		 */
		return cleaned
			.join('\n')
			.replace(/[ \t]+/g, ' ')
			.replace(/\n{2,}/g, '\n')
			.trim();
	}
	// ========================================
	// Process File
	// ========================================

	async function processFile(filePath: string) {
		/**
		 * Skip
		 */
		if (utils.isExcluded(filePath)) {
			return;
		}

		/**
		 * Not PHP
		 */
		if (!utils.isPhpFile(filePath)) {
			return;
		}

		logger.log(`🧹 Optimize: ${path.relative(Runtime.distDir, filePath)}`);

		let content = fs.readFileSync(filePath, 'utf8');

		/**
		 * Remove BOM
		 */
		content = content.replace(/^\uFEFF/, '');

		/**
		 * Strip Comments
		 */
		const cleaned = stripPhpComments(content);

		/**
		 * Save
		 */
		const headText = `<?php
/**
 * Build Date: ${new Date(buildContext.date).toLocaleDateString()}
 * Build Time: ${buildContext.time}
 * Build guid: ${buildContext.guid}
 * Arthur: Kuzuki Azusa <https://github.com/cirnotsuki>
*/
if (!defined('ABSPATH')) {
    header('Location: /');
    exit;
}
?>\n`;

		fs.writeFileSync(filePath, headText + cleaned, 'utf8');
	}

	// ========================================
	// Walk Directory
	// ========================================

	// ========================================
	// Main
	// ========================================

	console.log('🧹 开始清理 PHP 注释...\n');

	try {
		const phpFiles = await utils.scanPHPFile(Runtime.distDir);

		for (const fullPath of phpFiles) {
			/**
			 * Process File
			 */
			await processFile(fullPath);
		}
	} catch (err) {
		console.error(err);
	}

	console.log('\n🎉 PHP Optimize 完成');

	return buildContext;
}
