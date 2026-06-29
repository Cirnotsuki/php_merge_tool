<!-- Header Widget Info -->
<div class="navigator-wrapper">
    <!-- Mobile Toggle -->
    <div class="theme-mobile-nav d-lg-none d-block <?php echo esc_attr(specia_sticky_menu()) . ' ' . esc_attr(Cirnotob_Util::navigationActive()); ?>">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="theme-mobile-menu">
                        <div class="headtop-mobi">
                            <div class="headtop-shift">
                                <a href="javascript:void(0);" class="header-sidebar-toggle open-toggle"><span></span></a>
                                <a href="javascript:void(0);" class="header-sidebar-toggle close-button"><span></span></a>
                                <div id="mob-h-top" class="mobi-head-top animated"></div>
                            </div>
                        </div>
                        <div class="mobile-logo">
                            <?php
                            if (has_custom_logo()) {
                                the_custom_logo();
                            } else { ?>
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="navbar-brand">
                                    <?php echo esc_html(get_bloginfo('name')); ?>
                                </a>
                            <?php }
                            $cirnotob_description = get_bloginfo('description');
                            if ($cirnotob_description) : ?>
                                <p class="site-description"><?php echo esc_html($cirnotob_description); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="menu-toggle-wrap">
                            <div class="hamburger-menu">
                                <a href="javascript:void(0);" class="menu-toggle">
                                    <div class="top-bun"></div>
                                    <div class="meat"></div>
                                    <div class="bottom-bun"></div>
                                </a>
                            </div>
                        </div>
                        <div id="mobile-m" class="mobile-menu">
                            <div class="mobile-menu-shift">
                                <a href="javascript:void(0);" class="close-style close-menu"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- / -->
    <!-- Top Menu -->
    <div class="xl-nav-area d-none d-lg-block">
        <div class="navigation <?php echo esc_attr(specia_sticky_menu()) . ' ' . esc_attr(Cirnotob_Util::navigationActive()); ?>">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="theme-menu">
                            <div class="logo">
                                <?php
                                if (has_custom_logo()) {
                                    the_custom_logo();
                                } else { ?>
                                    <a href="<?php echo esc_url(home_url('/')); ?>" class="navbar-brand">
                                        <?php echo esc_html(get_bloginfo('name')); ?>
                                    </a>
                                <?php }
                                $cirnotob_description = get_bloginfo('description');
                                if ($cirnotob_description): ?>
                                    <p class="site-description">
                                        <?php echo esc_html($cirnotob_description); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <!-- 导航栏 -->
                            <nav class="menubar">
                                <?php echo (new Cirnotob_NavMenu())->render(); ?>
                            </nav>
                            <div class="menu-right">
                                <ul class="wrap-right">
                                    <li class="search-button">
                                        <button role="button" class="search-toggle" aria-label="<?php esc_attr_e('Search', 'cirnotob'); ?>">
                                            <i class="fa fa-search" aria-hidden="true"></i>
                                        </button>
                                    </li>
                                    <?php
                                    $cirnotob_header_cart        = get_theme_mod('header_cart', '1');
                                    if ($cirnotob_header_cart == '1') { ?>
                                        <li class="cart-wrapper">
                                            <div class="cart-icon-wrap">
                                                <?php if (class_exists('WooCommerce')) { ?>
                                                    <a href="javascript:void(0)" id="cart"><i class="fa fa-shopping-bag"></i>
                                                        <?php
                                                        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                                                            $count = WC()->cart->cart_contents_count;
                                                            $cart_url = wc_get_cart_url();

                                                            if ($count > 0) {
                                                        ?>
                                                                <span><?php echo esc_html($count); ?></span>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <span><?php echo esc_html__('0', 'cirnotob'); ?></span>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </a>
                                                <?php } ?>
                                            </div>

                                            <!-- Shopping Cart -->
                                            <?php if (class_exists('WooCommerce')) { ?>
                                                <div id="header-cart" class="shopping-cart">
                                                    <div class="cart-body">
                                                        <?php get_template_part('woocommerce/cart/mini', 'cart');     ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <!--end shopping-cart -->
                                        </li>
                                    <?php
                                    }
                                    $cirnotob_hdr_btn_hs      = get_theme_mod('header_book_hide_show', '1');
                                    $cirnotob_button_label    = __('Order Now', 'cirnotob');
                                    $cirnotob_button_url        = get_theme_mod('button_url');
                                    $cirnotob_button_target     = get_theme_mod('button_target');

                                    if (($cirnotob_button_target) == 1) {
                                        $cirnotob_button_target = "target='_blank'";
                                    }
                                    if ($cirnotob_hdr_btn_hs == '1') {
                                    ?>
                                        <li class="menu-item header_btn">
                                            <a href="<?php echo esc_url($cirnotob_button_url); ?>" <?php echo $cirnotob_button_target; ?> class="bt-primary bt-effect-2"><?php echo esc_html($cirnotob_button_label); ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?= Cirnotob_Search_Form::render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- / -->
    </header>

    <?php
    // if (!is_page_template('templates/template-homepage-one.php')) {
    //     specia_breadcrumbs_style();
    // }
    ?>