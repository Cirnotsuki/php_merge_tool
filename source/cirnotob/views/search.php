<?php
get_header();
wp_enqueue_script('search-page-script');
?>

<div class="clearfix"></div>

<section class="page-wrapper cirnotob-search">
	<div class="container">
		<!-- Search2 -->
		<?php
		$keyword = $_GET['q'] ?? '';
		$keyword = esc_html(trim($keyword));

		$type = $_GET['t'] ?? '';
		$type = (int) esc_html(trim($type));
		if (empty($type)) $type = 0;
		?>
		<div class="row padding-top-80">
			<?= Cirnotob_Search_Form::render([], $keyword); ?>
			<?= Cirnotob_Tab_Menu::render(['All', 'Help Center', 'Blog', 'News'], $type); ?>
		</div>

		<div class="row padding-top-20 padding-bottom-60">
			<?= (new Cirnotob_Search_Result(null, $keyword))->render(['query' => $keyword, 'type' => $type, 'search' => 10, 'catePos' => 'none']); ?>
		</div>
	</div>
</section>

<?php
get_footer();
