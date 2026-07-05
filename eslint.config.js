import js from '@eslint/js';
import globals from 'globals';
import { defineConfig } from 'eslint/config';

export default defineConfig([
	{
		files: ['**/*.{js,mjs,cjs}'],
		plugins: { js },
		extends: ['js/recommended'],
		languageOptions: {
			globals: {
				...globals.browser,
				...globals.node,
			},
		},
		rules: {
			'no-empty': 'warn',
			'no-unused-vars': 'warn',
			'no-useless-assignment': 'warn',
			'no-dupe-keys': 'warn',
		},
	},
]);
