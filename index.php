<?php
require __DIR__ . '/vendor/autoload.php';

use iSerter\Obfuscator\Obfuscator;
use iSerter\Obfuscator\Config;

// ===================== 配置 =====================
$sourceDir     = __DIR__ . '/temp';               // 你的源码目录
$outputDir     = __DIR__ . '/temp-obfuscated';    // 混淆后输出
$excludeFiles  = ['style.css'];                   // 保留不混淆
$phpExtensions = ['php'];                         // 只混淆PHP
// =================================================

// 清空/创建输出目录
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);

// 混淆器配置
$config = (new Config())
    ->setVariableRename(true)        // 重命名局部变量
    ->setFunctionRename(false)       // 不重命名函数（WordPress 钩子安全）
    ->setClassRename(false)          // 不重命名类
    ->setStringEncoding(true)        // 加密字符串
    ->setControlFlowFlatten(true)    // 打乱控制流
    ->setDeadCodeInjection(true);    // 插入垃圾代码干扰

$obfuscator = new Obfuscator($config);

// 遍历目录
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    $path = $file->getPathname();
    $rel = str_replace($sourceDir . '/', '', $path);
    $outPath = $outputDir . '/' . $rel;

    // 排除目录 / 保留文件
    if ($file->isDir()) {
        if (!is_dir($outPath)) mkdir($outPath, 0755, true);
        continue;
    }
    if (in_array(basename($path), $excludeFiles)) {
        copy($path, $outPath);
        echo "✅ 保留: $rel\n";
        continue;
    }
    if (!in_array($file->getExtension(), $phpExtensions)) {
        copy($path, $outPath);
        continue;
    }

    // 混淆PHP
    try {
        $code = file_get_contents($path);
        $obf = $obfuscator->obfuscate($code);
        file_put_contents($outPath, $obf);
        echo "🔐 混淆: $rel\n";
    } catch (Exception $e) {
        echo "❌ 失败: $rel ({$e->getMessage()})\n";
        copy($path, $outPath);
    }
}

echo "\n🎉 混淆完成：$outputDir\n";
