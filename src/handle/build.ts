import phpMerge from '..';
import fs from 'fs';
import path from 'path';
import { mkdirp } from 'mkdirp';
import { uuidv4 } from '@ka-libs/crypto/uuidv4';

import logger from '../utils/logger';
import { Runtime } from '../core/runtime';
import { MAP_DIR } from '../config/constans';
import { BuildOption } from '../core/buildOption';
import { normalizePath } from '../utils/utils';
import { RecordBase, RecordNode } from '../core/recordNode';

export default async function (
	buildDirs: string[],
	pathes: { source: string; dist: string },
	replace?: { [key: string]: string },
) {
	const buildOption = new BuildOption({
		replace,
	});

	Runtime.distRoot = pathes.dist;
	Runtime.sourceRoot = pathes.source;
	Runtime.options = buildOption;

	for (const dir of buildDirs) {
		const source = path.join(pathes.source, dir);
		const dist = path.join(pathes.dist, dir);

		try {
			fs.rmSync(dist, { recursive: true });
		} catch (error) {
			logger.error(error);
		}

		mkdirp.sync(dist);

		await phpMerge(source, dist, buildOption);
	}

	const jsonDir = path.join(MAP_DIR, new Date(buildOption.time).toLocaleDateString());

	mkdirp.sync(jsonDir);

	const json = JSON.stringify(
		buildOption,
		function (key, value) {
			if (key === 'entryDir') {
				return undefined;
			}
			if (key === 'distDir') {
				return normalizePath(path.relative(Runtime.distRoot, value));
			}
			if (key === 'runtime') {
				return undefined;
			}
			if (key === 'options') {
				return undefined;
			}
			if (key === 'classes' && value instanceof Map) {
				return Array.from(value, ([className, value]) => ({
					className,
					...value,
				}));
			}
			if (value instanceof Map) {
				if (value.size === 0) {
					return undefined;
				}
				return Array.from(value, ([key, value]) => ({ key, value }));
			}

			if (value instanceof RecordBase) {
				return {
					name: value.node.name,
					value: value.replace,
					location: normalizePath(path.relative(Runtime.distRoot, value.location)),
				};
			}
			if (Array.isArray(value) && value.length === 0) {
				return undefined;
			}

			if (typeof value === 'object' && value && Object.keys(value).length === 0) {
				return undefined;
			}

			return value;
		},
		2,
	);

	fs.writeFileSync(path.join(jsonDir, buildOption.guid + '.json'), json);

	Runtime.DEBUG = true;

	logger.log(
		'所有任务均已完成：',
		JSON.parse(
			JSON.stringify(
				JSON.parse(json),
				function (key, value) {
					if (key === 'contexts' || key === 'classes') return value;
					if (value instanceof Array) {
						return value.length;
					}
					return value;
				},
				2,
			),
		),
	);
}
