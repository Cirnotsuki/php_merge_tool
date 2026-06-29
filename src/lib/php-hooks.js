const fs = require("fs/promises");
const path = require("path");
const crypto = require("crypto");
const PHPParser = require("php-parser");

module.exports = async function (buildContext = {}) {
  const ROOT_DIR = buildContext.distDir;

  let ENTRY_FILE = path.join(ROOT_DIR, "functions.php");
  if(!fs.existsSync(ENTRY_FILE)) {
    ENTRY_FILE = path.join(ROOT_DIR, "index.php");
  }

  buildContext.functions ??= new Map();

  const functionMap = buildContext.functions;

  const parser = new PHPParser.Engine({
    parser: {
      php7: true,
      extractDoc: false,
    },
    ast: {
      withPositions: true,
    },
  });

  const FUNCTION_PREFIX = "KA_";

  const RESERVED_FUNCTIONS = new Set([
    "__construct",
    "__destruct",
    "__call",
    "__callStatic",
    "__get",
    "__set",
    "__isset",
    "__unset",
    "__sleep",
    "__wakeup",
    "__serialize",
    "__unserialize",
    "__toString",
    "__invoke",
    "__set_state",
    "__clone",
    "__debugInfo",
  ]);

  const RESERVED_PREFIXES = [
    "wp_",
    "wc_",
    "woocommerce_",
    "rest_",
    "admin_",
    "elementor_",
    "twig_",
    "gettext",
    "esc_",
    "__",
    "KA_",
  ];

  function isReservedFunction(name) {
    if (RESERVED_FUNCTIONS.has(name)) {
      return true;
    }

    return RESERVED_PREFIXES.some((prefix) => name.startsWith(prefix));
  }

  function generateFunctionName(original) {
    if (functionMap.has(original)) {
      return functionMap.get(original);
    }

    const hash = crypto
      .createHash("md5")
      .update(original)
      .digest("hex")
      .substring(0, 12);

    const newName = FUNCTION_PREFIX + hash;

    functionMap.set(original, newName);

    return newName;
  }

  function walk(node, parent, callback) {
    if (!node) {
      return;
    }

    callback(node, parent);

    if (Array.isArray(node)) {
      for (const item of node) {
        walk(item, parent, callback);
      }

      return;
    }

    if (typeof node !== "object") {
      return;
    }

    for (const key of Object.keys(node)) {
      if (key === "loc") {
        continue;
      }

      walk(node[key], node, callback);
    }
  }

  function applyReplacements(source, replacements) {
    replacements.sort((a, b) => b.start - a.start);

    for (const r of replacements) {
      source = source.slice(0, r.start) + r.value + source.slice(r.end);
    }

    return source;
  }

  try {
    let source = await fs.readFile(ENTRY_FILE, "utf8");

    const ast = parser.parseCode(source);

    const functions = new Set();

    // =========================
    // collect
    // =========================

    walk(ast, null, (node, parent) => {
      if (node.kind !== "function") {
        return;
      }

      if (
        parent &&
        (parent.kind === "class" ||
          parent.kind === "interface" ||
          parent.kind === "trait")
      ) {
        return;
      }

      const name = node.name?.name || node.name;

      if (!name || isReservedFunction(name)) {
        return;
      }

      functions.add(name);

      generateFunctionName(name);
    });

    console.log(`📦 发现 ${functions.size} 个函数`);

    const replacements = [];

    // =========================
    // transform
    // =========================

    walk(ast, null, (node) => {
      // function foo()

      if (node.kind === "function" && node.name?.loc) {
        const oldName = node.name.name || node.name;

        if (functionMap.has(oldName)) {
          replacements.push({
            start: node.name.loc.start.offset,
            end: node.name.loc.end.offset,
            value: functionMap.get(oldName),
          });
        }
      }

      // foo()

      if (node.kind === "call" && node.what?.kind === "name") {
        const fn = node.what.name;

        if (functionMap.has(fn)) {
          replacements.push({
            start: node.what.loc.start.offset,
            end: node.what.loc.end.offset,
            value: functionMap.get(fn),
          });
        }
      }

      // string callback

      if (node.kind === "string") {
        const value = node.value;

        if (!functionMap.has(value)) {
          return;
        }

        replacements.push({
          start: node.loc.start.offset + 1,
          end: node.loc.end.offset - 1,
          value: functionMap.get(value),
        });
      }
    });

    source = applyReplacements(source, replacements);

    await fs.writeFile(ENTRY_FILE, source, "utf8");

    console.log(`🎉 完成，共处理 ${functionMap.size} 个函数`);

    return buildContext;
  } catch (err) {
    console.error(err);
  }
};
