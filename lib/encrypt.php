<?php

/**
 * http://localhost:8001/wp-content/themes/cirnotob/encrypt.php
 */
error_reporting(0);
ini_set('display_errors', 0);

/*!
 * PHP-Screw-Mini 安全加密工具（ WordPress 专用 最终修复版 ）
 * 功能：不覆盖、不破坏、自动备份、生成加密主题/插件
 */

// ==================== 配置 ====================
$KEY = "WP_Secure_2025_Remaster";
$SOURCE_DIR = __DIR__;
$ENCRYPT_OUTPUT = __DIR__ . "_encrypted";
$BACKUP_DIR = __DIR__ . "_backup";

$EXCLUDE_FILES = [
    basename(__FILE__),
    'functions.php',
    'style.css',
    'screenshot.png',
    'readme.txt',
    'LICENSE'
];

$EXCLUDE_DIRS = [
    'css',
    'js',
    'images',
    'assets',
    'fonts',
    'dist',
    'vendor'
];

// ==================================================
// 安全创建目录
// ==================================================
if (!is_dir($BACKUP_DIR)) mkdir($BACKUP_DIR, 0755, true);
if (!is_dir($ENCRYPT_OUTPUT)) mkdir($ENCRYPT_OUTPUT, 0755, true);

// ==================================================
// 正确的加密函数（已修复 php_strip_whitespace 报错）
// ==================================================
function encrypt_file($file_path, $key)
{
    $code = php_strip_whitespace($file_path); // 必须传路径！
    $code = str_replace(['<?php', '?>'], '', $code);

    $len = strlen($code);
    $k_len = strlen($key);
    $result = '';

    for ($i = 0; $i < $len; $i++) {
        $result .= $code[$i] ^ $key[$i % $k_len];
    }

    return base64_encode($result);
}

// ==================================================
// 备份源码
// ==================================================
function full_backup($src, $dst)
{
    $dir = dir($src);
    if (!is_dir($dst)) mkdir($dst, 0755, true);

    while (($file = $dir->read()) !== false) {
        if ($file == '.' || $file == '..') continue;
        $src_path = $src . DIRECTORY_SEPARATOR . $file;
        $dst_path = $dst . DIRECTORY_SEPARATOR . $file;

        if (is_dir($src_path)) {
            full_backup($src_path, $dst_path);
            continue;
        }
        copy($src_path, $dst_path);
    }
    $dir->close();
}

// ==================================================
// 生成加密版
// ==================================================
function build_encrypted($src, $dst)
{
    global $KEY, $EXCLUDE_FILES, $EXCLUDE_DIRS;

    $dir = dir($src);
    if (!is_dir($dst)) mkdir($dst, 0755, true);

    while (($file = $dir->read()) !== false) {
        if ($file == '.' || $file == '..') continue;

        $src_path = $src . DIRECTORY_SEPARATOR . $file;
        $dst_path = $dst . DIRECTORY_SEPARATOR . $file;

        if (is_dir($src_path)) {
            if (in_array($file, $EXCLUDE_DIRS)) {
                build_encrypted($src_path, $dst_path);
                continue;
            }
            build_encrypted($src_path, $dst_path);
            continue;
        }

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($ext !== 'php') {
            copy($src_path, $dst_path);
            continue;
        }

        if (in_array($file, $EXCLUDE_FILES)) {
            copy($src_path, $dst_path);
            continue;
        }

        // 加密
        $encrypted_code = encrypt_file($src_path, $KEY);

        $output = '<?php
// PHP-SCREW-MINI SAFE
if (!defined("ABSPATH")) exit;
$__c = base64_decode("' . $encrypted_code . '");
$__k = "' . $KEY . '";
$__r = "";
$__ln = strlen($__c);
$__kl = strlen($__k);
for ($i=0;$i<$__ln;$i++) $__r .= $__c[$i] ^ $__k[$i % $__kl];
eval($__r);
?>';

        file_put_contents($dst_path, $output);
        echo "✅ 已加密：$dst_path<br>";
    }
    $dir->close();
}

// ==================================================
// 执行
// ==================================================
echo "<h3>正在备份源码...</h3>";
full_backup($SOURCE_DIR, $BACKUP_DIR);

echo "<h3>正在生成加密版...</h3>";
build_encrypted($SOURCE_DIR, $ENCRYPT_OUTPUT);

echo "<h2 style='color:green'>✅ 全部完成！</h2>";
echo "原始文件：<b>完全没有修改</b><br>";
echo "备份目录：<b>$BACKUP_DIR</b><br>";
echo "加密目录：<b>$ENCRYPT_OUTPUT</b><br>";

?>
<style>
    body {
        background: #1e1e1e;
        color: #eee;
        font-family: Arial;
        padding: 30px
    }
</style>