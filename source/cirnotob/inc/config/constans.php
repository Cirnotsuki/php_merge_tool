<?php
define('CINOTOB_THEME_VERSION', '0.0.1');

define('CIRNOTOB_GLOBAL_COLOR_NAME', 'hc_global_colors');
define('CIRNOTOB_GLOBAL_COLOR_GROUP', 'hc_global_color_group');
define('CIRNOTOB_GLOBAL_COLOR_DEFAULT', json_encode([
    [
        'id' => 1,
        'name' => '主要颜色',
        'classes' => 'primary',
        'code' => '#101828'
    ],
    [
        'id' => 2,
        'name' => '次要颜色',
        'classes' => 'secondary',
        'code' => '#475467'
    ],
    [
        'id' => 3,
        'name' => '文字颜色',
        'classes' => 'text',
        'code' => '#98A2B3'
    ],
    [
        'id' => 4,
        'name' => '标题颜色',
        'classes' => 'heading',
        'code' => '#1D2939'
    ],
    [
        'id' => 5,
        'name' => '链接颜色',
        'classes' => 'link',
        'code' => '#444CE7'
    ],
    [
        'id' => 6,
        'name' => '背景颜色',
        'classes' => 'background',
        'code' => '#F9FAFB'
    ],
    [
        'id' => 7,
        'name' => '按钮文字',
        'classes' => 'button-text',
        'code' => '#FFFFFF'
    ],
    [
        'id' => 8,
        'name' => '按钮背景颜色',
        'classes' => 'button-background',
        'code' => '#101828'
    ],
    [
        'id' => 9,
        'name' => '警告色',
        'classes' => 'warning',
        'code' => '#F56565'
    ],
    [
        'id' => 10,
        'name' => '成功色',
        'classes' => 'success',
        'code' => '#10B981'
    ],
    [
        'id' => 11,
        'name' => '错误色',
        'classes' => 'error',
        'code' => '#DC2626'
    ],
]));
define('CIRNOTOB_HOST_SERVER', 'http://114.132.229.184:5000/');
