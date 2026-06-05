const fs = require('fs/promises');
const fsSync = require('fs');
const path = require('path');

// ===================== 配置区域 =====================
const TARGET_DIR = path.resolve(__dirname, './temp');
const BACKUP_ORIGINALS = false;
const PROTECTED_BLOCKS = ['style', 'script', 'textarea'];
// ====================================================

// 清理保护块内部：去缩进 + 去注释
function cleanBlockContent(content) {
    return content
        .replace(/^[ \t]+/gm, '') // 去缩进
        .replace(/\/\/[^\n]*/g, '') // 去单行注释
        .replace(/\/\*[\s\S]*?\*\//g, ''); // 去多行注释
}

// 提取保护块（已清理内部注释）
function extractProtectedBlocks(code) {
    const blocks = [];
    let index = 0;
    const regex = new RegExp(`(<(${PROTECTED_BLOCKS.join('|')})\\b[^>]*>)([\\s\\S]*?)(<\\/\\2>)`, 'gi');

    const processed = code.replace(regex, (match, open, tag, content, close) => {
        const cleaned = cleanBlockContent(content);
        blocks.push({ tag, open, content: cleaned, close });
        return `§P_${index++}§`;
    });

    return { processed, blocks };
}

// 修复 PHP 标签
function fixPhpTags(code) {
    code = code.replace(/<\?php([^\s])/g, '<?php $1');
    code = code.replace(/<\?=([^\s])/g, '<?= $1');
    code = code.replace(/([^\s])\?>/g, '$1 ?>');
    return code;
}

// 移除所有注释
function removeAllComments(code) {
    let out = '';
    let inString = false, stringChar = '';
    let inMultiComment = false, inSingleComment = false;

    for (let i = 0; i < code.length; i++) {
        const c = code[i], n = code[i + 1];
        if (inMultiComment) {
            if (c === '*' && n === '/') { inMultiComment = false; i++; }
            continue;
        }
        if (inSingleComment) {
            if (c === '\n') inSingleComment = false;
            continue;
        }
        if ((c === "'" || c === '"') && !inString) {
            inString = true; stringChar = c; out += c; continue;
        }
        if (inString) {
            out += c;
            if (c === stringChar && code[i - 1] !== '\\') inString = false;
            continue;
        }
        if (c === '/' && n === '*') { inMultiComment = true; i++; continue; }
        if (c === '/' && n === '/') { inSingleComment = true; i++; continue; }
        if (c === '#') { inSingleComment = true; continue; }
        out += c;
    }
    return out;
}

// 安全紧凑
function safeCompact(code) {
    return code
        .replace(/\t/g, ' ')
        .replace(/ +/g, ' ')
        .replace(/\n\s+/g, '\n')
        .replace(/^\s+|\s+$/g, '')
        .replace(/\n+/g, '\n');
}

// 回填保护块
function restoreBlocks(code, blocks) {
    blocks.forEach((b, i) => {
        code = code.replace(`§P_${i}§`, b.open + b.content + b.close);
    });
    return code;
}

// 主压缩
function minifyPhp(code) {
    code = fixPhpTags(code);
    const { processed, blocks } = extractProtectedBlocks(code);
    const noComment = removeAllComments(processed);
    const compact = safeCompact(noComment);
    const final = restoreBlocks(compact, blocks);
    return fixPhpTags(final);
}

// ------------------- 执行 -------------------
async function processFile(file) {
    const rel = path.relative(TARGET_DIR, file);
    console.log(`🔨 处理: ${rel}`);
    try {
        let content = await fs.readFile(file, 'utf8').then(c => c.replace(/^\uFEFF/, ''));
        const minified = minifyPhp(content);
        if (BACKUP_ORIGINALS) await fs.rename(file, file + '.backup');
        await fs.writeFile(file, minified, 'utf8');
        console.log(`   ✅ 完成`);
    } catch (e) {
        console.error(`   ❌ 失败:`, e.message);
    }
}

async function walk(dir) {
    const items = await fs.readdir(dir, { withFileTypes: true });
    for (const item of items) {
        const full = path.join(dir, item.name);
        if (item.isDirectory()) await walk(full);
        else if (item.name.endsWith('.php')) await processFile(full);
    }
}

(async () => {
    console.log('========================================');
    console.log('   PHP 压缩（已删除 style/script 内部注释）');
    console.log('========================================');
    if (!fsSync.existsSync(TARGET_DIR)) { console.error('❌ 目录不存在'); return; }
    await walk(TARGET_DIR);
    console.log('\n🎉 全部完成！');
})();