<?php
get_header();
?>

<!-- Blog & Sidebar Section -->
<section class="page-wrapper">
	<div class="container">
		<?php
		$name = get_query_var('route_name');
		$type = get_query_var('route_type');
		$post = Cirnotob_Post_Util::getSinglePost($type === 'blog' ? 'post' : 'news', $name);

		$categories = Cirnotob_Post_Util::getPostCategories($post->ID);
		$post_views = new Cirnotob_Post_Views($post->ID);
		?>
		<!-- Single -->
		<div class="row padding-top-80 padding-bottom-20">
			<?php if (!empty($categories)):
				switch ($type):
					case 'blog':
						echo (new Cirnotob_Breadcrumb())->render([
							'Home' => '/',
							'Blog' => '/blog',
							$categories[0]->name => '/blog/category/' . $categories[0]->slug
						]);
						break;
					case 'news':
						echo (new Cirnotob_Breadcrumb())->render([
							'Home' => '/',
							'Newsroom' => '/news'
						]);
						break;
				endswitch;
			endif ?>
		</div>
		<div class="row padding-bottom-60">
			<?= (new Cirnotob_Post_Content())->render($post); ?>
		</div>
	</div>
</section>
<!-- End of Blog & Sidebar Section -->

<div class="clearfix"></div>

<?php get_footer(); ?>