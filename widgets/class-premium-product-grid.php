<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Premium_Product_Grid extends Widget_Base
{
	public function get_name()
	{
		return 'handzom_premium_product_grid';
	}

	public function get_title()
	{
		return esc_html__('Premium Product Grid', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-products';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-premium-product-grid-css'];
	}

	protected function register_controls()
	{
		/* Content Section */
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Query & Layout', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__('Products Per Page', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 8,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__('Columns', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => '4',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'selectors' => [
					'{{WRAPPER}} .hz_pg_grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'image_size',
				'default' => 'woocommerce_thumbnail',
				'label' => esc_html__('Image Size', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'show_pagination',
			[
				'label' => esc_html__('Show Pagination', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		/* Style Section - Card */
		$this->start_controls_section(
			'style_card_section',
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
					'{{WRAPPER}} .hz_pg_card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_radius',
			[
				'label' => esc_html__('Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .hz_pg_card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .hz_pg_image_wrapper img' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_shadow',
				'selector' => '{{WRAPPER}} .hz_pg_card',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_shadow_hover',
				'label' => esc_html__('Hover Box Shadow', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz_pg_card:hover',
			]
		);

		$this->end_controls_section();

		/* Style Section - Content */
		$this->start_controls_section(
			'style_content_section',
			[
				'label' => esc_html__('Content Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__('Title Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz_pg_title a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label' => esc_html__('Title Hover Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz_pg_title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__('Title Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz_pg_title',
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => esc_html__('Price Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz_pg_price' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz_pg_price ins' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz_pg_price .woocommerce-Price-amount' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'label' => esc_html__('Price Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz_pg_price',
			]
		);

		$this->end_controls_section();
		
		/* Style Section - Pagination */
		$this->start_controls_section(
			'style_pagination_section',
			[
				'label' => esc_html__('Pagination Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_pagination' => 'yes',
				],
			]
		);

		$this->add_control(
			'pagination_color',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-pagination .page-numbers' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'pagination_bg',
			[
				'label' => esc_html__('Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-pagination .page-numbers' => 'background-color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'pagination_active_color',
			[
				'label' => esc_html__('Active Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-pagination .page-numbers.current' => 'color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce-pagination .page-numbers:hover' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'pagination_active_bg',
			[
				'label' => esc_html__('Active Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .woocommerce-pagination .page-numbers.current' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
					'{{WRAPPER}} .woocommerce-pagination .page-numbers:hover' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		if (!class_exists('WooCommerce')) {
			echo '<div class="hz_pg_notice">WooCommerce is required for the Premium Product Grid widget.</div>';
			return;
		}

		$settings = $this->get_settings_for_display();
		
		$paged = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : 1);
		
		$query_args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $settings['posts_per_page'],
			'paged'          => $paged,
		];

		// Important: We use WP_Query instead of wc_get_products so that our pre_get_posts hook in the Filter widget can intercept and modify it!
		$products_query = new \WP_Query($query_args);

		?>
		<div class="hz_pg_wrapper">
			<?php if ($products_query->have_posts()) : ?>
				
				<!-- Notice the ul.products class. This is important so the Filter widget AJAX replacement targets it perfectly -->
				<ul class="hz_pg_grid products">
					<?php while ($products_query->have_posts()) : $products_query->the_post(); 
						$product = wc_get_product(get_the_ID());
						if (!$product) continue;
					?>
						<li class="hz_pg_card">
							<div class="hz_pg_image_wrapper">
								<a href="<?php echo esc_url($product->get_permalink()); ?>" class="hz_pg_image_link">
									<?php 
										$image_size = $settings['image_size_size'] ?? 'woocommerce_thumbnail';
										echo $product->get_image($image_size); 
									?>
								</a>
								<?php if ($product->is_on_sale()) : ?>
									<span class="hz_pg_badge hz_pg_sale"><?php esc_html_e('Sale', 'handzom-ui-kit'); ?></span>
								<?php endif; ?>
							</div>
							<div class="hz_pg_content">
								<h3 class="hz_pg_title">
									<a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a>
								</h3>
								<div class="hz_pg_price">
									<?php echo $product->get_price_html(); ?>
								</div>
								
								<div class="hz_pg_action">
									<a href="<?php echo esc_url($product->add_to_cart_url()); ?>" data-quantity="1" class="hz_pg_add_to_cart ajax_add_to_cart" data-product_id="<?php echo esc_attr($product->get_id()); ?>" aria-label="<?php echo esc_attr($product->add_to_cart_description()); ?>" rel="nofollow">
										<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
									</a>
								</div>
							</div>
						</li>
					<?php endwhile; ?>
				</ul>

				<?php if ('yes' === $settings['show_pagination']) : ?>
					<div class="hz_pg_pagination_wrapper">
						<?php
						$total   = $products_query->max_num_pages;
						$current = max(1, $paged);
						$base    = esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
						$format  = '';

						if ($total > 1) {
							echo '<nav class="woocommerce-pagination">';
							echo paginate_links([
								'base'      => $base,
								'format'    => $format,
								'add_args'  => false,
								'current'   => $current,
								'total'     => $total,
								'prev_text' => '&larr;',
								'next_text' => '&rarr;',
								'type'      => 'list',
								'end_size'  => 3,
								'mid_size'  => 3,
							]);
							echo '</nav>';
						}
						?>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<p class="hz_pg_no_products"><?php esc_html_e('No products found matching your selection.', 'handzom-ui-kit'); ?></p>
			<?php endif; ?>
		</div>
		<?php

		wp_reset_postdata();
	}
}
