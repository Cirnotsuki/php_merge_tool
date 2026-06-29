<?php
if (!defined('ABSPATH')) {
    header('Location: /');
    exit;
}
/**
 * 强制渲染并获取 Essential Blocks 内联样式
 */
function get_eb_block_styles($post)
{
    if (!$post) return '';

    global $wp_query, $wp_embed, $post;

    // 保存环境
    $old_post = $post;
    $old_wp_query = $wp_query;

    // 伪造环境让 EB 工作
    $wp_query->is_singular = true;
    $post = $post;

    // 捕获输出
    ob_start();
    do_blocks($post->post_content);
    wp_head(); // 触发样式输出
    $styles = ob_get_clean();

    // 恢复环境
    $post = $old_post;
    $wp_query = $old_wp_query;

    return $styles;
}
add_filter('essential_blocks_assets_load_condition', '__return_true');

// 修复商品页SEO标题（纯原生）
add_filter('pre_get_document_title', function ($title) {
    if (get_query_var('route_base') === 'product-page') {
        $route_name = get_query_var('route_name');
        if (empty($route_name)) return $title;

        $product_id = 0;
        if (is_numeric($route_name)) {
            $product_id = (int)$route_name;
        } else {
            $post = get_page_by_path(sanitize_title($route_name), OBJECT, 'product');
            $product_id = $post ? $post->ID : 0;
        }

        if ($product_id > 0) {
            $product = Cirnotob_Product_Util::getProduct($product_id);
            if ($product) {
                return esc_html($product['title']) . ' - ' . get_bloginfo('name');
            }
        }
    }
    return $title;
}, 999);

// 修复商品页Canonical链接（纯原生）
add_filter('get_canonical_url', function ($canonical, $post) {
    if (get_query_var('route_base') === 'product-page') {
        $route_name = get_query_var('route_name');
        if (empty($route_name)) return $canonical;

        $product_id = 0;
        if (is_numeric($route_name)) {
            $product_id = (int)$route_name;
        } else {
            $p = get_page_by_path(sanitize_title($route_name), OBJECT, 'product');
            $product_id = $p ? $p->ID : 0;
        }

        if ($product_id > 0) {
            $product = Cirnotob_Product_Util::getProduct($product_id);
            if ($product) {
                return esc_url($product['url']);
            }
        }
    }
    return $canonical;
}, 999, 2);

// 定义常量
require_once(get_stylesheet_directory() . '/inc/config/constans.php');

// 定义页面路由
require_once(get_stylesheet_directory() . '/inc/router/index.php');

// 创建子菜单到外观选项卡
require_once(get_stylesheet_directory() . '/inc/sidebar/sidebar.php');

// 定义模板渲染器
require_once(get_stylesheet_directory() . '/inc/renders/index.php');


// 修复后：仅在自定义器加载时引入
if (is_customize_preview() || isset($_GET['customize_theme'])) {
    require_once(get_stylesheet_directory() . '/inc/customize/index.php');
}

/**
 * Widgets.
 */
require_once(get_stylesheet_directory() . '/inc/widget/widget_feature.php');

// 注册调试方法
// require_once(get_stylesheet_directory() . '/inc/debug_to_console.php');

// 以下为等待父主题加载完成后，单独加载子主题功能的代码：
add_action('after_setup_theme', 'after_parent_setup', 999);
function after_parent_setup()
{
    require_once(get_stylesheet_directory() . '/inc/after-parent-setup.php');
}

/**
 * Import Options From Specia Theme
 *
 */
add_action('after_switch_theme', 'cirnotob_parent_theme_options');
function cirnotob_parent_theme_options()
{
    $specia_mods = get_option('theme_mods_specia');
    if (!empty($specia_mods)) {
        foreach ($specia_mods as $specia_mod_k => $specia_mod_v) {
            set_theme_mod($specia_mod_k, $specia_mod_v);
        }
    }
}

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
            <span class="count"><?= esc_html__('0', 'cirnotob'); ?></span>
        <?php } ?></a>
<?php
    $fragments['a.cart-icon'] = ob_get_clean();

    return $fragments;
}


// 显示自定义字段
add_filter('acf/settings/remove_wp_meta_box', '__return_false');
