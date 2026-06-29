<?php
$cirnotob_hide_show_blog		= get_theme_mod('hide_show_blog', 'on');
$cirnotob_blog_title			= get_theme_mod('blog_title');
$cirnotob_blog_description	= get_theme_mod('blog_description');
$cirnotob_blog_display_num	= get_theme_mod('blog_display_num', '3');

if ($cirnotob_hide_show_blog == 'on') :
?>
	<section id="cirnotob-blog" class="latest-blog">
		<div class="container">
			<div class="row text-center padding-top-60 padding-bottom-20">
				<div class="col-sm-12">
					<?php if ($cirnotob_blog_title) : ?>
						<h2 class="section-heading wow zoomIn"><?php echo wp_filter_post_kses($cirnotob_blog_title); ?></h2>
					<?php endif; ?>

					<?php if ($cirnotob_blog_description) : ?>
						<p class="section-description"><?php echo esc_html($cirnotob_blog_description); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="row blow-row-tap padding-bottom-20 wow fadeInUp">
				<?=  Cirnotob_Tab_Menu::render(['News', 'FAQs', 'Blog']); ?>
			</div>
			<div class="row blog-row-area blog-row-news padding-bottom-60 wow fadeInUp">
				<?php
				$newsData = Cirnotob_Post_Util::getPostList('news', '', '', 1, 5);
				$newsList = $newsData['list'];

				if (!empty($newsList)) :
					$featured = $newsList[0];
					$featuredLink = home_url('/news/' . $featured['name']);
					$featuredDate = date('M j, Y', strtotime($featured['last_modified']));
					$featuredCategory = !empty($featured['categories']) ? $featured['categories'][0]['name'] : 'News';
				?>
					<div class="col-lg-6 col-md-12 blog-news-featured">
						<a href="<?= esc_url($featuredLink); ?>" class="news-featured-card">
							<div class="news-featured-image">
								<img
									src="<?= esc_url($featured['thumbnail']); ?>"
									alt="<?= esc_attr($featured['title']); ?>">
							</div>

							<div class="news-featured-overlay">
								<div class="news-featured-category">
									<?= esc_html($featuredCategory); ?>
								</div>

								<div class="news-featured-content">
									<h3>
										<?= esc_html($featured['title']); ?>
									</h3>

									<span class="news-featured-date">
										<?= esc_html($featuredDate); ?>
									</span>
								</div>
							</div>
						</a>
					</div>

					<div class="col-lg-6 col-md-12 blog-news-list">
						<ul>
							<?php foreach (array_slice($newsList, 1) as $news) : ?>
								<?php
								$link = home_url('/' . $news['name']);
								$date = date('M j, Y', strtotime($news['last_modified']));
								?>
								<li>
									<a href="<?= esc_url($link); ?>" class="news-list-item">
										<div class="news-list-left">
											<span class="news-dot"></span>

											<span class="news-title">
												<?= esc_html($news['title']); ?>
											</span>
										</div>

										<div class="news-list-date">
											<?= esc_html($date); ?>
										</div>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>

						<div class="news-view-all">
							<a href="<?= esc_url(home_url('/news')); ?>">
								View All News >
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="row blog-row-area blog-row-faq padding-bottom-60 wow fadeInUp" style="display: none;">
				<?php
				$faqData = Cirnotob_Post_Util::getPostList('help', '', '', 1, 6);
				$faqList = $faqData['list'];

				if (!empty($faqList)) :
				?>
					<div class="col-md-12 blog-faq-list">
						<div class="cirnotob-faq-accordion" id="cirnotobFaqAccordion">
							<?php foreach ($faqList as $index => $faq) : ?>
								<?php
								$collapseId = 'faq-collapse-' . $faq['id'];
								$headingId = 'faq-heading-' . $faq['id'];

								$excerpt = trim(strip_tags($faq['excerpt']));

								if (empty($excerpt)) {
									$contentText = trim(wp_strip_all_tags($faq['content']));
									$contentLines = preg_split('/\r\n|\r|\n/', $contentText);
									$excerpt = trim($contentLines[0] ?? '');
								}

								$excerpt = wp_trim_words($excerpt, 35, '...');
								?>

								<div class="faq-item">
									<div
										class="faq-header"
										id="<?= esc_attr($headingId); ?>"
										data-toggle="collapse"
										data-target="#<?= esc_attr($collapseId); ?>"
										aria-expanded="false">
										<div class="faq-title">
											<?= esc_html($faq['title']); ?>
										</div>

										<div class="faq-icon">
											<span>+</span>
										</div>
									</div>

									<div
										id="<?= esc_attr($collapseId); ?>"
										class="collapse"
										data-parent="#cirnotobFaqAccordion">
										<div class="faq-content">
											<?= esc_html($excerpt); ?>

											<a href="<?= esc_url(home_url('/' . $faq['name'])); ?>">
												Learn more
											</a>
										</div>
									</div>
								</div>

							<?php endforeach; ?>
						</div>

						<div class="faq-view-all">
							<a href="<?= esc_url(home_url('/help')); ?>">
								View All FAQs >
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="row blog-row-area blog-row-blog padding-bottom-60 wow fadeInUp" style="display: none;">
				<?php
				$blogData = Cirnotob_Post_Util::getPostList('post', '', '', 1, 6);
				$blogList = $blogData['list'];

				if (!empty($blogList)) :
				?>
					<div class="col-md-12 blog-post-list">
						<div class="row">
							<?php foreach ($blogList as $blog) : ?>
								<?php
								$link = home_url('/blog/' . $blog['name']);
								$date = date('M j, Y', strtotime($blog['last_modified']));
								$category = !empty($blog['categories'])
									? $blog['categories'][0]['name']
									: 'Blog';
								?>
								<div class="col-lg-6 col-md-12">
									<a
										href="<?= esc_url($link); ?>"
										class="blog-post-card">
										<div class="blog-post-image">
											<img
												src="<?= esc_url($blog['thumbnail']); ?>"
												alt="<?= esc_attr($blog['title']); ?>">
										</div>

										<div class="blog-post-content">
											<div class="blog-post-category">
												<?= esc_html($category); ?>
											</div>

											<h3 class="blog-post-title">
												<?= esc_html($blog['title']); ?>
											</h3>

											<div class="blog-post-date">
												<?= esc_html($date); ?>
											</div>
										</div>
									</a>
								</div>
							<?php endforeach; ?>
						</div>

						<div class="blog-view-all">
							<a href="<?= esc_url(home_url('/blog')); ?>">
								View All Blog Posts >
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<div class="clearfix"></div>