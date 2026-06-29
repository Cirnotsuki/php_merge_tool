<?php
get_header();
$categories = Cirnotob_Post_Util::getCategories();
$siteName = get_bloginfo('name');
$name = get_query_var('route_name');
$type = get_query_var('route_type');
logger($categories);
$categoryObj = null;
if ($type === 'category') {
    foreach ($categories as $category) {
        if ($category->slug === $name) {
            $categoryObj = $category;
            break;
        }
    }
}
if (empty($categoryObj)) {
    $type = 'blog';
}
// 预留背景图接口
$blogHeaderBg = '';
// get_theme_mod('blog_header_bg');
?>
<section class="cirnotob-blog-categories">
    <div class="container padding-top-60" style="display: none;">
        <!-- 分类导航 -->
        <div class="row">
            <div class="col-md-12">
                <div class="blog-category-nav">
                    <a class="blog-category-item active" href="<?= home_url('/blog'); ?>">
                        Blog
                    </a>
                    <?php foreach ($categories as $category) : ?>
                        <a
                            class="blog-category-item"
                            href="<?= esc_url(get_category_link($category->term_id)); ?>">
                            <?= esc_html($category->name);
                            ?>
                        </a>
                    <?php endforeach;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="cirnotob-fullwidth-black" style="<?= !empty($blogHeaderBg) ? 'background-image:url(' . esc_url($blogHeaderBg) . ');' : '' ?>">
        <div class="blog-overlay"></div>
        <div class="container padding-top-60">
            <!-- Header -->
            <div class="row">
                <!-- 面包屑 -->
                <div class="col-md-12">
                    <?php
                    $breadcrumb = [];
                    if ($type === 'category') {
                        $breadcrumb = [
                            'Home' => '/',
                            'Blog' => '/blog',
                            $categoryObj->name => ''
                        ];
                    } else {
                        $breadcrumb = [
                            'Home' => '/',
                            'Blog' => '',
                        ];
                    }
                    echo Cirnotob_Breadcrumb::render($breadcrumb);
                    ?>
                </div>
                <div class="col-md-12">
                    <?php if ($type === 'category'): ?>
                        <div class="blog-header-content category">
                            <h1 class="blog-title"><?= $categoryObj->name ?></h1>
                            <div class="blog-description"><?= $categoryObj->description ?></div>
                        </div>
                    <?php else: ?>
                        <div class="blog-header-content">
                            <h1 class="blog-title">
                                <?= esc_html($siteName);
                                ?>
                                <span>Blog</span>
                            </h1>
                            <div class="blog-description">
                                Practical Glasses guides and manufacturing insights to simplify your next project.
                            </div>
                            <div class="blog-header-search">
                                <?= Cirnotob_Search_Form::render([
                                    'tag' => 'div',
                                    'placeholder' => 'Search the blog',
                                    'useIcon' => true
                                ]) ?>
                            </div>
                        </div>
                    <?php endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($type === 'category'): ?>
        <?php
        $featuredQuery = Cirnotob_Post_Util::getPopularPostList(4, $name);
        // 当前分类：精选5篇(1大图+4小卡片)、最新4篇
        $featuredPost = !empty($featuredQuery['list']) ? array_shift($featuredQuery['list']) : null;
        ?>
        <div class="container blog-content padding-top-20">
            <!-- Featured Posts 区块 -->
            <div class="row">
                <div class="col-md-12">
                    <div class="blog-section-title">
                        <span></span>
                        <h2>Featured Posts</h2>
                    </div>
                </div>
            </div>
            <div class="row blog-featured padding-top-20 padding-bottom-20">
                <!-- 左侧大图 col-md-6 -->
                <?php if ($featuredPost) : ?>
                    <div class="col-md-6 col-sm-12">
                        <a class="featured-post" href="<?= esc_attr(home_url('/') . $featuredPost['link']); ?>">
                            <div class="post-thumbnail">
                                <?= get_the_post_thumbnail($featuredPost['id'], 'large');
                                ?>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <a class="featured-post" href="<?= esc_attr(home_url('/') . $featuredPost['link']); ?>">
                            <div class="post-category">
                                <?php
                                $categories = $featuredPost['categories'];
                                echo !empty($categories)
                                    ? esc_html($categories[0]['name'])
                                    : 'Blog';
                                ?>
                            </div>
                            <h3 class="post-title">
                                <?= esc_html($featuredPost['title']);
                                ?>
                            </h3>
                            <div class="post-content">
                                <?= esc_html($featuredPost['content']);
                                ?>
                            </div>
                            <div class="post-date">
                                <?= esc_html(date('M d, Y', strtotime($featuredPost['date']))); ?>
                            </div>
                        </a>
                    </div>
                <?php endif;
                ?>
            </div>
            <!-- 右侧4个小卡片 2列 -->
            <?= Cirnotob_Search_Result::render(['result' => $featuredQuery, 'viewType' => 'grid', 'hideHeader' => true]); ?>
            <!-- Latest Posts 两栏 -->
            <div class="row padding-top-40">
                <div class="col-md-12">
                    <div class="blog-section-title">
                        <span></span>
                        <h2>Latest Posts</h2>
                    </div>
                </div>
            </div>
            <div class="row padding-bottom-40">
                <?= (new Cirnotob_Search_Result())->render([
                    'type' => 'blog',
                    'category' => $categoryObj->slug,
                    'viewType' => 'card',
                    'hideHeader' => true,
                    'search' => 10,
                ]) ?>
            </div>
        </div>
    <?php else: ?>
        <div class="container blog-content">
            <div class="row blog-featured padding-top-40">
                <div class="col-md-12">
                    <div class="blog-section-title">
                        <span></span>
                        <h2>Popular Articles</h2>
                    </div>
                </div>
            </div>
            <?php
            $popularPosts = Cirnotob_Post_Util::getPopularPostList(4);
            $categories = Cirnotob_Post_Util::getCategories();

            echo Cirnotob_Search_Result::render([
                'result' => $popularPosts,
                'viewType' => 'picture-grid',
                'className' => 'blog-featured',
                'hideHeader' => true
            ]);
            ?>
            <div class="row blog-category-content padding-top-40">
                <?php foreach ($categories as $category) : ?>
                    <?php
                    $query = Cirnotob_Post_Util::getBlog('', $category->term_id, 1, 5);
                    if (!$query->have_posts()) {
                        continue;
                    }
                    ?>
                    <div class="col-md-6 col-sm-12">
                        <div class="blog-category-card">
                            <div class="category-card-title">
                                <span></span>
                                <h2><?= esc_html($category->name);
                                    ?></h2>
                            </div>
                            <div class="category-post-list">
                                <?php foreach ($query->posts as $post) : ?>
                                    <a
                                        class="category-post-item"
                                        href="<?= get_permalink($post->ID); ?>">
                                        <span class="dot"></span>
                                        <span class="post-title">
                                            <?= esc_html($post->post_title);
                                            ?>
                                        </span>
                                    </a>
                                <?php endforeach;
                                ?>
                            </div>
                            <a
                                class="category-more-btn"
                                href="<?= esc_url(home_url('/blog/category/' . $category->slug)); ?>">
                                <span>
                                    View more about <?= esc_html($category->name);
                                                    ?>
                                </span>
                                <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach;
                ?>
            </div>
            <?php
            $latestPosts = Cirnotob_Post_Util::getBlog('', '', 1, 6);
            ?>
            <div class="row latest-post-content">
                <div class="col-md-12">
                    <div class="blog-section-title">
                        <span></span>
                        <h2>Latest Posts</h2>
                    </div>
                </div>
                <?php foreach ($latestPosts->posts as $post) : ?>
                    <?php
                    $categories = Cirnotob_Post_Util::get_post_category($post->ID, $post->post_type);
                    $description = get_the_excerpt($post->ID);
                    if (empty($description)) {
                        $description = wp_strip_all_tags($post->post_content);
                    }
                    $description = trim($description);
                    ?>
                    <div class="col-md-6 col-sm-12">
                        <a
                            class="latest-post-card"
                            href="<?= get_permalink($post->ID); ?>">
                            <div class="post-category">
                                <?= !empty($categories)
                                    ? esc_html($categories[0]->name)
                                    : 'Blog';
                                ?>
                            </div>
                            <h3 class="post-title">
                                <?= esc_html($post->post_title);
                                ?>
                            </h3>
                            <div class="post-description">
                                <?= esc_html($description);
                                ?>
                            </div>
                            <div class="post-date">
                                <?= get_the_date('M d, Y', $post->ID);
                                ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach;
                ?>
            </div>
        </div>
        <div class="container blog-search-content padding-top-20 padding-bottom-20" style=" display:none;">
            <div class="col-search col-md-12 col-sm-12">
                <?= (new Cirnotob_Search_Result())->render(['showBack' => true, 'vieType' => 'grid']) ?>
            </div>
        </div>
    <?php endif;
    ?>
</section>
<?php get_footer();
?>