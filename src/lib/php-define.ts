import fs from 'fs';
import path from 'path';
import uuidv4 from '@ka-libs/crypto/uuidv4';
import logger from '../utils/logger';
import { CONST_PREFIX, EXCLUDE_STRING } from '../config/constans';
import utils  from '../utils/utils';
import { Runtime } from '../core/runtime';
import { BuildContext } from '../core/buildOption';

// ========================================
// PHP Define Symbol Compiler
// ========================================

export default async function (buildContext: BuildContext) {
	// ========================================
	// Context
	// ========================================

	// ========================================
	// Config
	// ========================================

	// ========================================

	/**
	 * Symbol Table
	 */
	const constantMap = buildContext.constants;

	/**
	 * PHP Files
	 */
	const phpFiles: string[] = [];

	// ========================================
	// Logger
	// ========================================

	// ========================================
	// Utils
	// ========================================

	/**
	 * Runtime Skip
	 */
	function isRuntimeFile(content: string | string[]) {
		return content.includes('KA_RUNTIME_START');
	}

	/**
	 * Generate Const Name
	 */
	function generateConstName(original: string) {
		if (constantMap.has(original)) {
			return constantMap.get(original);
		}

		const hash = uuidv4(true);

		const newName = CONST_PREFIX + hash.slice(-6);

		constantMap.set(original, newName);

		return newName;
	}

	/**
	 * Skip System Const
	 */
	function shouldSkipConst(constName: string) {
		return (
			EXCLUDE_STRING.includes(constName) ||
			/**
			 * WP
			 */
			constName.startsWith('WP_') ||
			/**
			 * PHP
			 */
			constName.startsWith('PHP_') ||
			/**
			 * WC
			 */
			constName.startsWith('WC_') ||
			/**
			 * Elementor
			 */
			constName.startsWith('ELEMENTOR_') ||
			/**
			 * Core
			 */
			constName === 'ABSPATH' ||
			constName === 'OBJECT' ||
			constName === 'ARRAY_A' ||
			constName === 'ARRAY_N' ||
			/**
			 * Runtime
			 */
			constName.startsWith('KA_')
		);
	}

	// ========================================
	// Scan Directory
	// ========================================

	// ========================================
	// Collect Constants
	// ========================================

	async function collectConstants() {
		const defineRegex = /define\s*\(\s*['"]([A-Z0-9_]+)['"]/g;
		await utils.fileIterator(phpFiles, async (file) => {
			logger.log(`🔍 扫描常量: ${path.relative(Runtime.distDir, file)}`);

			const content = fs.readFileSync(file, 'utf8');

			/**
			 * Skip Runtime
			 */
			if (isRuntimeFile(content)) {
				return;
			}

			let match;

			while ((match = defineRegex.exec(content)) !== null) {
				const constName = match[1];

				/**
				 * Skip
				 */
				if (shouldSkipConst(constName)) {
					continue;
				}

				generateConstName(constName);
			}
		});
	}

	// ========================================
	// Replace Define
	// ========================================

	function replaceDefineConstants(content: string) {
		for (const [oldName, newName] of constantMap) {
			const regex = new RegExp(`(define\\s*\\(\\s*['"])${oldName}(['"])`, 'g');

			content = content.replace(regex, `$1${newName}$2`);
		}

		return content;
	}

	// ========================================
	// Replace defined()
	// ========================================

	function replaceDefinedCalls(content: string) {
		for (const [oldName, newName] of constantMap) {
			const regex = new RegExp(`(defined\\s*\\(\\s*['"])${oldName}(['"])`, 'g');

			content = content.replace(regex, `$1${newName}$2`);
		}

		return content;
	}

	// ========================================
	// Replace constant()
	// ========================================

	function replaceConstantCalls(content: string) {
		for (const [oldName, newName] of constantMap) {
			const regex = new RegExp(`(constant\\s*\\(\\s*['"])${oldName}(['"])`, 'g');

			content = content.replace(regex, `$1${newName}$2`);
		}

		return content;
	}

	// ========================================
	// Replace Usage
	// ========================================

	function replaceConstUsage(content: string) {
		for (const [oldName, newName] of constantMap) {
			/**
			 * 跳过字符串中的内容
			 */
			const regex = new RegExp(`\\b${oldName}\\b`, 'g');

			content = content.replace(regex, (match: string, offset: number) => {
				/**
				 * 前后字符检测
				 */
				const before = content[offset - 1];

				const after = content[offset + match.length];

				/**
				 * 字符串中跳过
				 */
				if (before === "'" || before === '"' || after === "'" || after === '"') {
					return match;
				}

				return newName;
			});
		}

		return content;
	}

	// ========================================
	// Process Files
	// ========================================

	async function processFiles() {
		await utils.fileIterator(phpFiles, async (file) => {
			logger.log(`🔄 替换常量: ${path.relative(Runtime.distDir, file)}`);

			let content = fs.readFileSync(file, 'utf8');

			/**
			 * Skip Runtime
			 */
			if (isRuntimeFile(content)) {
				return;
			}

			content = replaceDefineConstants(content);

			content = replaceDefinedCalls(content);

			content = replaceConstantCalls(content);

			content = replaceConstUsage(content);

			fs.writeFileSync(file, content, 'utf8');
		});
	}

	// ========================================
	// Main
	// ========================================

	try {
		logger.log('🚀 开始扫描 PHP 常量...\n');

		phpFiles.push(...(await utils.scanPHPFile(Runtime.distDir)));

		logger.log(`📦 共发现 ${phpFiles.length} 个 PHP 文件\n`);

		/**
		 * 收集
		 */
		await collectConstants();

		logger.log(`📦 共发现 ${constantMap.size} 个常量\n`);

		/**
		 * Debug
		 */
		for (const [oldName, newName] of constantMap) {
			logger.log(`🔄 ${oldName} -> ${newName}`);
		}

		logger.log('\n🚀 开始替换常量...\n');

		/**
		 * 替换
		 */
		await processFiles();

		logger.log('\n🎉 常量替换完成');

		return buildContext;
	} catch (err) {
		logger.error('❌ 执行失败:', err);
	}
}
