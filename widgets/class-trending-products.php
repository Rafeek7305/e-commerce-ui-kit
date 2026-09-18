<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Trending_Products extends Widget_Base
{

	public function get_name()
	{
		return 'handzom_trending_products';
	}

	public function get_title()
	{
		return esc_html__('Trending Products Tabs', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-tabs';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-trending-products'];
	}

	public function get_script_depends()
	{
		return ['handzom-trending-products'];
	}

	protected function _register_controls()
	{
		$this->register_controls();
	}

	protected function register_controls()
	{
		/* Content Tab */
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Header', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'small_heading',
			[
				'label' => esc_html__('Small Heading', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('STAY AHEAD OF THE FASHION CURVE', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'main_heading',
			[
				'label' => esc_html__('Main Heading', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Trending Products', 'handzom-ui-kit'),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'tabs_section',
			[
				'label' => esc_html__('Tabs', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'tab_title',
			[
				'label' => esc_html__('Tab Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('New Tab', 'handzom-ui-kit'),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'source',
			[
				'label' => esc_html__('Product Source', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'latest',
				'options' => [
					'latest' => esc_html__('Latest Products', 'handzom-ui-kit'),
					'featured' => esc_html__('Featured Products', 'handzom-ui-kit'),
					'bestselling' => esc_html__('Best Selling', 'handzom-ui-kit'),
					'sale' => esc_html__('Sale Products', 'handzom-ui-kit'),
					'category' => esc_html__('Category', 'handzom-ui-kit'),
				],
			]
		);

		$repeater->add_control(
			'category',
			[
				'label' => esc_html__('Select Category', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT2,
				'options' => $this->get_product_categories(),
				'multiple' => false,
				'condition' => [
					'source' => 'category',
				],
			]
		);

		$repeater->add_control(
			'count',
			[
				'label' => esc_html__('Products Count', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 4,
			]
		);

		$this->add_control(
			'tabs',
			[
				'label' => esc_html__('Tabs Items', 'handzom-ui-kit'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'tab_title' => esc_html__('Featured', 'handzom-ui-kit'),
						'source' => 'featured',
					],
					[
						'tab_title' => esc_html__('New Arrival', 'handzom-ui-kit'),
						'source' => 'latest',
					],
					[
						'tab_title' => esc_html__('Best Seller', 'handzom-ui-kit'),
						'source' => 'bestselling',
					],
				],
				'title_field' => '{{{ tab_title }}}',
			]
		);

		$this->end_controls_section();

		/* Style Tab */
		$this->start_controls_section(
			'style_heading_section',
			[
				'label' => esc_html__('Heading', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => esc_html__('Main Heading Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tp-main-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'heading_typography',
				'label' => esc_html__('Main Heading Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-tp-main-heading',
			]
		);

		$this->add_control(
			'small_heading_color',
			[
				'label' => esc_html__('Small Heading Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tp-small-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'small_heading_typography',
				'label' => esc_html__('Small Heading Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-tp-small-heading',
			]
		);

		$this->end_controls_section();

		/* Style Tab - Nav */
		$this->start_controls_section(
			'style_tabs_section',
			[
				'label' => esc_html__('Tabs Navigation', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'tab_title_color',
			[
				'label' => esc_html__('Tab Title Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tp-tab-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-tp-tab-separator' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tab_title_active_color',
			[
				'label' => esc_html__('Tab Active Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tp-tab-title.hz-tp-active' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-tp-tab-title:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'tab_title_typography',
				'label' => esc_html__('Tab Title Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-tp-tab-title, {{WRAPPER}} .hz-tp-tab-separator',
			]
		);

		$this->end_controls_section();

		/* Style Tab - Product Card */
		$this->start_controls_section(
			'style_card_section',
			[
				'label' => esc_html__('Product Card', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image',
				'default' => 'full',
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
					'auto' => esc_html__('Original (Auto)', 'handzom-ui-kit'),
				],
				'selectors' => [
					'{{WRAPPER}} .hz-tp-card-image-wrap' => 'aspect-ratio: {{VALUE}};',
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
					'{{WRAPPER}} .hz-tp-card-image-wrap' => 'aspect-ratio: {{VALUE}};',
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
					'cover' => esc_html__('Cover (Fill Card View)', 'handzom-ui-kit'),
					'contain' => esc_html__('Contain (Full Image View)', 'handzom-ui-kit'),
					'fill' => esc_html__('Fill / Stretch', 'handzom-ui-kit'),
				],
				'selectors' => [
					'{{WRAPPER}} .hz-tp-image' => 'object-fit: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'image_position',
			[
				'label' => esc_html__('Image Focus / Position', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'top center',
				'description' => esc_html__('Top Center ensures model heads and faces are fully preserved.', 'handzom-ui-kit'),
				'options' => [
					'top center' => esc_html__('Top Center (Apparel / Models - Best)', 'handzom-ui-kit'),
					'center center' => esc_html__('Center Center', 'handzom-ui-kit'),
					'bottom center' => esc_html__('Bottom Center', 'handzom-ui-kit'),
				],
				'selectors' => [
					'{{WRAPPER}} .hz-tp-image' => 'object-position: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'card_image_height',
			[
				'label' => esc_html__('Custom Image Height', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'vh', 'em'],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .hz-tp-card-image-wrap' => 'height: {{SIZE}}{{UNIT}}; aspect-ratio: auto;',
					'{{WRAPPER}} .hz-tp-image' => 'height: 100%; object-fit: cover;',
				],
			]
		);

		$this->end_controls_section();
	}

	private function get_product_categories()
	{
		$options = [];
		if (class_exists('WooCommerce')) {
			$terms = get_terms([
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
			]);
			if (!empty($terms) && !is_wp_error($terms)) {
				foreach ($terms as $term) {
					$options[$term->term_id] = $term->name;
				}
			}
		}
		return $options;
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		?>
		<div class="hz-trending-products-wrapper">
			<div class="hz-tp-header">
				<?php if (!empty($settings['small_heading'])): ?>
					<h5 class="hz-tp-small-heading"><?php echo esc_html($settings['small_heading']); ?></h5>
				<?php endif; ?>
				<?php if (!empty($settings['main_heading'])): ?>
					<h2 class="hz-tp-main-heading"><?php echo esc_html($settings['main_heading']); ?></h2>
				<?php endif; ?>
			</div>

			<div class="hz-tp-tabs-nav">
				<?php foreach ($settings['tabs'] as $index => $tab):
					$active_class = ($index === 0) ? 'hz-tp-active' : '';
					?>
					<div class="hz-tp-tab-title <?php echo esc_attr($active_class); ?>"
						data-target="hz-tp-tab-<?php echo esc_attr($tab['_id']); ?>">
						<?php echo esc_html($tab['tab_title']); ?>
					</div>
					<?php if ($index < count($settings['tabs']) - 1): ?>
						<span class="hz-tp-tab-separator">/</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<div class="hz-tp-tabs-content">
				<?php foreach ($settings['tabs'] as $index => $tab):
					$active_class = ($index === 0) ? 'hz-tp-active' : '';
					?>
					<div id="hz-tp-tab-<?php echo esc_attr($tab['_id']); ?>"
						class="hz-tp-tab-pane <?php echo esc_attr($active_class); ?>">
						<?php
						// We will use AJAX to load products, or output them directly. 
						// The prompt says: "Load products dynamically."
						// For SEO and initial render speed, it's often best to output the first tab directly, and AJAX the rest, 
						// OR just output all of them and hide via CSS.
						// Since it says "Load products dynamically", let's output a container and let JS fetch, or use standard WP queries here if possible.
						// Doing standard WP query here is more reliable for Elementor. Let's output it here directly.
						if (class_exists('WooCommerce')) {
							$args = [
								'post_type' => 'product',
								'post_status' => 'publish',
								'posts_per_page' => $tab['count'] ? (int) $tab['count'] : 4,
							];

							switch ($tab['source']) {
								case 'featured':
									$args['tax_query'] = [
										[
											'taxonomy' => 'product_visibility',
											'field' => 'name',
											'terms' => 'featured',
											'operator' => 'IN',
										],
									];
									break;
								case 'bestselling':
									$args['meta_key'] = 'total_sales';
									$args['orderby'] = 'meta_value_num';
									break;
								case 'sale':
									$args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
									break;
								case 'category':
									if (!empty($tab['category'])) {
										$args['tax_query'] = [
											[
												'taxonomy' => 'product_cat',
												'field' => 'term_id',
												'terms' => $tab['category'],
											],
										];
									}
									break;
								default: // latest
									$args['orderby'] = 'date';
									$args['order'] = 'DESC';
									break;
							}

							$query = new \WP_Query($args);

							if ($query->have_posts()) {
								echo '<div class="hz-tp-grid">';
								while ($query->have_posts()) {
									$query->the_post();
									global $product;
									$this->render_product_card($product, $settings);
								}
								echo '</div>';
								wp_reset_postdata();
							} else {
								echo '<p>' . esc_html__('No products found.', 'handzom-ui-kit') . '</p>';
							}
						} else {
							echo '<p>' . esc_html__('WooCommerce is not active.', 'handzom-ui-kit') . '</p>';
						}
						?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	private function render_product_card($product, $settings)
	{
		$configured_size = !empty($settings['image_size']) ? $settings['image_size'] : (!empty($settings['image_size_size']) ? $settings['image_size_size'] : '');
		if (empty($configured_size) || 'woocommerce_thumbnail' === $configured_size) {
			$image_size = 'full';
		} else {
			$image_size = $configured_size;
		}
		?>
		<div class="hz-tp-card">
			<div class="hz-tp-card-image-wrap">
				<?php if ($product->is_on_sale()): ?>
					<div class="hz-tp-badge hz-tp-sale-badge">
						<?php
						if ($product->get_type() == 'variable') {
							echo esc_html__('Sale', 'handzom-ui-kit');
						} else {
							$regular_price = (float) $product->get_regular_price();
							$sale_price = (float) $product->get_price();
							if ($regular_price > 0) {
								$percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
								echo '-' . esc_html($percentage) . '%';
							} else {
								echo esc_html__('Sale', 'handzom-ui-kit');
							}
						}
						?>
					</div>
				<?php endif; ?>

				<?php
				$product_type = $product->get_type();
				$is_purchasable = $product->is_purchasable() && $product->is_in_stock();
				$product_id = $product->get_id();
				$supports_ajax = $product->supports('ajax_add_to_cart');
				
				$is_in_cart = false;
				if (class_exists('WooCommerce') && WC()->cart) {
					foreach (WC()->cart->get_cart() as $cart_item) {
						if ($cart_item['product_id'] == $product_id || $cart_item['variation_id'] == $product_id) {
							$is_in_cart = true;
							break;
						}
					}
				}
				
				$ajax_class = ($supports_ajax && $is_purchasable) ? 'add_to_cart_button ajax_add_to_cart' : '';
				$added_class = $is_in_cart ? 'hz-is-added' : '';
				$add_to_cart_url = ($supports_ajax && $is_purchasable) ? '?add-to-cart=' . $product_id : $product->add_to_cart_url();
				?>
				<a href="<?php echo esc_url($add_to_cart_url); ?>" class="hz-tp-wishlist-icon <?php echo esc_attr($ajax_class); ?> <?php echo esc_attr($added_class); ?> product_type_<?php echo esc_attr($product_type); ?>" data-product_id="<?php echo esc_attr($product_id); ?>" data-quantity="1" aria-label="Add to bag" rel="nofollow">
					<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
						<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
					</svg>
				</a>

				<a href="<?php echo esc_url($product->get_permalink()); ?>" class="hz-tp-image-link">
					<?php echo $product->get_image($image_size, ['class' => 'hz-tp-image hz-tp-image-main']); ?>
					<?php
					$attachment_ids = $product->get_gallery_image_ids();
					if (!empty($attachment_ids)) {
						echo wp_get_attachment_image($attachment_ids[0], $image_size, false, ['class' => 'hz-tp-image hz-tp-image-hover']);
					}
					?>
				</a>
			</div>
			<div class="hz-tp-card-content">
				<?php
				$categories = wc_get_product_category_list($product->get_id(), ', ');
				if ($categories): ?>
					<div class="hz-tp-category"><?php echo wp_strip_all_tags($categories); ?></div>
				<?php endif; ?>

				<h3 class="hz-tp-title">
					<a
						href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a>
				</h3>

				<div class="hz-tp-price">
					<?php echo $product->get_price_html(); ?>
				</div>

				<?php if (get_option('woocommerce_enable_review_rating') === 'yes'): ?>
					<div class="hz-tp-rating">
						<?php echo wc_get_rating_html($product->get_average_rating()); ?>
					</div>
				<?php endif; ?>

				<div class="hz-tp-add-to-cart">
					<?php
					$supports_ajax = $product->supports('ajax_add_to_cart');
					$is_purchasable = $product->is_purchasable() && $product->is_in_stock();
					
					$classes = implode(' ', array_filter([
						'button',
						'product_type_' . $product->get_type(),
						$is_purchasable ? 'add_to_cart_button' : '',
						($supports_ajax && $is_purchasable && !$is_in_cart) ? 'ajax_add_to_cart' : '',
						$is_in_cart ? 'added' : ''
					]));
					
					$add_to_cart_url = ($supports_ajax && $is_purchasable) ? '?add-to-cart=' . $product_id : $product->add_to_cart_url();
					?>
					<a href="<?php echo esc_url($add_to_cart_url); ?>" data-quantity="1" class="<?php echo esc_attr($classes); ?>" data-product_id="<?php echo esc_attr($product_id); ?>" data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" aria-label="Add to bag" rel="nofollow">
						<?php echo esc_html__('ADD TO BAG', 'handzom-ui-kit'); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
}
