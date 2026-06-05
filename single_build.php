<?php
// 自动合并生成 - 入口：cirnotob/functions.php
// 合并时间：2026-04-02 06:36:43


function get_stylesheet_directory()
{
    return __DIR__;
}

require_once(get_stylesheet_directory() . '/inc/page-type.php');
// 定义工具
require_once(get_stylesheet_directory() . '/inc/utils.php');

require_once(get_stylesheet_directory() . '/inc/config/constans.php');
// 创建子菜单到外观选项卡
require_once(get_stylesheet_directory() . '/inc/sidebar/sidebar.php');
// 定义数据库操作API
require_once(get_stylesheet_directory() . '/inc/api/index.php');
// 定义页面路由
require_once(get_stylesheet_directory() . '/inc/router/index.php');
// 加载模板页面配置项
require_once(get_stylesheet_directory() . '/views/index.php');


// 定义模板渲染器
require_once(get_stylesheet_directory() . '/inc/renders/index.php');

// 定义条件类名
require_once(get_stylesheet_directory() . '/inc/template-tags.php');



// 修复后：仅在自定义器加载时引入
if (is_customize_preview() || isset($_GET['customize_theme'])) {
    require_once(get_stylesheet_directory() . '/inc/customize/index.php');
}
/**
 * Widgets.
 */
require_once(get_stylesheet_directory() . '/inc/widget/widget_feature.php');





// 注册调试方法
require_once(get_stylesheet_directory() . '/inc/debug_to_console.php');

// 以下为等待父主题加载完成后，单独加载子主题功能的代码：
function after_parent_setup()
{
    require_once(get_stylesheet_directory() . '/inc/after-parent-setup.php');
}

add_action('after_setup_theme', 'after_parent_setup', 999);




/**
 * Add WooCommerce Cart Icon With Cart Count
 */

add_filter('woocommerce_add_to_cart_fragments', 'cirnotob_add_to_cart_fragment');
function cirnotob_add_to_cart_fragment($fragments)
{
    ob_start();
    $count = WC()->cart->cart_contents_count; ?>
    <a class="cart-icon" href="<?= esc_url(wc_get_cart_url()); ?>"><i class='fa fa-cart-plus'></i>
        <?php if ($count > 0) { ?>
            <span class="count"><?= esc_html($count); ?></span>
        <?php } else { ?>
            <span class="count"><?= esc_html_e('0', 'cirnotob'); ?></span>
        <?php } ?></a>
<?php
    $fragments['a.cart-icon'] = ob_get_clean();

    return $fragments;
}

/**
 * Import Options From Specia Theme
 *
 */
function cirnotob_parent_theme_options()
{
    $specia_mods = get_option('theme_mods_specia');
    if (!empty($specia_mods)) {
        foreach ($specia_mods as $specia_mod_k => $specia_mod_v) {
            set_theme_mod($specia_mod_k, $specia_mod_v);
        }
    }
}
add_action('after_switch_theme', 'cirnotob_parent_theme_options');
// 显示自定义字段
add_filter('acf/settings/remove_wp_meta_box', '__return_false');
