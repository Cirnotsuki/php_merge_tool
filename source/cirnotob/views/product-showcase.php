<?php
/*
Template Name: Products Showcase
*/
get_header();

wp_enqueue_style('products-showcase-style');
wp_enqueue_script('products-showcase-script');

// 🔥 直接获取 WooCommerce 产品分类（已过滤未分类 + 按order排序 + 重置索引）
$showcase_menu_items = Cirnotob_Product_Util::getProductCategories();
?>

<!-- Products Showcase Section -->
<section class="page-wrapper cirnotob-product-showcase">
    <div class="container">
        <div class="row padding-top-80 padding-bottom-60">
            <!-- 左侧分类栏 -->
            <div class="col-sidebar col-md-2 col-sm-12">
                <!-- 产品分类 -->
                <div class="sidebar-categories">
                    <h3 class="category-title"><?= __("Product Categories", "cirnotob") ?></h3>
                    <ul class="category-list">
                        <li class="category-item active"><span>All</span></li>
                        <?php foreach ($showcase_menu_items as $index => $item) : ?>
                            <li class=" category-item" data-id="<?= esc_attr($item['id']) ?>" data-name="<?= esc_attr($item['slug']) ?>">
                                <span><?= esc_html($item['name']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- 右侧主内容区 -->
            <div class="col-main col-md-10 col-sm-12">
                <!-- 顶部标题与筛选栏 -->
                <div class="products-header">
                    <h1 class="products-title"><span class="products-title--category">All</span> <span><?= __("Products", "cirnotob") ?></span></h1>

                    <div class="products-controls">
                        <!-- 搜索 -->
                        <div class="products-search">
                            <?= Cirnotob_Search_Form::render([
                                'tag' => 'div',
                                'placeholder' => 'Search products',
                                'useIcon' => true,
                                'plain' => true

                            ]) ?>
                        </div>
                        <div class="filter-dropdown">
                            <select class="form-select" id="product-select">
                                <option value="new"><?= __("New Products", "cirnotob") ?></option>
                                <option value="price_asc"><?= __("Price: Low to High", "cirnotob") ?></option>
                                <option value="price_desc"><?= __("Price: High to Low", "cirnotob") ?></option>
                            </select>
                        </div>
                        <div class="view-toggle">
                            <button class="view-btn" name="list"><i class="fa fa-th-list"></i></button>
                            <button class="view-btn active" name="grid"><i class="fa fa-th"></i></button>
                        </div>
                    </div>
                </div>

                <!-- 商品网格（原样保留） -->
                <div class="products-grid">
                </div>

                <div class="cirnotob-empty" style="display: none;">
                    <span alt="no result"></span>
                    <p>No Product Found</p>
                    <p>Try a different categories, or browse the</p>
                    <p><a href="<?= home_url('http://localhost:8001/help/article/') ?>">help center</a></p>
                </div>

                <div class="cirnotob-loading" style="display: none;">
                    <span alt="searching"></span>
                    <p><?= __('Searching...', 'cirnotob') ?></p>
                </div>

                <div class="products-footer">
                    <div class="cirnotob-paging"
                        data-show-count="0"
                        data-middle-range="0"
                        data-front-threshold="0"
                        data-back-threshold="0"></div>
                </div>
            </div>
        </div>
        <template id="productCard">
            <a class="product-card" href="<?= home_url('product/') ?>{SLUG}">
                <div class="product-image">
                    <div class="product-image-box">
                        <img src="{THUMBNAIL}" alt="{TITLE}">
                        <span>NO IMAGE</span>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name" title="{TITLE}">{TITLE}</h3>
                    <div class="product-desc">{EXCERPT}</div>
                    <div class="product-price-group">
                        <div class="product-price price-sale"><span class="money-type">{MONEYTYPE}</span><span>{SALEPRICE}</span></div>
                        <div class="product-price price-regular"><span class="money-type">{MONEYTYPE}</span><span>{PRICE}</span></div>
                        <div class="discount-tag">{DISCOUNT}% OFF</div>
                    </div>

                    <div class="product-price-group">
                        <div class="product-price price-regular">
                            <span class="money-type">{MONEYTYPE}</span><span>{PRICEMIN}</span>
                            <span> - </span>
                            <span class="money-type">{MONEYTYPE}</span><span>{PRICEMAX}</span>
                        </div>
                    </div>
                </div>
                <div class="product-actions">
                    <button class="btn-chat-now bt-primary bt-effect-2 full-width" link="{CHATLINK}"><?= __("Chat Now", "cirnotob") ?></button>
                </div>
            </a>
        </template>
    </div>
</section>
<!-- End of Products Showcase Section -->

<!-- 完整样式表 -->
<style>

</style>

<?php get_footer(); ?>