<?php
add_action('customize_controls_enqueue_scripts', 'enqueue_wp_scripts');
function enqueue_wp_scripts()
{
    // 加载WP菜单核心脚本
    wp_enqueue_script('nav-menu', admin_url('js/nav-menu.js'), array('jquery'), false, true);
    // 加载WP原生菜单编辑样式
    wp_enqueue_style('nav-menu', admin_url('css/nav-menus.css'));

    // 加载WordPress内置的jQuery UI（可选，用于增强样式/交互）
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_style('wp-jquery-ui-dialog'); // 复用WP内置的jQuery UI样式

    // 加载拖拽排序脚本
    wp_enqueue_script('jquery-ui-sortable');
}

add_action('admin_enqueue_scripts', 'cirnotob_customize_scripts');
add_action('customize_controls_enqueue_scripts', 'cirnotob_customize_scripts');
function cirnotob_customize_scripts()
{
    // 1. 加载远程 CSS
    wp_enqueue_style(
        'cirnotob-customize-style',
        'http://127.0.0.1:5000/elementor.css'
    );
    // 2. 加载远程 JS
    wp_enqueue_script(
        'cirnotob-customize-script',
        'http://127.0.0.1:5000/bootstrap.bundle.min.js',
        array('jquery'),
        false,
        true
    );
}

require_once(__DIR__ . '/cirnotob-general-section.php');
require_once(__DIR__ . '/cirnotob-showcase-section.php');
require_once(__DIR__ . '/cirnotob-call-action.php');
require_once(__DIR__ . '/cirnotob-header-section.php');
require_once(__DIR__ . '/cirnotob-slider-section.php');
require_once(__DIR__ . '/cirnotob-features.php');

/** 
 * 设定页面排序
 */
// 删除升级按钮，反正也用不了
add_action('customize_register', 'cirnotob_remove_settings', 900);
function cirnotob_remove_settings($wp_customize)
{
    $wp_customize->remove_section('upgrade_premium');
    // $wp_customize->remove_section('specia_general');
}

// 按照主页布局调整左侧菜单顺序
add_action('customize_register', 'cirno_priority_sort', 11);
function cirno_priority_sort($wp_customize)
{
    $panels = [
        "header_section" => '页眉',
        "nav_menus" => '导航菜单',
        "specia_general" => '单页设置',

        "home_section" => '全宽幻灯器',
        "features_panel" => '产品特点',

        "showcase_section" => '产品展示列表',
        "service_panel" => '公司服务',


        "portfolio_panel" => '代表作',
        "call_panel" => '联系我们',

        "blog_panel" => '最新博客',

        "widgets" => '小工具',
        "footer_section" => '页脚',

        'themes' => '额外 CSS',

    ];

    $priority = 122; // 你想要的起始优先级数值
    foreach (array_keys($panels) as $key) {
        // 1. 修改控件的priority（决定后台显示顺序）
        $panel = $wp_customize->get_panel($key);

        if (isset($panel)) {
            $panel->priority = $priority++;
            // 修改文字
            $panel->title = $panels[$key];
        }
    }
}
