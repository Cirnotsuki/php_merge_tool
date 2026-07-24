import path from 'path';
import fs from 'fs';
import fsPromises from 'fs/promises';
import { toCamelCase } from '@ka-libs/utils/helper';
import { EXCLUDES, EXTERNAL, RESERGVED } from '../config/constans';
import { Runtime } from '../core/runtime';
import { getRandomBytes } from '@ka-libs/crypto/get-random-bytes';
import type PHPParser from 'php-parser';

export function normalizePath(p: string) {
	return path.normalize(p).replace(/\\/g, '/');
}

export function getFileOption(filePath: string) {
	const opt: {
		[key: string]: string[] | undefined;

		excluded?: string[];
	} = {};
	if (!isPhpFile(filePath)) return opt;
	const fileData = fs.readFileSync(filePath, 'utf-8');
	let findStr = '';
	let i = 0;
	for (; i < 30; i += 1) {
		findStr += fileData[i];
		if (findStr.endsWith('*/')) break;
	}

	if (!findStr.includes('/*')) return opt;

	while (!findStr.endsWith('*/')) {
		findStr += fileData[i];
		i += 1;
	}

	findStr = findStr
		.replace(/<\?php/, '')
		.replace('/*', '')
		.replace('*/', '')
		.replace(/\n/g, '')
		.replace(/\s+/g, ' ')
		.trim();

	for (const str of findStr.split(' ')) {
		const [name, value = ''] = str.split('=');
		opt[toCamelCase(name)] = value.split(',').filter((v) => v !== '');
	}
	return opt;
}

export function isExcluded(filePath: string) {
	const rootDir = filePath.includes(Runtime.sourceDir) ? Runtime.sourceDir : Runtime.distDir;
	const relative = normalizePath(path.relative(rootDir, filePath));

	const { excluded = [] } = getFileOption(path.resolve(Runtime.sourceDir, relative));
	if (excluded.includes(Runtime.period)) return true;

	return EXCLUDES.some((ex) => {
		ex = normalizePath(ex);

		return relative === ex || relative.startsWith(ex + '/');
	});
}
export function getRelativeFilePath(filePath: string, prefix: string = '') {
	const rootDir = filePath.includes(Runtime.sourceDir) ? Runtime.sourceDir : Runtime.distDir;
	return normalizePath(path.relative(rootDir, prefix + filePath));
}
export function isReserved(filePath: string) {
	const rootDir = filePath.includes(Runtime.sourceDir) ? Runtime.sourceDir : Runtime.distDir;
	const relative = normalizePath(path.relative(rootDir, filePath));

	return RESERGVED.some((ex) => {
		ex = normalizePath(ex);

		return relative === ex || relative.startsWith(ex + '/');
	});
}

export function isPhpFile(filePath: string) {
	return path.extname(filePath).toLowerCase() === '.php';
}

export function exists(filePath: string) {
	try {
		return fs.existsSync(filePath);
	} catch (error) {
		return false;
	}
}

export function randomNumber(size: number): number {
	const bytes = getRandomBytes(4);

	switch (size) {
		case 1:
			return bytes[0];

		case 2:
			return (bytes[0] << 8) | bytes[1];

		case 3:
			return (bytes[0] << 16) | (bytes[1] << 8) | bytes[2];

		default:
			return ((bytes[0] << 24) | (bytes[1] << 16) | (bytes[2] << 8) | bytes[3]) >>> 0;
	}
}

export async function scanDirectory(source: string) {
	const files: string[] = [];

	function scan(dir: string) {
		const entries = fs.readdirSync(dir, {
			withFileTypes: true,
		});
		for (const entry of entries) {
			const fullPath = path.join(dir, entry.name);
			if (isExcluded(fullPath)) {
				continue;
			}

			if (entry.isDirectory()) {
				scan(fullPath);
				continue;
			}

			files.push(fullPath);
		}
	}

	scan(source);

	return files;
}

export async function scanPHPFile(source: string) {
	const files = await scanDirectory(source);

	return files.filter((fullPath) => {
		if (!isPhpFile(fullPath)) return false;
		if (isReserved(fullPath)) return false;
		return true;
	});
}

export async function fileIterator(files: string[], callback: (file: string) => void) {
	for (const file of files) {
		Runtime.currentFile = file;
		await callback(file);
	}
}

export function getRaw(loc?: PHPParser.Location | null, highlightLoc?: PHPParser.Location) {
	if (!loc) return '';

	const fileData = fs.readFileSync(Runtime.currentFile, 'utf8');

	let start = loc.start.offset ?? 0;
	let end = loc.end.offset ?? 0;

	if (!highlightLoc) {
		return fileData.slice(start, end);
	}

	while (start > 0 && fileData[start - 1] !== '\n') {
		start--;
	}

	while (end < fileData.length && fileData[end] !== '\n') {
		end++;
	}

	const raw = fileData.slice(start, end);

	const highlightStart = (highlightLoc.start.offset ?? 0) - start;

	const highlightEnd = (highlightLoc.end.offset ?? 0) - start;

	return (
		raw.slice(0, highlightStart) +
		'【 ' +
		raw.slice(highlightStart, highlightEnd) +
		' 】' +
		raw.slice(highlightEnd)
	);
}

export * as default from './utils';
