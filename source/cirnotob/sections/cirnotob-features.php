<?php
$specia_hs_features				= get_theme_mod('hide_show_features', 'on');
$specia_features_title			= get_theme_mod('features_title');
$specia_features_description	= get_theme_mod('features_description');
$specia_features_bg_setting		= get_theme_mod('features_background_setting', esc_url(get_template_directory_uri() . '/images/features.jpg'));
if ($specia_hs_features == 'on') {
?>

	<section id="specia-feature" class="cirnotob-features features-version-one" style="background: url('<?php echo esc_url($specia_features_bg_setting); ?>') no-repeat fixed 0 0 / cover rgba(0, 0, 0, 0);">

		<div class="features-overlay">
			<div class="container">

				<div class="row text-center padding-top-30 padding-bottom-30">
					<?php if ($specia_features_title || $specia_features_description) : ?>
						<div class="col-sm-12">
							<?php if ($specia_features_title) : ?>
								<h2 class="section-heading wow zoomIn"><?php echo wp_filter_post_kses($specia_features_title); ?></span></h2>
							<?php endif; ?>

							<?php if ($specia_features_description) : ?>
								<p class="section-description"><?php echo esc_html($specia_features_description); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

				</div>

				<?php
				if (is_active_sidebar('cirnotob_feature_widget')) : ?>
					<div class="cirnotob-feature-widget row padding-bottom-30">
						<?php dynamic_sidebar('cirnotob_feature_widget'); ?>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</section>
	<div class="clearfix"></div>
<?php } ?>