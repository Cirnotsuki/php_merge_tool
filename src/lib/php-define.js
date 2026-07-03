const fs = require("fs");
const path = require("path");
const crypto = require("crypto");
const { getUUID } = require('ka-crypto');

// ========================================
// PHP Define Symbol Compiler
// ========================================

module.exports = async function (buildContext = {}) {
  // ========================================
  // Context
  // ========================================

  const ROOT_DIR = buildContext.distDir;

  buildContext.constants ??= new Map();

  // ========================================
  // Config
  // ========================================

  const DEBUG = true;

  const CONST_PREFIX = "KA_";

  const EXCLUDES = ["node_modules", ".git", "vendor"];

  // ========================================

  /**
   * Symbol Table
   */
  const constantMap = buildContext.constants;

  /**
   * PHP Files
   */
  const phpFiles = [];

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
    const relative = normalizePath(path.relative(ROOT_DIR, filePath));

    return EXCLUDES.some((ex) => {
      ex = normalizePath(ex);

      return relative === ex || relative.startsWith(ex + "/");
    });
  }

  function isPhpFile(filePath) {
    return path.extname(filePath).toLowerCase() === ".php";
  }

  /**
   * Runtime Skip
   */
  function isRuntimeFile(content) {
    return content.includes("KA_RUNTIME_START");
  }

  /**
   * Generate Const Name
   */
  function generateConstName(original) {
    if (constantMap.has(original)) {
      return constantMap.get(original);
    }

    const hash = getUUID(true);

    const newName = CONST_PREFIX + hash.slice(-6);

    constantMap.set(original, newName);

    return newName;
  }

  /**
   * Skip System Const
   */
  function shouldSkipConst(constName) {
    return (
      /**
       * WP
       */
      constName.startsWith("WP_") ||
      /**
       * PHP
       */
      constName.startsWith("PHP_") ||
      /**
       * WC
       */
      constName.startsWith("WC_") ||
      /**
       * Elementor
       */
      constName.startsWith("ELEMENTOR_") ||
      /**
       * Core
       */
      constName === "ABSPATH" ||
      constName === "OBJECT" ||
      constName === "ARRAY_A" ||
      constName === "ARRAY_N" ||
      /**
       * Runtime
       */
      constName.startsWith("KA_")
    );
  }

  // ========================================
  // Scan Directory
  // ========================================

  async function scanDirectory(dir) {
    const entries = fs.readdirSync(dir, {
      withFileTypes: true,
    });

    for (const entry of entries) {
      const fullPath = path.join(dir, entry.name);

      if (isExcluded(fullPath)) {
        continue;
      }

      if (entry.isDirectory()) {
        await scanDirectory(fullPath);

        continue;
      }

      if (isPhpFile(fullPath)) {
        phpFiles.push(fullPath);
      }
    }
  }

  // ========================================
  // Collect Constants
  // ========================================

  async function collectConstants() {
    const defineRegex = /define\s*\(\s*['"]([A-Z0-9_]+)['"]/g;

    for (const file of phpFiles) {
      logger.log(`🔍 扫描常量: ${path.relative(ROOT_DIR, file)}`);

      const content = fs.readFileSync(file, "utf8");

      /**
       * Skip Runtime
       */
      if (isRuntimeFile(content)) {
        continue;
      }

      let match;

      while ((match = defineRegex.exec(content)) !== null) {
        const constName = match[1];

        /**
         * Skip
         */
        if (shouldSkipConst(constName)) {
          continue;
        }

        generateConstName(constName);
      }
    }
  }

  // ========================================
  // Replace Define
  // ========================================

  function replaceDefineConstants(content) {
    for (const [oldName, newName] of constantMap) {
      const regex = new RegExp(`(define\\s*\\(\\s*['"])${oldName}(['"])`, "g");

      content = content.replace(regex, `$1${newName}$2`);
    }

    return content;
  }

  // ========================================
  // Replace defined()
  // ========================================

  function replaceDefinedCalls(content) {
    for (const [oldName, newName] of constantMap) {
      const regex = new RegExp(`(defined\\s*\\(\\s*['"])${oldName}(['"])`, "g");

      content = content.replace(regex, `$1${newName}$2`);
    }

    return content;
  }

  // ========================================
  // Replace constant()
  // ========================================

  function replaceConstantCalls(content) {
    for (const [oldName, newName] of constantMap) {
      const regex = new RegExp(
        `(constant\\s*\\(\\s*['"])${oldName}(['"])`,
        "g"
      );

      content = content.replace(regex, `$1${newName}$2`);
    }

    return content;
  }

  // ========================================
  // Replace Usage
  // ========================================

  function replaceConstUsage(content) {
    for (const [oldName, newName] of constantMap) {
      /**
       * 跳过字符串中的内容
       */
      const regex = new RegExp(`\\b${oldName}\\b`, "g");

      content = content.replace(regex, (match, offset) => {
        /**
         * 前后字符检测
         */
        const before = content[offset - 1];

        const after = content[offset + match.length];

        /**
         * 字符串中跳过
         */
        if (
          before === "'" ||
          before === '"' ||
          after === "'" ||
          after === '"'
        ) {
          return match;
        }

        return newName;
      });
    }

    return content;
  }

  // ========================================
  // Process Files
  // ========================================

  async function processFiles() {
    for (const file of phpFiles) {
      logger.log(`🔄 替换常量: ${path.relative(ROOT_DIR, file)}`);

      let content = fs.readFileSync(file, "utf8");

      /**
       * Skip Runtime
       */
      if (isRuntimeFile(content)) {
        continue;
      }

      content = replaceDefineConstants(content);

      content = replaceDefinedCalls(content);

      content = replaceConstantCalls(content);

      content = replaceConstUsage(content);

      fs.writeFileSync(file, content, "utf8");
    }
  }

  // ========================================
  // Main
  // ========================================

  try {
    console.log("🚀 开始扫描 PHP 常量...\n");

    await scanDirectory(ROOT_DIR);

    console.log(`📦 共发现 ${phpFiles.length} 个 PHP 文件\n`);

    /**
     * 收集
     */
    await collectConstants();

    console.log(`📦 共发现 ${constantMap.size} 个常量\n`);

    /**
     * Debug
     */
    for (const [oldName, newName] of constantMap) {
      logger.log(`🔄 ${oldName} -> ${newName}`);
    }

    console.log("\n🚀 开始替换常量...\n");

    /**
     * 替换
     */
    await processFiles();

    console.log("\n🎉 常量替换完成");

    return buildContext;
  } catch (err) {
    console.error("❌ 执行失败:", err);
  }
};
