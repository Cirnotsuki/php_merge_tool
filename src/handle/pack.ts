import { path7za } from '7zip-bin';
import { spawnSync } from 'child_process';
import path from 'path';
import fs from 'fs';

export default function (name: string, sourceDir: string, outputDir: string) {
	let zipName = name + '.7z';
	zipName = zipName.replace('.7z.7z', '.7z');

	try {
		fs.mkdirSync(outputDir);
    } catch (error) { }
    
	spawnSync(path7za, ['a', path.resolve(outputDir, zipName), '.', '-mx=9', '-mmt=on'], {
		cwd: sourceDir,
		stdio: 'inherit',
	});
}
