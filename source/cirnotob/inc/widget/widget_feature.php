<?php
// ===================== 新小工具（完全独立，避免冲突） =====================
// 1. 注册新小工具（独立函数+独立优先级）
function cirnotob_feature_widget()
{
	register_widget('cirnotob_feature');
}
add_action('widgets_init', 'cirnotob_feature_widget', 20); // 优先级20，晚于原但独立

// 2. 新小工具类（完全独立，字段名/Base ID 都不重复）
class cirnotob_feature extends WP_Widget
{
	// 构造函数：Base ID 完全独立，名称/描述自定义
	function __construct()
	{
		parent::__construct(
			'cirnotob_feature_widget', // 唯一Base ID（和原 specia_feature_widget 区分）
			'* 产品特点 *', // 后台显示名称（和原区分）
			array(
				'classname' => 'cirnotob_feature', // 独立CSS类名
				'description' => '将这个拖进产品列表里，用来在主页显示产品特点列表', // 独立描述
			)
		);
	}

	// 前端渲染：完全独立的HTML结构（col-md-3）
	public function widget($args, $instance)
	{
		echo $args['before_widget']; // 修复原漏写的echo
?>
		<?php if (!empty($instance['cirnotob_feature_icon'])) : ?>
			<div class="col-md-3 col-sm-6 wow fadeInDown">
				<div class="feature-item cirnotob-feature"> <!-- 加独立类名，避免样式冲突 -->
					<div class="feature-box-icon">
						<i class="fa <?php echo esc_attr($instance['cirnotob_feature_icon']); ?>"></i>
					</div>
					<div class="feature-box-info">
						<?php if (!empty($instance['cirnotob_feature_title'])) : ?>
							<h4><?php echo esc_html($instance['cirnotob_feature_title']); ?></h4>
						<?php endif; ?>
						<?php if (!empty($instance['cirnotob_feature_desc'])) : ?>
							<p><?php echo esc_html($instance['cirnotob_feature_desc']); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php
		echo $args['after_widget'];
	}

	// 后台表单：字段名完全独立（避免和原共用）
	public function form($instance)
	{
		// 初始化独立字段（cirnotob_xxx，而非 features_widget_xxx）
		$title = isset($instance['cirnotob_feature_title']) ? $instance['cirnotob_feature_title'] : '';
		$icon = isset($instance['cirnotob_feature_icon']) ? $instance['cirnotob_feature_icon'] : '';
		$desc = isset($instance['cirnotob_feature_desc']) ? $instance['cirnotob_feature_desc'] : '';
	?>
		<p>
			<label for="<?php echo $this->get_field_id('cirnotob_feature_title'); ?>">产品标题</label>
			<input class="widefat" id="<?php echo $this->get_field_id('cirnotob_feature_title'); ?>"
				name="<?php echo $this->get_field_name('cirnotob_feature_title'); ?>"
				type="text" value="<?php echo esc_html($title); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cirnotob_feature_icon'); ?>">图标类名（如 fa-home）</label>
			<input class="widefat" id="<?php echo $this->get_field_id('cirnotob_feature_icon'); ?>"
				name="<?php echo $this->get_field_name('cirnotob_feature_icon'); ?>"
				type="text" value="<?php echo esc_html($icon); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cirnotob_feature_desc'); ?>">产品描述</label>
			<input class="widefat" id="<?php echo $this->get_field_id('cirnotob_feature_desc'); ?>"
				name="<?php echo $this->get_field_name('cirnotob_feature_desc'); ?>"
				type="text" value="<?php echo esc_html($desc); ?>" />
		</p>
<?php
	}

	// 数据更新：独立字段处理（避免和原冲突）
	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['cirnotob_feature_title'] = (!empty($new_instance['cirnotob_feature_title'])) ? sanitize_text_field($new_instance['cirnotob_feature_title']) : '';
		$instance['cirnotob_feature_icon'] = (!empty($new_instance['cirnotob_feature_icon'])) ? sanitize_text_field($new_instance['cirnotob_feature_icon']) : '';
		$instance['cirnotob_feature_desc'] = (!empty($new_instance['cirnotob_feature_desc'])) ? sanitize_text_field($new_instance['cirnotob_feature_desc']) : '';
		return $instance;
	}
}
?>