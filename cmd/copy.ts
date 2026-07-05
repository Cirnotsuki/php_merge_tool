import phpMerge from '../src';
// import fs from 'fs';
import fs from 'fs/promises';
import path from 'path';
import { mkdirp } from 'mkdirp';
import { uuidv4 } from '@ka-libs/crypto/uuidv4';

import { BUILD_DIRS, COPY_FILES, ROOT_PATH } from '../src/config/constans';

(async () => {
	for (const file of COPY_FILES) {
		const source = path.join(ROOT_PATH.source, file);
		const dist = path.join(ROOT_PATH.dist, file);

		try {
			await fs.rm(dist, { recursive: true });
            
			await mkdirp(path.dirname(dist));
			await fs.copyFile(source, dist);
		} catch (error) {
			console.error(error);
		}
	}
})();
