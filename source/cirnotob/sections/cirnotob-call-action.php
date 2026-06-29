<?php 
	$cirnotob_hs_call_actions			= get_theme_mod('hide_show_call_actions','on'); 
	$cirnotob_call_action_btn_lbl		= get_theme_mod('call_action_button_label');
	$cirnotob_call_action_btn_link	= get_theme_mod('call_action_button_link');
	$cirnotob_cta_button2_icon		= get_theme_mod('call_action_button2_icon','fa-bell');
	$cirnotob_cta_button_title		= get_theme_mod('call_action_button_title');
	$cirnotob_cta_btn_middle_text		= get_theme_mod('call_action_btn_middle_text');
	$cirnotob_cta_button_label2		= get_theme_mod('call_action_button_label2');
	$cirnotob_cta_button_link2		= get_theme_mod('call_action_button_link2');
	$cirnotob_cta_button3_icon		= get_theme_mod('call_action_button3_icon','fa-phone');
	$cirnotob_cta_button3_title		= get_theme_mod('call_action_button3_title');
	$cirnotob_cta_button_label3		= get_theme_mod('call_action_button_label3');
	$cirnotob_cta_button_link3		= get_theme_mod('call_action_button_link3');
	$cirnotob_cta_bg					= get_theme_mod('call_action_background_setting',esc_url(get_template_directory_uri() .'/images/cta.jpg'));
	
	if($cirnotob_hs_call_actions == 'on') :
?>
<section id="cta-unique" class="call-to-action-five wow fadeInDown">
    <div class="background-overlay" style="background-image:url('<?php echo esc_url($cirnotob_cta_bg); ?>'); background-attachment: fixed;">
        <div class="container">
            <div class="row padding-top-25 padding-bottom-25">
                
                <div class="col-md-9">
					<?php 
						$cirnotob_aboutusquery1 = new wp_query('page_id='.get_theme_mod('call_action_page',true)); 
						if( $cirnotob_aboutusquery1->have_posts() ) 
						{   while( $cirnotob_aboutusquery1->have_posts() ) { $cirnotob_aboutusquery1->the_post(); 
					?>
                    <h2 class="demo1"> <?php the_title(); ?> </h2>
                    <?php the_content(); ?>					
					<?php } } wp_reset_postdata(); ?>

					<?php if(!empty($cirnotob_cta_button2_icon) || !empty($cirnotob_cta_button_title) || !empty($cirnotob_cta_button_label2)): ?>
						<div class="call-wrapper call-wrapper1">
							<?php if(!empty($cirnotob_cta_button2_icon)): ?>
								<div class="call-icon-box"><i class="fa <?php echo esc_attr($cirnotob_cta_button2_icon); ?>"></i></div>
							<?php endif; ?>	
							<div class="cta-info">
								<?php if(!empty($cirnotob_cta_button_title)): ?>
									<div class="call-title"><?php echo wp_kses_post($cirnotob_cta_button_title); ?></div>
								<?php endif; ?>
								<?php if(!empty($cirnotob_cta_button_label2)): ?>
									<div class="call-phone"><a href="<?php echo esc_url($cirnotob_cta_button_link2); ?>"><?php echo wp_kses_post($cirnotob_cta_button_label2); ?></a></div>
								<?php endif; ?>		
							</div>
						</div>
					<?php endif; ?>	
					
					<?php if(!empty($cirnotob_cta_btn_middle_text)): ?>
						<span class="cta-or"><?php echo wp_kses_post($cirnotob_cta_btn_middle_text); ?></span>
					<?php endif; ?>
					
					<?php if(!empty($cirnotob_cta_button3_icon) || !empty($cirnotob_cta_button3_title) || !empty($cirnotob_cta_button_label3)): ?>
						<div class="call-wrapper call-wrapper2">
							<?php if(!empty($cirnotob_cta_button3_icon)): ?>
								<div class="call-icon-box"><i class="fa <?php echo esc_attr($cirnotob_cta_button3_icon); ?>"></i></div>
							<?php endif; ?>	
							<div class="cta-info">
								<?php if(!empty($cirnotob_cta_button3_title)): ?>
									<div class="call-title"><?php echo wp_kses_post($cirnotob_cta_button3_title); ?></div>
								<?php endif; ?>
								<?php if(!empty($cirnotob_cta_button_label3)): ?>
									<div class="call-phone"><a href="<?php echo esc_url($cirnotob_cta_button_link3); ?>"><?php echo wp_kses_post($cirnotob_cta_button_label3); ?></a></div>
								<?php endif; ?>		
							</div>
						</div>
					<?php endif; ?>
                </div>
				
				<?php if($cirnotob_call_action_btn_lbl) :?>
                <div class="col-md-3 text-md-right">
                    <a href="<?php echo esc_url($cirnotob_call_action_btn_link); ?>" class="bt-primary bt-effect-1 call-btn-1"><?php echo esc_html($cirnotob_call_action_btn_lbl); ?></a>
                </div>
				<?php endif; ?>
            </div>
        </div>
    </div>
</section>
<div class="clearfix"></div>
<?php endif; ?>
