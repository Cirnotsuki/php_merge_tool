const phpMerge = require("./src");
const fs = require("fs");
const path = require("path");
const mkdirp = require("mkdirp");
const { getUUID } = require("ka-crypto");

const rootPath = {
  source: "D:/laragon/www",
  dist: "D:/laragon 5.0/www",
  // dist: path.join(__dirname, "dist"),
};
const buildDirs = [
  "./api",
  "./wp-content/mu-plugins",
  "./wp-content/themes/cirnotob",
];

(async () => {
  const opt = {
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
  };
  for (const dir of buildDirs) {
    const source = path.join(rootPath.source, dir);
    const dist = path.join(rootPath.dist, dir);

    try {
      fs.rmSync(dist, { recursive: true });
    } catch (error) {
      console.error(error);
    }

    mkdirp.sync(dist);

    await phpMerge(source, dist, opt);
  }
})();
