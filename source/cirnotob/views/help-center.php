<?php get_header();
wp_enqueue_style('help-center-style');
?>

<!-- Help Center Section -->
<section class="page-wrapper">
	<?php

	$menuId = get_theme_mod('helpcenter_selected_menu');
	// $menu = wp_get_nav_menu_object($menuId);
	$menuItems = wp_get_nav_menu_items($menuId);

	function getUL()
	{
		global $menuItems;
		return array_filter($menuItems, function ($item) {
			return (int) $item->menu_item_parent === 0;
		});
	};

	function getLI($parentId)
	{
		global $menuItems;
		return array_filter($menuItems, function ($item) use ($parentId) {
			return (int) $item->menu_item_parent === $parentId;
		});
	};


	function format_name($name)
	{
		global $menuItems;
		$firstName = '';
		foreach ($menuItems as $item) {
			if ((int) $item->menu_item_parent === 0) continue;
			$itemName = Cirnotob_Util::parse_url($item->url)->name;
			if (empty($firstName)) {
				$firstName = $itemName;
			}
			if ($itemName === $name) {
				return $itemName;
			}
		}
		return $firstName;
	}

	function first_li_name()
	{
		global $menuItems;
		foreach ($menuItems as $item) {
			if ((int) $item->menu_item_parent === 0) continue;
			return Cirnotob_Util::parse_url($item->url)->name;
		}
		return '';
	}

	function get_segments($name)
	{
		global $menuItems;
		foreach ($menuItems as $item) {
			$urlObj = Cirnotob_Util::parse_url($item->url);
			if ($urlObj->name === $name) {
				return $urlObj->segments;
			}
		}
	}

	function get_category_title($name)
	{
		global $menuItems;
		foreach ($menuItems as $item) {
			$urlObj = Cirnotob_Util::parse_url($item->url);
			if ($urlObj->name === $name) {
				return $item->title;
			}
		}
	}

	$type = get_query_var('route_type');
	$name = get_query_var('route_name');

	if (!in_array($type, ['article', 'category'])) {
		$type = 'category';
	}

	if ($type === 'category') {
		$name = format_name($name);
	}

	if ($type === 'article') {
		$posts = get_posts([
			'post_type' => 'help',
			'name'      => $name,
			'numberposts' => 1,
		]);
		if (!$name || empty($posts)) {
			$type = 'category';
			$name = first_li_name();
		} else {
			foreach (Cirnotob_Post_Util::getPostTaxonomies($posts[0]->ID) as $taxes) {
				foreach ($taxes as $tax) {
					$slug = '';

					if (is_object($tax)) {
						$slug = $tax->slug;
					} elseif (is_array($tax)) {
						$slug = $tax['slug'] ?? '';
					}
					if (empty($slug)) continue;

					$name = format_name($slug);
				}
			}
		}
	}
	$segments = [];
	$categoryTitle = '';
	if ($type === 'category') {
		$segments = get_segments($name);
		$categoryTitle = get_category_title($name);
	}
	?>
	<div class="container cirnotob-help-center">
		<div class="row padding-top-80 padding-bottom-60">
			<div class="col-menu col-md-<?= $type === 'category' ? 3 : 2 ?> col-sm-12">
				<div class="help-center-search-box">
					<div class="help-center-title">Help Center</div>
					<div class="help-center-search">
						<?= Cirnotob_Search_Form::render([
							'tag' => 'div',
							'placeholder' => 'Search',
							'useIcon' => true
						]) ?>
					</div>
					<div class="help-center-menu">
						<?php foreach (getUL() as $ul) : ?>
							<ul class="list-box">
								<li class="menu-category"><i class="fa fa-angle-up"></i><span><?= $ul->title ?></span></li>
								<div class="list-inner">
									<ul>
										<?php foreach (getLI($ul->ID) as $li) :
											$liName = Cirnotob_Util::parse_url($li->url)->name;
										?>
											<a href="<?= home_url('/help/category/' . $liName); ?>">
												<li class="menu-sub-item <?= $liName === $name ? 'active' : '' ?>"><?php echo esc_html($li->title) ?></li>
											</a>
										<?php endforeach; ?>
									</ul>
								</div>
							</ul>
						<?php endforeach; ?>

					</div>
				</div>
			</div>
			<?php if ($type === 'category') : ?>
				<div class="col-content col-md-9 col-sm-12">
					<div class="help-center-category">
						<h1 class="category-title"><?= esc_html($categoryTitle) ?></h1>
						<?php
						// 倒数第 2 个
						$segment1 = $segments[count($segments) - 2];
						// 倒数第 1 个
						$segment2 = end($segments);
						$query = Cirnotob_Post_Util::getPostsByTaxonomy('help', $segment1, $segment2);
						if ($query->have_posts()) :
							foreach ($query->posts as $post): ?>
								<div class="category-row">
									<a class="category-row-title" href="<?= home_url('/help/article/' . $post->post_name); ?>">
										<span>
											<i class="fa fa-file-text-o"></i>
											<span><?= esc_html($post->post_title) ?></span>
										</span>
										<i class="fa fa-arrow-right"></i>
									</a>
								</div>
						<?php endforeach;
						endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<?php if ($type === 'article' && !empty($posts)): ?>
				<?= (new Cirnotob_Post_Content())->render($posts[0], 10) ?>
			<?php endif; ?>

			<div class="col-search col-md-9 col-sm-12">
				<?= (new Cirnotob_Search_Result())->render() ?>
			</div>
		</div>
	</div>
</section>
<!-- End of Help Center Section -->

<div class="clearfix"></div>

<?php get_footer(); ?>