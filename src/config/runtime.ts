import path from 'path';
import fs from 'fs';
import { ENTRIES } from './constans';
import * as logger from '../utils/logger';

export class Runtime {
	private static _sourceDir = '';
	private static _distDir = '';
	private static _entryFile = '';
	public static period:
		| 'init'
		| 'merge'
		| 'variable'
		| 'define'
		| 'function'
		| 'hooks'
		| 'string'
		| 'optimize'
		| string = 'init';

	static get sourceDir() {
		return this._sourceDir;
	}

	static set sourceDir(v) {
		this._entryFile = '';
		for (const phpFile of ENTRIES) {
			if (fs.existsSync(path.resolve(v, phpFile))) {
				this._entryFile = phpFile;
			}
		}

		this._sourceDir = v;
	}

	static get distDir() {
		return this._distDir;
	}

	static set distDir(v) {
		this._distDir = v;
	}

	static get entryFile() {
		return this._entryFile;
	}
}
