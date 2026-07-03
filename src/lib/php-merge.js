const fs = require("fs");
const path = require("path");
const mkdirp = require("mkdirp");
// ========================================
// PHP Merge Compiler
// ========================================

module.exports = async function (buildContext = {}) {
  // ========================================
  // Context
  // ========================================

  const ROOT_DIR = buildContext.entryDir;
  const OUTPUT_DIR = buildContext.distDir;

  let ENTRY_FILE = "functions.php";
  if (fs.existsSync(path.join(ROOT_DIR, "index.php"))) {
    ENTRY_FILE = "index.php";
  }
  console.log("MERGE ENTRY_FILE", ENTRY_FILE);

  // ========================================
  // Config
  // ========================================

  const DEBUG = true;

  const REQUIRE_TYPES = ["require", "require_once", "include", "include_once"];

  const EXCLUDES = ["vendor", "node_modules", ".git"];

  // ========================================

  const processedFiles = new Set();

  const fileCache = new Map();

  // ========================================
  // Logger
  // ========================================

  const logger = {
    log(...args) {
      if (DEBUG) {
        console.log(...args);
      }
    },

    warn(...args) {
      console.warn(...args);
    },

    error(...args) {
      console.error(...args);
    },
  };

  // ========================================
  // Utils
  // ========================================

  function exists(filePath) {
    try {
      fs.accessSync(filePath);

      return true;
    } catch {
      return false;
    }
  }

  function normalizePath(p) {
    return path.normalize(p).replace(/\\/g, "/");
  }

  function isExcluded(filePath) {
    const relativePath = normalizePath(path.relative(ROOT_DIR, filePath));

    return EXCLUDES.some((ex) => {
      const normalized = normalizePath(ex);

      return (
        relativePath === normalized || relativePath.startsWith(normalized + "/")
      );
    });
  }

  function isPhpFile(filePath) {
    return path.extname(filePath).toLowerCase() === ".php";
  }

  async function readFileCached(filePath) {
    if (fileCache.has(filePath)) {
      return fileCache.get(filePath);
    }

    let content = await fs.readFileSync(filePath, "utf8");

    /**
     * Remove BOM
     */
    content = content.replace(/^\uFEFF/, "");

    fileCache.set(filePath, content);

    return content;
  }

  // ========================================
  // PHP Inline Comment Strip
  // ========================================

  function stripPhpComment(line) {
    let result = "";

    let inString = false;

    let stringChar = "";

    for (let i = 0; i < line.length; i++) {
      const char = line[i];

      const next = line[i + 1];

      /**
       * String Start
       */
      if (!inString) {
        if (char === '"' || char === "'") {
          inString = true;

          stringChar = char;

          result += char;

          continue;
        }

        /**
         * //
         */
        if (char === "/" && next === "/") {
          break;
        }

        /**
         * #
         */
        if (char === "#") {
          break;
        }

        result += char;

        continue;
      }

      result += char;

      /**
       * String End
       */
      if (char === stringChar && line[i - 1] !== "\\") {
        inString = false;
      }
    }

    return result;
  }

  // ========================================
  // Parse Variables
  // ========================================

  function parseVariables(lines, currentDir) {
    const variables = {};

    for (const rawLine of lines) {
      const line = stripPhpComment(rawLine).trim();

      /**
       * Skip
       */
      if (!line.startsWith("$")) {
        continue;
      }

      const match = line.match(/^\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*(.+?)\s*;$/);

      if (!match) {
        continue;
      }

      const [, name, value] = match;

      let resolved = value;

      /**
       * __DIR__
       */
      resolved = resolved.replace(/__DIR__/g, `'${currentDir}'`);

      /**
       * get_stylesheet_directory()
       */
      resolved = resolved.replace(
        /get_stylesheet_directory\(\)/g,
        `'${ROOT_DIR}'`
      );

      variables[name] = resolved;

      logger.log(`📦 Variable: $${name}`);
    }

    return variables;
  }

  // ========================================
  // Extract Require
  // ========================================

  function extractRequire(line) {
    const cleanLine = stripPhpComment(line);
    let inString = false;

    let stringChar = "";

    for (let i = 0; i < cleanLine.length; i++) {
      const char = cleanLine[i];

      /**
       * Outside String
       */
      if (!inString) {
        /**
         * Enter String
         */
        if (char === '"' || char === "'") {
          inString = true;

          stringChar = char;

          continue;
        }

        /**
         * require
         */
        for (const type of REQUIRE_TYPES) {
          const next = cleanLine[i + type.length];

          if (
            cleanLine.startsWith(type, i) &&
            (next === undefined || /\s|\(/.test(next))
          ) {
            if (new RegExp(type + "\\s+dirname\\(").test(cleanLine)) continue;
            
            console.log("MergeInclude: " + JSON.stringify({ cleanLine, type }));
            return parseRequireExpression(cleanLine, i, type);
          }
        }

        continue;
      }

      /**
       * Leave String
       */
      if (char === stringChar && cleanLine[i - 1] !== "\\") {
        inString = false;
      }
    }

    return null;
  }

  // ========================================
  // Parse Require Expression
  // ========================================

  function parseRequireExpression(line, start, type) {
    let i = start + type.length;

    /**
     * Skip Space
     */
    while (/\s/.test(line[i])) {
      i++;
    }

    let expression = "";

    let inString = false;

    let stringChar = "";

    let depth = 0;

    /**
     * (
     */
    if (line[i] === "(") {
      depth = 1;

      i++;
    }

    for (; i < line.length; i++) {
      const char = line[i];

      /**
       * Outside String
       */
      if (!inString) {
        if (char === '"' || char === "'") {
          inString = true;

          stringChar = char;

          expression += char;

          continue;
        }

        /**
         * (
         */
        if (char === "(") {
          depth++;
        } else if (char === ")") {
          /**
           * )
           */
          depth--;

          if (depth <= 0) {
            break;
          }
        } else if (char === ";" && depth === 0) {
          /**
           * ;
           */
          break;
        }

        expression += char;

        continue;
      }

      expression += char;

      /**
       * End String
       */
      if (char === stringChar && line[i - 1] !== "\\") {
        inString = false;
      }
    }

    return {
      type,
      expression: expression.trim(),
    };
  }

  // ========================================
  // Resolve Require Path
  // ========================================

  function resolveRequirePath(expression, currentDir, variables) {
    let expr = expression;

    /**
     * __DIR__
     */
    expr = expr.replace(/__DIR__/g, `'${currentDir}'`);

    /**
     * get_stylesheet_directory()
     */
    expr = expr.replace(/get_stylesheet_directory\(\)/g, `'${ROOT_DIR}'`);

    /**
     * Variables
     */
    const sortedVariables = Object.entries(variables).sort(
      (a, b) => b[0].length - a[0].length
    );

    for (const [name, value] of sortedVariables) {
      expr = expr.replace(new RegExp(`\\$${name}\\b`, "g"), value);
    }

    /**
     * Extract String
     */
    const segments = [];

    const regex = /['"]([^'"]+)['"]/g;

    let match;

    while ((match = regex.exec(expr)) !== null) {
      segments.push(match[1]);
    }

    if (!segments.length) {
      return null;
    }

    let finalPath = segments.join("");

    /**
     * Relative
     */
    if (!path.isAbsolute(finalPath)) {
      finalPath = path.resolve(currentDir, finalPath);
    }

    return path.normalize(finalPath);
  }

  // ========================================
  // Process PHP File
  // ========================================

  async function processPhpFile(filePath) {
    filePath = path.normalize(filePath);

    /**
     * Skip
     */
    if (processedFiles.has(filePath)) {
      logger.log(`⏭️ Already Processed: ${filePath}`);

      return {
        content: "",
        skipped: true,
      };
    }

    /**
     * Excluded
     */
    if (isExcluded(filePath)) {
      return {
        content: "",
        excluded: true,
      };
    }

    /**
     * Exists
     */
    if (!(await exists(filePath))) {
      logger.warn(`⚠️ File Not Found: ${filePath}`);

      return {
        content: "",
        notFound: true,
      };
    }

    processedFiles.add(filePath);

    logger.log(`\n📄 Processing: ${path.relative(ROOT_DIR, filePath)}`);

    const currentDir = path.dirname(filePath);

    const content = await readFileCached(filePath);

    const lines = content.split(/\r?\n/);

    /**
     * Variables
     */
    const variables = parseVariables(lines, currentDir);

    const result = [];

    for (const line of lines) {
      const requireInfo = extractRequire(line);

      /**
       * Normal Line
       */
      if (!requireInfo) {
        result.push(line);

        continue;
      }

      logger.log(`🔗 Dependency: ${requireInfo.expression}`);

      /**
       * Resolve
       */
      const dependencyPath = resolveRequirePath(
        requireInfo.expression,
        currentDir,
        variables
      );

      /**
       * Exists
       */
      if (dependencyPath && (await exists(dependencyPath))) {
        const child = await processPhpFile(dependencyPath);

        /**
         * Valid
         */
        if (
          !child.skipped &&
          !child.notFound &&
          !child.excluded &&
          child.content
        ) {
          /**
           * Remove PHP Tag
           */
          const cleanContent = child.content

            .replace(/^\s*<\?php(\s|[\r\n])*/i, "")

            .replace(/\s*\?>\s*$/i, "");

          result.push(
            `// ===== Merge From: ${path.relative(
              ROOT_DIR,
              dependencyPath
            )} =====`
          );

          result.push(cleanContent);

          result.push("// ===== Merge End =====");

          continue;
        }
      }

      /**
       * Keep Original
       */
      result.push(line);
    }

    return {
      content: result.join("\n"),
    };
  }

  // ========================================
  // Copy Remaining Files
  // ========================================

  async function copyRemainingFiles(dir) {
    const entries = fs.readdirSync(dir, {
      withFileTypes: true,
    });

    for (const entry of entries) {
      const fullPath = path.join(dir, entry.name);

      /**
       * Excluded
       */
      if (isExcluded(fullPath)) {
        continue;
      }

      /**
       * Directory
       */
      if (entry.isDirectory()) {
        await copyRemainingFiles(fullPath);

        continue;
      }

      /**
       * Skip Merged
       */
      if (processedFiles.has(fullPath)) {
        continue;
      }

      const relativePath = path.relative(ROOT_DIR, fullPath);

      const outputPath = path.join(OUTPUT_DIR, relativePath);

      /**
       * Ensure Dir
       */
      mkdirp.sync(path.dirname(outputPath));

      /**
       * Copy
       */
      fs.copyFileSync(fullPath, outputPath);

      logger.log(`📄 Copy File: ${relativePath}`);
    }
  }

  // ========================================
  // Main
  // ========================================

  try {
    const entryPath = path.resolve(ROOT_DIR, ENTRY_FILE);

    /**
     * Root Exists
     */
    if (!exists(ROOT_DIR)) {
      throw new Error(`Project Directory Not Found: ${ROOT_DIR}`);
    }

    /**
     * Entry Exists
     */
    if (!exists(entryPath)) {
      throw new Error(`Entry File Not Found: ${entryPath}`);
    }

    /**
     * Clean Dist
     */
    if (exists(OUTPUT_DIR)) {
      fs.rmSync(OUTPUT_DIR, {
        recursive: true,
        force: true,
      });
    }

    /**
     * Create Dist
     */
    mkdirp.sync(OUTPUT_DIR);

    console.log(`🚀 开始合并 PHP 文件: ${entryPath}\n`);

    /**
     * Merge
     */
    const { content } = await processPhpFile(entryPath);

    const outputEntry = path.join(OUTPUT_DIR, ENTRY_FILE);

    /**
     * Save
     */
    fs.writeFileSync(outputEntry, content, "utf8");

    console.log("\n📦 复制未处理文件...\n");

    /**
     * Copy Assets
     */
    await copyRemainingFiles(ROOT_DIR);

    console.log("\n🎉 合并完成");

    console.log(`📄 合并文件数: ${processedFiles.size}`);

    return buildContext;
  } catch (err) {
    console.error(err);
  }
};
