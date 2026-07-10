import path from 'path';
import config from '../config';
import build from '../src/handle/build';
import copy from '../src/handle/copy';
import pack from '../src/handle/pack';
import libServe from './lib.serve';
(async () => {
	const pathes = { ...config.pathes, dist: path.resolve(config.rootDir, './dist') };
	await build(config.buildDirs, pathes, config.replace);
	await copy(config.copyFiles, pathes);
	libServe({ ...config, pathes });
	pack(
		new Date().toLocaleDateString().replace(/\D/g, '') +
			new Date().toLocaleTimeString().replace(/\D/g, ''),
		pathes.dist,
		path.resolve(config.rootDir, './releases'),
	);
})();
