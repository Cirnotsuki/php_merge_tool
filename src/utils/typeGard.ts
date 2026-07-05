import { AstNodeMap, AstNode } from '../types';

export function isKind<K extends keyof AstNodeMap>(
	node: any,
	kind: K,
): node is AstNode<AstNodeMap[K]> {
	if (!node) return false;
	return node.kind === kind;
}
