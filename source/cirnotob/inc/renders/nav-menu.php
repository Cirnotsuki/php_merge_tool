<?php
// 自定义渲染类
class Cirnotob_NavMenu
{

    // 构造函数（可选）
    public function __construct() {}

    // 核心：输出 HTML 内容的方法
    public function render()
    {
        // 这里面随便写 PHP + HTML

        // 获取主题位置的菜单（primary_menu = 你的主题位置）
        // $location = 'primary_menu';
        // $locations = get_nav_menu_locations();

        // if (!isset($locations[$location]) || !$locations[$location]) {
        //     return '';
        // }

        // $menu_id = $locations[$location];
        // $items = wp_get_nav_menu_items($menu_id);

        // if (!$items) {
        //     return '';
        // }


        // 开始渲染
        ob_start();
?>

        <!-- 你完全自由写的菜单 HTML -->
        <ul class="menu-wrap">
            <?php
            $items = [
                (object)[
                    'title' => 'Products',
                    'link' => 'product',
                    'icon' => 'fa-list'
                ],
                // (object)[
                //     'title' => 'Capabilities',
                //     'link' => 'capabilities',
                // ],
                (object)[
                    'title' => 'Support',
                    'dropdown' => 1,

                ],
                (object)[
                    'title' => 'About US',
                    'dropdown' => 2,
                ],
            ];
            foreach ($items as $item) {
                $this->render_item($item);
            }
            ?>
        </ul>

    <?php
        return ob_get_clean();
    }

    // 渲染菜单项
    private function render_item($item)
    {
        $icon = $item->icon ?? '';
        $dropDown = (int) ($item->dropdown ?? 0);
        $link = $item->link ?? '';
        $tag = 'span';
        if (!empty($link) && $link !== '#') {
            $tag = 'a';
        }
    ?>

        <li class="menu-item">
            <<?= $tag ?> class="menu-item-label" <?php echo $tag === 'a' ? 'href="' . esc_url(home_url() . '/' . ltrim($link, '/')) . '"' : ''; ?>>
                <?php
                if (!empty($icon)) {
                    echo '<i class="icon fa ' . esc_html($icon) . '"></i>';
                }
                ?>
                <span><?= esc_html($item->title); ?></span>
                <?php
                if (empty($icon) && !empty($dropDown)) {
                    echo '<i class="dropdown fa fa-chevron-up"></i>';
                }
                ?>
            </<?= $tag ?>>
            <?php
            if (!empty($dropDown)) {
                $this->render_dropdown($dropDown);
            } ?>
        </li>
        <?php }

    private function render_dropdown($dropDown)
    {
        switch ($dropDown) {
            case 1:
                // 读取主题自定义器配置（与你最新修改的字段完全对应）
                $hide_cta = get_theme_mod('hide_show_call_actions', 'on');
                if ($hide_cta !== 'on') return;

                // ======================
                // Help Center (第一个)
                // ======================
                $help_icon    = get_theme_mod('call_action_button_icon', 'fa-question-circle');
                $help_title   = get_theme_mod('call_action_button_title');
                $help_text    = get_theme_mod('call_action_button_label');
                $help_desc    = get_theme_mod('call_action_button_desc');
                $help_link    = get_theme_mod('call_action_button_link');

                // ======================
                // Contact (第二个)
                // ======================
                $contact_icon = get_theme_mod('call_action_button2_icon', 'fa-bell');
                $contact_title = get_theme_mod('call_action_button2_title');
                $contact_text = get_theme_mod('call_action_button2_label');
                $contact_desc = get_theme_mod('call_action_button2_desc');
                $contact_link = get_theme_mod('call_action_button2_link');

                // ======================
                // Email (第三个)
                // ======================
                $email_icon   = get_theme_mod('call_action_button3_icon', 'fa-envelope');
                $email_title  = get_theme_mod('call_action_button3_title');
                $email_text   = get_theme_mod('call_action_button3_label');
                $email_desc   = get_theme_mod('call_action_button3_desc');
                $email_link   = get_theme_mod('call_action_button3_link');
        ?>

                <div class="menu-dropdown">
                    <!-- 左侧：Contact Us -->
                    <div class="dropdown-box dropdown-contact">
                        <div class="dropdown-box-inner">
                            <h3>Contact Us</h3>
                            <!-- Contact -->
                            <div class="dropdown-item">
                                <i class="fa <?php echo esc_attr($contact_icon); ?>"></i>
                                <div class="text-wrap">
                                    <a href="<?php echo esc_url($contact_link ?: '#'); ?>">
                                        <strong><?php echo esc_html($contact_title ?: 'Contact'); ?></strong>
                                    </a>
                                    <p><?php echo esc_html($contact_desc); ?></p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="dropdown-item">
                                <i class="fa <?php echo esc_attr($email_icon); ?>"></i>
                                <div class="text-wrap">
                                    <a href="<?php echo esc_url($email_link ?: '#'); ?>">
                                        <strong><?php echo esc_html($email_title ?: 'Email'); ?></strong>
                                    </a>
                                    <p><?php echo esc_html($email_desc); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 右侧：Support -->
                    <div class="dropdown-box dropdown-support">
                        <div class="dropdown-box-inner">
                            <h3>Support</h3>
                            <!-- Help Center -->
                            <div class="dropdown-item">
                                <div class="text-wrap">
                                    <a href="<?php echo esc_url($help_link ?: '#'); ?>">
                                        <strong><?php echo esc_html($help_title ?: 'Help Center'); ?></strong>
                                    </a>
                                    <p><?php echo esc_html($help_desc); ?></p>
                                </div>
                            </div>

                            <div class="dropdown-item">
                                <div class="text-wrap">
                                    <a href="<?php echo esc_url('/blog'); ?>">
                                        <strong>Blog</strong>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php break;
            case 2:
                // 获取页面
                $about_page   = get_page_by_path('about-us');
                $explore_page = get_page_by_path('explore-us');
            ?>

                <div class="menu-dropdown" index="<?= $dropDown ?>">
                    <div class="dropdown-box dropdown-about">
                        <?php if ($about_page): ?>
                            <div class="dropdown-box-inner">
                                <h3 class="dropdown-heading"><?php echo esc_html($about_page->post_title); ?></h3>
                                <div class="dropdown-desc">
                                    <?php echo apply_filters('the_content', $about_page->post_content); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="dropdown-box dropdown-explore">
                        <?php if ($explore_page): ?>
                            <div class="dropdown-box-inner">
                                <h3 class="dropdown-heading"><?php echo esc_html($explore_page->post_title); ?></h3>
                                <div class="dropdown-desc">
                                    <?php echo apply_filters('the_content', $explore_page->post_content); ?>
                                </div>
                            </div>
                            <?php
                            $image = wp_get_attachment_url(get_post_thumbnail_id($explore_page->ID));

                            if (!empty($image)) {
                                echo '<div class="dropdown-image"><img src="' . esc_url($image) . '" alt="Explore Us"></div>';
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php break;
            default: ?>
<?php break;
        }
    }
}
