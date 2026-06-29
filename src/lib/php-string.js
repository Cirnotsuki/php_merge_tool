const fs = require("fs/promises");
const path = require("path");
const crypto = require("crypto");
const zlib = require("zlib");

// ========================================
// String Pool Compiler
// ========================================

module.exports = async function (buildContext = {}) {
  const ROOT_DIR = path.resolve(__dirname, buildContext.distDir || "./dist");

  const ENTRY_FILE = path.join(ROOT_DIR, "functions.php");

  const DEBUG = true;

  /**
   * 启用字符串池
   */
  const ENABLE_STRING_POOL = true;

  /**
   * 启用字符串池压缩
   */
  const ENABLE_POOL_COMPRESS = true;

  /**
   * 最短字符串长度
   */
  const MIN_STRING_LENGTH = 2;

  /**
   * 最大字符串长度
   */
  const MAX_STRING_LENGTH = 500;

  /**
   * 排除目录
   */
  const EXCLUDES = ["node_modules", ".git", "vendor"];

  // ========================================

  buildContext.runtime ??= {};

  buildContext.strings ??= new Map();

  buildContext.constants ??= new Map();

  buildContext.functions ??= new Map();

  buildContext.hooks ??= new Map();

  // ========================================

  const phpFiles = [];

  const stringMap = buildContext.strings;

  const constantMap = buildContext.constants;

  /**
   * Runtime Function Name
   */
  const runtimeFunctionName = "KA_" + crypto.randomBytes(6).toString("hex");

  buildContext.runtime.stringPoolFunction = runtimeFunctionName;

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

  function shouldSkipString(str) {
    if (!str) {
      return true;
    }

    /**
     * PHP Template
     */
    if (
      str.includes("<?php") ||
      str.includes("<?= ") ||
      str.includes("<?=") ||
      str.includes("?>")
    ) {
      return true;
    }

    /**
     * 常量名
     */
    // if (constantMap.has(str)) {
    //   return true;
    // }

    /**
     * 太短
     */
    if (str.length < MIN_STRING_LENGTH) {
      return true;
    }

    /**
     * 太长
     */
    // if (str.length > MAX_STRING_LENGTH) {
    //   return true;
    // }

    /**
     * URL
     */
    // if (str.startsWith("http://") || str.startsWith("https://")) {
    //   return true;
    // }

    /**
     * HTML
     */
    // if (str.includes("<") || str.includes(">")) {
    //   return true;
    // }

    /**
     * SQL
     */
    // if (/\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b/i.test(str)) {
    //   return true;
    // }

    /**
     * WP Hook
     */
    // if (/^[a-z0-9_\-\/]+$/.test(str)) {
    //   return true;
    // }

    /**
     * 纯常量
     */
    // if (/^[A-Z0-9_]+$/.test(str)) {
    //   return true;
    // }

    /**
     * Runtime 自身
     */
    if (str.includes("KA_POOL")) {
      return true;
    }

    return false;
  }

  /**
   * 检测是否为 Compile-Time Context
   */
  function shouldSkipByContext(content, matchIndex) {
    const before = content.substring(Math.max(0, matchIndex - 300), matchIndex);

    return (
      /**
       * const A = 'xxx'
       */
      /\bconst\s+[A-Z0-9_]+\s*=\s*$/i.test(before) ||
      /**
       * public const A =
       */
      /\b(public|protected|private)\s+\$[A-Z0-9_]+\s*=\s*$/i.test(before) ||
      /**
       * enum case A = 'xxx'
       */
      /\bcase\s+[A-Z0-9_]+\s*=\s*$/i.test(before) ||
      /**
       * function test($a = 'xxx')
       */
      /function\s+[a-zA-Z0-9_]+\s*\([^)]*=\s*$/i.test(before) ||
      /**
       * fn($a = 'xxx')
       */
      /fn\s*\([^)]*=\s*$/i.test(before) ||
      /**
       * attribute:
       * #[Route('xxx')]
       */
      /#\[[^\]]*$/i.test(before)
    );
  }

  /**
   * 查找 Runtime 插入位置
   */
  function findInsertIndex(lines) {
    for (let i = 0; i < lines.length; i++) {
      if (lines[i].includes("if (!defined('ABSPATH'))")) {
        for (let j = i; j < lines.length; j++) {
          if (lines[j].trim() === "}") {
            return j + 1;
          }
        }
      }
    }

    return 1;
  }

  // ========================================
  // Scan Directory
  // ========================================

  async function scanDirectory(dir) {
    const entries = await fs.readdir(dir, {
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
  // Collect Strings
  // ========================================

  async function collectStrings() {
    /**
     * 只处理单引号
     */
    const stringRegex = /'((?:\\.|[^'\\])*)'/g;

    let stringId = stringMap.size;

    for (const file of phpFiles) {
      logger.log(`🔍 扫描字符串: ${path.relative(ROOT_DIR, file)}`);

      const content = await fs.readFile(file, "utf8");

      /**
       * Runtime 文件跳过
       */
      if (content.includes("KA_RUNTIME_START")) {
        continue;
      }

      let match;

      while ((match = stringRegex.exec(content)) !== null) {
        const value = match[1];

        const matchIndex = match.index;

        /**
         * Symbol Context
         */
        if (shouldSkipByContext(content, matchIndex)) {
          continue;
        }

        /**
         * Skip
         */
        if (shouldSkipString(value)) {
          continue;
        }

        /**
         * 已存在
         */
        if (stringMap.has(value)) {
          continue;
        }

        stringMap.set(value, stringId++);
      }
    }
  }

  // ========================================
  // Replace Strings
  // ========================================

  async function replaceStrings() {
    const stringRegex = /'((?:\\.|[^'\\])*)'/g;

    for (const file of phpFiles) {
      logger.log(`🔄 替换字符串: ${path.relative(ROOT_DIR, file)}`);

      let content = await fs.readFile(file, "utf8");

      /**
       * Runtime Skip
       */
      if (content.includes("KA_RUNTIME_START")) {
        continue;
      }

      content = content.replace(stringRegex, (match, value, offset) => {
        /**
         * Symbol Context
         */
        if (shouldSkipByContext(content, offset)) {
          return match;
        }

        /**
         * 未收录
         */
        if (!stringMap.has(value)) {
          return match;
        }

        const id = stringMap.get(value);

        return `${runtimeFunctionName}(${id})`;
      });

      await fs.writeFile(file, content, "utf8");
    }
  }

  // ========================================
  // Generate Runtime
  // ========================================

  function generateRuntimeCode() {
    const pool = {};

    for (const [value, id] of stringMap) {
      pool[id] = value;
    }

    const json = JSON.stringify(pool);

    let runtimePoolCode = "";

    if (ENABLE_POOL_COMPRESS) {
      const compressed = zlib.gzipSync(json).toString("base64");

      runtimePoolCode = `
if (!defined('KA_POOL')) {

    define(
        'KA_POOL',
        gzdecode(
            base64_decode(
                '${compressed}'
            )
        )
    );

}
`;
    } else {
      runtimePoolCode = `
if (!defined('KA_POOL')) {

    define(
        'KA_POOL',
        '${json.replace(/'/g, "\\'")}'
    );

}
`;
    }

    return `

/* KA_RUNTIME_START */

/* ========================================
 * KA String Pool Runtime
 * ======================================== */

${runtimePoolCode}

if (!function_exists('${runtimeFunctionName}')) {

    function ${runtimeFunctionName}($id)
    {
        static $pool = null;

        if ($pool === null) {

            $pool = json_decode(
                KA_POOL,
                true
            );

        }

        return $pool[$id] ?? '';
    }

}

/* ======================================== */

/* KA_RUNTIME_END */

`;
  }

  // ========================================
  // Inject Runtime
  // ========================================

  async function injectRuntime() {
    logger.log("⚡ 注入 Runtime");

    let content = await fs.readFile(ENTRY_FILE, "utf8");

    /**
     * 防止重复注入
     */
    if (content.includes("KA_RUNTIME_START")) {
      return;
    }

    const runtime = generateRuntimeCode();

    const lines = content.split(/\r?\n/);

    const insertIndex = findInsertIndex(lines);

    lines.splice(insertIndex, 0, "", runtime, "");

    content = lines.join("\n");

    await fs.writeFile(ENTRY_FILE, content, "utf8");
  }

  // ========================================
  // Main
  // ========================================

  try {
    console.log("🚀 开始 String Pool Build...\n");

    await scanDirectory(ROOT_DIR);

    console.log(`📦 共扫描 ${phpFiles.length} 个 PHP 文件\n`);

    /**
     * 收集字符串
     */
    await collectStrings();

    console.log(`📦 收集到 ${stringMap.size} 个字符串\n`);

    if (ENABLE_STRING_POOL) {
      /**
       * 替换字符串
       */
      await replaceStrings();

      /**
       * 注入 Runtime
       */
      await injectRuntime();
    }

    console.log("\n🎉 String Pool 完成");

    console.log(`⚡ Runtime Function: ${runtimeFunctionName}`);

    return buildContext;
  } catch (err) {
    console.error("❌ 执行失败:", err);
  }
};
