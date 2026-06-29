<?php
/*
 * 商品详情页模板
 * 支持：
 * - 左侧纵向缩略图
 * - 主图横向滑动
 * - Lightbox 放大
 * - WooCommerce 产品字段
 * - 同分类最新产品推荐
 */

get_header();
wp_enqueue_style('swiper');
wp_enqueue_style('glightbox');

$route_name = get_query_var('route_name');

if (empty($route_name)) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    get_footer();
    exit;
}

/* ==============================================
   获取产品
============================================== */
$product = null;

if (is_numeric($route_name)) {
    $product = Cirnotob_Product_Util::getProduct((int)$route_name);
} else {
    $product_id = Cirnotob_Post_Util::getPostIdBySlug($route_name);
    if ($product_id > 0) {
        $product = Cirnotob_Product_Util::getProduct($product_id);
    }
}

if (!$product) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    get_footer();
    exit;
}

/* ==============================================
   预加载主图
============================================== */
if (!empty($product['full_image'])) {
    echo '<link rel="preload" as="image" href="' . esc_url($product['full_image']) . '" fetchpriority="high">';
}

/* ==============================================
   评分
============================================== */
$average_rating = get_post_meta($product['id'], '_wc_average_rating', true);
$review_count   = get_post_meta($product['id'], '_wc_review_count', true);
?>

<section class="page-wrapper cirnotob-product-page">
    <div class="container">
        <!-- 面包屑 -->
        <div class="row padding-top-40 padding-bottom-40">
            <!-- <div class="col-md-12">
                <nav class="woocommerce-breadcrumb">
                    <a href="<?= esc_url(home_url('/')) ?>">
                        <?= get_bloginfo('name') ?>
                    </a>
                    <span class="delimiter">/</span>
                    <a href="<?= esc_url(home_url('/product')) ?>">Products</a>
                    <?php if (!empty($product['categories'])): ?>
                        <span class="delimiter">/</span>
                        <a href="<?= esc_url(home_url('/product?category=' . $product['categories'][0]['slug'])) ?>">
                            <?= esc_html($product['categories'][0]['name']) ?>
                        </a>
                    <?php endif; ?>
                    <span class="delimiter">/</span>
                    <span><?= esc_html($product['title']) ?></span>
                </nav>
            </div> -->
        </div>

        <!-- 主体 -->
        <div class="row product-main-row">
            <!-- 左侧画廊 -->
            <div class="col-lg-7 col-md-12">
                <div class="product-gallery">
                    <div class="product-gallery-layout">
                        <!-- 缩略图 -->
                        <?php if (count($product['gallery']) > 1): ?>
                            <div class="product-thumbs-wrap">
                                <div class="swiper productThumbSwiper">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($product['gallery'] as $index => $image): ?>
                                            <div class="swiper-slide">
                                                <img
                                                    src="<?= esc_url($image['thumbnail']) ?>"
                                                    alt="<?= esc_attr($product['title'] . ' Thumbnail ' . ($index + 1)) ?>"
                                                    loading="lazy"
                                                    width="100"
                                                    height="100">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 主图 -->
                        <div class="product-main-wrap">
                            <div class="swiper productSwiper <?= count($product['gallery']) <= 1 ? 'single-image' : '' ?>">
                                <div class="swiper-wrapper">
                                    <?php foreach ($product['gallery'] as $index => $image): ?>
                                        <div class="swiper-slide">
                                            <a href="<?= esc_url($image['full']) ?>" class="glightbox">
                                                <img
                                                    src="<?= esc_url($image['full']) ?>"
                                                    alt="<?= esc_attr($product['title'] . ' Image ' . ($index + 1)) ?>"
                                                    loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                                                    fetchpriority="<?= $index === 0 ? 'high' : 'low' ?>"
                                                    width="800"
                                                    height="800">
                                                <div class="zoom-icon">
                                                    <i class="fa fa-search-plus"></i>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (count($product['gallery']) > 1): ?>
                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-pagination"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 中间信息 -->
            <div class="col-lg-5 col-md-12">
                <div class="product-summary">
                    <h1 class="product-title"><?= esc_html($product['title']) ?></h1>

                    <!-- Meta -->
                    <div class="product-meta">
                        <?php if (!empty($product['sku'])): ?>
                            <span class="sku">SKU: <?= esc_html($product['sku']) ?></span>
                        <?php endif; ?>
                        <span class="stock-status <?= esc_attr($product['stock_status']) ?>">
                            <?php
                            if ($product['stock_status'] === 'instock') {
                                echo __('In Stock', 'cirnotob');
                            } elseif ($product['stock_status'] === 'outofstock') {
                                echo __('Out of Stock', 'cirnotob');
                            } else {
                                echo __('Backorder', 'cirnotob');
                            }
                            ?>
                        </span>
                    </div>

                    <!-- Rating -->
                    <?php if ($review_count > 0): ?>
                        <div class="product-rating">
                            <?= wc_get_rating_html($average_rating, $review_count) ?>
                            <span class="review-count">(<?= esc_html($review_count) ?>)</span>
                        </div>
                    <?php endif; ?>

                    <!-- Price -->
                    <div class="product-price">
                        <?= $product['price_html'] ?>
                        <?php if (!empty($product['sale_price']) && !empty($product['regular_price'])): ?>
                            <span class="discount">
                                <?= sprintf(
                                    '-%d%%',
                                    round(100 - (((float)$product['sale_price'] / (float)$product['regular_price']) * 100))
                                ) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- 简介 -->
                    <?php if (!empty($product['short_description'])): ?>
                        <div class="product-short-description">
                            <?= wp_kses_post($product['short_description']) ?>
                        </div>
                    <?php endif; ?>

                    <!-- 属性 -->
                    <?php if (!empty($product['attributes'])): ?>
                        <div class="product-attributes">
                            <?php foreach ($product['attributes'] as $attribute): ?>
                                <div class="attribute-item">
                                    <div class="attribute-label"><?= esc_html($attribute['name']) ?></div>
                                    <div class="attribute-value"><?= esc_html($attribute['value']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['external_url'])): ?>
                        <div class="product-external-btns">
                            <a
                                class="bt bt-primary"
                                href="<?= esc_url($product['external_url']) ?>"
                                target="_blank"
                                rel="noopener noreferrer">
                                <?= esc_html($product['button_text'] ?: __('Contact Now', 'cirnotob')) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 内容 -->
        <div class="row">
            <div class="col-lg-12 col-md-24">
                <div class="product-content">
                    <div class="content-header">
                        <h2><?= __('Product Details', 'cirnotob') ?></h2>
                    </div>
                    <div class="content-body">
                        <?= wp_kses_post($product['content']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 相关推荐 -->
        <?php if (!empty($product['related_products'])): ?>
            <div class="row">
                <div class="col-lg-12 col-md-24">
                    <div class="related-products">
                        <div class="section-title">
                            <h2><?= __('Related Products', 'cirnotob') ?></h2>
                        </div>
                        <div class="row">
                            <?php foreach ($product['related_products'] as $related): ?>
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <a class="related-product-card" href="<?= esc_url($related['url']) ?>">
                                        <div class="related-image">
                                            <?php if (!empty($related['thumbnail'])): ?>
                                                <img
                                                    src="<?= esc_url($related['thumbnail']) ?>"
                                                    alt="<?= esc_attr($related['title']) ?>"
                                                    loading="lazy"
                                                    width="300"
                                                    height="300">
                                            <?php else: ?>
                                                <div class="related-no-image">
                                                    <span>NO IMAGE</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="related-info">
                                            <h3><?= esc_html($related['title']) ?></h3>
                                            <div class="related-price">
                                                <?= $related['price_html'] ?>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Schema.org -->
<script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "Product",
        "name": "<?= esc_js($product['title']) ?>",
        "image": "<?= esc_js($product['full_image']) ?>",
        "description": "<?= esc_js(wp_strip_all_tags($product['short_description'] ?: $product['content'])) ?>",
        "sku": "<?= esc_js($product['sku']) ?>",
        "brand": {
            "@type": "Brand",
            "name": "<?= esc_js(get_bloginfo('name')) ?>"
        },
        "offers": {
            "@type": "Offer",
            "url": "<?= esc_js($product['url']) ?>",
            "priceCurrency": "<?= esc_js(get_woocommerce_currency()) ?>",
            "price": "<?= esc_js($product['price']) ?>",
            "availability": "<?= $product['stock_status'] === 'instock' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>"
        }
    }
</script>

<?php get_footer(); ?>