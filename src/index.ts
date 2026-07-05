import fs from 'fs';
import path from 'path';
import { MAP_DIR } from './config/constans';

import phpMerge from './lib/php-merge';
import phpHooks from './lib/php-hooks';
import phpOptimize from './lib/php-optimize';
import phpDefine from './lib/php-define';
import phpString from './lib/php-string';
import phpStringAst from './lib/php-string-ast';
import phpFunction from './lib/php-function';

import phpVariable from './lib/php-variable';
import { uuidv4 } from '@ka-libs/crypto/uuidv4';
import { mkdirp } from 'mkdirp';
import { BuildContext, BuildOption } from './types/index';
import * as utils from './utils/utils';
import { Runtime } from './config/runtime';
import { log } from './utils/logger';

export default async function (entryDir: string, distDir: string, options: BuildOption) {
	const buildContext: BuildContext = {
		entryDir,
		distDir,

		date: new Date().toLocaleDateString(),
		time: +new Date(),
		guid: uuidv4(),
		pool: '',

		constants: new Map(),
		functions: new Map(),
		hooks: new Map(),
		classes: new Map(),
		strings: new Map(),
		variables: new Map(),

		runtime: {
			stringPoolFunction: null,
		},

		...options,
	};

	// set root
	Runtime.distDir = buildContext.distDir;
	Runtime.sourceDir = buildContext.entryDir;

	try {
		fs.rmSync(buildContext.distDir, { recursive: true, force: true });
	} finally {
		Runtime.period = 'merge';
		await phpMerge(buildContext);

		Runtime.period = 'variable';
		await phpVariable(buildContext);

		Runtime.period = 'function';
		// await phpFunction(buildContext);

		Runtime.period = 'hooks';
		//   await phpHooks(buildContext);

		Runtime.period = 'define';
		await phpDefine(buildContext);

		Runtime.period = 'string';
		// await phpString(buildContext);
		await phpStringAst(buildContext);

		Runtime.period = 'optimize';
		await phpOptimize(buildContext);

		const jsonDir = path.join(MAP_DIR, new Date(buildContext.time).toLocaleDateString());

		mkdirp.sync(jsonDir);

		fs.writeFileSync(
			path.join(jsonDir, buildContext.guid + '.json'),
			JSON.stringify(
				buildContext,
				function (key, value) {
					if (key === 'entryDir') {
						return undefined;
					}
					if (key === 'distDir') {
						return undefined;
					}
					if (key === 'runtime') {
						return undefined;
					}
					if (value instanceof Map) {
						return Array.from(value, ([key, value]) => ({ key, value }));
					}
					return value;
				},
				2,
			),
		);
	}
}
