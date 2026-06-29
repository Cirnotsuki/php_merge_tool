<?php
if (!defined('ABSPATH')) exit; // 防止直接访问

function cirnotob_submenu_init()
{
	require_once(get_stylesheet_directory() . '/inc/views/icon-preview.php');

	// 添加为「外观」的子菜单
	$iconpreview = add_submenu_page(
		'themes.php',                  // 父菜单Slug（外观的Slug是themes.php）
		'图标列表',                // 页面标题（浏览器标签显示）
		'图标列表',                    // 子菜单名称（外观菜单下显示的文字）
		'manage_options',              // 权限（仅管理员可访问）
		'custom-icon-preview',          // 子菜单唯一Slug
		"render_custom_custom_icon_preview"      // 渲染页面的回调函数
	);

	// 只在当前页面挂载 enqueue 钩子
	add_action("admin_print_styles-$iconpreview", 'load_font_css');
}
add_action('admin_menu', 'cirnotob_submenu_init');

// 1. 添加后台菜单

add_action('admin_menu', 'color_manager_add_menu');
function color_manager_add_menu()
{
	require_once(get_stylesheet_directory() . '/inc/views/global-color.php');

	// 添加为「外观」的子菜单
	$global_color = add_submenu_page(
		'themes.php',                  // 父菜单Slug（外观的Slug是themes.php）
		'全局颜色管理',                // 页面标题（浏览器标签显示）
		'全局颜色管理',                    // 子菜单名称（外观菜单下显示的文字）
		'manage_options',              // 权限（仅管理员可访问）
		'custom-color-manager',          // 子菜单唯一Slug
		"render_custom_color_manager"      // 渲染页面的回调函数
	);
}



function cirnotb_widgets_init()
{
	// 重载小工具区
	// unregister_sidebar('specia_feature_widget');
	register_sidebar(array(
		'name' => '产品特点列表',
		'id' => 'cirnotob_feature_widget',
		'description' => '如果需要查询图标列表，可在点击 <a
			href="'. esc_url(admin_url('themes.php?page=custom-icon-preview')) .'"
			class="wp-menu-edit-link"
			target="_blank">图标预览</a> 进行查询',
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
	));
}
add_action('widgets_init', 'cirnotb_widgets_init', 999);
