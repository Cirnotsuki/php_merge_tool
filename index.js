const phpMerge = require("./src");
const fs = require("fs");
const path = require("path");
const mkdirp = require("mkdirp");

mkdirp.sync("dist");
mkdirp.sync("entries");

const dirs = fs.readdirSync("source");

(async () => {
  for (const dir of dirs) {
    const source = path.join("source", dir);
    const dist = path.join("dist", dir);
    mkdirp.sync(dist);

    await phpMerge(source, dist);
  }
})();
