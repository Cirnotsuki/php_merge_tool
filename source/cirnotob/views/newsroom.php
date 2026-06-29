<?php
get_header();

$categories = Cirnotob_Post_Util::getCategories('news');

logger($categories);

$featuredPosts = Cirnotob_Post_Util::getPopularPostList(1, '', 'news');

logger($featuredPosts);
$featuredPost = !empty($featuredPosts['list'])
    ? $featuredPosts['list'][0]
    : null;

$currentCategory = get_query_var('route_name');

?>
<section class="cirnotob-newsroom">
    <div class="container padding-top-60">

        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-md-12">
                <?= Cirnotob_Breadcrumb::render([
                    'Home' => '/',
                    'Newsroom' => ''
                ]) ?>
            </div>
        </div>

        <!-- Hero -->
        <?php if ($featuredPost): ?>
            <div class="row newsroom-hero padding-top-20">
                <div class="col-md-6 col-sm-12">
                    <a
                        class="hero-image"
                        href="<?= home_url('/') . $featuredPost['link'] ?>">
                        <?= get_the_post_thumbnail($featuredPost['id'], 'large') ?>
                    </a>
                </div>

                <div class="col-md-6 col-sm-12">
                    <a class="hero-content" href="<?= home_url('/') . $featuredPost['link'] ?>">
                        <div class="hero-category">
                            Newsroom
                        </div>

                        <p class="hero-title">
                            <?= esc_html($featuredPost['title']) ?>
                        </p>

                        <div class="hero-description">
                            <?= esc_html($featuredPost['content']) ?>
                        </div>

                        <div class="hero-date">
                            <?= date('M d, Y', strtotime($featuredPost['last_modified'])) ?>
                            <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Latest News -->
        <div class="row padding-top-60">
            <div class="col-md-12">
                <div class="newsroom-title">
                    <h2>Latest News</h2>
                </div>
            </div>
        </div>

        <!-- Category + Search -->
        <div class="row newsroom-toolbar padding-top-20 padding-bottom-20">
            <div class="col-md-8 col-sm-12 newsroom-menu">
                <?= Cirnotob_Tab_Menu::render(array_merge([(object)['name' => 'All']], $categories)) ?>
            </div>

            <div class="col-md-4 col-sm-12 newsroom-search">
                <?= Cirnotob_Search_Form::render([
                    'tag' => 'div',
                    'placeholder' => 'Search',
                    'useIcon' => true,
                    'plain' => true

                ]) ?>
            </div>
        </div>

        <!-- News List -->
        <div class="row newsroom-list padding-bottom-40">
            <?= Cirnotob_Search_Result::render([
                'type' => 'news',
                'category' => $currentCategory,
                'viewType' => 'picture-row',
                'catPos' => 'bottom',
                'hideHeader' => true,
                'search' => 12,
            ]) ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>