const fs = require("fs");
const path = require("path");

const phpMerge = require("./lib/php-merge.js");
const phpHooks = require("./lib/php-hooks.js");
const phpOptimize = require("./lib/php-optimize.js");
const phpDefine = require("./lib/php-define.js");
const phpString = require("./lib/php-string.js");
const phpVariable = require("./lib/php-variable.js");

module.exports = async function (entryDir, distDir) {
  const buildContext = {
    entryDir,
    distDir,

    constants: new Map(),
    functions: new Map(),
    hooks: new Map(),
    classes: new Map(),
    strings: new Map(),
    variables: new Map(),

    runtime: {
      stringPoolFunction: null,
    },
  };

  try {
    fs.rmSync(buildContext.distDir, { recursive: true, force: true });
  } finally {
    await phpMerge(buildContext);
    await phpVariable(buildContext);

    //   await phpHooks(buildContext);
    await phpDefine(buildContext);
    await phpString(buildContext);

    await phpOptimize(buildContext);

    fs.writeFileSync(
      path.join(distDir, "buildContext.json"),
      JSON.stringify(
        buildContext,
        function (key, value) {
          if (key === "runtime") {
            return undefined;
          }
          if (value instanceof Map) {
            return Array.from(value, ([key, value]) => ({ key, value }));
          }
          return value;
        },
        2,
      ),
    );
  }
}
