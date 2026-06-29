<?php

/**
 * WordPress 全局颜色管理插件
 * 功能：新增/删除颜色、拾色器选择、数据持久化存储
 */
if (!defined('ABSPATH')) exit; // 防止直接访问

// 2. 注册设置项（存储颜色数据）


add_action('admin_init', 'global_color_manager_register_settings');
function global_color_manager_register_settings()
{
    register_setting(
        CIRNOTOB_GLOBAL_COLOR_GROUP, // 设置组名
        CIRNOTOB_GLOBAL_COLOR_NAME,       // 选项名
        [
            'sanitize_callback' => 'global_color_manager_sanitize_colors',
            'default' => CIRNOTOB_GLOBAL_COLOR_DEFAULT
        ]
    );
}


// 3. 数据验证与清理
function global_color_manager_sanitize_colors($value)
{
    if (empty($value)) return CIRNOTOB_GLOBAL_COLOR_DEFAULT;

    $colors = json_decode($value, true);
    if (!is_array($colors)) return CIRNOTOB_GLOBAL_COLOR_DEFAULT;

    $sanitized = [];
    foreach ($colors as $color) {
        // 验证必要字段
        if (empty($color['name']) || empty($color['code'])) continue;

        // 清理颜色代码（确保是合法的十六进制颜色）
        $colorCode = sanitize_hex_color($color['code']);
        if (!$colorCode) continue;

        $sanitized[] = [
            'id' => isset($color['id']) ? intval($color['id']) : uniqid(),
            'name' => sanitize_text_field($color['name']),
            'classes' => sanitize_text_field($color['classes']),
            'code' => $colorCode
        ];
    }
    return json_encode($sanitized);
}

// 4. 渲染颜色管理页面
function render_custom_color_manager()
{
    // // 调试：检查设置是否注册成功
    // $registered = get_registered_settings();
    // var_dump('注册的设置项：', isset($registered[CIRNOTOB_GLOBAL_COLOR_NAME])); // 应该输出 true
    // var_dump('设置组：', CIRNOTOB_GLOBAL_COLOR_GROUP); // 查看你的设置组名
    // var_dump('选项名：', CIRNOTOB_GLOBAL_COLOR_NAME); // 查看你的选项名（应该是 hc_global_colors）
    // die();
    // 获取已保存的颜色数据
    $colorsJson = get_option(CIRNOTOB_GLOBAL_COLOR_NAME);
    $colors = json_decode($colorsJson, true) ?: [];

    // var_dump($colorsJson); // 会在后台页面顶部显示当前页面的 hook 名称
    // die(); // 暂停执行，方便查看
?>
    <div class="global-color-wrap"
        data-colors="<?= urlencode(json_encode($colors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)); ?>"
        data-color-name="<?= CIRNOTOB_GLOBAL_COLOR_NAME ?>">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form method="post" action="options.php" id="color-manager-form">
            <?php
            settings_fields(CIRNOTOB_GLOBAL_COLOR_GROUP); // 安全字段
            do_settings_sections(CIRNOTOB_GLOBAL_COLOR_GROUP);
            ?>
            <input type="hidden" name="<?= CIRNOTOB_GLOBAL_COLOR_NAME ?>">

            <div class="cirnotob-global-color card">
                <div class="card-header">
                    <h2 class="h4 mb-0">颜色列表</h2>
                </div>
                <div class="card-body">
                    <div id="color-list"></div>
                </div>
                <div class="card-footer">
                    <button type="button" id="add-new-color" class="btn btn-sm btn-primary mt-3">
                        <i class="dashicons dashicons-plus"></i> 添加新颜色
                    </button>
                </div>
            </div>

            <?php submit_button('保存所有颜色设置', 'primary', 'submit', true); ?>
        </form>
    </div>

    <!-- 颜色模板（隐藏） -->
    <template id="color-template">
        <div class="color-item d-flex align-items-center mb-3 p-2 border rounded" data-id="{ID}">
            <div class="color-item-left">
                <div class="color-picker-row me-3">
                    <input type="text" name="color_code[{ID}]" value="{CODE}" class="color-picker form-control form-control-sm" data-default-color="#cccccc" value="#cccccc">
                </div>
                <div class="me-3">
                    <label>颜色名称</label>
                    <input type="text" name="color_name[{ID}]" value="{NAME}" class="form-control form-control-sm" placeholder="颜色名称">
                </div>
                <div class="me-3">
                    <label>颜色类名</label>
                    <input type="text" name="color_classes[{ID}]" value="{CLASSES}" class="form-control form-control-sm" placeholder="颜色类名">
                </div>

            </div>
            <button type="button" class="btn btn-sm btn-danger remove-color">删除</button>
        </div>
    </template>
<?php
}

// 5. 加载WordPress颜色选择器脚本和样式（修复版）
add_action('admin_enqueue_scripts', 'custom_color_manager_enqueue_scripts');
function custom_color_manager_enqueue_scripts($hook)
{
    // 临时打印 $hook 值，查看当前页面的标识
    // var_dump($hook); // 会在后台页面顶部显示当前页面的 hook 名称
    // die(); // 暂停执行，方便查看
    // 只在颜色管理页面加载
    if ($hook !== 'appearance_page_custom-color-manager') return;

    // 修复：确保加载jQuery（WP颜色选择器依赖jQuery）
    wp_enqueue_script('jquery');
    wp_enqueue_style('wp-color-picker');
    // 修复：使用正确的脚本依赖，确保加载顺序
    wp_enqueue_script(
        'custom-color-manager',
        admin_url('js/color-picker.js'), // WP内置颜色选择器脚本
        ['jquery', 'wp-color-picker'],  // 明确依赖
        false,
        true // 在body底部加载
    );
    wp_enqueue_style('dashicons');

    wp_enqueue_style('global-color', get_stylesheet_directory_uri() . '/css/global-color.css', [], '1.0.0');
    wp_enqueue_script('global-color', get_stylesheet_directory_uri() . '/js/global-color.js', ['jquery'], '1.0.0', true);
}
?>