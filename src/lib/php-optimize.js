const fs = require("fs/promises");
const path = require("path");

// ========================================
// PHP Optimize Compiler
// ========================================

module.exports = async function (buildContext = {}) {
  // ========================================
  // Context
  // ========================================

  const DIST_DIR = path.resolve(__dirname, buildContext.distDir || "./dist");

  // ========================================
  // Config
  // ========================================

  const DEBUG = true;

  const TARGET_EXTENSION = ".php";

  const EXCLUDES = ["vendor", "node_modules", ".git"];

  // ========================================
  // Logger
  // ========================================

  const logger = {
    log(...args) {
      if (DEBUG) {
        console.log(...args);
      }
    },
  };

  // ========================================
  // Utils
  // ========================================

  function normalizePath(p) {
    return path.normalize(p).replace(/\\/g, "/");
  }

  function isExcluded(filePath) {
    const relative = normalizePath(path.relative(DIST_DIR, filePath));

    return EXCLUDES.some((ex) => {
      ex = normalizePath(ex);

      return relative === ex || relative.startsWith(ex + "/");
    });
  }

  function isPhpFile(filePath) {
    return path.extname(filePath).toLowerCase() === TARGET_EXTENSION;
  }

  // ========================================
  // Strip PHP Comments
  // ========================================

  function stripPhpComments(content) {
    let result = "";

    let inString = false;

    let stringChar = "";

    let inLineComment = false;

    let inBlockComment = false;

    for (let i = 0; i < content.length; i++) {
      const char = content[i];

      const next = content[i + 1];

      const prev = content[i - 1];

      // ====================================
      // Block Comment
      // ====================================

      if (inBlockComment) {
        if (char === "*" && next === "/") {
          inBlockComment = false;

          i++;
        }

        continue;
      }

      // ====================================
      // Line Comment
      // ====================================

      if (inLineComment) {
        if (char === "\n") {
          inLineComment = false;

          result += "\n";
        }

        continue;
      }

      // ====================================
      // String
      // ====================================

      if (inString) {
        result += char;

        if (char === stringChar && prev !== "\\") {
          inString = false;
        }

        continue;
      }

      // ====================================
      // String Start
      // ====================================

      if (char === '"' || char === "'") {
        inString = true;

        stringChar = char;

        result += char;

        continue;
      }

      // ====================================
      // #
      // ====================================

      if (char === "#") {
        inLineComment = true;

        continue;
      }

      // ====================================
      // //
      // ====================================

      if (char === "/" && next === "/") {
        /**
         * Avoid URL
         */
        const recent = result.slice(-10);

        if (recent.endsWith("http:") || recent.endsWith("https:")) {
          result += char;

          continue;
        }

        inLineComment = true;

        i++;

        continue;
      }

      // ====================================
      // /*
      // ====================================

      if (char === "/" && next === "*") {
        /**
         * Preserve License
         */
        const ahead = content.substring(i, i + 15);

        if (ahead.includes("@license")) {
          result += char;

          continue;
        }

        inBlockComment = true;

        i++;

        continue;
      }

      result += char;
    }

    return cleanupEmptyLines(result);
  }

  // ========================================
  // Cleanup Empty Lines
  // ========================================

  // ========================================
  // Cleanup Empty Lines
  // ========================================

  function cleanupEmptyLines(content) {
    const lines = content.split(/\r?\n/);

    const cleaned = [];

    for (const line of lines) {
      /**
       * 去除行尾空格
       */
      const trimmedRight = line.replace(/\s+$/g, "");

      /**
       * 去除首尾后为空
       */
      if (!trimmedRight.trim()) {
        continue;
      }

      cleaned.push(trimmedRight);
    }

    /**
     * 压缩连续空格
     */
    return cleaned
      .join("\n")
      .replace(/[ \t]+/g, " ")
      .replace(/\n{2,}/g, "\n")
      .trim();
  }
  // ========================================
  // Process File
  // ========================================

  async function processFile(filePath) {
    /**
     * Skip
     */
    if (isExcluded(filePath)) {
      return;
    }

    /**
     * Not PHP
     */
    if (!isPhpFile(filePath)) {
      return;
    }

    logger.log(`🧹 Optimize: ${path.relative(DIST_DIR, filePath)}`);

    let content = await fs.readFile(filePath, "utf8");

    /**
     * Remove BOM
     */
    content = content.replace(/^\uFEFF/, "");

    /**
     * Strip Comments
     */
    const cleaned = stripPhpComments(content);

    /**
     * Save
     */
    await fs.writeFile(filePath, cleaned, "utf8");
  }

  // ========================================
  // Walk Directory
  // ========================================

  async function walk(dir) {
    const entries = await fs.readdir(dir, {
      withFileTypes: true,
    });

    for (const entry of entries) {
      const fullPath = path.join(dir, entry.name);

      /**
       * Skip Excluded
       */
      if (isExcluded(fullPath)) {
        continue;
      }

      /**
       * Directory
       */
      if (entry.isDirectory()) {
        await walk(fullPath);

        continue;
      }

      /**
       * Process File
       */
      await processFile(fullPath);
    }
  }

  // ========================================
  // Main
  // ========================================

  console.log("🧹 开始清理 PHP 注释...\n");

  try {
    await walk(DIST_DIR);
  } catch (err) {
    console.error(err);
  }

  console.log("\n🎉 PHP Optimize 完成");

  return buildContext;
};
