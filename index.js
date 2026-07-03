const phpMerge = require("./src");
const fs = require("fs");
const path = require("path");
const mkdirp = require("mkdirp");
const { getUUID } = require("ka-crypto");

const sourceDir = path.join(__dirname, "entries");
const distDir = path.join(__dirname, "dist");

(async () => {
  try {
    fs.rmSync(distDir, { recursive: true });
  } catch (error) {}

  const dirs = fs.readdirSync(sourceDir);
  for (const dir of dirs) {
    const source = path.join(sourceDir, dir);
    const dist = path.join(distDir, dir);
    mkdirp.sync(dist);

    await phpMerge(source, dist, {
      date: new Date(),
      uuid: getUUID(),
    });
  }
})();
