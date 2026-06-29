<?php
$cirnotob_hs_showcase = get_theme_mod('hide_show_showcase', 'on');
if ($cirnotob_hs_showcase == 'on'):

    // ==========================
    // 🔥 关键改动：直接获取 WooCommerce 产品分类
    // ==========================
    $showcase_menu_items = Cirnotob_Product_Util::getProductCategories('', 50); // 获取所有产品分类
    $showcase_title = get_theme_mod('showcase_title', 'showcase');

    if (!empty($showcase_menu_items)): ?>
        <section id="showcase-section" class="showcase-wrapper">
            <div class="container">
                <?php if (!empty($showcase_title)): ?>
                    <div class="row padding-top-30 padding-bottom-30">
                        <div class="col-md-12 col-sm-12">
                            <h2 class="showcase-title wow fadeInDown text-left">
                                <?= esc_html($showcase_title) ?>
                            </h2>
                        </div>
                    </div>
                <?php endif ?>

                <div class="showcase-box row padding-top-30 padding-bottom-30">
                    <div class="showcase-menu wow fadeInLeft">
                        <ul class="showcase-menu-list">
                            <?php foreach ($showcase_menu_items as $index => $item) : ?>
                                <li class="showcase-menu-item <?= $index === 0 ? 'active' : '' ?>">
                                    <?= esc_html($item['name']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <?php foreach ($showcase_menu_items as $index => $item) :
                        $image = $item['thumbnail'] ?: '';
                        $title = $item['name'];
                        $excerpt = $item['description'];
                        $contact_link = get_theme_mod('call_action_button2_link');
                    ?>
                        <div class="showcase-product" style="<?= $index > 0 ? 'display: none;' : '' ?>">
                            <div class="showcase-product-image wow fadeInUp">
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>">
                            </div>
                            <div class="showcase-product-info wow fadeInUp">
                                <div class="info-card">
                                    <h3 class="info-card-title">
                                        <?php echo esc_html($title); ?>
                                    </h3>
                                    <ul class="info-card-excerpt">
                                        <?php
                                        $lines = preg_split("/(\s*?\n)+/", $excerpt);
                                        foreach ($lines as $line) {
                                            echo "<li>" . esc_html($line) . "</li>";
                                        }
                                        ?>
                                    </ul>

                                    <a role="button" class="bt-primary" href="<?php echo esc_url($contact_link ?: '#'); ?>"
                                        aria-label="<?php esc_attr_e('Contact Us', 'cirnotob'); ?>">
                                        <?php esc_attr_e('Contact Us', 'cirnotob'); ?>
                                    </a>
                                    <a role="button" class="bt-primary bt-white"
                                        href="<?= home_url('product/?category=' . $item['name']) ?>"
                                        aria-label="<?= esc_html__($title) . ' ' . esc_attr__('Products', 'cirnotob'); ?>">
                                        <?= esc_html__($title, 'cirnotob') ?> <?= esc_html__('Products', 'cirnotob'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <div class="clearfix"></div>
<?php endif;
    wp_reset_postdata();
endif; ?>