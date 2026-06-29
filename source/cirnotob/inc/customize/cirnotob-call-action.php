<?php
function cirnotob_call_action_setting($wp_customize)
{
	$selective_refresh = isset($wp_customize->selective_refresh) ? 'postMessage' : 'refresh';

	/*=========================================
	Call Action Section Panel
	=========================================*/
	$wp_customize->add_panel(
		'cirnotob_call_panel',
		array(
			'priority'      => 128, // 起始 10
			'capability'    => 'edit_theme_options',
			'title'			=> __('联系方式', 'cirnotob'),
		)
	);

	// Call to Action Settings
	$wp_customize->add_section(
		'call_action_setting',
		array(
			'priority'      => 1, // 大类 10
			'title' 		=> __('Settings', 'cirnotob'),
			'panel'  		=> 'cirnotob_call_panel',
		)
	);

	$wp_customize->add_setting(
		'hide_show_call_actions',
		array(
			'default' => 'on',
			'capability'     => 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_select',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'hide_show_call_actions',
		array(
			'label'          => __('Hide / Show Section', 'cirnotob'),
			'section'        => 'call_action_setting',
			'type'           => 'radio',
			'priority'       => 1,
			'choices'        =>
			array(
				'on' => __('Show', 'cirnotob'),
				'off' => __('Hide', 'cirnotob')
			)
		)
	);

	// Call Action Content Section
	$wp_customize->add_section(
		'cirnotob_call_action_content',
		array(
			'priority'      => 2, // 下一个大类 +10
			'title' 		=> __('Content', 'cirnotob'),
			'panel'  		=> 'cirnotob_call_panel',
		)
	);

	// ------------------------------
	// Button First → Help Center
	// ------------------------------
	$wp_customize->add_setting(
		'cta_btn_first_head',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'cta_btn_first_head',
		array(
			'type' => 'hidden',
			'label' => __('Help Center', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'priority' => 10
		)
	);

	// Help Center Icon
	$wp_customize->add_setting(
		'call_action_button_icon',
		array(
			'default'			=> 'fa-question-circle',
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
		)
	);

	$wp_customize->add_control(
		'call_action_button_icon',
		array(
			'label'   => __('Help Center Icon', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 11,
			'description' => __('输入FontAwesome图标类名，例如：fa-question-circle', 'cirnotob'),
		)
	);

	// Help Center Title
	$wp_customize->add_setting(
		'call_action_button_title',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'call_action_button_title',
		array(
			'label'   => __('Help Center Title', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 12,
			'description' => __('输入帮助中心标题', 'cirnotob'),
		)
	);


	// Help Center Text
	$wp_customize->add_setting(
		'call_action_button_label',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_text',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'call_action_button_label',
		array(
			'label'   => __('Help Center Text', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 13,
			'description' => __('输入帮助中心按钮显示文字', 'cirnotob'),
		)
	);

	// Help Center Description
	$wp_customize->add_setting(
		'call_action_button_desc',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'call_action_button_desc',
		array(
			'label'   => __('Help Center Description', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 14,
			'description' => __('输入帮助中心简短描述文字', 'cirnotob'),
		)
	);

	// Help Center Link
	$wp_customize->add_setting(
		'call_action_button_link',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_url',
		)
	);

	$wp_customize->add_control(
		'call_action_button_link',
		array(
			'label'   => __('Help Center Link', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 15,
			'description' => __('输入帮助中心跳转链接', 'cirnotob'),
		)
	);

	// ------------------------------
	// Button Second → Contact
	// ------------------------------
	$wp_customize->add_setting(
		'cta_btn_second_head',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'cta_btn_second_head',
		array(
			'type' => 'hidden',
			'label' => __('Contact', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'priority' => 20
		)
	);

	// Contact Icon
	$wp_customize->add_setting(
		'call_action_button2_icon',
		array(
			'default'			=> __('fa-bell', 'cirnotob'),
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
		)
	);

	$wp_customize->add_control(
		'call_action_button2_icon',
		array(
			'label'   => __('Contact Icon', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 21,
			'description' => __('输入联系图标类名，例如：fa-phone', 'cirnotob'),
		)
	);

	// Contact Title ✅ 修复了和Email标题ID重复的bug
	$wp_customize->add_setting(
		'call_action_button2_title',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'call_action_button2_title',
		array(
			'label'   => __('Contact Title', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 22,
			'description' => __('输入联系模块标题', 'cirnotob'),
		)
	);


	// Contact Text
	$wp_customize->add_setting(
		'call_action_button2_label',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'call_action_button2_label',
		array(
			'label'   => __('Contact Text', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 23,
			'description' => __('输入联系按钮显示文字', 'cirnotob'),
		)
	);

	// Contact Description
	$wp_customize->add_setting(
		'call_action_button2_desc',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'call_action_button2_desc',
		array(
			'label'   => __('Contact Description', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 24,
			'description' => __('输入联系模块简短描述文字', 'cirnotob'),
		)
	);

	// Contact Link
	$wp_customize->add_setting(
		'call_action_button2_link',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_url',
		)
	);

	$wp_customize->add_control(
		'call_action_button2_link',
		array(
			'label'   => __('Contact Link', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 25,
			'description' => __('输入联系链接（电话/邮箱/页面链接）', 'cirnotob'),
		)
	);

	// ------------------------------
	// Button Third → Email
	// ------------------------------
	$wp_customize->add_setting(
		'cta_btn_third_head',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'cta_btn_third_head',
		array(
			'type' => 'hidden',
			'label' => __('Email', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'priority' => 30
		)
	);

	// Email Icon
	$wp_customize->add_setting(
		'call_action_button3_icon',
		array(
			'default'			=> 'fa-envelope',
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
		)
	);

	$wp_customize->add_control(
		'call_action_button3_icon',
		array(
			'label'   		=> __('Email Icon', 'cirnotob'),
			'type' => 'text',
			'priority'       => 31,
			'description' => __('输入邮箱图标类名，例如：fa-envelope', 'cirnotob'),
			'section' 		=> 'cirnotob_call_action_content',
		)
	);

	// Email Title
	$wp_customize->add_setting(
		'call_action_button3_title',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'call_action_button3_title',
		array(
			'label'   => __('Email Title', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 32,
			'description' => __('输入邮箱模块标题', 'cirnotob'),
		)
	);

	// Email Text
	$wp_customize->add_setting(
		'call_action_button3_label',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'call_action_button3_label',
		array(
			'label'   => __('Email Text', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 33,
			'description' => __('输入邮箱按钮显示文字', 'cirnotob'),
		)
	);

	// Email Description
	$wp_customize->add_setting(
		'call_action_button3_desc',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_html',
			'transport'         => $selective_refresh,
		)
	);

	$wp_customize->add_control(
		'call_action_button3_desc',
		array(
			'label'   => __('Email Description', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 34,
			'description' => __('输入邮箱模块简短描述文字', 'cirnotob'),
		)
	);

	// Email Link
	$wp_customize->add_setting(
		'call_action_button3_link',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_url',
		)
	);

	$wp_customize->add_control(
		'call_action_button3_link',
		array(
			'label'   => __('Email Link', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'type'           => 'text',
			'priority'       => 35,
			'description' => __('输入邮箱链接或邮箱地址', 'cirnotob'),
		)
	);

	// Background
	$wp_customize->add_setting(
		'cta_bg_head',
		array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_text',
		)
	);

	$wp_customize->add_control(
		'cta_bg_head',
		array(
			'type' => 'hidden',
			'label' => __('Background Image', 'cirnotob'),
			'section' => 'cirnotob_call_action_content',
			'priority' => 40
		)
	);

	// Background Image
	$wp_customize->add_setting(
		'call_action_background_setting',
		array(
			'default' 			=> esc_url(get_template_directory_uri() . '/images/cta.jpg'),
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_url',
		)
	);

	$wp_customize->add_control(new WP_Customize_Image_Control(
		$wp_customize,
		'call_action_background_setting',
		array(
			'label'          => __('Background Image', 'cirnotob'),
			'section'        => 'cirnotob_call_action_content',
			'priority'       => 41
		)
	));
}
add_action('customize_register', 'cirnotob_call_action_setting', 99);



// ✅ 补全所有选择性刷新
function cirnotob_home_cta_section_partials($wp_customize)
{
	// 整体显示/隐藏
	$wp_customize->selective_refresh->add_partial(
		'hide_show_call_actions',
		array(
			'selector' => '.call-to-action',
			'container_inclusive' => true,
			'render_callback' => 'call_action_setting',
			'fallback_refresh' => true,
		)
	);

	// ------------------------------
	// Help Center 实时刷新
	// ------------------------------
	$wp_customize->selective_refresh->add_partial('call_action_button_title', array(
		'selector'            => '.call-to-action .cta-info .call-title',
		'settings'            => 'call_action_button_title',
		'render_callback'  => 'cirnotob_call_action_button_title_render_callback',
	));

	$wp_customize->selective_refresh->add_partial('call_action_button_desc', array(
		'selector'            => '.call-to-action .cta-info .call-desc',
		'settings'            => 'call_action_button_desc',
		'render_callback'  => 'cirnotob_call_action_button_desc_render_callback',
	));

	$wp_customize->selective_refresh->add_partial('call_action_button_label', array(
		'selector'            => '.call-to-action a',
		'settings'            => 'call_action_button_label',
		'render_callback'  => 'cirnotob_call_action_button_label_render_callback',
	));

	// ------------------------------
	// Contact 实时刷新
	// ------------------------------
	$wp_customize->selective_refresh->add_partial('call_action_button2_title', array(
		'selector'            => '.call-to-action-four .cta-info .call-title',
		'settings'            => 'call_action_button2_title',
		'render_callback'  => 'cirnotob_call_action_button2_title_render_callback',
	));

	$wp_customize->selective_refresh->add_partial('call_action_button2_desc', array(
		'selector'            => '.call-to-action-four .cta-info .call-desc',
		'settings'            => 'call_action_button2_desc',
		'render_callback'  => 'cirnotob_call_action_button2_desc_render_callback',
	));

	$wp_customize->selective_refresh->add_partial('call_action_button2_label', array(
		'selector'            => '#cta-unique .cta-info .call-phone a',
		'settings'            => 'call_action_button2_label',
		'render_callback'  => 'cirnotob_call_action_button2_label_render_callback',
	));

	// ------------------------------
	// Email 实时刷新
	// ------------------------------
	$wp_customize->selective_refresh->add_partial('call_action_button3_title', array(
		'selector'            => '.call-to-action-five .call-wrapper2 .cta-info .call-title',
		'settings'            => 'call_action_button3_title',
		'render_callback'  => 'cirnotob_call_action_button3_title_render_callback',
	));

	$wp_customize->selective_refresh->add_partial('call_action_button3_desc', array(
		'selector'            => '.call-to-action-five .call-wrapper2 .cta-info .call-desc',
		'settings'            => 'call_action_button3_desc',
		'render_callback'  => 'cirnotob_call_action_button3_desc_render_callback',
	));

	$wp_customize->selective_refresh->add_partial('call_action_button3_label', array(
		'selector'            => '.call-to-action-five .call-wrapper2 .call-phone a',
		'settings'            => 'call_action_button3_label',
		'render_callback'  => 'cirnotob_call_action_button3_label_render_callback',
	));
}

add_action('customize_register', 'cirnotob_home_cta_section_partials', 99);

// ✅ 补全所有渲染回调函数
function cirnotob_call_action_button_label_render_callback()
{
	return get_theme_mod('call_action_button_label');
}

function cirnotob_call_action_button_title_render_callback()
{
	return get_theme_mod('call_action_button_title');
}

function cirnotob_call_action_button_desc_render_callback()
{
	return get_theme_mod('call_action_button_desc');
}

function cirnotob_call_action_button2_title_render_callback()
{
	return get_theme_mod('call_action_button2_title');
}

function cirnotob_call_action_button2_desc_render_callback()
{
	return get_theme_mod('call_action_button2_desc');
}

function cirnotob_call_action_button2_label_render_callback()
{
	return get_theme_mod('call_action_button2_label');
}

function cirnotob_call_action_button3_title_render_callback()
{
	return get_theme_mod('call_action_button3_title');
}

function cirnotob_call_action_button3_desc_render_callback()
{
	return get_theme_mod('call_action_button3_desc');
}

function cirnotob_call_action_button3_label_render_callback()
{
	return get_theme_mod('call_action_button3_label');
}
