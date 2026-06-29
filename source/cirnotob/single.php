<?php
get_header();
?>

<!-- Blog & Sidebar Section -->
<section class="page-wrapper">
	<div class="container">
		<!-- Single -->

		<div class="row padding-top-80 padding-bottom-60">
			<?php
			global $post;
			echo (new Cirnotob_Post_Content())->render($post);
			?>
		</div>
	</div>
</section>
<!-- End of Blog & Sidebar Section -->

<div class="clearfix"></div>

<?php get_footer(); ?>