<?php
function cirno_slider_setting($wp_customize)
{
    // Slider 4 //
    $wp_customize->add_setting(
        'slider-page4',
        array(
            'default' => '0',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'specia_sanitize_integer',
        )
    );

    $wp_customize->add_control(
        'slider-page4',
        array(
            'type' => 'dropdown-pages',
            'allow_addition' => true,
            'label' => __('Select Page', 'specia'),
            'section' => 'slider_content',
        )
    );
    // Slider 5 //
    $wp_customize->add_setting(
        'slider-page5',
        array(
            'default' => '0',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'specia_sanitize_integer',
        )
    );

    $wp_customize->add_control(
        'slider-page5',
        array(
            'type' => 'dropdown-pages',
            'allow_addition' => true,
            'label' => __('Select Page', 'specia'),
            'section' => 'slider_content',
        )
    );
}

add_action('customize_register', 'cirno_slider_setting', 999);
