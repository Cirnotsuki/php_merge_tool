import type PHPParser from 'php-parser';
interface BuildRuntime {
	stringPoolFunction: null | string;
}
export interface BuildContext {
	entryDir: string;
	distDir: string;
	date: string;
	time: number;
	guid: string;
	pool: string;
	replace: { [key: string]: string };

	constants: Map<string, string>;
	functions: Map<string, string>;
	hooks: Map<string, string>;
	classes: Map<string, string>;
	strings: Map<string, number>;
	variables: Map<string, string>;

	runtime: BuildRuntime;
}

export interface BuildOption {
	date?: string;
	time?: number;
	guid?: string;
	pool?: string;
	replace?: { [key: string]: string };

	constants?: Map<string, string>;
	functions?: Map<string, string>;
	hooks?: Map<string, string>;
	classes?: Map<string, string>;
	strings?: Map<string, number>;
	variables?: Map<string, string>;

	runtime?: BuildRuntime;
}

export interface AstReplacement {
	start: number;
	end: number;
	value: string;
}

export type PHPParserWalkCallBack = (
	node: PHPParser.Block,
	parent: PHPParser.Block | null,
	callback?: PHPParserWalkCallBack,
) => void;

export type AnyAstNode = AstNodeMap[keyof AstNodeMap];

export type AstNode<T extends PHPParser.Node = PHPParser.Node> = T & {
	name?: string;
	parent?: AstNode<AnyAstNode> | null;
	index?: number;
	isFirstChild?: boolean;
	isLastChild?: boolean;
};

export interface AstNodeMap {
	node: PHPParser.Node;
	variable: PHPParser.Variable;
	foreach: PHPParser.Foreach;
	function: PHPParser.Function;
	class: PHPParser.Class;
	method: PHPParser.Method;
	parameter: PHPParser.Parameter;
	global: PHPParser.Global;
	staticvariable: PHPParser.StaticVariable;
	catch: PHPParser.Catch;
	use: PHPParser.UseItem;
	closure: PHPParser.Closure;
	arrowfunc: PHPParser.ArrowFunc;
	static: PHPParser.Static;
	return: PHPParser.Return;
	silent: PHPParser.Silent;
	encapsed: PHPParser.Encapsed;
	string: PHPParser.String;
	constant: PHPParser.Constant;
	constantstatement: PHPParser.ConstantStatement;
	classconstant: PHPParser.ClassConstant;
	property: PHPParser.Property;
	propertystatement: PHPParser.PropertyStatement;
	attribute: PHPParser.Attribute;
	attrgroup: PHPParser.AttrGroup;
	enumcase: PHPParser.EnumCase;
	nowdoc: PHPParser.NowDoc;
	call: PHPParser.Call;
	identifier: PHPParser.Identifier;
	name: PHPParser.Name;
}
