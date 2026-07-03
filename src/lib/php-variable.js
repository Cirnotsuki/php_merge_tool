const fs = require("fs");
const path = require("path");
const { getUUID } = require("ka-crypto");
const PHPParser = require("php-parser");
const randomPrefix = require("../utils/randomPrefix");

async function fixMissedVariables(content, buildContext) {
  for (const [key, newName] of buildContext.variables) {
    const original = key;
    const regex = new RegExp(`(?<!\\$)\\$${original}\\b`, "g");
    content = content.replace(regex, newName);
  }
  return content;
}

module.exports = async function (buildContext = {}) {
  buildContext.variables ??= new Map();
  const variableMap = buildContext.variables;
  const PREFIX = () => {
    return randomPrefix();
  };
  const ROOT_DIR = buildContext.distDir;

  const parser = new PHPParser.Engine({
    parser: { php7: true, extractDoc: false },
    ast: { withPositions: true },
  });

  const RESERVED = new Set([
    "this",
    "GLOBALS",
    "_GET",
    "_POST",
    "_REQUEST",
    "_COOKIE",
    "_SESSION",
    "_SERVER",
    "_FILES",
    "_ENV",
    "wpdb",
    "wp_query",
    "post",
    "posts",
    "title",
    "route_name",
    "font",
    "iconClass",
    "item",
    "key",
    "value",
    "instance",
    "wp_customize",
    "selective_refresh",
    "args",
    "atts",
    "request",
    "response",
    "route",
  ]);

  const phpFiles = [];

  async function scan(dir) {
    const entries = fs.readdirSync(dir, { withFileTypes: true });
    for (const entry of entries) {
      const full = path.join(dir, entry.name);
      if (entry.isDirectory()) scan(full);
      if (full.endsWith(".php")) phpFiles.push(full);
    }
  }

  function generateName(name) {
    if (variableMap.has(name)) {
      return variableMap.get(name);
    }

    const uuid = getUUID(true);
    const newName = PREFIX() + uuid.slice(-4);

    variableMap.set(name, newName);

    return newName;
  }

  function attachParent(node, parent = null) {
    if (!node || typeof node !== "object") return;
    node.parent = parent;
    for (const key in node) {
      if (key === "parent" || key === "loc") continue;
      const value = node[key];
      if (Array.isArray(value)) value.forEach((v) => attachParent(v, node));
      else attachParent(value, node);
    }
  }

  function isLocalVariable(node, excluded) {
    if (!node || node.kind !== "variable") return false;
    if (typeof node.name !== "string") return false;
    if (RESERVED.has(node.name)) return false;
    if (excluded.has(node.name)) return false;

    const parent = node.parent;
    if (!parent) return false;
    if (
      ["parameter", "global", "staticvariable", "catch", "use"].includes(
        parent.kind
      )
    )
      return false;
    if (parent.kind === "foreach") {
      if (
        (parent.key && parent.key.name === node.name) ||
        (parent.value && parent.value.name === node.name)
      )
        return false;
    }
    return true;
  }

  function walk(node, callback) {
    if (!node) {
      return;
    }

    callback(node);

    if (Array.isArray(node)) {
      for (const child of node) {
        walk(child, callback);
      }

      return;
    }

    if (typeof node !== "object") {
      return;
    }

    const keys = Object.keys(node);

    for (const key of keys) {
      if (key === "loc" || key === "parent") {
        continue;
      }

      walk(node[key], callback);
    }
  }

  function applyReplacements(source, replacements) {
    replacements.sort((a, b) => b.start - a.start);
    for (const r of replacements) {
      source = source.slice(0, r.start) + r.value + source.slice(r.end);
    }
    return source;
  }

  await scan(ROOT_DIR);

  for (const file of phpFiles) {
    let source = fs.readFileSync(file, "utf8");
    let ast;
    try {
      ast = parser.parseCode(source);
    } catch (err) {
      console.error("Parse Error:", file, err.message);
      continue;
    }

    attachParent(ast);

    const visitedOffsets = new Set();
    const replacements = [];
    let scopeCounter = 0;

    walk(ast, (node) => {
      if (["function", "method", "closure", "arrowfunc"].includes(node.kind)) {
        scopeCounter++;
        const scopeId = scopeCounter;
        const excluded = new Set();

        // 收集函数参数
        for (const param of node.arguments || []) {
          if (param.name && param.name.name) excluded.add(param.name.name);
        }

        // 收集 static 变量和 closure use
        walk(node.body || node.expr, (n) => {
          if (n.kind === "static") {
            for (const v of n.items || n.variables || []) {
              const name = v.name || (v.variable && v.variable.name);
              if (name) excluded.add(name);
            }
          }
          if (n.kind === "closure") {
            for (const u of n.uses || []) {
              if (u.name) excluded.add(u.name);
            }
          }
        });

        walk(node.body || node.expr, (child) => {
          if (!isLocalVariable(child, excluded)) return;
          if (
            !child.loc ||
            child.loc.start.offset === undefined ||
            child.loc.end.offset === undefined
          )
            return;

          const pos = `${child.loc.start.offset}:${child.loc.end.offset}`;
          if (visitedOffsets.has(pos)) return; // 已处理过，跳过
          visitedOffsets.add(pos);

          replacements.push({
            start: child.loc.start.offset,
            end: child.loc.end.offset,
            value: generateName(child.name), // 如果你已经决定全局统一变量名，可以只用 name
          });
        });
      }
    });

    source = applyReplacements(source, replacements);
    // 第二步修复漏掉的变量
    source = await fixMissedVariables(source, buildContext);
    fs.writeFileSync(file, source, "utf8");
  }

  console.log(`变量混淆完成，共 ${variableMap.size} 个变量`);
  return buildContext;
};
