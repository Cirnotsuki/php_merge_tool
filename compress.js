const fs = require('fs/promises');
const fsSync = require('fs');
const path = require('path');
const { minify } = require('terser');

// ===================== 配置区域 =====================
const TARGET_DIR = path.resolve(__dirname, 'temp');
const BACKUP_ORIGINALS = false;

const ENABLE_COMPRESS = true;
const ENABLE_OBFUSCATE = true;
const ENABLE_BASE64_WRAP = true;
// ====================================================

/**
 * Base64 编码并直接用 eval 执行
 */
function wrapInBase64Eval(code) {
    const base64 = Buffer.from(code, 'utf-8').toString('base64');
    // 最直接的原位置执行
    // return `eval(atob('${base64}'));`;
    return code;
}

async function processJsCode(code) {
    let result = code;

    // 1. 压缩 & 混淆
    if (ENABLE_COMPRESS || ENABLE_OBFUSCATE) {
        try {
            const options = {
                compress: ENABLE_COMPRESS ? {
                    drop_console: false,
                    drop_debugger: true,
                    dead_code: true,
                    unused: true,
                } : false,
                mangle: ENABLE_OBFUSCATE ? {
                    toplevel: true,
                    properties: false,
                } : false,
                format: {
                    comments: false,
                    beautify: false,
                }
            };
            const terserResult = await minify(result, options);
            if (terserResult.error) throw terserResult.error;
            result = terserResult.code;
        } catch (err) {
            console.error(`    ⚠️  混淆失败，保留原代码继续:`, err.message);
        }
    }

    // 2. Base64 + Eval 包装
    if (ENABLE_BASE64_WRAP) {
        result = wrapInBase64Eval(result);
    }

    return result;
}

async function processDirectory(dir) {
    const entries = await fs.readdir(dir, { withFileTypes: true });

    for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);

        if (entry.isDirectory()) {
            await processDirectory(fullPath);
            continue;
        }

        const ext = path.extname(entry.name).toLowerCase();
        if (ext !== '.js' || entry.name.includes('.min.')) continue;

        const relativePath = path.relative(TARGET_DIR, fullPath);
        console.log(`🔨 处理: ${relativePath}`);

        try {
            const content = await fs.readFile(fullPath, 'utf-8');
            const originalSize = Buffer.byteLength(content, 'utf8');

            const finalCode = await processJsCode(content);

            if (BACKUP_ORIGINALS) {
                await fs.rename(fullPath, fullPath + '.backup');
            }

            await fs.writeFile(fullPath, finalCode, 'utf-8');

            const finalSize = Buffer.byteLength(finalCode, 'utf-8');
            const sizeDiff = finalSize > originalSize ? `(+${((finalSize / originalSize - 1) * 100).toFixed(1)}%)` : `(-${((1 - finalSize / originalSize) * 100).toFixed(1)}%)`;

            console.log(`   ✅ 完成 (原始: ${(originalSize / 1024).toFixed(1)}kb -> 最终: ${(finalSize / 1024).toFixed(1)}kb ${sizeDiff})`);

        } catch (err) {
            console.error(`   ❌ 错误:`, err.message);
        }
    }
}

async function main() {
    console.log('========================================');
    console.log('   JS Protector');
    console.log(`   步骤: ${ENABLE_COMPRESS ? '压缩' : ''} ${ENABLE_OBFUSCATE ? '-> 混淆' : ''} ${ENABLE_BASE64_WRAP ? '-> Base64封装' : ''}`);
    console.log('========================================\n');

    if (!fsSync.existsSync(TARGET_DIR)) {
        console.error(`❌ 目录不存在: ${TARGET_DIR}`);
        process.exit(1);
    }

    await processDirectory(TARGET_DIR);
    console.log('\n🎉 全部处理完成！');
}

main();