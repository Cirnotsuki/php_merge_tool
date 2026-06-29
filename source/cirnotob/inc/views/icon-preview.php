<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * 提取CSS文件中的所有类名
 * @param string $filePath CSS文件路径
 * @return array 提取的类名数组
 */
function extractCssClasses($filePath)
{
    $icons = [];
    $lineNum = 0;

    // 读取文件内容（处理编码）
    $content = file_get_contents($filePath);
    $content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content));

    // 移除注释
    $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content);

    // 按行解析
    $lines = explode("\n", $content);

    $iconClass = '';

    foreach ($lines as $line) {
        $lineNum++;
        $line = trim($line);

        if (empty($line))
            continue;

        // 匹配基类：.fa 或 .fa-name 这种
        if (empty($iconClass) && preg_match('/\.([a-zA-Z0-9_\-]+)(,|\s{).*/', $line, $matches)) {
            $iconClass = $matches[1];
            continue;
        }

        if (empty($iconClass)) {
            continue;
        }

        // ✅ 修复：支持 fa-user、fa-name-o、fa-icon-lg 等带短横线的类
        $pattern = '/\.' . preg_quote($iconClass, '/') . '-([a-zA-Z0-9_\-]+):before(,|\s{).*/';

        if (preg_match($pattern, $line, $matches)) {
            $icons[] = $matches[1];
        }
    }

    return [
        'path' => $filePath,
        'class' => $iconClass,
        'icons' => $icons,
        'content' => $content,
    ];
}

/**
 * 遍历目录下的所有CSS文件
 * @param string $dir 目录路径
 * @return array 文件路径列表
 */
function scanCssFiles($dir, $allowedExt)
{
    $files = [];
    $dirHandle = opendir($dir);

    if (!$dirHandle)
        return $files;

    while (($file = readdir($dirHandle)) !== false) {
        if ($file == '.' || $file == '..')
            continue;

        $filePath = $dir . $file;
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (is_file($filePath) && in_array($ext, $allowedExt)) {
            if (str_ends_with($filePath, 'min.' . $ext)) {
                continue;
            }
            $files[] = $filePath;
        }
    }

    closedir($dirHandle);
    return $files;
}

/**
 * 计算两个绝对路径之间的相对路径
 * @param string $from 源路径（如当前PHP脚本的绝对路径）
 * @param string $to 目标路径（如CSS文件的绝对路径）
 * @return string 相对路径
 */
function getRelativePath($from, $to)
{
    // 统一路径分隔符为 /（兼容Windows）
    $from = str_replace(DIRECTORY_SEPARATOR, '/', realpath($from));
    $to = str_replace(DIRECTORY_SEPARATOR, '/', realpath($to));

    // 拆分路径为目录数组
    $fromParts = explode('/', rtrim($from, '/'));
    $toParts = explode('/', rtrim($to, '/'));

    // 移除文件名（如果源路径是文件）
    if (is_file($from)) {
        array_pop($fromParts);
    }
    // 移除文件名（如果目标路径是文件）
    if (is_file($to)) {
        $toFileName = array_pop($toParts);
    } else {
        $toFileName = '';
    }

    // 找到公共目录的最后索引
    $commonIndex = 0;
    $maxIndex = min(count($fromParts), count($toParts));
    while ($commonIndex < $maxIndex && $fromParts[$commonIndex] === $toParts[$commonIndex]) {
        $commonIndex++;
    }

    // 计算需要回退的层级（用 ../）
    $backSteps = count($fromParts) - $commonIndex;
    $relativeParts = [];
    for ($i = 0; $i < $backSteps; $i++) {
        $relativeParts[] = '..';
    }

    // 拼接目标路径的剩余部分
    $relativeParts = array_merge($relativeParts, array_slice($toParts, $commonIndex));
    if (!empty($toFileName)) {
        $relativeParts[] = $toFileName;
    }

    // 处理根目录情况，生成最终相对路径
    $relativePath = implode('/', $relativeParts);
    // 空路径表示当前目录，返回 ./
    return $relativePath === '' ? './' : $relativePath;
}

/**
 * CSS 类名提取工具（带图标预览）
 * 功能：遍历CSS文件提取类名，生成含图标预览的可视化表格
 */

// ===================== 配置项（根据你的需求修改） =====================
define('ICON_CSS_DIRS', [get_stylesheet_directory() . '/css/icons/css/']);
define('ALLOWED_EXT', ['css']); // 允许解析的文件后缀

add_action('admin_enqueue_scripts', 'load_font_css');
function load_font_css($hook)
{
    // 只在颜色管理页面加载
    if ($hook !== 'appearance_page_custom-icon-preview') return;

    wp_enqueue_style('icon-preview', get_stylesheet_directory_uri() . '/css/icon-preview.css', [], '1.0.0');
    // wp_enqueue_script('global-color', get_stylesheet_directory_uri() . '/js/global-color.js', ['jquery'], '1.0.0', true);
    // ===================== 核心执行逻辑 =====================
    foreach (ICON_CSS_DIRS as $cssDir) {
        $cssFiles = scanCssFiles($cssDir, ALLOWED_EXT);
        foreach ($cssFiles as $file) {
            wp_enqueue_style("cirnotob-icon-preview", path_join(get_stylesheet_directory_uri(), getRelativePath(get_stylesheet_directory(), $file)));
        }
    }
}

function render_custom_custom_icon_preview()
{
    // 初始化结果数组
    $iconFonts = [];

    // ===================== 核心执行逻辑 =====================
    foreach (ICON_CSS_DIRS as $cssDir) {
        $cssFiles = scanCssFiles($cssDir, ALLOWED_EXT);
        foreach ($cssFiles as $file) {
            $iconFonts[] = extractCssClasses($file);
        }
    }
?>
    <div class="cirnotob-icon-preview-wrap">
        <h1>图标预览</h1>

        <?php if (empty($iconFonts)): ?>
            <tr>
                <td colspan="6" style="text-align: center; color: #999;">未提取到任何图标相关类名</td>
            </tr>
        <?php endif ?>
        <!-- 类名表格（新增图标预览列） -->
        <?php foreach ($iconFonts as $font):
            $iconClass = $font['class'];
        ?>
            <div>图标大类: <?= $iconClass ?></div>
            <div class="icon-preview-box">
                <?php
                foreach ($font['icons'] as $name): ?>
                    <div class="icon-preview-item">
                        <!-- 图标预览 -->
                        <div class="icon-preview">
                            <i class="<?= $iconClass . ' ' . $iconClass . '-' . $name ?> " aria-hidden="true"></i>
                        </div>
                        <!-- 图标标签（指定格式） -->
                        <div class="icon-tag">
                            <?= $iconClass . '-' . $name ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endforeach; ?>
    </div>
<?php }
?>