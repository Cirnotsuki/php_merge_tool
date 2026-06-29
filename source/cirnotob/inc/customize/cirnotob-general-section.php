<?php
add_action('customize_register', 'cirnotob_general_settings');
// 1. 注册自定义器配置（展示橱窗动态配置）
function cirnotob_general_settings($wp_customize)
{
    $selective_refresh = isset($wp_customize->selective_refresh) ? 'postMessage' : 'refresh';

    // 注册Showcase主面板
    $wp_customize->add_section(
        'cirnotob_helpcenter_settings',
        array(
            'priority'      => 1,
            'title'         => __('Help Center', 'cirnotob'),
            'panel' => 'specia_general',
        )
    );

    /*=========================================
    Showcase 菜单选择分区（关键修复）
    =========================================*/
    $wp_customize->add_section(
        'helpcenter_menu_selection',
        array(
            'priority' => 2, // 优先级2，避免和基础设置冲突
            'title'    => __('Help Center Category Menu', 'cirnotob'),
            'panel'    => 'specia_general', // 挂载到Showcase面板
        )
    );

    // 菜单选择设置项（修复：单值整数清洗）
    $wp_customize->add_setting(
        'helpcenter_selected_menu',
        array(
            'default'           => '',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'absint', // 菜单ID是整数，用absint清洗
            'transport'         => $selective_refresh,
        )
    );

    // 构建菜单选项（原生风格）
    $menu_choices = array(0 => __('— Select Help Center Menu —', 'cirnotob'));
    if (function_exists('wp_get_nav_menus')) {
        $menus = wp_get_nav_menus();
        // 确保有菜单才添加选项
        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $menu_choices[$menu->term_id] = $menu->name;
            }
        } else {
            $menu_choices[0] = __('No menus created yet', 'cirnotob');
        }
    }

    // 添加菜单选择控件（关键修复：挂载到正确的section）
    $wp_customize->add_control(
        'helpcenter_selected_menu',
        array(
            'default'       => '',
            'label'       => __('Help Center Menu', 'cirnotob'),
            'description' => __('Select a menu to display in Help Center single page.', 'cirnotob'),
            'section'     => 'helpcenter_menu_selection', // 挂载到菜单选择分区
            'type'        => 'select',
            'choices'     => $menu_choices,
        )
    );

    // 3. 添加【动态编辑菜单按钮】（根据选择自动显示/隐藏）
    $wp_customize->add_setting('jump_to_helpcenter_menu_button', [
        'sanitize_callback' => '__return_false',
        'transport'         => $selective_refresh,

    ]);

    $wp_customize->add_control('jump_to_helpcenter_menu_button', [
        'section'     => 'helpcenter_menu_selection',
        'label'       => '',
        'type'        => 'hidden',
        'description' => '<div id="helpcenter-menu-edit-button"></div>', // 动态渲染按钮
        'active_callback' => function () {
            // 关键：只有选中菜单才显示按钮
            return ! empty(get_theme_mod('helpcenter_selected_menu'));
        }
    ]);
}

// 输出JS：实现下拉选择 → 自动更新按钮链接
add_action('customize_controls_print_footer_scripts', function () {
?>
    <script>
        // jQuery(document).ready(function($) {
        //     [
        //         function helpCenter() {
        //             const menuSelect = $('#_customize-input-helpcenter_selected_menu');
        //             const buttonWrap = $('#helpcenter-menu-edit-button');

        //             // 初始化
        //             updateMenuButton();

        //             // 下拉变化时更新
        //             menuSelect.on('change', updateMenuButton);

        //             function updateMenuButton() {
        //                 const menuId = menuSelect.val();
        //                 console.log({
        //                     menuId
        //                 });

        //                 if (+menuId) {
        //                     buttonWrap.closest('li').show();
        //                     const editUrl = '<?= esc_url(admin_url('nav-menus.php?action=edit&menu=')); ?>' + menuId;
        //                     buttonWrap.html('<a href="' + editUrl + '" class="button button-primary" target="_blank"><?= __('Edit Help Center Menu', 'cirnotob') ?>></a>');
        //                 } else {
        //                     buttonWrap.closest('li').hide();
        //                     buttonWrap.empty();
        //                 }
        //             }
        //         }
        //     ].forEach(fn => fn());
        // });
    </script>
<?php
});
