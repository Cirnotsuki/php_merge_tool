const fs = require('fs/promises');
const fsSync = require('fs');
const path = require('path');

// ===================== 配置区域 =====================
const global_dir = path.resolve(__dirname, './cirnotob');
const ENTRY_FILE = 'functions.php';
const OUTPUT_DIR = path.resolve(__dirname, './temp');
const DEBUG = true;
const REQUIRE_TYPES = ['require', 'require_once', 'include', 'include_once'];
const EXCLUDES = ['vendor', 'node_modules', '.git'];
// ====================================================

const processedFiles = new Set();

function isExcluded(filePath) {
    const relativePath = path.relative(global_dir, filePath);
    return EXCLUDES.some(ex =>
        relativePath === ex || relativePath.startsWith(ex + path.sep)
    );
}

/**
 * 解析变量（保持不变，确认支持缩进）
 */
function parseVariablesFromFile(lines, currentFileDir) {
    const variables = {};
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const pureLine = line.split('//')[0].trim();
        // 匹配 $var = value; 支持行首空格
        const varMatch = pureLine.match(/^\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*(.+?)\s*;$/);
        if (varMatch) {
            const [, varName, rawValueExpr] = varMatch;
            let resolvedValue = rawValueExpr;
            resolvedValue = resolvedValue.replace(/__DIR__/g, `'${currentFileDir}'`);
            resolvedValue = resolvedValue.replace(/get_stylesheet_directory\(\)/g, `'${global_dir}'`);
            variables[varName] = resolvedValue;
            if (DEBUG) console.log(`  📦 收集变量 [行 ${i + 1}]: \$${varName} = ${resolvedValue}`);
        }
    }
    return variables;
}

/**
 * 【核心重写】智能提取 require 后面的路径表达式
 * 不再依赖复杂正则，而是逐个字符扫描，处理字符串、括号、拼接等
 */
function extractRequireExpression(line) {
    // 1. 快速定位关键词
    const typeMatch = line.match(new RegExp(`\\b(${REQUIRE_TYPES.join('|')})\\b`, 'i'));
    if (!typeMatch) return null;

    const type = typeMatch[1];
    const startIndex = typeMatch.index + type.length;
    const content = line.substring(startIndex);

    let expr = '';
    let i = 0;
    let inString = false;
    let stringChar = '';
    let parenDepth = 0;
    let foundStart = false;

    // 跳过开头空白
    while (i < content.length && /\s/.test(content[i])) i++;

    // 检查是否有括号
    if (content[i] === '(') {
        parenDepth = 1;
        i++;
        foundStart = true;
    } else {
        foundStart = true; // 无括号模式，直接开始
    }

    // 开始扫描内容直到分号或结束
    for (; i < content.length; i++) {
        const char = content[i];

        // 字符串处理逻辑
        if ((char === '"' || char === "'") && !inString) {
            inString = true;
            stringChar = char;
            expr += char;
            continue;
        }
        if (inString && char === stringChar) {
            // 检查是否是转义的 \" 或 \'
            if (i > 0 && content[i - 1] === '\\') {
                expr += char;
                continue;
            }
            inString = false;
            expr += char;
            continue;
        }

        if (inString) {
            expr += char;
            continue;
        }

        // 非字符串逻辑
        if (char === '(') {
            parenDepth++;
            expr += char;
        } else if (char === ')') {
            parenDepth--;
            if (parenDepth < 0) break; // 括号闭合结束
            expr += char;
            if (parenDepth === 0 && foundStart) {
                i++; // 跳过这个右括号
                break;
            }
        } else if (char === ';') {
            break; // 遇到分号结束
        } else {
            expr += char;
        }
    }

    return { type: type, expression: expr.trim() };
}

/**
 * 解析路径（保持不变）
 */
function resolveSafePath(bracketContent, currentFileDir, variables) {
    let expr = bracketContent;
    if (DEBUG) console.log(`     原始表达式: ${expr}`);

    expr = expr.replace(/__DIR__/g, `'${currentFileDir}'`);
    expr = expr.replace(/get_stylesheet_directory\(\)/g, `'${global_dir}'`);

    // 替换变量 (注意要全字匹配)
    // 先按变量名长度排序，防止 $route 替换了 $route_public 的一部分
    const sortedVars = Object.entries(variables).sort((a, b) => b[0].length - a[0].length);
    for (const [varName, varValue] of sortedVars) {
        expr = expr.replace(new RegExp(`\\$${varName}\\b`, 'g'), varValue);
    }

    if (DEBUG) console.log(`     替换后表达式: ${expr}`);

    const stringMatch = expr.match(/['"](.*?)['"]/g);
    if (!stringMatch) {
        if (DEBUG) console.log(`     ⚠️  未提取到路径片段`);
        return null;
    }

    const pathStr = stringMatch.map(str => str.replace(/['"]/g, '')).join('');
    if (DEBUG) console.log(`     拼接后路径: ${pathStr}`);

    let finalPath = path.normalize(pathStr);
    if (!path.isAbsolute(finalPath)) {
        finalPath = path.resolve(currentFileDir, finalPath);
    }

    return finalPath;
}

/**
 * 处理文件主逻辑
 */
async function processPhpFile(filePath) {
    if (processedFiles.has(filePath)) {
        if (DEBUG) console.log(`⏭️  跳过已处理: ${path.basename(filePath)}`);
        return { content: '', alreadyProcessed: true };
    }

    if (isExcluded(filePath)) {
        if (DEBUG) console.log(`🚫 跳过排除项: ${path.relative(global_dir, filePath)}`);
        return { content: '', excluded: true };
    }

    if (!fsSync.existsSync(filePath)) {
        if (DEBUG) console.warn(`⚠️  文件不存在: ${filePath}`);
        return { content: '', notFound: true };
    }

    processedFiles.add(filePath);
    const currentFileDir = path.dirname(filePath);
    const relativeFilePath = path.relative(global_dir, filePath);

    if (DEBUG) console.log(`\n====================================`);
    if (DEBUG) console.log(`📄 开始处理: ${relativeFilePath}`);
    if (DEBUG) console.log(`====================================`);

    let content = await fs.readFile(filePath, 'utf8');
    content = content.replace(/^\uFEFF/, '');
    const lines = content.split('\n');

    if (DEBUG) console.log(`\n  [1/4] 扫描变量定义...`);
    const variables = parseVariablesFromFile(lines, currentFileDir);
    if (DEBUG) console.log(`  共收集到 ${Object.keys(variables).length} 个变量`);

    if (DEBUG) console.log(`\n  [2/4] 处理依赖语句...`);
    const resultLines = [];

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];

        // 【核心调用】使用新的提取器
        const requireInfo = extractRequireExpression(line);

        if (!requireInfo) {
            resultLines.push(line);
            continue;
        }

        const { type: requireType, expression } = requireInfo;
        if (DEBUG) console.log(`\n  ✅ 找到${requireType} [行 ${i + 1}]: ${expression}`);

        const finalPath = resolveSafePath(expression, currentFileDir, variables);
        const fileExists = finalPath && fsSync.existsSync(finalPath);

        if (DEBUG) {
            console.log(`     📂 最终路径: ${finalPath || '解析失败'}`);
            console.log(`     📂 文件是否存在: ${fileExists}`);
        }

        if (fileExists) {
            const { content: childContent, alreadyProcessed, notFound, excluded } = await processPhpFile(finalPath);

            if (!alreadyProcessed && !notFound && !excluded && childContent) {
                let cleanContent = childContent
                    .replace(/^\s*<\?php(\s|[\r\n])*/i, '')
                    .replace(/\s*\?>\s*$/i, '');

                resultLines.push(`// ===== 合并自: ${path.relative(global_dir, finalPath)} =====`);
                resultLines.push(cleanContent);
                resultLines.push(`// ===== 合并结束 =====`);
                continue;
            }
        }

        if (DEBUG) console.log(`     ⚠️  保留原语句`);
        resultLines.push(line);
    }

    return { content: resultLines.join('\n') };
}

async function copyUnprocessedFiles(currentDir) {
    const files = await fs.readdir(currentDir);
    for (const file of files) {
        const fullPath = path.join(currentDir, file);

        if (isExcluded(fullPath)) continue;

        const stat = await fs.stat(fullPath);

        if (stat.isDirectory()) {
            await copyUnprocessedFiles(fullPath);
            continue;
        }

        if (processedFiles.has(fullPath)) continue;

        const relativePath = path.relative(global_dir, fullPath);
        const outputPath = path.join(OUTPUT_DIR, relativePath);
        await fs.mkdir(path.dirname(outputPath), { recursive: true });
        await fs.copyFile(fullPath, outputPath);
        if (DEBUG) console.log(`✅ 复制未合并文件: ${relativePath}`);
    }
}

async function main() {
    try {
        const entryPath = path.resolve(global_dir, ENTRY_FILE);
        if (!fsSync.existsSync(global_dir)) throw new Error(`项目目录不存在: ${global_dir}`);
        if (!fsSync.existsSync(entryPath)) throw new Error(`入口文件不存在: ${entryPath}`);

        if (fsSync.existsSync(OUTPUT_DIR)) await fs.rm(OUTPUT_DIR, { recursive: true, force: true });
        await fs.mkdir(OUTPUT_DIR, { recursive: true });

        console.log('🚀 开始合并PHP文件...');
        const { content: mergedContent } = await processPhpFile(entryPath);
        const outputEntryPath = path.join(OUTPUT_DIR, ENTRY_FILE);
        await fs.writeFile(outputEntryPath, mergedContent, 'utf8');

        console.log('\n🔍 复制未被合并的文件...');
        await copyUnprocessedFiles(global_dir);

        console.log('\n🎉 全部操作完成！');
        console.log(`📦 已合并文件总数: ${processedFiles.size}`);
        console.log(`📂 最终输出目录: ${OUTPUT_DIR}`);

    } catch (err) {
        console.error('❌ 执行失败:', err);
    }
}

main();