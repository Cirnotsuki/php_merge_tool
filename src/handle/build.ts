import phpMerge from '..';
import fs from 'fs';
import path from 'path';
import { mkdirp } from 'mkdirp';
import { uuidv4 } from '@ka-libs/crypto/uuidv4';

import * as logger from '../utils/logger';

export default async function (
	buildDirs: string[],
	pathes: { source: string; dist: string },
	replace?: { [key: string]: string },
) {
	const opt = {
		date: new Date().toLocaleDateString(),
		time: +new Date(),
		guid: uuidv4(),
		pool: '',
		replace: replace || {},

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
	for (const dir of buildDirs) {
		const source = path.join(pathes.source, dir);
		const dist = path.join(pathes.dist, dir);

		try {
			fs.rmSync(dist, { recursive: true });
		} catch (error) {
			logger.error(error);
		}

		mkdirp.sync(dist);

		await phpMerge(source, dist, opt);
	}

	logger.log(
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
	return opt;
}
