<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Premium_Filter extends Widget_Base
{
	public function get_name()
	{
		return 'handzom_premium_filter';
	}

	public function get_title()
	{
		return esc_html__('Premium Filter', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-filter';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_script_depends()
	{
		return ['jquery-ui-slider', 'handzom-premium-filter-js'];
	}

	public function get_style_depends()
	{
		return ['handzom-premium-filter-css'];
	}

	public function __construct($data = [], $args = null)
	{
		parent::__construct($data, $args);
		
		// Hook into WordPress queries to apply filters from URL anywhere (Elementor, Shortcodes, Archives)
		add_action('pre_get_posts', [$this, 'apply_pre_get_posts_filters'], 99);
	}

	public function apply_pre_get_posts_filters($query)
	{
		if (is_admin()) return;

		// Check if querying products
		$post_type = $query->get('post_type');
		$is_product_query = ($post_type === 'product' || (is_array($post_type) && in_array('product', $post_type)));
		
		// If it's a WooCommerce taxonomy archive, it's also a product query
		if (!$is_product_query) {
			$attribute_taxonomies = wc_get_attribute_taxonomies();
			$attribute_names = [];
			if (!empty($attribute_taxonomies)) {
				foreach ($attribute_taxonomies as $tax) {
					$attribute_names[] = wc_attribute_taxonomy_name($tax->attribute_name);
				}
			}
			
			if ($query->is_tax('product_cat') || $query->is_tax('product_tag') || (!empty($attribute_names) && $query->is_tax($attribute_names))) {
				$is_product_query = true;
			}
		}

		if ($is_product_query) {
			$meta_query = $query->get('meta_query') ?: [];
			$tax_query = $query->get('tax_query') ?: [];

			$this->build_query_args($meta_query, $tax_query);

			if (!empty($meta_query)) $query->set('meta_query', $meta_query);
			if (!empty($tax_query)) $query->set('tax_query', $tax_query);

			// Handle Search
			if (!empty($_GET['s'])) {
				$query->set('s', sanitize_text_field($_GET['s']));
			}
		}
	}

	private function build_query_args(&$meta_query, &$tax_query)
	{
		// Categories
		if (!empty($_GET['product_cat']) && is_array($_GET['product_cat'])) {
			$tax_query[] = [
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => array_map('sanitize_title', $_GET['product_cat']),
				'operator' => 'IN',
			];
		}

		// Price
		if (isset($_GET['min_price']) || isset($_GET['max_price'])) {
			$min = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
			$max = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 9999999;
			$meta_query[] = [
				'key'     => '_price',
				'value'   => [$min, $max],
				'type'    => 'NUMERIC',
				'compare' => 'BETWEEN',
			];
		}

		// Stock
		if (!empty($_GET['instock_filter'])) {
			$meta_query[] = [
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '=',
			];
		}

		// Sale
		if (!empty($_GET['onsale_filter'])) {
			$post_ids = wc_get_product_ids_on_sale();
			if (!empty($post_ids)) {
				// We can't easily add post__in to meta_query, so we'd have to modify post__in globally.
				// Since we are inside meta_query/tax_query builder, let's use the _sale_price meta
				$meta_query[] = [
					'key'     => '_sale_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC'
				];
			}
		}

		// Featured
		if (!empty($_GET['featured_filter'])) {
			$tax_query[] = [
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
				'operator' => 'IN',
			];
		}

		// Attributes
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if (!empty($attribute_taxonomies)) {
			foreach ($attribute_taxonomies as $tax) {
				$query_key = 'filter_' . $tax->attribute_name;
				if (!empty($_GET[$query_key]) && is_array($_GET[$query_key])) {
					$taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
					$tax_query[] = [
						'taxonomy' => $taxonomy_name,
						'field'    => 'slug',
						'terms'    => array_map('sanitize_title', $_GET[$query_key]),
						'operator' => 'IN',
					];
				}
			}
		}
	}

	protected function register_controls()
	{
		/* Content Section */
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Filter Settings', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_search',
			[
				'label' => esc_html__('Enable Search', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'enable_categories',
			[
				'label' => esc_html__('Enable Categories', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'enable_price',
			[
				'label' => esc_html__('Enable Price Slider', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'enable_attributes',
			[
				'label' => esc_html__('Enable Attributes', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'description' => esc_html__('Automatically displays available WooCommerce attributes like Size, Color, etc.', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'enable_stock',
			[
				'label' => esc_html__('Enable Stock Status', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'enable_sale',
			[
				'label' => esc_html__('Enable Sale Filter', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'enable_featured',
			[
				'label' => esc_html__('Enable Featured Filter', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_product_count',
			[
				'label' => esc_html__('Show Product Count', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'accordion_open_by_default',
			[
				'label' => esc_html__('Accordion Open By Default', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'target_selector',
			[
				'label' => esc_html__('Products Target Selector', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => 'ul.products',
				'description' => esc_html__('The CSS selector for your WooCommerce products grid. Default is ul.products.', 'handzom-ui-kit'),
			]
		);

		$this->end_controls_section();

		/* Style Section */
		$this->start_controls_section(
			'style_container_section',
			[
				'label' => esc_html__('Container', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => esc_html__('Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz_pf_wrapper' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .hz_pf_drawer_content' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'glass_effect',
			[
				'label' => esc_html__('Enable Glass Effect', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'selectors' => [
					'{{WRAPPER}} .hz_pf_wrapper' => 'backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); background-color: rgba(255,255,255,0.7);',
					'{{WRAPPER}} .hz_pf_drawer_content' => 'backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); background-color: rgba(255,255,255,0.9);',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__('Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .hz_pf_wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .hz_pf_drawer_content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'container_radius',
			[
				'label' => esc_html__('Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .hz_pf_wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .hz_pf_wrapper',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_shadow',
				'selector' => '{{WRAPPER}} .hz_pf_wrapper',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_typography_section',
			[
				'label' => esc_html__('Typography & Colors', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => esc_html__('Heading Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz_pf_accordion_title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz_pf_mobile_title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'heading_typography',
				'label' => esc_html__('Heading Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz_pf_accordion_title, {{WRAPPER}} .hz_pf_mobile_title',
			]
		);

		$this->add_control(
			'filter_color',
			[
				'label' => esc_html__('Filter Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz_pf_checkbox_label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'filter_typography',
				'label' => esc_html__('Filter Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz_pf_checkbox_label',
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label' => esc_html__('Accent / Active Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz_pf_checkbox input:checked + .hz_pf_checkmark' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
					'{{WRAPPER}} .hz_pf_price_slider .ui-slider-range' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .hz_pf_price_slider .ui-slider-handle' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .hz_pf_btn_reset' => 'color: {{VALUE}}; border-color: {{VALUE}};',
					'{{WRAPPER}} .hz_pf_btn_reset:hover' => 'background-color: {{VALUE}}; color: #fff;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		// Check if WooCommerce is active
		if (!class_exists('WooCommerce')) {
			echo '<div class="hz_pf_notice">WooCommerce is required for the Premium Filter widget.</div>';
			return;
		}

		$is_open_class = ('yes' === $settings['accordion_open_by_default']) ? 'hz_pf_open' : '';
		$target_selector = esc_attr($settings['target_selector']);
		?>
		
		<!-- Mobile Toggle Button -->
		<div class="hz_pf_mobile_toggle">
			<button class="hz_pf_btn_toggle" type="button">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
				<span><?php esc_html_e('Filter', 'handzom-ui-kit'); ?></span>
			</button>
		</div>

		<!-- Main Wrapper / Drawer -->
		<div class="hz_pf_drawer" data-target="<?php echo $target_selector; ?>">
			<div class="hz_pf_drawer_overlay"></div>
			<div class="hz_pf_drawer_content hz_pf_wrapper">
				
				<div class="hz_pf_drawer_header">
					<h3 class="hz_pf_mobile_title"><?php esc_html_e('Filters', 'handzom-ui-kit'); ?></h3>
					<button class="hz_pf_btn_close">
						<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
					</button>
				</div>

				<form class="hz_pf_form" action="" method="GET">
					
					<?php if ('yes' === $settings['enable_search']) : ?>
						<div class="hz_pf_group hz_pf_search_group">
							<div class="hz_pf_search_wrapper">
								<svg class="hz_pf_search_icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
								<input type="text" name="s" class="hz_pf_search_input" placeholder="<?php esc_attr_e('Search products...', 'handzom-ui-kit'); ?>" value="<?php echo get_search_query(); ?>">
								<input type="hidden" name="post_type" value="product">
							</div>
						</div>
					<?php endif; ?>

					<div class="hz_pf_scrollable">
						<?php 
						// Categories
						if ('yes' === $settings['enable_categories']) {
							$this->render_categories($is_open_class, $settings['show_product_count']);
						}

						// Price
						if ('yes' === $settings['enable_price']) {
							$this->render_price($is_open_class);
						}

						// Stock & Sale & Featured
						if ('yes' === $settings['enable_stock'] || 'yes' === $settings['enable_sale'] || 'yes' === $settings['enable_featured']) {
							$this->render_availability($is_open_class, $settings);
						}

						// Attributes
						if ('yes' === $settings['enable_attributes']) {
							$this->render_attributes($is_open_class, $settings['show_product_count']);
						}
						?>
					</div>

					<div class="hz_pf_actions">
						<button type="button" class="hz_pf_btn_reset"><?php esc_html_e('Reset Filters', 'handzom-ui-kit'); ?></button>
						<button type="submit" class="hz_pf_btn_apply_mobile"><?php esc_html_e('Apply', 'handzom-ui-kit'); ?></button>
					</div>

				</form>
			</div>
		</div>
		<?php
	}

	private function render_categories($is_open_class, $show_count)
	{
		$categories = get_terms([
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0
		]);

		if (empty($categories) || is_wp_error($categories)) return;

		$current_cat = isset($_GET['product_cat']) ? explode(',', $_GET['product_cat']) : [];

		?>
		<div class="hz_pf_accordion <?php echo esc_attr($is_open_class); ?>">
			<div class="hz_pf_accordion_header">
				<span class="hz_pf_accordion_title"><?php esc_html_e('Categories', 'handzom-ui-kit'); ?></span>
				<span class="hz_pf_accordion_icon">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
				</span>
			</div>
			<div class="hz_pf_accordion_content">
				<div class="hz_pf_checkbox_list">
					<?php foreach ($categories as $cat) : 
						$checked = in_array($cat->slug, $current_cat) ? 'checked' : '';
					?>
						<label class="hz_pf_checkbox">
							<input type="checkbox" name="product_cat[]" value="<?php echo esc_attr($cat->slug); ?>" <?php echo $checked; ?>>
							<span class="hz_pf_checkmark"></span>
							<span class="hz_pf_checkbox_label">
								<?php echo esc_html($cat->name); ?>
								<?php if ('yes' === $show_count) : ?>
									<span class="hz_pf_count">(<?php echo esc_html($cat->count); ?>)</span>
								<?php endif; ?>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_price($is_open_class)
	{
		global $wpdb;
		$min_price = 0;
		$max_price = 1000;

		// Try to get min/max price from transients or db
		$prices = $wpdb->get_row("SELECT MIN(meta_value+0) as min_price, MAX(meta_value+0) as max_price FROM {$wpdb->postmeta} WHERE meta_key = '_price'");
		if ($prices) {
			$min_price = floor($prices->min_price);
			$max_price = ceil($prices->max_price);
		}

		$current_min = isset($_GET['min_price']) ? floatval($_GET['min_price']) : $min_price;
		$current_max = isset($_GET['max_price']) ? floatval($_GET['max_price']) : $max_price;

		?>
		<div class="hz_pf_accordion <?php echo esc_attr($is_open_class); ?>">
			<div class="hz_pf_accordion_header">
				<span class="hz_pf_accordion_title"><?php esc_html_e('Price', 'handzom-ui-kit'); ?></span>
				<span class="hz_pf_accordion_icon">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
				</span>
			</div>
			<div class="hz_pf_accordion_content">
				<div class="hz_pf_price_wrapper" data-min="<?php echo esc_attr($min_price); ?>" data-max="<?php echo esc_attr($max_price); ?>">
					<div class="hz_pf_price_slider"></div>
					<div class="hz_pf_price_inputs">
						<div class="hz_pf_price_input_group">
							<span class="hz_pf_currency"><?php echo get_woocommerce_currency_symbol(); ?></span>
							<input type="number" name="min_price" class="hz_pf_price_min" value="<?php echo esc_attr($current_min); ?>" min="<?php echo esc_attr($min_price); ?>" max="<?php echo esc_attr($max_price); ?>">
						</div>
						<span class="hz_pf_price_sep">-</span>
						<div class="hz_pf_price_input_group">
							<span class="hz_pf_currency"><?php echo get_woocommerce_currency_symbol(); ?></span>
							<input type="number" name="max_price" class="hz_pf_price_max" value="<?php echo esc_attr($current_max); ?>" min="<?php echo esc_attr($min_price); ?>" max="<?php echo esc_attr($max_price); ?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_availability($is_open_class, $settings)
	{
		?>
		<div class="hz_pf_accordion <?php echo esc_attr($is_open_class); ?>">
			<div class="hz_pf_accordion_header">
				<span class="hz_pf_accordion_title"><?php esc_html_e('Availability', 'handzom-ui-kit'); ?></span>
				<span class="hz_pf_accordion_icon">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
				</span>
			</div>
			<div class="hz_pf_accordion_content">
				<div class="hz_pf_checkbox_list">
					<?php if ('yes' === $settings['enable_stock']) : 
						$checked_instock = isset($_GET['instock_filter']) && $_GET['instock_filter'] === '1' ? 'checked' : '';
					?>
						<label class="hz_pf_checkbox">
							<input type="checkbox" name="instock_filter" value="1" <?php echo $checked_instock; ?>>
							<span class="hz_pf_checkmark"></span>
							<span class="hz_pf_checkbox_label"><?php esc_html_e('In Stock', 'handzom-ui-kit'); ?></span>
						</label>
					<?php endif; ?>

					<?php if ('yes' === $settings['enable_sale']) : 
						$checked_sale = isset($_GET['onsale_filter']) && $_GET['onsale_filter'] === '1' ? 'checked' : '';
					?>
						<label class="hz_pf_checkbox">
							<input type="checkbox" name="onsale_filter" value="1" <?php echo $checked_sale; ?>>
							<span class="hz_pf_checkmark"></span>
							<span class="hz_pf_checkbox_label"><?php esc_html_e('On Sale', 'handzom-ui-kit'); ?></span>
						</label>
					<?php endif; ?>

					<?php if ('yes' === $settings['enable_featured']) : 
						$checked_feat = isset($_GET['featured_filter']) && $_GET['featured_filter'] === '1' ? 'checked' : '';
					?>
						<label class="hz_pf_checkbox">
							<input type="checkbox" name="featured_filter" value="1" <?php echo $checked_feat; ?>>
							<span class="hz_pf_checkmark"></span>
							<span class="hz_pf_checkbox_label"><?php esc_html_e('Featured', 'handzom-ui-kit'); ?></span>
						</label>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_attributes($is_open_class, $show_count)
	{
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if (empty($attribute_taxonomies)) return;

		foreach ($attribute_taxonomies as $tax) {
			$taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
			$terms = get_terms([
				'taxonomy'   => $taxonomy_name,
				'hide_empty' => true,
			]);

			if (empty($terms) || is_wp_error($terms)) continue;

			// Skip color field completely as requested
			if (strpos(strtolower($tax->attribute_name), 'color') !== false) {
				continue;
			}

			$query_key = 'filter_' . $tax->attribute_name;
			$current_val = isset($_GET[$query_key]) ? explode(',', $_GET[$query_key]) : [];

			$is_color = (strpos(strtolower($tax->attribute_name), 'color') !== false);

			?>
			<div class="hz_pf_accordion <?php echo esc_attr($is_open_class); ?>">
				<div class="hz_pf_accordion_header">
					<span class="hz_pf_accordion_title"><?php echo esc_html($tax->attribute_label); ?></span>
					<span class="hz_pf_accordion_icon">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
					</span>
				</div>
				<div class="hz_pf_accordion_content">
					<?php if ($is_color) : ?>
						<div class="hz_pf_swatch_list">
							<?php foreach ($terms as $term) : 
								$checked = in_array($term->slug, $current_val) ? 'checked' : '';
								// Extract hex if possible (assuming slug or description might hold it, fallback to gray)
								// For standard WooCommerce, without a swatch plugin, it's hard to get hex.
								// We will use standard checkboxes but styled compactly.
							?>
								<label class="hz_pf_swatch_label <?php echo $checked ? 'hz_pf_active' : ''; ?>" title="<?php echo esc_attr($term->name); ?>">
									<input type="checkbox" name="<?php echo esc_attr($query_key); ?>[]" value="<?php echo esc_attr($term->slug); ?>" <?php echo $checked; ?>>
									<span><?php echo esc_html($term->name); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="hz_pf_checkbox_list">
							<?php foreach ($terms as $term) : 
								$checked = in_array($term->slug, $current_val) ? 'checked' : '';
							?>
								<label class="hz_pf_checkbox">
									<input type="checkbox" name="<?php echo esc_attr($query_key); ?>[]" value="<?php echo esc_attr($term->slug); ?>" <?php echo $checked; ?>>
									<span class="hz_pf_checkmark"></span>
									<span class="hz_pf_checkbox_label">
										<?php echo esc_html($term->name); ?>
										<?php if ('yes' === $show_count) : ?>
											<span class="hz_pf_count">(<?php echo esc_html($term->count); ?>)</span>
										<?php endif; ?>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
	}
}
