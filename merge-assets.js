const fs = require('fs').promises;
const fsSync = require('fs');
const path = require('path');

// ===================== 配置 =====================
const SCAN_DIR = path.resolve(__dirname, 'temp');
const TARGET_JS = 'theme.js';
const TARGET_CSS = 'modify-style.css';
const EXCLUDED_CSS = 'style.css'; // 这个文件不碰
// ==================================================

/**
 * 递归获取所有文件
 */
async function getAllFiles(dir) {
    let files = [];
    const items = await fs.readdir(dir, { withFileTypes: true });
    for (const item of items) {
        const full = path.join(dir, item.name);
        if (item.isDirectory()) {
            files = files.concat(await getAllFiles(full));
        } else {
            files.push(full);
        }
    }
    return files;
}

/**
 * 删除所有 .less
 */
async function removeLessFiles(files) {
    const lessFiles = files.filter(f => path.extname(f).toLowerCase() === '.less');
    for (const file of lessFiles) {
        await fs.unlink(file);
        console.log(`🗑️  删除LESS: ${path.basename(file)}`);
    }
}

/**
 * 删除已合并文件
 */
async function deleteMergedFiles(filesToDelete) {
    for (const file of filesToDelete) {
        await fs.unlink(file);
        console.log(`🗑️  已删除: ${path.basename(file)}`);
    }
}

/**
 * 递归删除空文件夹
 */
async function removeEmptyFolders(dir) {
    const items = await fs.readdir(dir, { withFileTypes: true });
    for (const item of items) {
        const full = path.join(dir, item.name);
        if (item.isDirectory()) await removeEmptyFolders(full);
    }
    const after = await fs.readdir(dir);
    if (after.length === 0 && dir !== SCAN_DIR) {
        await fs.rmdir(dir);
        console.log(`🗑️  删除空文件夹: ${path.basename(dir)}`);
    }
}

/**
 * 压缩 CSS：去注释、去换行、去多余空格
 */
function compressCss(content) {
    return content
        .replace(/\/\*[\s\S]*?\*\//g, '')
        .replace(/\/\/[^\n]*/g, '')
        .replace(/\s+/g, ' ')
        .replace(/\{\s+/g, '{')
        .replace(/\s+\}/g, '}')
        .replace(/;\s+/g, ';')
        .trim();
}

/**
 * 合并文件（自动排除 style.css）
 */
async function mergeFiles(files, targetName, ext) {
    const target = files.find(f => path.basename(f) === targetName);
    if (!target) return [];

    const others = files.filter(f =>
        path.basename(f) !== targetName &&
        path.basename(f) !== EXCLUDED_CSS && // 排除 style.css
        path.extname(f).toLowerCase() === ext
    );
    if (others.length === 0) return [];

    console.log(`\n🔗 合并到 ${targetName}:`);
    const original = await fs.readFile(target, 'utf8');
    const parts = [];

    for (const file of others) {
        console.log(`   + ${path.basename(file)}`);
        const c = await fs.readFile(file, 'utf8');
        parts.push(`/* ${path.basename(file)} */\n${c}`);
    }

    let final = parts.join('\n') + '\n\n' + original;

    if (ext === '.css') {
        console.log(`📦 正在压缩 ${targetName} (去换行/去空格)`);
        final = compressCss(final);
    }

    await fs.writeFile(target, final, 'utf8');
    return others;
}

/**
 * 主程序
 */
async function run() {
    console.log('========================================');
    console.log('  主题资源一体化处理工具（最终版）');
    console.log('  ✅ 合并 JS/CSS   ✅ 删源文件   ✅ 删LESS');
    console.log('  ✅ 压缩 CSS     ✅ 删空目录   ✅ 保留 style.css');
    console.log('========================================\n');

    if (!fsSync.existsSync(SCAN_DIR)) {
        console.error('❌ 找不到 temp 文件夹');
        return;
    }

    const allFiles = await getAllFiles(SCAN_DIR);
    const assets = allFiles.filter(f => ['.js', '.css'].includes(path.extname(f).toLowerCase()));

    // 1. 删除LESS
    await removeLessFiles(allFiles);

    // 2. 合并JS
    const deletedJs = await mergeFiles(assets, TARGET_JS, '.js');

    // 3. 合并CSS + 压缩
    const deletedCss = await mergeFiles(assets, TARGET_CSS, '.css');

    // 4. 删除已合并文件
    await deleteMergedFiles([...deletedJs, ...deletedCss]);

    // 5. 删除空文件夹
    await removeEmptyFolders(SCAN_DIR);

    console.log('\n🎉 🚀 全部 100% 完成！');
    console.log(`✅ 安全提醒：${EXCLUDED_CSS} 已保护，未参与任何操作`);
}

run();