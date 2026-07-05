import phpMerge from '../src';
import fs from 'fs';
import path from 'path';
import { mkdirp } from 'mkdirp';
import { uuidv4 } from '@ka-libs/crypto/uuidv4';

import { BUILD_DIRS, ROOT_PATH } from '../src/config/constans';
import { log } from '../src/utils/logger';

(async () => {
	const opt = {
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
	};
	for (const dir of BUILD_DIRS) {
		const source = path.join(ROOT_PATH.source, dir);
		const dist = path.join(ROOT_PATH.dist, dir);

		try {
			fs.rmSync(dist, { recursive: true });
		} catch (error) {
			console.error(error);
		}

		mkdirp.sync(dist);

		await phpMerge(source, dist, opt);
	}

	log(
		'所有任务均已完成：',
		JSON.parse(
			JSON.stringify(
				opt,
				function (key, value) {
					if (key === 'runtime') {
						return undefined;
					}
					if (value instanceof Map) {
						return value.size;
					}
					return value;
				},
				2,
			),
		),
	);
})();
