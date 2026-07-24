import phpMerge from '..';
// import fs from 'fs';
import fs from 'fs/promises';
import path from 'path';
import { mkdirp } from 'mkdirp';
import logger from '../utils/logger';
export default async function (copyFiles: string[], pathes: { source: string; dist: string }) {
	for (const file of copyFiles) {
		const source = path.resolve(pathes.source, file);
		const dist = path.resolve(pathes.dist, file);

		try {
			logger.log(`copy file ${source} to ${dist}`);
			await fs.mkdir(path.dirname(dist), { recursive: true });
			await fs.copyFile(source, dist);
		} catch (error) {
			logger.error(error);
		}
	}
}
