<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;

if (!defined('ABSPATH')) {
	exit;
}

class Category_Showcase extends Widget_Base
{
	public function get_name()
	{
		return 'handzom_category_showcase';
	}

	public function get_title()
	{
		return esc_html__('Handzom Category Showcase', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-archive-posts';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-category-showcase'];
	}

	public function get_script_depends()
	{
		return ['handzom-category-showcase'];
	}

	protected function get_woocommerce_categories()
	{
		$categories = get_terms([
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		]);
		$options = ['' => esc_html__('Select Category', 'handzom-ui-kit')];
		if (!is_wp_error($categories) && !empty($categories)) {
			foreach ($categories as $category) {
				$options[$category->slug] = $category->name;
			}
		}
		return $options;
	}

	protected function register_controls()
	{
		/* -------------------------------------
		 * CONTENT: General
		 * ------------------------------------- */
		$this->start_controls_section(
			'section_general',
			[
				'label' => esc_html__('Showcase Setup', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'category',
			[
				'label' => esc_html__('WooCommerce Category', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT2,
				'options' => $this->get_woocommerce_categories(),
				'default' => '',
				'description' => esc_html__('Select the category to dynamically display products from.', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__('Products Per Page', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 12,
			]
		);

		$this->add_control(
			'show_sorting',
			[
				'label' => esc_html__('Show Sorting', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'enable_filter_drawer',
			[
				'label' => esc_html__('Enable Filter Drawer', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'description' => esc_html__('Show an elegant off-canvas filter drawer on the right.', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'show_subcategories',
			[
				'label' => esc_html__('Show Sub-Category Filters', 'handzom-ui-kit'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'handzom-ui-kit'),
				'label_off' => esc_html__('No', 'handzom-ui-kit'),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label' => esc_html__('Pagination', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'load_more',
				'options' => [
					'none' => esc_html__('Disable', 'handzom-ui-kit'),
					'load_more' => esc_html__('Load More', 'handzom-ui-kit'),
					'numbers' => esc_html__('Number Pagination', 'handzom-ui-kit'),
				],
			]
		);

		$this->add_control(
			'load_more_text',
			[
				'label' => esc_html__('Load More Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('VIEW MORE', 'handzom-ui-kit'),
				'condition' => [
					'pagination_type' => 'load_more',
				],
			]
		);

		$this->add_control(
			'no_products_text',
			[
				'label' => esc_html__('No Products Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('The selected product is not available now.', 'handzom-ui-kit'),
			]
		);

		$this->end_controls_section();

		/* -------------------------------------
		 * CONTENT: Hero Section
		 * ------------------------------------- */
		$this->start_controls_section(
			'section_hero',
			[
				'label' => esc_html__('Hero Section', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'show_hero',
			[
				'label' => esc_html__('Enable Hero', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'hero_title',
			[
				'label' => esc_html__('Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('SHIRTS', 'handzom-ui-kit'),
				'condition' => ['show_hero' => 'yes'],
			]
		);

		$this->add_control(
			'hero_desc',
			[
				'label' => esc_html__('Description', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('Refined essentials crafted for modern everyday style.', 'handzom-ui-kit'),
				'condition' => ['show_hero' => 'yes'],
			]
		);

		$this->add_control(
			'show_product_count',
			[
				'label' => esc_html__('Show Product Count', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => ['show_hero' => 'yes'],
			]
		);

		$this->add_control(
			'hero_image',
			[
				'label' => esc_html__('Hero Image (Optional)', 'handzom-ui-kit'),
				'type' => Controls_Manager::MEDIA,
				'condition' => ['show_hero' => 'yes'],
			]
		);

		$this->add_responsive_control(
			'hero_alignment',
			[
				'label' => esc_html__('Alignment', 'handzom-ui-kit'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => ['title' => 'Left', 'icon' => 'eicon-text-align-left'],
					'center' => ['title' => 'Center', 'icon' => 'eicon-text-align-center'],
					'right' => ['title' => 'Right', 'icon' => 'eicon-text-align-right'],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .hz-cs-hero' => 'text-align: {{VALUE}};',
				],
				'condition' => ['show_hero' => 'yes'],
			]
		);

		$this->end_controls_section();

		/* -------------------------------------
		 * CONTENT: Layout
		 * ------------------------------------- */
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__('Grid Layout', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'layout_style',
			[
				'label' => esc_html__('Grid Style', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'standard',
				'options' => [
					'standard' => esc_html__('Standard Grid', 'handzom-ui-kit'),
					'editorial' => esc_html__('Editorial Grid (Featured Item)', 'handzom-ui-kit'),
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__('Columns', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				],
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label' => esc_html__('Column Gap', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'default' => ['size' => 30],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label' => esc_html__('Row Gap', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'default' => ['size' => 50],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-grid' => 'row-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* -------------------------------------
		 * CONTENT: Product Card
		 * ------------------------------------- */
		$this->start_controls_section(
			'section_product_card',
			[
				'label' => esc_html__('Product Card Elements', 'handzom-ui-kit'),
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'image_size',
				'default' => 'woocommerce_single',
				'label' => esc_html__('Image Size', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'image_ratio',
			[
				'label' => esc_html__('Image Ratio', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => '3/4',
				'options' => [
					'1/1' => esc_html__('Square (1:1)', 'handzom-ui-kit'),
					'3/4' => esc_html__('Portrait (3:4)', 'handzom-ui-kit'),
					'2/3' => esc_html__('Tall (2:3)', 'handzom-ui-kit'),
					'4/3' => esc_html__('Landscape (4:3)', 'handzom-ui-kit'),
					'custom' => esc_html__('Custom', 'handzom-ui-kit'),
				],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-card-img-wrap' => 'aspect-ratio: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'custom_image_ratio',
			[
				'label' => esc_html__('Custom Ratio (e.g. 3/4 or 2/3)', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => '3/4',
				'condition' => [
					'image_ratio' => 'custom',
				],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-card-img-wrap' => 'aspect-ratio: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'image_fit',
			[
				'label' => esc_html__('Image Fit', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => [
					'cover' => esc_html__('Cover (Full Card Fill)', 'handzom-ui-kit'),
					'contain' => esc_html__('Contain (Show Entire Image)', 'handzom-ui-kit'),
					'fill' => esc_html__('Fill', 'handzom-ui-kit'),
				],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-slide img' => 'object-fit: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'image_position',
			[
				'label' => esc_html__('Image Focus / Position', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'top center',
				'description' => esc_html__('Top Center is optimal for apparel and fashion models to ensure heads are never cropped.', 'handzom-ui-kit'),
				'options' => [
					'top center' => esc_html__('Top Center (Apparel / Models - Best)', 'handzom-ui-kit'),
					'center center' => esc_html__('Center Center', 'handzom-ui-kit'),
					'bottom center' => esc_html__('Bottom Center', 'handzom-ui-kit'),
					'top left' => esc_html__('Top Left', 'handzom-ui-kit'),
					'top right' => esc_html__('Top Right', 'handzom-ui-kit'),
				],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-slide img' => 'object-position: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'image_custom_scale',
			[
				'label' => esc_html__('Image Zoom / Scale (%)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['%'],
				'range' => [
					'%' => [
						'min' => 100,
						'max' => 150,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-slide img' => '--hz-cs-img-scale: calc({{SIZE}} / 100); transform: scale(calc({{SIZE}} / 100));',
				],
			]
		);

		$this->add_control(
			'image_zoom',
			[
				'label' => esc_html__('Hover Subtle Zoom', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => esc_html__('Show Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_price',
			[
				'label' => esc_html__('Show Price', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_wishlist',
			[
				'label' => esc_html__('Show Wishlist Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_add_to_cart',
			[
				'label' => esc_html__('Show Add to Cart', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'hover_secondary_image',
			[
				'label' => esc_html__('Show Secondary Image on Hover', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();


		/* =====================================
		 * STYLE SECTIONS
		 * ===================================== */

		/* STYLE: Hero */
		$this->start_controls_section(
			'style_hero_section',
			[
				'label' => esc_html__('Hero Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => ['show_hero' => 'yes'],
			]
		);

		$this->add_control(
			'hero_title_color',
			[
				'label' => esc_html__('Title Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-hero-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'hero_title_typo',
				'selector' => '{{WRAPPER}} .hz-cs-hero-title',
			]
		);

		$this->add_control(
			'hero_desc_color',
			[
				'label' => esc_html__('Description Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-hero-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'hero_desc_typo',
				'selector' => '{{WRAPPER}} .hz-cs-hero-desc',
			]
		);

		$this->add_control(
			'hero_count_color',
			[
				'label' => esc_html__('Product Count Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-hero-count' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'hero_count_typo',
				'selector' => '{{WRAPPER}} .hz-cs-hero-count',
			]
		);

		$this->add_responsive_control(
			'hero_padding',
			[
				'label' => esc_html__('Hero Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-hero' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* STYLE: Cards */
		$this->start_controls_section(
			'style_card',
			[
				'label' => esc_html__('Card Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_bg',
			[
				'label' => esc_html__('Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label' => esc_html__('Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-card-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_alignment',
			[
				'label' => esc_html__('Content Alignment', 'handzom-ui-kit'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => ['title' => 'Left', 'icon' => 'eicon-text-align-left'],
					'center' => ['title' => 'Center', 'icon' => 'eicon-text-align-center'],
					'right' => ['title' => 'Right', 'icon' => 'eicon-text-align-right'],
				],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-card-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label' => esc_html__('Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .hz-cs-card-img-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} 0 0;',
				],
			]
		);

		$this->add_control(
			'image_zoom',
			[
				'label' => esc_html__('Image Zoom on Hover', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		/* STYLE: Content */
		$this->start_controls_section(
			'style_content',
			[
				'label' => esc_html__('Product Typography', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__('Title Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-title a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typo',
				'selector' => '{{WRAPPER}} .hz-cs-title',
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => esc_html__('Title Spacing', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => esc_html__('Price Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-price' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-cs-price ins' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-cs-price .woocommerce-Price-amount' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'price_typo',
				'selector' => '{{WRAPPER}} .hz-cs-price',
			]
		);

		$this->end_controls_section();

		/* STYLE: Pagination */
		$this->start_controls_section(
			'style_pagination',
			[
				'label' => esc_html__('Pagination / Load More', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'btn_color',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-load-more' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-cs-pagination .page-numbers' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_bg',
			[
				'label' => esc_html__('Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-load-more' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .hz-cs-pagination .page-numbers' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'btn_typo',
				'selector' => '{{WRAPPER}} .hz-cs-load-more, {{WRAPPER}} .hz-cs-pagination .page-numbers',
			]
		);

		$this->add_responsive_control(
			'btn_padding',
			[
				'label' => esc_html__('Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-load-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* -------------------------------------
		 * STYLE: Empty State (No Products)
		 * ------------------------------------- */
		$this->start_controls_section(
			'section_style_no_products',
			[
				'label' => esc_html__('No Products Message', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'no_products_color',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-cs-no-products' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'no_products_typography',
				'selector' => '{{WRAPPER}} .hz-cs-no-products',
			]
		);

		$this->add_responsive_control(
			'no_products_padding',
			[
				'label' => esc_html__('Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .hz-cs-no-products' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		if (!class_exists('WooCommerce')) {
			echo '<div class="hz-cs-notice">WooCommerce is required.</div>';
			return;
		}

		$settings = $this->get_settings_for_display();
		$category_slug = $settings['category'];

		$paged = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : 1);

		$query_args = [
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => $settings['posts_per_page'],
			'paged' => $paged,
		];

		if (!empty($category_slug)) {
			$query_args['tax_query'] = [
				[
					'taxonomy' => 'product_cat',
					'field' => 'slug',
					'terms' => $category_slug,
				],
			];
		}

		$products_query = new \WP_Query($query_args);
		$total_products = $products_query->found_posts;

		$hover_zoom = ('yes' === ($settings['image_zoom'] ?? 'yes')) ? 'hz-cs-zoom' : '';
		$editorial = 'editorial' === $settings['layout_style'] ? 'hz-cs-editorial' : '';
		$configured_image_size = (!empty($settings['image_size_size']) && 'woocommerce_thumbnail' !== $settings['image_size_size']) ? $settings['image_size_size'] : 'woocommerce_single';

		?>
		<div class="hz-category-showcase" data-category="<?php echo esc_attr($category_slug); ?>"
			data-ppp="<?php echo esc_attr($settings['posts_per_page']); ?>" data-page="<?php echo esc_attr($paged); ?>"
			data-max="<?php echo esc_attr($products_query->max_num_pages); ?>"
			data-image-size="<?php echo esc_attr($configured_image_size); ?>"
			data-empty-msg="<?php echo esc_attr($settings['no_products_text']); ?>">

			<?php if ('yes' === $settings['show_hero']): ?>
				<div class="hz-cs-hero">
					<?php if (!empty($settings['hero_image']['url'])): ?>
						<div class="hz-cs-hero-bg"
							style="background-image: url('<?php echo esc_url($settings['hero_image']['url']); ?>');"></div>
					<?php endif; ?>
					<div class="hz-cs-hero-inner">
						<?php if (!empty($settings['hero_title'])): ?>
							<h1 class="hz-cs-hero-title"><?php echo nl2br(esc_html($settings['hero_title'])); ?></h1>
						<?php endif; ?>
						<?php if (!empty($settings['hero_desc'])): ?>
							<p class="hz-cs-hero-desc"><?php echo nl2br(esc_html($settings['hero_desc'])); ?></p>
						<?php endif; ?>
						<div class="hz-cs-hero-divider"></div>
					</div>
				</div>
			<?php endif; ?>



			<div class="hz-cs-toolbar">
				<div class="hz-cs-toolbar-left">
					<?php if ('yes' === $settings['show_product_count']): ?>
						<div class="hz-cs-product-count">Products (<span
								class="hz-cs-count-val"><?php echo esc_html($total_products); ?></span>)</div>
					<?php endif; ?>
				</div>

				<div class="hz-cs-toolbar-center">
					<?php if ($settings['enable_filter_drawer'] === 'yes'): ?>
						<button class="hz-cs-filter-toggle" aria-label="Open Filters">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
								stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
								<line x1="4" y1="21" x2="4" y2="14"></line>
								<line x1="4" y1="10" x2="4" y2="3"></line>
								<line x1="12" y1="21" x2="12" y2="12"></line>
								<line x1="12" y1="8" x2="12" y2="3"></line>
								<line x1="20" y1="21" x2="20" y2="16"></line>
								<line x1="20" y1="12" x2="20" y2="3"></line>
								<line x1="1" y1="14" x2="7" y2="14"></line>
								<line x1="9" y1="8" x2="15" y2="8"></line>
								<line x1="17" y1="16" x2="23" y2="16"></line>
							</svg>
							Filters
						</button>
					<?php endif; ?>
				</div>

				<div class="hz-cs-toolbar-right">
					<?php if ($settings['show_sorting'] === 'yes'): ?>
						<div class="hz-cs-sort-wrapper">
							<button class="hz-cs-sort-toggle">
								Sort By
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
									stroke-linecap="round" stroke-linejoin="round" style="margin-left:5px;">
									<polyline points="6 9 12 15 18 9"></polyline>
								</svg>
							</button>
							<div class="hz-cs-sort-dropdown-menu">
								<label class="hz-cs-sort-option"><input type="radio" name="hz_sort_main" value="menu_order" checked>
									<span>Relevance</span></label>
								<label class="hz-cs-sort-option"><input type="radio" name="hz_sort_main" value="price"> <span>Price
										- from low to high</span></label>
								<label class="hz-cs-sort-option"><input type="radio" name="hz_sort_main" value="price-desc">
									<span>Price - from high to low</span></label>
								<label class="hz-cs-sort-option"><input type="radio" name="hz_sort_main" value="date"> <span>New
										In</span></label>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="hz-cs-grid <?php echo esc_attr($editorial); ?>">
				<?php if ($products_query->have_posts()): ?>
					<?php while ($products_query->have_posts()):
						$products_query->the_post();
						global $product;
						if (!$product)
							continue;

						$image_size = $configured_image_size;
						$main_image = $product->get_image($image_size);
						$attachment_ids = $product->get_gallery_image_ids();
						$has_gallery = !empty($attachment_ids);
						?>
						<div class="hz-cs-card">
							<div class="hz-cs-card-img-wrap <?php echo esc_attr($hover_zoom); ?>">
								<div class="hz-cs-image-slider" data-current="0">
									<a href="<?php echo esc_url($product->get_permalink()); ?>" class="hz-cs-slide">
										<?php echo $main_image; ?>
									</a>
									<?php if ($has_gallery): ?>
										<?php foreach ($attachment_ids as $attachment_id): ?>
											<a href="<?php echo esc_url($product->get_permalink()); ?>" class="hz-cs-slide">
												<?php echo wp_get_attachment_image($attachment_id, $image_size); ?>
											</a>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>

								<?php if ($has_gallery): ?>
									<button class="hz-cs-nav-btn hz-cs-prev" aria-label="Previous image">
										<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"
											stroke-linecap="round" stroke-linejoin="round">
											<polyline points="15 18 9 12 15 6"></polyline>
										</svg>
									</button>
									<button class="hz-cs-nav-btn hz-cs-next" aria-label="Next image">
										<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"
											stroke-linecap="round" stroke-linejoin="round">
											<polyline points="9 18 15 12 9 6"></polyline>
										</svg>
									</button>
								<?php endif; ?>

								<?php
								$product_type = $product->get_type();
								$is_purchasable = $product->is_purchasable() && $product->is_in_stock();

								$target_product_id = $product->get_id();
								$supports_ajax = $product->supports('ajax_add_to_cart');

								$is_in_cart = false;
								if (WC()->cart) {
									foreach (WC()->cart->get_cart() as $cart_item) {
										if ($cart_item['product_id'] == $target_product_id || $cart_item['variation_id'] == $target_product_id) {
											$is_in_cart = true;
											break;
										}
									}
								}

								$ajax_class = ($supports_ajax && $is_purchasable) ? 'add_to_cart_button ajax_add_to_cart' : '';
								$added_class = $is_in_cart ? 'hz-is-added' : '';
								$add_to_cart_url = ($supports_ajax && $is_purchasable) ? '?add-to-cart=' . $target_product_id : $product->add_to_cart_url();

								// Fetch first category dynamically
								$terms = get_the_terms($product->get_id(), 'product_cat');
								$material = '';
								if ($terms && !is_wp_error($terms)) {
									$material = $terms[0]->name;
								}
								?>

								<?php if ('yes' === $settings['show_wishlist']): ?>
									<!-- Top Right Add to Cart Icon (styled as bookmark per user request) -->
									<a href="<?php echo esc_url($add_to_cart_url); ?>"
										class="hz-cs-wishlist-icon <?php echo esc_attr($ajax_class); ?> <?php echo esc_attr($added_class); ?> product_type_<?php echo esc_attr($product_type); ?>"
										data-product_id="<?php echo esc_attr($target_product_id); ?>" data-quantity="1"
										aria-label="Add to cart" rel="nofollow">
										<svg viewBox="0 0 24 24" width="16" height="16" stroke="#555" fill="none" stroke-width="1.5"
											stroke-linecap="round" stroke-linejoin="round">
											<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
										</svg>
									</a>
								<?php endif; ?>
								<?php if ('yes' === $settings['show_add_to_cart']): ?>
									<div class="hz-cs-quick-add">
										<a href="<?php echo esc_url($add_to_cart_url); ?>"
											class="hz-cs-quick-add-btn <?php echo esc_attr($ajax_class); ?> product_type_<?php echo esc_attr($product_type); ?>"
											data-product_id="<?php echo esc_attr($target_product_id); ?>" data-quantity="1"
											aria-label="Add to cart" rel="nofollow">
											<span class="hz-cs-quick-add-text">Add to Bag</span>
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
												stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
												<line x1="3" y1="6" x2="21" y2="6"></line>
												<path d="M16 10a4 4 0 0 1-8 0"></path>
											</svg>
										</a>
									</div>
								<?php endif; ?>
							</div> <!-- Close hz-cs-card-img-wrap -->

							<div class="hz-cs-card-content">
								<?php if ('yes' === $settings['show_title']): ?>
									<h3 class="hz-cs-title"><a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
								<?php endif; ?>
								
								<?php if (!empty($material)): ?>
									<div class="hz-cs-material"><?php echo esc_html($material); ?></div>
								<?php endif; ?>
								
								<?php if ('yes' === $settings['show_price']): ?>
									<div class="hz-cs-price"><?php echo $product->get_price_html(); ?></div>
								<?php endif; ?>
							</div>
						</div>
					<?php endwhile; ?>
				<?php else: ?>
					<div class="hz-cs-no-products"><?php echo esc_html($settings['no_products_text']); ?></div>
				<?php endif; ?>
			</div>

			<?php if ($products_query->max_num_pages > 1): ?>
				<div class="hz-cs-pagination-wrap">
					<?php if ('load_more' === $settings['pagination_type']): ?>
						<button class="hz-cs-load-more" data-text="<?php echo esc_attr($settings['load_more_text']); ?>">
							<span><?php echo esc_html($settings['load_more_text']); ?></span>
							<div class="hz-cs-spinner"></div>
						</button>
					<?php elseif ('numbers' === $settings['pagination_type']): ?>
						<div class="hz-cs-pagination">
							<?php
							$base = esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
							echo paginate_links([
								'base' => $base,
								'format' => '',
								'add_args' => false,
								'current' => max(1, $paged),
								'total' => $products_query->max_num_pages,
								'prev_text' => '&larr;',
								'next_text' => '&rarr;',
							]);
							?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ($settings['enable_filter_drawer'] === 'yes'): ?>
				<div class="hz-cs-drawer-overlay"></div>
				<div class="hz-cs-drawer">
					<button class="hz-cs-drawer-close" aria-label="Close Filters"></button>
					<div class="hz-cs-drawer-header">
						<h3 class="hz-cs-drawer-title">Filter</h3>
						<div class="hz-cs-drawer-meta">
							<span class="hz-cs-drawer-count"><span
									class="hz-cs-count-val"><?php echo esc_html($total_products); ?></span> Products
								available</span>
							<button class="hz-cs-reset-btn">Reset</button>
						</div>
					</div>

					<div class="hz-cs-drawer-body">
						<!-- Dynamic Brand Filter (Subcategories mapped to Brand accordion) -->
						<?php 
						if ('yes' === $settings['show_subcategories'] && !empty($category_slug)):
							$main_term = get_term_by('slug', $category_slug, 'product_cat');
							if ($main_term && !is_wp_error($main_term)):
								$subcats = get_terms([
									'taxonomy' => 'product_cat',
									'parent' => $main_term->term_id,
									'hide_empty' => true
								]);
								if (!is_wp_error($subcats) && !empty($subcats)):
						?>
									<div class="hz-cs-accordion">
										<div class="hz-cs-accordion-head">
											<span>Brand</span>
											<span class="hz-cs-acc-icon"></span>
										</div>
										<div class="hz-cs-accordion-body">
											<div class="hz-cs-filter-list">
												<?php foreach ($subcats as $subcat): ?>
													<label class="hz-cs-checkbox-label">
														<input type="checkbox" name="hz_subcat[]" value="<?php echo esc_attr($subcat->slug); ?>"
															class="hz-cs-drawer-filter">
														<span><?php echo esc_html($subcat->name); ?> [<?php echo esc_html($subcat->count); ?>]</span>
													</label>
												<?php endforeach; ?>
											</div>
										</div>
									</div>
						<?php 
								endif;
							endif;
						endif; 
						?>

						<!-- Dynamic Color Filter -->
						<?php
						$colors = get_terms(['taxonomy' => 'pa_color', 'hide_empty' => true]);
						if (!is_wp_error($colors) && !empty($colors)):
							?>
							<div class="hz-cs-accordion">
								<div class="hz-cs-accordion-head">
									<span>Color</span>
									<span class="hz-cs-acc-icon"></span>
								</div>
								<div class="hz-cs-accordion-body">
									<div class="hz-cs-filter-list">
										<?php foreach ($colors as $color): ?>
											<label class="hz-cs-checkbox-label">
												<input type="checkbox" name="hz_color[]" value="<?php echo esc_attr($color->slug); ?>"
													class="hz-cs-drawer-filter">
												<span><?php echo esc_html($color->name); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<!-- Dynamic Size Filter -->
						<?php
						$sizes = get_terms(['taxonomy' => 'pa_size', 'hide_empty' => true]);
						if (!is_wp_error($sizes) && !empty($sizes)):
							?>
							<div class="hz-cs-accordion">
								<div class="hz-cs-accordion-head">
									<span>Size</span>
									<span class="hz-cs-acc-icon"></span>
								</div>
								<div class="hz-cs-accordion-body">
									<div class="hz-cs-filter-list hz-cs-size-grid">
										<?php foreach ($sizes as $size): ?>
											<label class="hz-cs-size-box">
												<input type="checkbox" name="hz_size[]" value="<?php echo esc_attr($size->slug); ?>"
													class="hz-cs-drawer-filter">
												<span><?php echo esc_html($size->name); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
						<!-- Dynamic Brand Filter -->
						<?php
						// Find any active taxonomy that relates to 'brand'
						$active_brand_tax = false;
						$all_taxonomies = get_taxonomies([], 'names');
						foreach($all_taxonomies as $tax) {
							if (strpos($tax, 'brand') !== false) {
								$active_brand_tax = $tax;
								break;
							}
						}
						
						if ($active_brand_tax) {
							$brands = get_terms(['taxonomy' => $active_brand_tax, 'hide_empty' => true]);
							if (!is_wp_error($brands) && !empty($brands)):
								?>
								<div class="hz-cs-accordion">
									<div class="hz-cs-accordion-head">
										<span>Brand</span>
										<span class="hz-cs-acc-icon"></span>
									</div>
									<div class="hz-cs-accordion-body">
										<div class="hz-cs-filter-list">
											<?php foreach ($brands as $brand): ?>
												<label class="hz-cs-checkbox-label">
													<input type="checkbox" name="hz_brand[]" value="<?php echo esc_attr($brand->slug); ?>"
														class="hz-cs-drawer-filter">
													<span><?php echo esc_html($brand->name); ?></span>
												</label>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							<?php 
							endif; 
						}
						?>
					</div>

					<div class="hz-cs-drawer-footer">
						<button class="hz-cs-apply-btn">Show <span
								class="hz-cs-count-val"><?php echo esc_html($total_products); ?></span> Products</button>
					</div>
				</div>
			<?php endif; ?>

		</div>
		<?php
		wp_reset_postdata();
	}
}
