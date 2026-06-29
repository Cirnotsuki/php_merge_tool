<?php
$clickToWidget = 'wp.customize.section("sidebar-widgets-cirnotob_feature_widget").focus(); wp.customize.Widgets.Editor.open("widget-cirnotob_feature_widget-1"); return false;';
add_action('customize_register', function ($wp_customize) {
	global $clickToWidget;

	// 2. 添加跳转按钮（核心：用JS实现点击跳转）
	$wp_customize->add_setting('jump_to_cirnotob_feature_widget', [
		'sanitize_callback' => 'sanitize_text_field',
	]);
	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'jump_to_cirnotob_feature_widget', [
		'label' => '点击按钮编辑产品特点列表', // 按钮文字
		'section' => 'features_setting', // 所属面板
		'type' => 'button', // 控件类型：按钮
		'input_attrs' => [
			'class' => 'button button-primary jump-to-cirnotob-feature-widget', // WordPress原生按钮样式
			'onclick' => esc_attr($clickToWidget),

			// 替换为你的小工具ID：widget-cirnotob_feature_widget-1
		],
	]));

	// features_title
	$wp_customize->selective_refresh->add_partial('jump_to_cirnotob_feature_widget', array(
		'selector'            => '.features-version-one .cirnotob-feature-widget',
		'settings'            => 'jump_to_cirnotob_feature_widget',
		'render_callback'  => 'jump_to_cirnotob_feature_widget_render_callback',

	));

	// features_title
	function jump_to_cirnotob_feature_widget_render_callback()
	{
		return get_theme_mod('jump_to_cirnotob_feature_widget');
	}
});

add_action('admin_enqueue_scripts', function () {
	global $clickToWidget;
	$handle = 'jump-to-cirnotob-feature-widget';
	// 注册一个空的基础脚本（仅作为载体）
	wp_register_script($handle, '', ['jquery'], false, true);
	wp_enqueue_script($handle);

	$selecors = [
		'.cirnotob-feature-widget',
		'.customize-partial-edit-shortcut',
		'button',
	];
	wp_add_inline_script('jump-to-cirnotob-feature-widget', '
(function () {
    jQuery(document).ready(function ($) {
        $(".jump-to-cirnotob-feature-widget").val("点击跳转");
    });
})();
	');
});
