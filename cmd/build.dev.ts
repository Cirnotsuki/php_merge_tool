import build from '../src/handle/build';
import copy from '../src/handle/copy';
import config from '../config';

(async () => {
    await build(config.buildDirs, config.pathes);
    await copy(config.copyFiles, config.pathes);
    while (true) {}
})();
