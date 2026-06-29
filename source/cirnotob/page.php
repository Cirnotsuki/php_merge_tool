<?php
get_header();
?>

<!-- Blog & Sidebar Section -->
<section class="page-wrapper">
	<div class="container">
		<!-- Single -->

		<div class="row padding-top-80 padding-bottom-60">

			<!--Blog Detail-->
			<div class="col-md-12">

				<?php if (have_posts()): ?>

					<?php while (have_posts()): the_post(); ?>

						<?php get_template_part('template-parts/content', 'page'); ?>

					<?php endwhile; ?>

				<?php else: ?>

					<?php get_template_part('template-parts/content', 'none'); ?>

				<?php endif; ?>

			</div>
		</div>
	</div>
</section>
<!-- End of Blog & Sidebar Section -->

<div class="clearfix"></div>

<?php get_footer(); ?>