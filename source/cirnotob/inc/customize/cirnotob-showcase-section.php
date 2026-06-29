<?php
// 加载WP核心菜单编辑依赖
require_once(ABSPATH . 'wp-admin/includes/nav-menu.php');

add_action('customize_register', 'cirno_showcase_settings');
// 1. 注册自定义器配置（展示橱窗动态配置）
function cirno_showcase_settings($wp_customize)
{
    $selective_refresh = isset($wp_customize->selective_refresh) ? 'postMessage' : 'refresh';

    // 注册Showcase主面板
    $wp_customize->add_panel(
        'showcase_panel',
        array(
            'priority' => 122,
            'capability' => 'edit_theme_options',
            'title' => __('Showcase Section', 'cirnotob'),
            'description' => '通过拖拽菜单项，生成产品列表展示区。',
        )
    );

    /*=========================================
    Showcase 基础设置分区
    =========================================*/
    $wp_customize->add_section(
        'showcase_setting',
        array(
            'priority' => 1, // 优先级1，显示在最上方
            'title' => __('Settings', 'specia'), // 统一文本域
            'panel' => 'showcase_panel',
        )
    );

    // 显示/隐藏开关
    $wp_customize->add_setting(
        'hide_show_showcase',
        array(
            'default' => 'on',
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'showcase_sanitize_select',
            'transport' => $selective_refresh,
        )
    );

    $wp_customize->add_control(
        'hide_show_showcase',
        array(
            'label' => __('Hide / Show Section', 'specia'),
            'section' => 'showcase_setting',
            'type' => 'radio',
            'choices' => array(
                'on' => __('Show', 'specia'),
                'off' => __('Hide', 'specia')
            )
        )
    );

    // hide_show_features
    $wp_customize->selective_refresh->add_partial(
        'hide_show_showcase',
        array(
            'selector' => '.showcase-wrapper',
            'container_inclusive' => true,
            'render_callback' => 'showcase_setting',
            'fallback_refresh' => true,
        )
    );

    /*=========================================
    Showcase 标题
    =========================================*/

    // Features Header Section // 
    $wp_customize->add_section(
        'showcase_header',
        array(
            'priority'      => 1,
            'title'         => __('Header', 'specia'),
            'panel'          => 'showcase_panel',
        )
    );
    // Features Title // 
    $wp_customize->add_setting(
        'showcase_title',
        array(
            'capability'         => 'edit_theme_options',
            'sanitize_callback' => 'specia_sanitize_html',
            'transport'         => $selective_refresh,
        )
    );

    $wp_customize->add_control(
        'showcase_title',
        array(
            'label'   => __('Title', 'specia'),
            'section' => 'showcase_header',
            'type'           => 'text',
        )
    );

    // features_title
    $wp_customize->selective_refresh->add_partial('showcase_title', array(
        'selector'            => '.showcase-wrapper .showcase-title',
        'settings'            => 'showcase_title',
        'render_callback'  => function () {
            return get_theme_mod('showcase_title');
        },
    ));

    /*=========================================
    Showcase 菜单选择分区（关键修复）
    =========================================*/
    // $wp_customize->add_section(
    //     'showcase_menu_selection',
    //     array(
    //         'priority' => 2, // 优先级2，避免和基础设置冲突
    //         'title'    => '产品分类设置',
    //         'panel'    => 'showcase_panel', // 挂载到Showcase面板
    //     )
    // );

    // // 菜单选择设置项（修复：单值整数清洗）
    // $wp_customize->add_setting(
    //     'showcase_selected_menu',
    //     array(
    //         'default'           => '',
    //         'capability'        => 'edit_theme_options',
    //         'sanitize_callback' => 'absint', // 菜单ID是整数，用absint清洗
    //         'transport'         => $selective_refresh,
    //     )
    // );

    // // 构建菜单选项（原生风格）
    // $menu_choices = array(0 => __('— Select Showcase Menu —', 'cirnotob'));
    // if (function_exists('wp_get_nav_menus')) {
    //     $menus = wp_get_nav_menus();
    //     // 确保有菜单才添加选项
    //     if (!empty($menus)) {
    //         foreach ($menus as $menu) {
    //             $menu_choices[$menu->term_id] = $menu->name;
    //         }
    //     } else {
    //         $menu_choices[0] = __('No menus created yet', 'cirnotob');
    //     }
    // }

    // // 添加菜单选择控件（关键修复：挂载到正确的section）
    // $wp_customize->add_control(
    //     'showcase_selected_menu',
    //     array(
    //         'default'       => '',
    //         'label'       => __('Showcase Menu', 'cirnotob'),
    //         'description' => __('Choose the menu to display below', 'cirnotob'),
    //         'section'     => 'showcase_menu_selection', // 挂载到菜单选择分区
    //         'type'        => 'select',
    //         'choices'     => $menu_choices,
    //     )
    // );

    // // 绑定快速渲染
    // $wp_customize->selective_refresh->add_partial('showcase_selected_menu', array(
    //     'selector'            => '.showcase-wrapper .showcase-menu',
    //     'settings'            => 'showcase_selected_menu',
    //     'render_callback'  => function () {
    //         return get_theme_mod('showcase_selected_menu');
    //     },

    // ));

    // // ========== 核心：直接挂载原生菜单编辑控件到指定分区 ==========
    // // 1. 注册菜单编辑设置（复用WP核心设置类型）
    // $wp_customize->add_setting(
    //     'showcase_menu_editor_setting',
    //     array(
    //         'capability' => 'edit_theme_options',
    //         'transport'  => $selective_refresh,
    //         'sanitize_callback' => '__return_null',
    //         'default' => '',
    //     )
    // );

    // // 3. 添加自定义控件到指定分区（无报错）
    // $wp_customize->add_control(
    //     new Showcase_Menu_Editor_Control(
    //         $wp_customize,
    //         'showcase_menu_editor_control',
    //         array(
    //             'label'     => __('Menu Editor', 'cirnotob'),
    //             'section'   => 'showcase_menu_selection',
    //             'priority'  => 20, // 高优先级（显示在菜单选择框下方）
    //             'settings'  => 'showcase_menu_editor_setting', // 关联设置项
    //         )
    //     )
    // );

    // 1. 先获取你现有的“产品展示”面板ID（如果还没有，可以先注册）
    // $wp_customize->add_panel('your_product_panel_id', [...]);

    // 2. 添加一个新的 section，或者加到你现有的 section 里
    $wp_customize->add_section('showcase_product_cat_link', [
        'title'       => '产品分类管理',
        'panel'       => 'showcase_panel', // 替换成你“产品展示”面板的ID
        'description' => '点击下方按钮前往产品分类管理页面，分类会自动同步到产品菜单中。',
        'priority'    => 10,
    ]);

    // 3. 添加一个“控制项”，类型为 HTML，用来放按钮
    $wp_customize->add_setting('showcase_product_cat_button', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control(new WP_Customize_Control($wp_customize, 'showcase_product_cat_button', [
        'section'  => 'showcase_product_cat_link',
        'settings' => 'showcase_product_cat_button',
        'type'     => 'hidden', // 隐藏默认的输入框，我们用 render_content 自定义输出
    ]));
}

// 4. 渲染按钮 HTML
add_action('customize_controls_print_footer_scripts', function () {
    $admin_url = admin_url('edit-tags.php?taxonomy=product_cat&post_type=product');
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // 找到我们的控制项容器，插入自定义按钮
            var $container = $('#customize-control-showcase_product_cat_button');
            if ($container.length) {
                $container.html(`
                    <a href="<?php echo esc_url($admin_url); ?>" target="_blank" class="button button-primary" style="width:100%;text-align:center;padding:8px 12px;">
                        前往管理产品分类
                    </a>
                `);
            }
        });
    </script>
    <?php
});

// 2. 开关选择的清洗函数
function showcase_sanitize_select($input, $setting)
{
    $input = sanitize_key($input);
    $choices = $setting->manager->get_control($setting->id)->choices;
    return (array_key_exists($input, $choices)) ? $input : $setting->default;
}

// 修复后：仅在自定义器上下文加载该类
if (class_exists('WP_Customize_Control')) { // 关键：先判断类是否存在
    class Showcase_Menu_Editor_Control extends WP_Customize_Control
    {
        public $type = 'native_menu_editor';

        public function render_content()
        {
            // 初始选中的菜单ID（仅用于首次渲染）
            $selected_menu_id = $this->manager->get_setting('showcase_selected_menu')->value();
            // 生成唯一ID（避免多控件冲突）
            $unique_id = 'showcase-product-selector-' . uniqid();

            $created_menus = wp_get_nav_menus(array('count' => -1)); // -1 表示获取所有菜单
            $created_menu_count = count($created_menus);
    ?>
            <div class="cirnotob-showcase-wrap">
                <!-- 未创建菜单时的提示 -->
                <div class="showcase-menu-tip" style="<?php echo $created_menu_count ? 'display:none;' : ''; ?>">
                    <p class="description">菜单实例为空，<a
                            href="<?php echo esc_url(admin_url('nav-menus.php')); ?>"
                            class="wp-menu-edit-link"
                            target="_blank">点击跳转创建一个新菜单</a></p>
                </div>

                <!-- 1. 未选择菜单时的提示 -->
                <div class="showcase-menu-tip" style="<?php echo $selected_menu_id ? 'display:none;' : ''; ?>">
                    <p class="description">请先在上方选择一个菜单</p>
                </div>

                <!-- 1. 未选择菜单时的提示（初始显示） -->
                <div class="showcase-menu-tip">
                    <p class="description">展示列表为空，请先在下方输入下拉框选择一个产品添加进展示列表中</p>
                </div>

                <!-- 2. 菜单编辑区域（初始隐藏/显示） -->
                <div class="showcase-menu-editor showcase-row" id="showcase-menu-editor" style="display:none;">
                    <label>编辑产品展示列表（拖动可进行排序）</label>
                    <!-- 菜单项列表（支持拖拽/编辑） -->
                    <ul id="showcase-menu-list" data-id="<?= $selected_menu_id ?>"></ul>
                    <!-- 菜单项模板（用于动态生成） -->
                    <template id="showcase-menu-item-template">
                        <li class="showcase-menu-item" data-id="{ID}" data-index="{INDEX}">
                            <dl class="menu-item-bar">
                                <dt class="menu-item-handle">
                                    <span class="item-title">{TITLE}</span>
                                    <span class="item-controls">
                                        <button type="button" class="item-delete"></button>
                                    </span>
                                </dt>
                            </dl>
                        </li>
                    </template>
                </div>

                <div class="showcase-dropdown-container showcase-row">
                    <!-- 下拉菜单（原生select，基于WP内置样式增强） -->
                    <label>产品选择器</label>
                    <div class="showcase-dropdown-wrapper wp-core-ui">
                        <!-- 输入框（用于搜索） -->
                        <input type="text" id="showcase-input" class="showcase-input"
                            placeholder="请输入产品名" name="showcase-input">
                        <div id="showcase-dropdown" class="showcase-dropdown" style="display: none;"></div>
                        <!-- 下拉菜单项模板（用于动态生成） -->
                        <template id="showcase-dropdown-template">
                            <div class="showcase-dropdown-item" data-id="{ID}" title="{DESC}">
                                <span class="showcase-dropdown-item-title">{TITLE}</span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="showcase-footer showcase-row">
                    <p id="showcase-notice"></p>
                    <!-- 文字输入框（复用WP内置样式） -->
                    <label>产品展示列表编辑结束后记得点下方的保存按钮</label>
                    <div class="showcase-footer-controls">
                        <!-- 添加菜单项按钮 -->
                        <button type="button" class="button button-secondary" id="showcase-add">添加产品到列表</button>
                        <!-- 保存按钮 -->
                        <button type="button" class="button button-primary" id="showcase-save">保存产品展示列表</button>
                    </div>
                </div>
            </div>
<?php
        }
    }
}
