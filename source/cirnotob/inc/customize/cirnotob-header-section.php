<?php
function cirnotob_header_setting( $wp_customize ) {
$selective_refresh = isset( $wp_customize->selective_refresh ) ? 'postMessage' : 'refresh';
	/*=========================================
	Header Navigation
	=========================================*/
	$wp_customize->add_section(
        'header_navigation',
        array(
        	'priority'      => 4,
            'title' 		=> __('Logo Right Content','cirnotob'),
			'panel'  		=> 'header_section',
		)
    );
	
	// Contact Info Head
	$wp_customize->add_setting(
		'nav_contact_info_head'
			,array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_text',
			'priority'  => 3
		)
	);

	$wp_customize->add_control(
	'nav_contact_info_head',
		array(
			'type' => 'hidden',
			'label' => __('Contact Info','cirnotob'),
			'section' => 'header_navigation',
		)
	);
	
	//  Hide/Show  // 
	$wp_customize->add_setting( 
		'hs_nav_contact_info' , 
			array(
			'default' => '1',
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_select',
			'priority'      => 4,
		) 
	);
	
	$wp_customize->add_control(
	'hs_nav_contact_info', 
		array(
			'label'	      => esc_html__( 'Hide / Show Section', 'cirnotob' ),
			'section'     => 'header_navigation',
			'type'           => 'radio',
			'choices'        => 
			array(
				'1' => 'Show',
				'0'  => 'Hide'
			) 		
		) 
	);
	
	// Contact Info Head
	$wp_customize->add_setting(
		'nav_contact_info1_head'
			,array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_text',
			'priority'  => 5
		)
	);

	$wp_customize->add_control(
	'nav_contact_info1_head',
		array(
			'type' => 'hidden',
			'label' => __('Contact Info First','cirnotob'),
			'section' => 'header_navigation',
		)
	);
	
	
	// Icon 1
	$wp_customize->add_setting(
    	'nav_ct_info1_icon',
    	array(
			'default' => 'fa-clock-o',
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
			'priority'  => 6
		)
	);	

	$wp_customize->add_control( 
		'nav_ct_info1_icon',
		array(
		    'label'   => __('Icon','cirnotob'),
		    'section' => 'header_navigation',
			'type' => 'text'
		)  
	);
	
	
	// Title 1
	$wp_customize->add_setting(
    	'nav_ct_info1_ttl',
    	array(
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
			'priority'  => 7,
			'transport'         => $selective_refresh,
		)
	);	

	$wp_customize->add_control( 
		'nav_ct_info1_ttl',
		array(
		    'label'   => __('Title','cirnotob'),
		    'section' => 'header_navigation',
			'type' => 'text'
		)  
	);
	
	// Subtitle 1
	$wp_customize->add_setting(
    	'nav_ct_info1_subttl',
    	array(
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
			'priority'  => 8,
		)
	);	

	$wp_customize->add_control( 
		'nav_ct_info1_subttl',
		array(
		    'label'   => __('Subtitle','cirnotob'),
		    'section' => 'header_navigation',
			'type' => 'text'
		)  
	);
	
	
	// Contact Info Head
	$wp_customize->add_setting(
		'nav_contact_info2_head'
			,array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_text',
			'priority'  => 11
		)
	);
	
	$wp_customize->add_control(
	'nav_contact_info2_head',
		array(
			'type' => 'hidden',
			'label' => __('Contact Info Second','cirnotob'),
			'section' => 'header_navigation',
		)
	);
	
	// Icon 1
	$wp_customize->add_setting(
    	'nav_ct_info2_icon',
    	array(
			'default' => 'fa-phone',
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
			'priority'  => 12
		)
	);	

	$wp_customize->add_control( 
		'nav_ct_info2_icon',
		array(
		    'label'   => __('Icon','cirnotob'),
		    'section' => 'header_navigation',
			'type' => 'text'
		)  
	);
	
	
	// Title 2
	$wp_customize->add_setting(
    	'nav_ct_info2_ttl',
    	array(
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
			'priority'  => 13,
			'transport'         => $selective_refresh,
		)
	);	

	$wp_customize->add_control( 
		'nav_ct_info2_ttl',
		array(
		    'label'   => __('Title','cirnotob'),
		    'section' => 'header_navigation',
			'type' => 'text'
		)  
	);
	
	// Subtitle 2
	$wp_customize->add_setting(
    	'nav_ct_info2_subttl',
    	array(
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
			'priority'  => 14
		)
	);	

	$wp_customize->add_control( 
		'nav_ct_info2_subttl',
		array(
		    'label'   => __('Subtitle','cirnotob'),
		    'section' => 'header_navigation',
			'type' => 'text'
		)  
	);
	
	
	// Contact Info Head
	$wp_customize->add_setting(
		'nav_contact_info3_head'
			,array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'specia_sanitize_text',
			'priority'  => 16
		)
	);

	$wp_customize->add_control(
	'nav_contact_info3_head',
		array(
			'type' => 'hidden',
			'label' => __('Contact Info Third','cirnotob'),
			'section' => 'header_navigation',
		)
	);
	
	
	// Icon 1
	$wp_customize->add_setting(
    	'nav_ct_info3_icon',
    	array(
			'default' => 'fa-clock-o',
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
			'priority'  => 17
		)
	);	

	$wp_customize->add_control( 
		'nav_ct_info3_icon',
		array(
		    'label'   => __('Icon','cirnotob'),
		    'section' => 'header_navigation',
			'type' => 'text'
		)  
	);
	
	
	// Title 2
	$wp_customize->add_setting(
    	'nav_ct_info3_ttl',
    	array(
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
			'priority'  => 18,
			'transport'         => $selective_refresh,
		)
	);	

	$wp_customize->add_control( 
		'nav_ct_info3_ttl',
		array(
		    'label'   => __('Title','cirnotob'),
		    'section' => 'header_navigation',
			'type' => 'text'
		)  
	);
	
	// Subtitle 3
	$wp_customize->add_setting(
    	'nav_ct_info3_subttl',
    	array(
			'sanitize_callback' => 'specia_sanitize_html',
			'capability' => 'edit_theme_options',
			'priority'  => 19
		)
	);	

	$wp_customize->add_control( 
		'nav_ct_info3_subttl',
		array(
		    'label'   => __('Subtitle','cirnotob'),
		    'section' => 'header_navigation',
			'type' => 'text'
		)  
	);
		
}

add_action( 'customize_register', 'cirnotob_header_setting' );

// Header selective refresh
function cirnotob_home_header_section_partials( $wp_customize ){
	
	// header_cart
	$wp_customize->selective_refresh->add_partial(
		'header_cart', array(
			'selector' => '.cart-icon',
			'container_inclusive' => true,
			'render_callback' => 'cart_button',
			'fallback_refresh' => true,
		)
	);
	
	//nav_ct_info1_ttl
	$wp_customize->selective_refresh->add_partial( 'nav_ct_info1_ttl', array(
		'selector'            => '.header-info .widget_first .text',
		'settings'            => 'nav_ct_info1_ttl',
		'render_callback'  => 'cirnotob_nav_ct_info1_ttl_render_callback',	
	) );
	
	//nav_ct_info2_ttl
	$wp_customize->selective_refresh->add_partial( 'nav_ct_info2_ttl', array(
		'selector'            => '.header-info .widget_second .text',
		'settings'            => 'nav_ct_info2_ttl',
		'render_callback'  => 'cirnotob_nav_ct_info2_ttl_render_callback',	
	) );
	
	//nav_ct_info3_ttl
	$wp_customize->selective_refresh->add_partial( 'nav_ct_info3_ttl', array(
		'selector'            => '.header-info .widget_third .text',
		'settings'            => 'nav_ct_info3_ttl',
		'render_callback'  => 'cirnotob_nav_ct_info3_ttl_render_callback',	
	) );
	}

add_action( 'customize_register', 'cirnotob_home_header_section_partials' );

// nav_ct_info1_ttl
function cirnotob_nav_ct_info1_ttl_render_callback() {
	return get_theme_mod( 'nav_ct_info1_ttl' );
}

// nav_ct_info2_ttl
function cirnotob_nav_ct_info2_ttl_render_callback() {
	return get_theme_mod( 'nav_ct_info2_ttl' );
}

// nav_ct_info3_ttl
function cirnotob_nav_ct_info3_ttl_render_callback() {
	return get_theme_mod( 'nav_ct_info3_ttl' );
}