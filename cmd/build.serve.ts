import path from 'path';
import config from '../config';
import build from '../src/handle/build';
import copy from '../src/handle/copy';
import pack from '../src/handle/pack';
import libServe from './lib.serve';
(async () => {
	await build(config.buildDirs, config.pathes, config.replace);
	await copy(config.copyFiles, config.pathes);
	libServe(config);
	pack('www', config.pathes.dist, path.resolve(config.rootDir, './releases'));
})();
