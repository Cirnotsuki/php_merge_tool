<?php
$cirnotob_hs_slider = get_theme_mod('hide_show_slider', 'on');
if ($cirnotob_hs_slider == 'on'):

	// 【初始化空数组】用来存放轮播图的图片地址和对应页面ID
	$img_arr = [];   // 存轮播图背景图地址
	$id_arr = [];    // 存轮播图绑定的页面ID

	// 初始化公用按钮
	$slide_button = get_post_meta(get_the_ID(), 'slidebutton', true);
	$slide_button_link = get_post_meta(get_the_ID(), 'slidebuttonlink', true);

	// 【循环遍历1-5号轮播图】$slide从1开始，小于6即循环1/2/3/4/5号轮播图
	for ($slide = 1; $slide < 6; $slide++) {
		// 读取后台设置的"第$slide号轮播图绑定的页面ID"（比如slider-page1/slider-page2）
		$slider_page_id = get_theme_mod('slider-page' . $slide);
		// 如果这个轮播图绑定了有效页面ID（不是空）
		if ($slider_page_id) {
			// 【WP_Query查询】根据页面ID获取这个页面的完整数据
			$slidequery = new WP_Query(array('page_id' => $slider_page_id));
			// 遍历查询结果（因为是单页面，只会循环1次）
			while ($slidequery->have_posts()) {
				$slidequery->the_post();
				// 获取这个页面的特色图片（轮播图背景图）地址
				$image = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
				// 把图片地址和页面ID存入之前初始化的数组
				$img_arr[] = $image;   // []表示往数组末尾添加元素
				$id_arr[] = $post->ID;
			}
			// 【重置查询】WordPress必备：避免后续获取文章ID出错
			wp_reset_postdata();
		}
	}

	// 【判断是否有有效轮播图】只有id_arr数组非空（至少1个轮播图绑定了页面），才渲染轮播图
	if (!empty($id_arr)) { ?>
		<!-- 轮播图外层容器：HTML结构，class是样式类 -->
		<section id="slider-section" class="slider-wrapper slider-section-five">
			<div class="main-slider arrows-small arrows-transparent">
				<?php
				$i = 1;
				foreach ($id_arr as $id) {
					$title = get_the_title($id);
					$post = get_post($id);
				

					// 【核心：拆分页面正文为段落】
					// 1. 处理页面正文：还原WordPress编辑器的格式（换行、短代码等）
					$full_content = apply_filters('the_content', $post->post_content);

					// 2. 按</p>标签拆分正文为数组（<p>是HTML段落标签，拆分成一个个段落）
					$paragraphs = explode('</p>', $full_content);

					// 3. 清理段落：过滤空值、保留基础HTML标签（a/strong/em/br）
					$paragraphs = array_filter(array_map(function ($p) {
						// 补回</p>标签，只保留允许的标签，去掉其他多余标签
						$p = trim(strip_tags($p . '</p>', '<a><em><strong><br>'));
						// 空段落返回null，后续会被过滤
						return $p ?: null;
					}, $paragraphs));

					// 4. 重置数组索引（拆分后可能出现索引不连续，比如[0,2,3]→[0,1,2]）
					$paragraphs = array_values($paragraphs);

					$slider_title = $paragraphs[0] ?? $title;
					$slider_subtitle = $paragraphs[1] ?? '';
					$slider_content = $paragraphs[2] ?? '';

					if (!empty($slider_title)) {
						$slider_title = str_replace('strong', 'span', $slider_title);
					}

					if (!empty($slider_subtitle)) {
						$slider_subtitle = str_replace('strong', 'span', $slider_subtitle);
					}

					if (!empty($slider_content)) {
						$slider_content = str_replace('strong', 'span', $slider_content);
					}

					$button_paragraph = $paragraphs[3] ?? '';
					$button_text = '';
					$button_link = '';

					$button_class = '';
					$c = [];
					if (!empty($button_paragraph)) {
						// 步骤1：先还原转义字符（把 &lt;/&gt;/&quot; 转回 < > "）
						$button_html = htmlspecialchars_decode($button_paragraph);

						// 步骤2：修正正则 → 按 <a class="xxx" href="xxx">文字</a> 顺序匹配
						// 核心正则：匹配class、href、按钮文字（忽略属性顺序，兼容不同写法）
						$pattern = '/<a\s+.*?class=["\'](.*?)["\'].*?href=["\'](.*?)["\'].*?>(.*?)<\/a>/i';
						preg_match($pattern, $button_html, $matches);

						// 步骤3：正确取值（matches[1]=class，matches[2]=href，matches[3]=文字）
						if (!empty($matches) && count($matches) >= 4) {
							$button_class = $matches[1];
							$button_link = $matches[2];
							$button_text = $matches[3];

							// 步骤4：提取所有 has-xxx-color 格式的class
							if (!empty($button_class)) {
								preg_match_all('/has-[a-zA-Z0-9_-]+-color/i', $button_class, $color_matches);
								$button_colors = $color_matches[0] ?? []; // 颜色class数组
							}
						}
					}

				?>

					<div class="item">

						<?php
						$image = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
						$thumbnail_id = get_post_thumbnail_id($post->ID);
						$alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
						?>
						<img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>">
						<div class="specia-slider <?php echo get_post_meta(get_the_ID(), 'slider_caption_align', true); ?>"
							style="background: rgba(0,0,0,0.3);">
							<div class="specia-table">
								<div class="specia-table-cell">
									<div class="container">
										<div class="specia-content">
											<!-- 幻灯片标题 -->
											<h6 data-animation="fadeInRight" data-delay="90ms">
												<?php echo wp_kses_post($slider_title); ?><span></span>
											</h6>

											<!-- 幻灯片副标题 -->
											<?php if (!empty($slider_subtitle)): ?>
												<h1 data-animation="fadeInUp" data-delay="350ms">
													<?php echo wp_kses_post($slider_subtitle); ?><span></span>
												</h1>
											<?php endif; ?>

											<!-- 幻灯片内容 -->
											<?php if (!empty($slider_content)): ?>
												<span data-animation="fadeInUp" data-delay="650ms">

													<p><?php echo wp_kses_post($slider_content); ?></p>
												</span>
											<?php endif; ?>

											<!-- 额外按钮 -->
											<?php if ($button_text): ?>
												<a data-animation="fadeInUp" data-delay="850ms"
													href="<?php echo esc_url($button_link); ?>"
													class="<?php echo esc_attr(join(' ', $button_colors)) ?> bt-primary bt-effect-1 specia-btn-1">
													<?php echo esc_html($button_text); ?></a>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

				<?php $i++;
				} ?>

			</div>
		</section>

		<div class="clearfix"></div>

<?php }
	wp_reset_postdata();
endif; ?>