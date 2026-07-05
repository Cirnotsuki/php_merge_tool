import phpMerge from '../src';
import fs from 'fs';
import path from 'path';
import mkdirp from 'mkdirp';
import uuidv4 from '@ka-libs/crypto/uuidv4';

const sourceDir = path.join(__dirname, 'entries');
const distDir = path.join(__dirname, 'dist');

(async () => {
	try {
		fs.rmSync(distDir, { recursive: true });
	} catch (error) {}

	const dirs = fs.readdirSync(sourceDir);
	for (const dir of dirs) {
		const source = path.join(sourceDir, dir);
		const dist = path.join(distDir, dir);
		mkdirp.sync(dist);

		await phpMerge(source, dist, {
			date: new Date(),
			uuid: uuidv4(),
		});
	}
})();
