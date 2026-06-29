<?php

/**
 * Remove Customize Panel from parent theme
 */
function cirnotob_remove_parent_setting($wp_customize)
{
    $wp_customize->remove_control('call_action_button_target');
    $wp_customize->remove_control('cta_content_head');
    $wp_customize->remove_control('call_action_page');
    $wp_customize->remove_control('call_action_button_label2');
    $wp_customize->remove_control('call_action_button_link2');


    $wp_customize->remove_control('call_action_btn_middle_text');
}
add_action('customize_register', 'cirnotob_remove_parent_setting');

remove_action('customize_register', 'specia_call_action_setting', 999);


/**
 * Register Google fonts for Cirnotob.
 */
function cirnotob_google_font()
{

    $get_fonts_url = '';

    $font_families = array();

    $font_families = array('Open Sans:300,400,600,700,800', 'Raleway:400,700');

    $query_args = array(
        'family' => urlencode(implode('|', $font_families)),
        'subset' => urlencode('latin,latin-ext'),
    );

    $get_fonts_url = add_query_arg($query_args, '//fonts.googleapis.com/css');

    return esc_url($get_fonts_url);
}

function cirnotob_enqueue_array($route_base)
{
    $array = ['jquery'];

    if ($route_base === 'product-page') {
        $array = ['jquery', 'swiper', 'glightbox'];
    }

    return $array;
}

/**
 * Enqueue styles and scripts 
 */
add_action('wp_enqueue_scripts', 'cirnotob_enqueue_scripts_styles');
function cirnotob_enqueue_scripts_styles()
{

    // styles
    // styles
    $parent_style = 'specia-parent-style';
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style('cirnotob-style', get_stylesheet_uri(), array($parent_style));
    wp_enqueue_style('cirnotob-fonts', cirnotob_google_font(), array(), null);

    wp_enqueue_style('cirnotob-media-query', get_template_directory_uri() . '/css/media-query.css');
    wp_dequeue_style('specia-media-query');
    wp_dequeue_style('specia-default');

    // 强制加载核心 + Essential Blocks 官方样式（Readme验证过的正确句柄）
    wp_enqueue_style('essential-blocks-frontend-style');
    wp_enqueue_style('essential-blocks-blocks-style');

    wp_dequeue_style('font-awesome');
    wp_enqueue_style('font-awesome', get_stylesheet_uri() . '/css/icons/css/font-awesome.min.css');


    // wp_enqueue_style('cirnotob-default', get_stylesheet_directory_uri() . '/css/colors/default.css');
    // wp_enqueue_style('cirnotob-main', get_stylesheet_directory_uri() . '/css/main-style.css');
    // wp_enqueue_style('cirnotob-custom', get_stylesheet_directory_uri() . '/css/colors/custom.css');
    // wp_enqueue_style('cirnotob-modify-style', get_stylesheet_directory_uri() . '/css/modify-style.css');
    // wp_enqueue_style('cirnotob-animate', get_stylesheet_directory_uri() . '/css/animate.css');
    // scripts
    // wp_enqueue_script('cirnotob-hooks-js', get_stylesheet_directory_uri() . '/js/hooks.js', array('jquery'), false, true);
    // wp_enqueue_script('cirnotob-theme-js', get_stylesheet_directory_uri() . '/js/theme.js', array('jquery'), false, true);

    $route = ['base' => get_query_var('route_base'), 'type' => Cirnotob_Util::pageType()];
    $query = http_build_query($route);


    // =========================
    // Product Detail Assets
    // =========================

    wp_register_style('swiper', CIRNOTOB_HOST_SERVER . 'swiper-bundle.min.css', [], '11.0.0');
    wp_register_script('swiper', CIRNOTOB_HOST_SERVER . 'swiper-bundle.min.js', [], '11.0.0', true);
    wp_register_style('glightbox', CIRNOTOB_HOST_SERVER . 'glightbox.min.css', [], '3.3.0');
    wp_register_script('glightbox', CIRNOTOB_HOST_SERVER . 'glightbox.min.js', [], '3.3.0', true);

    // 1. 加载远程 CSS
    wp_enqueue_style('cirnotob-theme-style', CIRNOTOB_HOST_SERVER . 'bootstrap-5.3.8.min.css?' . $query, [], '1.0.0');
    // 2. 加载远程 JS
    wp_enqueue_script('cirnotob-theme-script', CIRNOTOB_HOST_SERVER . 'jquery-3.6.0.min.js?' . $query, cirnotob_enqueue_array(get_query_var('route_base')), '1.0.0', true);

    // wp_enqueue_script('cirnotob-hooks-script', CIRNOTOB_HOST_SERVER . 'owl.carousel.min.js', array('jquery'), false, true);

}

// 隐藏Specia主题的推荐插件提示Section
function hide_specia_notify_section($wp_customize)
{
    // 清除父主题的广告
    $section_id = 'Specia-customizer-notify-section';
    if ($wp_customize->get_section($section_id)) {
        $wp_customize->remove_section($section_id);
    }
}
add_action('customize_register', 'hide_specia_notify_section');

/**
 * 阻止父主题干扰子主题翻译
 */
load_child_theme_textdomain('cirnotob', get_stylesheet_directory() . '/languages');
add_editor_style(array('css/editor-style.css', cirnotob_google_font()));


// 注入全局颜色
add_action('wp_head', 'inject_global_color');
function inject_global_color()
{
    $colors_json = get_option(CIRNOTOB_GLOBAL_COLOR_NAME, []);
    $colors = json_decode($colors_json);

    if (empty($colors)) return;
    echo '<style id="' . CIRNOTOB_GLOBAL_COLOR_NAME . '_css" type="text/css">:root {';
    foreach ($colors as $color_object) {
        echo '--global--color--' . $color_object->classes . ':' . $color_object->code . ';';
    }
    echo '</style>';
}

// 搜索只显示 post，排除 page、product 等
add_filter('pre_get_posts', 'filter_search_post_type');
function filter_search_post_type($query)
{
    if ($query->is_main_query() && $query->is_search()) {
        $query->set('post_type', ['post']);
    }
    return $query;
}

global $l10n;
// 确保子主题文本域独立，不被父主题覆盖
if (isset($l10n['cirnotob'])) {
    $l10n['cirnotob']->domain = 'cirnotob'; // 强制锁定文本域
    $l10n['cirnotob']->loaded = true; // 标记为已加载
}

// 加载语言目录
$child_lang_dir = get_stylesheet_directory() . '/languages';
load_theme_textdomain('cirnotob', $child_lang_dir);

$locale = get_locale();

// 2. 加载子主题 .mo 文件
$mo_file = $child_lang_dir . "/cirnotob-{$locale}.mo";
if (file_exists($mo_file)) {
    load_textdomain('cirnotob', $mo_file);
}

// 1. 隐藏 WooCommerce 其他子菜单，只留设置
add_action('admin_menu', 'clean_woocommerce_submenu_exact', 999);
function clean_woocommerce_submenu_exact()
{
    global $submenu;
    if (isset($submenu['woocommerce'])) {
        foreach ($submenu['woocommerce'] as $key => $item) {
            unset($submenu['woocommerce'][$key]);
        }
    }

    // 删除原来的 WooCommerce 主菜单
    remove_menu_page('woocommerce');
    remove_menu_page('woocommerce-marketing');
    remove_menu_page('wc-admin&path=/analytics/overview');
    remove_menu_page('admin.php?page=wc-settings&tab=checkout&from=PAYMENTS_MENU_ITEM');
}


// =========================
// Remove WooCommerce Elements
// =========================

remove_action(
    'woocommerce_before_main_content',
    'woocommerce_breadcrumb',
    20
);

remove_action(
    'woocommerce_after_single_product_summary',
    'woocommerce_output_related_products',
    20
);

remove_action(
    'woocommerce_single_product_summary',
    'woocommerce_template_single_meta',
    40
);

remove_action(
    'woocommerce_after_single_product_summary',
    'woocommerce_output_product_data_tabs',
    10
);

remove_action(
    'woocommerce_single_product_summary',
    'woocommerce_template_single_add_to_cart',
    30
);
