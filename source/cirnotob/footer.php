<?php
$cirnotob_hs_call_actions		= get_theme_mod('hide_show_call_actions', 'on');

$cirnotob_cta_button_icon		= get_theme_mod('call_action_button_icon', 'fa-question');
$cirnotob_cta_button_title		= get_theme_mod('call_action_button_title');
$cirnotob_cta_button_label		= get_theme_mod('call_action_button_label');
$cirnotob_cta_button_desc		= get_theme_mod('call_action_button_desc');
$cirnotob_cta_button_link		= get_theme_mod('call_action_button_link');

$cirnotob_cta_button2_icon		= get_theme_mod('call_action_button2_icon', 'fa-bell');
$cirnotob_cta_button2_title		= get_theme_mod('call_action_button2_title');
$cirnotob_cta_button2_label		= get_theme_mod('call_action_button2_label');
$cirnotob_cta_button2_desc		= get_theme_mod('call_action_button2_desc');
$cirnotob_cta_button2_link		= get_theme_mod('call_action_button2_link');

$cirnotob_cta_button3_icon		= get_theme_mod('call_action_button3_icon', 'fa-phone');
$cirnotob_cta_button3_title		= get_theme_mod('call_action_button3_title');
$cirnotob_cta_button3_label		= get_theme_mod('call_action_button3_label');
$cirnotob_cta_button3_desc		= get_theme_mod('call_action_button3_desc');
$cirnotob_cta_button3_link		= get_theme_mod('call_action_button3_link');

$cirnotob_cta_bg				= get_theme_mod('call_action_background_setting', esc_url(get_template_directory_uri() . '/images/cta.jpg'));

if ($cirnotob_hs_call_actions == 'on') :
?>
	<footer id="cta-unique" class="call-to-action-five footer-sidebar">
		<div class="background-overlay" style="background-image:url('<?= esc_url($cirnotob_cta_bg); ?>'); background-attachment: fixed;">
			<div class="container">
				<div class="row padding-top-25">
					<div class="col-md-4">
						<?php if (!empty($cirnotob_cta_button2_icon) || !empty($cirnotob_cta_button2_title) || !empty($cirnotob_cta_button2_label)): ?>
							<div class="call-wrapper call-wrapper1">
								<?php if (!empty($cirnotob_cta_button2_icon)): ?>
									<div class="call-icon-box"><i class="fa <?= esc_attr($cirnotob_cta_button2_icon); ?>"></i></div>
								<?php endif; ?>
								<div class="cta-info">
									<?php if (!empty($cirnotob_cta_button2_title)): ?>
										<div class="call-title">
											<a href="<?= esc_url($cirnotob_cta_button2_link); ?>"><?= wp_kses_post($cirnotob_cta_button2_title); ?><i class="fa fa-chevron-right"></i></a>

										</div>
									<?php endif; ?>
									<?php if (!empty($cirnotob_cta_button2_label)): ?>
										<div class="call-text"><?= wp_kses_post($cirnotob_cta_button2_label); ?></div>
									<?php endif; ?>
									<?php if (!empty($cirnotob_cta_button2_desc)): ?>
										<div class="call-desc"><?= wp_kses_post($cirnotob_cta_button2_desc); ?></div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<div class="col-md-5">
						<?php if (!empty($cirnotob_cta_button3_icon) || !empty($cirnotob_cta_button3_title) || !empty($cirnotob_cta_button3_label)): ?>
							<div class="call-wrapper call-wrapper2">
								<?php if (!empty($cirnotob_cta_button3_icon)): ?>
									<div class="call-icon-box"><i class="fa <?= esc_attr($cirnotob_cta_button3_icon); ?>"></i></div>
								<?php endif; ?>
								<div class="cta-info">
									<?php if (!empty($cirnotob_cta_button3_title)): ?>
										<div class="call-title">
											<a href="<?= esc_url($cirnotob_cta_button3_link); ?>"><?= wp_kses_post($cirnotob_cta_button3_title); ?><i class="fa fa-chevron-right"></i></a>
										</div>
									<?php endif; ?>
									<?php if (!empty($cirnotob_cta_button3_label)): ?>
										<div class="call-text"><?= wp_kses_post($cirnotob_cta_button3_label); ?></div>
									<?php endif; ?>
									<?php if (!empty($cirnotob_cta_button3_desc)): ?>
										<div class="call-desc"><?= wp_kses_post($cirnotob_cta_button3_desc); ?></div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>


					<div class="col-md-3 text-md-left">
						<?php if (!empty($cirnotob_cta_button_icon) || !empty($cirnotob_cta_button_title) || !empty($cirnotob_cta_button_label)): ?>
							<div class="call-wrapper call-wrapper1">
								<?php if (!empty($cirnotob_cta_button_icon)): ?>
									<div class="call-icon-box"><i class="fa <?= esc_attr($cirnotob_cta_button_icon); ?>"></i></div>
								<?php endif; ?>
								<div class="cta-info">
									<?php if (!empty($cirnotob_cta_button_title)): ?>
										<div class="call-title">
											<a href="<?= esc_url($cirnotob_cta_button_link); ?>"><?= wp_kses_post($cirnotob_cta_button_title); ?><i class="fa fa-chevron-right"></i></a>
										</div>
									<?php endif; ?>
									<?php if (!empty($cirnotob_cta_button_label)): ?>
										<div class="call-text"><?= wp_kses_post($cirnotob_cta_button_label); ?></div>
									<?php endif; ?>
									<?php if (!empty($cirnotob_cta_button_desc)): ?>
										<div class="call-desc"><?= wp_kses_post($cirnotob_cta_button_desc); ?></div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</footer>
	<div class="clearfix"></div>
<?php endif; ?>

<!--======================================
    Footer Section
========================================-->
<?php if (is_active_sidebar('footer-widget-area')) {
	$footer_widgets = Cirnotob_Util::dynamicSidebar('footer-widget-area');
	$specia_hide_show_payment 		= get_theme_mod('hide_show_payment', 'on');
	$cirnotob_hs_social			= get_theme_mod('hide_show_social', '1');
?>
	<footer class="footer-sidebar" role="contentinfo">
		<div class="background-overlay">
			<div class="container">
				<div class="row padding-top-60 padding-bottom-60">
					<div class="col-md-9 footer-columns">
						<?php foreach ($footer_widgets as $column) : ?>
							<div class="footer-column">
								<h4 class="footer-column__title"><?php echo esc_html($column['title']); ?></h4>
								<ul class="footer-column__list">
									<?php foreach ($column['items'] as $item) : ?>
										<?php
										// 区分分类和菜单链接
										if ('categories' === $column['type']) {
											$url = home_url('product/?category=' . $item['name']);
											$label = $item['name'];
										} else {
											$url = $item['url'] ?? '#';
											$label = $item['title'] ?? '';
										}
										?>
										<li class="footer-column__item">
											<a href="<?php echo esc_url($url); ?>" class="footer-column__link">
												<?php echo esc_html($label); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="col-md-3 text-md-left">
						<?php
						if (has_custom_logo()): ?>
							<div class="footer-info"><?php the_custom_logo(); ?></div>
						<?php endif;

						if ($specia_hide_show_payment == 'on'): ?>
							<div class="footer-info">
								<?php
								$specia_icon_one	= get_theme_mod('icon_one', '');
								$specia_icon_two	= get_theme_mod('icon_two', '');
								$specia_icon_three	= get_theme_mod('icon_three', '');
								$specia_icon_four	= get_theme_mod('icon_four', '');
								$specia_icon_five	= get_theme_mod('icon_five', '');
								?>
								<label class="footer-sidebar-label">PAYMENT</label>
								<ul class="payment-icon">
									<?php if ($specia_icon_one) { ?>
										<li><a href="<?= esc_url($specia_icon_one); ?>"><i class="fa fa-cc-paypal"></i></a></li>
									<?php } ?>

									<?php if ($specia_icon_two) { ?>
										<li><a href="<?= esc_url($specia_icon_two); ?>"><i class="fa fa-cc-visa"></i></a></li>
									<?php } ?>

									<?php if ($specia_icon_three) { ?>
										<li><a href="<?= esc_url($specia_icon_three); ?>"><i class="fa fa-cc-mastercard"></i></a></li>
									<?php } ?>

									<?php if ($specia_icon_four) { ?>
										<li><a href="<?= esc_url($specia_icon_four); ?>"><i class="fa fa-cc-amex"></i></a></li>
									<?php } ?>

									<?php if ($specia_icon_five) { ?>
										<li><a href="<?= esc_url($specia_icon_five); ?>"><i class="fa fa-cc-stripe"></i></a></li>
									<?php } ?>
								</ul>
							</div>
						<?php endif; ?>
						<!-- /End Social Media Icons-->

						<?php if ($cirnotob_hs_social == '1') : ?>
							<div class="footer-info">
								<?php
								$cirnotob_facebook_link		= get_theme_mod('facebook_link', '');
								$cirnotob_linkedin_link		= get_theme_mod('linkedin_link', '');
								$cirnotob_twitter_link		= get_theme_mod('twitter_link', '');
								$cirnotob_googleplus_link		= get_theme_mod('googleplus_link', '');
								$cirnotob_instagram_link		= get_theme_mod('instagram_link', '');
								$cirnotob_dribble_link		= get_theme_mod('dribble_link', '');
								$cirnotob_github_link			= get_theme_mod('github_link', '');
								$cirnotob_bitbucket_link		= get_theme_mod('bitbucket_link', '');
								$cirnotob_email_link			= get_theme_mod('email_link', '');
								$cirnotob_skype_link			= get_theme_mod('skype_link', '');
								$cirnotob_skype_action_link	= get_theme_mod('skype_action_link', '');
								$cirnotob_vk_link				= get_theme_mod('vk_link', '');
								$cirnotob_pinterest_link		= get_theme_mod('pinterest_link', '');
								?>
								<label class="footer-sidebar-label">CONNECT WITH US</label>
								<ul class="social-icon">
									<?php if ($cirnotob_facebook_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_facebook_link); ?>" aria-label="facebook"><i class="fa fa-facebook-square"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_linkedin_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_linkedin_link); ?>" aria-label="linkedin"><i class="fa fa-linkedin-square"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_twitter_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_twitter_link); ?>" aria-label="twitter"><i class="fa fa-twitter-square"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_googleplus_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_googleplus_link); ?>" aria-label="google-plus"><i class="fa fa-google-plus-square"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_instagram_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_instagram_link); ?>" aria-label="instagram"><i class="fa fa-instagram square"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_dribble_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_dribble_link); ?>" aria-label="dribbble"><i class="fa fa-dribbble square"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_github_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_github_link); ?>" aria-label="github-alt"><i class="fa fa-github-alt square"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_bitbucket_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_bitbucket_link); ?>" aria-label="bitbucket"><i class="fa fa-bitbucket-square"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_email_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="mailto:<?php echo esc_attr($cirnotob_email_link); ?>" aria-label="envelope-o"><i class="fa fa-envelope"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_skype_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_attr($cirnotob_skype_link); ?>?<?php echo esc_attr($cirnotob_skype_action_link); ?>" aria-label="skype"><i class="fa fa-skype"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_vk_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_vk_link); ?>" aria-label="vk"><i class="fa fa-vk"></i></a></li>
									<?php } ?>

									<?php if ($cirnotob_pinterest_link) { ?>
										<li><a class="tool-bounce tool-bottom-left" href="<?php echo esc_url($cirnotob_pinterest_link); ?>" aria-label="pinterest"><i class="fa fa-pinterest-square"></i></a></li>
									<?php } ?>
								</ul>
							</div>
						<?php endif; ?>
						<!-- /End Social Media Icons-->
					</div>
				</div>
			</div>
		</div>
		</div>
	</footer>
<?php } ?>

<div class="clearfix"></div>

<!--======================================
    Footer Copyright
========================================-->
<?php
$specia_hide_show_copyright		= get_theme_mod('hide_show_copyright', 'on');
?>
<?php if ($specia_hide_show_copyright == 'on' || $specia_hide_show_payment == 'on'): ?>
	<section id="specia-footer" class="footer-copyright">
		<div class="container">
			<div class="row padding-top-20 padding-bottom-10 ">
				<div class="col-md-12 text-center">
					<?php
					$specia_copyright_content = get_theme_mod('copyright_content', '&copy; [current_year] [site_title]');
					?>
					<?php if ($specia_hide_show_copyright == 'on') : ?>
						<p class="copyright">
							<?php
							$specia_copyright_allowed_tags = array(
								'[current_year]' => date_i18n('Y'),
								'[site_title]'   => strtoupper(str_replace(array("http://", "https://"), "", get_bloginfo('url'))) . " All Rights Reserved.",
							);
							echo apply_filters('specia_footer_copyright', wp_kses_post(specia_str_replace_assoc($specia_copyright_allowed_tags, $specia_copyright_content)));
							?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>
<!--======================================
    Top Scroller
========================================-->
<a href="#" class="top-scroll"><i class="fa fa-hand-o-up"></i></a>
</div>
</div>
<?php wp_footer(); ?>
</body>

</html>