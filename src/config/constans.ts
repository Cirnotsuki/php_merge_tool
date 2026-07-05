import path from 'path';
import fs from 'fs';
import os from 'os';

export const DEBUG = true;

export const CONST_PREFIX = 'KA_';

export const RANDOM_NUMBER_SIZE = 2;

export const TARGET_EXTENSION = '.php';

export const EXCLUDES = ['vendor', 'node_modules', '.git', 'languages'];
export const RESERGVED = ['languages'];

export const EXCLUDE_STRING = ['/mu-plugins/decrypt.php', 'SHORTINIT', 'ABSPATH'];
export const EXTERNAL: string[] = [];
export const ENTRIES = ['functions.php', 'index.php'];
export const REQUIRE_TYPES = ['require', 'require_once', 'include', 'include_once'];
export const STRING_OPT = {
	/**
	 * 启用字符串池
	 */
	ENABLE_STRING_POOL: true,

	/**
	 * 启用字符串池压缩
	 */
	ENABLE_POOL_COMPRESS: true,

	/**
	 * 最短字符串长度
	 */
	MIN_STRING_LENGTH: 2,

	/**
	 * 最大字符串长度
	 */
	MAX_STRING_LENGTH: 500,
};

export const RESERVED = new Set([
	'this',
	'GLOBALS',
	'_GET',
	'_POST',
	'_REQUEST',
	'_COOKIE',
	'_SESSION',
	'_SERVER',
	'_FILES',
	'_ENV',
	'wpdb',
	'wp_query',
	'post',
	'posts',
	'title',
	'route_name',
	'font',
	'iconClass',
	'item',
	'key',
	'value',
	'instance',
	'wp_customize',
	'selective_refresh',
	'args',
	'atts',
	'request',
	'response',
	'route',
]);
export const ROOT_PATH =
	os.hostname().toLocaleLowerCase() === 'cirnotsuki'
		? {
				source: 'X:/ChengdaGlass/@wp',
				dist: 'X:/ChengdaGlass/laragon/www',
				// dist: path.join(__dirname, "dist"),
			}
		: {
				source: 'D:/laragon/www',
				dist: 'D:/laragon5/www',
				// dist: path.join(__dirname, "dist"),
			};

export const BUILD_DIRS = ['./api', './wp-content/mu-plugins', './wp-content/themes/cirnotob'];
export const COPY_FILES = ['./wp-config.php', './.htaccess'];

export const ROOT_DIR = path.resolve('./');
export const MAP_DIR = path.join(ROOT_DIR, 'maps');
export const PUBLIC_KEY = fs.readFileSync(
	path.resolve(ROOT_DIR, './src/config/public.pem'),
	'utf-8',
);

export const PRIVATE_KEY = fs.readFileSync(
	path.resolve(ROOT_DIR, './src/config/private.pem'),
	'utf-8',
);