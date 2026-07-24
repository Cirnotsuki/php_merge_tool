import { encrypt, base64ToArrayBuffer } from '@ka-libs/crypto';
import path from 'path';
import fs from 'fs';
import { PUBLIC_KEY, STRING_OPT } from '../config/constans';
import { Runtime } from '../core/runtime';
import { AstNode } from '../types';
import { isKind } from './typeGard';
import type PhpParser from 'php-parser';
import logger from './logger';
import { fileURLToPath } from 'url';
import { BuildContext } from '../core/buildOption';
const __filename = fileURLToPath(import.meta.url);

const { ENABLE_STRING_POOL, ENABLE_POOL_COMPRESS, MIN_STRING_LENGTH } = STRING_OPT;

export async function generateRuntimeCode(buildContext: BuildContext): Promise<string> {
	const POOL_NAME = buildContext.pool;
	const runtimeFunctionName = buildContext.runtime.stringPoolFunction;

	const pool: Record<number, string> = {};
	for (const [value, id] of buildContext.strings) {
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

export function findInsertIndex(lines: string[]): number {
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

export async function injectRuntimeCode(buildContext: BuildContext) {
	const entryPath = path.resolve(Runtime.distDir, Runtime.entryFile); // 根据实际入口调整

	let content = fs.readFileSync(entryPath, 'utf8');
	if (content.includes('KA_RUNTIME_START')) return;

	try {
		const runtime = await generateRuntimeCode(buildContext);
		const lines = content.split(/\r?\n/);
		const insertIndex = findInsertIndex(lines);

		lines.splice(insertIndex, 0, '', runtime, '');
		fs.writeFileSync(entryPath, lines.join('\n'), 'utf8');
	} catch (err: any) {
		logger.error(`❌ Runtime 注入失败: ${__filename}:${err.lineNumber}:${err.columnNumber}`);
	}
}

/**
 * 从 AST 节点递归提取纯字符串值
 * - 普通字符串字面量：直接返回
 * - Encapsed / Heredoc / Nowdoc：递归拼接所有子片段
 * - 包含变量插值或无法识别的节点：返回 null（不参与字符串池）
 */
export function extractStringValue(node: AstNode | null | undefined): string | null {
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
export function getPartContentInEncapsedRaw(
	encapsedRaw: string,
	part: AstNode<PhpParser.EncapsedPart>,
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

export * as default from './stringUtil';
