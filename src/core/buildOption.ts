import uuidv4 from '@ka-libs/crypto/uuidv4';
import { RecordFunction, RecordNode } from './recordNode';

export class BuildOption {
	date: string;
	time: number;
	guid: string;
	replace: { [key: string]: string } = {};
	classes = new Map<string, BuildClass>();
	functions = new Map<string, RecordFunction>();
	contexts: BuildContext[] = [];

	constructor(_opt?: any) {
		const opt = {
			..._opt,
		};

		this.date = opt.date || new Date().toLocaleDateString();
		this.time = opt.time || +new Date();
		this.guid = opt.guid || uuidv4();
	}
}

export class BuildClass {
	name: RecordNode;
	methods = new Map<string, RecordFunction>();
	properties = new Map<string, RecordNode>();

	constructor(name: RecordNode) {
		this.name = name;
	}
}

export class BuildContext {
	entryDir: string;
	distDir: string;

	constants = new Map<string, string>();
	functions = new Map<string, string>();
	hooks = new Map<string, string>();

	strings = new Map<string, number>();

	variables: {
		varname: string;
		replace: string;
		location: string;
	}[] = [];

	pool: string = '';

	runtime: {
		stringPoolFunction: null | string;
	} = {
		stringPoolFunction: null,
	};

	options: BuildOption;

	constructor(entry: string, dist: string, options: BuildOption) {
		this.entryDir = entry;
		this.distDir = dist;
		this.options = options;
	}

	get replace() {
		return this.options.replace;
	}

	get classes() {
		return this.options.classes;
	}

	get date() {
		return this.options.date;
	}

	get time() {
		return this.options.time;
	}

	get guid() {
		return this.options.guid;
	}
}
