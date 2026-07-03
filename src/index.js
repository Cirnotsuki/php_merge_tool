const fs = require("fs");
const path = require("path");
const { mapDir } = require("../config.js");

const phpMerge = require("./lib/php-merge.js");
const phpHooks = require("./lib/php-hooks.js");
const phpOptimize = require("./lib/php-optimize.js");
const phpDefine = require("./lib/php-define.js");
const phpString = require("./lib/php-string.js");
const phpVariable = require("./lib/php-variable.js");
const { getUUID } = require("ka-crypto");
const { mkdirp } = require("mkdirp");

module.exports = async function (entryDir, distDir, options = {}) {
  const buildContext = {
    entryDir,
    distDir,

    date: new Date().toLocaleDateString(),
    time: +new Date(),
    guid: getUUID(),
    pool: [],

    constants: new Map(),
    functions: new Map(),
    hooks: new Map(),
    classes: new Map(),
    strings: new Map(),
    variables: new Map(),

    runtime: {
      stringPoolFunction: null,
    },

    ...options,
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

    const jsonDir = path.join(mapDir, new Date(buildContext.time).toLocaleDateString());

    mkdirp.sync(jsonDir);

    fs.writeFileSync(
      path.join(jsonDir, buildContext.guid + ".json"),
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
        2
      )
    );
  }
};
