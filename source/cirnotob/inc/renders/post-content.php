<?php
if (!defined('ABSPATH')) exit;

class Cirnotob_Post_Content
{
    public function render($post, $colslength = 12)
    {
        // 开始渲染
        ob_start();
?>
        <div class="cirnotob-post-content col-md-<?= $colslength ?> col-sm-12">
            <div class="col-content col-md-9 col-sm-12">
                <div class="cirnotob-post-article">
                    <?php if (empty($post)) : ?>
                        <p><?php _e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'specia'); ?></p>
                        <?= Cirnotob_Search_Form::render() ?>
                    <?php else : ?>
                        <h1 class="article-title"><?= esc_html($post->post_title) ?></h1>
                        <div class="article-last-modified">
                            <i class="fa fa-clock-o"></i>
                            <span>Last updated on <?= date('F j, Y', strtotime($post->post_modified)) ?></span>
                        </div>
                        <div class="article-content">
                            <?php
                            $content = $post->post_content;
                            // 1. 运行短代码
                            $content = do_shortcode($content);
                            // 2. 自动加 <p> 换行（wpautop）
                            $content = wpautop($content);
                            // 3. 安全输出（防止 XSS）
                            echo wp_kses_post($content); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div id="cirnotob-post-nav-wrapper" class="col-content col-md-3 col-sm-12">
                <div id="cirnotob-post-nav" class="list-box open" style="display: none;">
                    <div class="nav-title"><span>Table of Contents</span><i class="fa fa-angle-right"></i></div>
                    <div class="list-inner">
                        <ul class="nav-list"></ul>
                    </div>
                </div>
                <?php
                $url_base = Cirnotob_Util::parse_url($_SERVER['REQUEST_URI'])->segments[0] ?? '';
                $type = empty($post) ? $url_base : $post->post_type;
                if ($type === 'post') {
                    $type = 'blog';
                }
                switch ($type) {
                    case 'news':
                        $newsPosts = Cirnotob_Post_Util::getPostList('news', '', '', 1, 5);
                ?>
                        <div class="cirnotob-sidebar-popular">
                            <h3 class="sidebar-title">Latest News</h3>
                            <ul class="relate-post-list news-post-list">
                                <?php if (!empty($newsPosts['list'])): ?>
                                    <?php foreach ($newsPosts['list'] as $post): ?>
                                        <li>
                                            <a href="<?= esc_url(home_url('/news/' . $post['name'])) ?>" class="relate-post-link">
                                                <span class="relate-post-title">
                                                    <?= esc_html($post['title']); ?>
                                                </span>
                                                <span class="relate-post-date">
                                                    <?= esc_html(date('M j, Y', strtotime($post['last_modified']))); ?>
                                                </span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php
                        break;
                    case 'blog':
                        $popularPosts = Cirnotob_Post_Util::getPopularPostList(5); ?>
                        <div class="cirnotob-sidebar-popular">
                            <h3 class="sidebar-title">Popular Articles</h3>
                            <ul class="relate-post-list">
                                <?php if (!empty($popularPosts['list'])): ?>
                                    <?php foreach ($popularPosts['list'] as $thePost): ?>
                                        <li>
                                            <a href="<?= esc_url(home_url('/' . $thePost['link'])) ?>" class="relate-post-link">
                                                <span class="relate-post-title">
                                                    <?= esc_html($thePost['title']); ?>
                                                </span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                <?php
                        break;

                    default:
                        # code...
                        break;
                } ?>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}
