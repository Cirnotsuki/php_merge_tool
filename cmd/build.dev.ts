import build from '../src/handle/build';
import copy from '../src/handle/copy';
import config from '../config';
import path from 'path';

(async () => {
	const pathes = { ...config.pathes, dist: path.resolve(config.rootDir, './.dev') };
	await build(config.buildDirs, pathes, config.replace);
	await copy(config.copyFiles, pathes);
	while (true) {}
})();
