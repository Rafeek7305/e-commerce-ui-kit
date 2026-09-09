<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
	exit;
}

class Ajax_Handler
{
	private static $instance = null;

	public static function instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct()
	{
		add_action('wp_ajax_handzom_cs_load_more', [$this, 'category_showcase_load_more']);
		add_action('wp_ajax_nopriv_handzom_cs_load_more', [$this, 'category_showcase_load_more']);
		add_action('wp_ajax_handzom_remove_from_cart', [$this, 'remove_from_cart']);
		add_action('wp_ajax_nopriv_handzom_remove_from_cart', [$this, 'remove_from_cart']);
		add_filter('woocommerce_add_to_cart_fragments', [$this, 'update_cart_fragments']);
		add_action('wp_footer', [$this, 'output_cart_sync_data']);
	}

	public function output_cart_sync_data()
	{
		$in_cart_ids = [];
		if (class_exists('WooCommerce') && !is_null(WC()->cart)) {
			foreach (WC()->cart->get_cart() as $cart_item) {
				$in_cart_ids[] = $cart_item['product_id'];
				if (!empty($cart_item['variation_id'])) {
					$in_cart_ids[] = $cart_item['variation_id'];
				}
			}
		}
		?>
		<script id="hz-cart-sync-data" type="application/json"><?php echo wp_json_encode(array_unique($in_cart_ids)); ?></script>
		<?php
	}

	public function update_cart_fragments($fragments)
	{
		ob_start();
		$cart_count = 0;
		if (class_exists('WooCommerce') && !is_null(WC()->cart)) {
			$cart_count = WC()->cart->get_cart_contents_count();
		}
		?>
		<span class="hz-pgh-badge hz-pgh-cart-count"><?php echo esc_html($cart_count); ?></span>
		<?php
		$fragments['.hz-pgh-cart-count'] = ob_get_clean();

		ob_start();
		$this->output_cart_sync_data();
		$fragments['#hz-cart-sync-data'] = ob_get_clean();

		return $fragments;
	}

	public function remove_from_cart()
	{
		$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
		if ($product_id && WC()->cart) {
			foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
				if ($cart_item['product_id'] == $product_id || $cart_item['variation_id'] == $product_id) {
					WC()->cart->remove_cart_item($cart_item_key);
				}
			}
		}
		wp_send_json_success();
	}

	public function category_showcase_load_more()
	{
		$category_slug = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
		$paged = isset($_POST['page']) ? absint($_POST['page']) : 1;
		$ppp = isset($_POST['ppp']) ? absint($_POST['ppp']) : 12;
		$subcats = isset($_POST['subcats']) ? array_map('sanitize_text_field', (array) $_POST['subcats']) : [];
		
		$sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'menu_order';
		$colors = isset($_POST['colors']) ? array_map('sanitize_text_field', (array) $_POST['colors']) : [];
		$sizes = isset($_POST['sizes']) ? array_map('sanitize_text_field', (array) $_POST['sizes']) : [];
		$brands = isset($_POST['brands']) ? array_map('sanitize_text_field', (array) $_POST['brands']) : [];
		$cats = isset($_POST['cats']) ? array_map('sanitize_text_field', (array) $_POST['cats']) : [];
		$min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
		$max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 0;
		$image_size = !empty($_POST['image_size']) && 'woocommerce_thumbnail' !== $_POST['image_size'] ? sanitize_text_field($_POST['image_size']) : 'woocommerce_single';

		$query_args = [
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => $ppp,
			'paged' => $paged,
		];

		$tax_query = [];

		if (!empty($category_slug) || !empty($subcats) || !empty($cats)) {
			$terms = [];
			if (!empty($cats)) {
				$terms = $cats;
			} elseif (!empty($subcats)) {
				$terms = $subcats;
			} else {
				$terms = $category_slug;
			}

			$tax_query[] = [
				'taxonomy' => 'product_cat',
				'field' => 'slug',
				'terms' => $terms,
			];
		}

		if (!empty($colors)) {
			$tax_query[] = [
				'taxonomy' => 'pa_color',
				'field' => 'slug',
				'terms' => $colors,
				'operator' => 'IN'
			];
		}

		if (!empty($sizes)) {
			$tax_query[] = [
				'taxonomy' => 'pa_size',
				'field' => 'slug',
				'terms' => $sizes,
				'operator' => 'IN'
			];
		}
		
		if (!empty($brands)) {
			// Find which brand taxonomy is active
			$active_brand_tax = false;
			$all_taxonomies = get_taxonomies([], 'names');
			foreach($all_taxonomies as $tax) {
				if (strpos($tax, 'brand') !== false) {
					$active_brand_tax = $tax;
					break;
				}
			}
			
			if ($active_brand_tax) {
				$tax_query[] = [
					'taxonomy' => $active_brand_tax,
					'field' => 'slug',
					'terms' => $brands,
					'operator' => 'IN'
				];
			}
		}

		if (count($tax_query) > 1) {
			$tax_query['relation'] = 'AND';
		}

		if (!empty($tax_query)) {
			$query_args['tax_query'] = $tax_query;
		}

		if ($min_price > 0 || $max_price > 0) {
			$meta_query = ['relation' => 'AND'];
			if ($min_price > 0) {
				$meta_query[] = [
					'key' => '_price',
					'value' => $min_price,
					'compare' => '>=',
					'type' => 'NUMERIC'
				];
			}
			if ($max_price > 0) {
				$meta_query[] = [
					'key' => '_price',
					'value' => $max_price,
					'compare' => '<=',
					'type' => 'NUMERIC'
				];
			}
			$query_args['meta_query'] = $meta_query;
		}

		switch ($sort) {
			case 'price':
				$query_args['meta_key'] = '_price';
				$query_args['orderby'] = 'meta_value_num';
				$query_args['order'] = 'ASC';
				break;
			case 'price-desc':
				$query_args['meta_key'] = '_price';
				$query_args['orderby'] = 'meta_value_num';
				$query_args['order'] = 'DESC';
				break;
			case 'date':
				$query_args['orderby'] = 'date';
				$query_args['order'] = 'DESC';
				break;
			default:
				$query_args['orderby'] = 'menu_order title';
				$query_args['order'] = 'ASC';
				break;
		}

		$products_query = new \WP_Query($query_args);

		ob_start();

		if ($products_query->have_posts()) {
			while ($products_query->have_posts()) {
				$products_query->the_post();
				global $product;
				if (!$product)
					continue;

				$main_image = $product->get_image($image_size);
				$attachment_ids = $product->get_gallery_image_ids();
				$has_gallery = !empty($attachment_ids);
				?>
				<div class="hz-cs-card">
					<div class="hz-cs-card-img-wrap">
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

						<!-- Top Right Add to Cart Icon (styled as bookmark per user request) -->
						<?php 
						$product_type = $product->get_type();
						$is_purchasable = $product->is_purchasable() && $product->is_in_stock();
						$product_id = $product->get_id();
						
						$is_in_cart = false;
						if (WC()->cart) {
							foreach (WC()->cart->get_cart() as $cart_item) {
								if ($cart_item['product_id'] == $product_id || $cart_item['variation_id'] == $product_id) {
									$is_in_cart = true;
									break;
								}
							}
						}
						
						$ajax_class = $is_in_cart ? '' : 'ajax_add_to_cart';
						$added_class = $is_in_cart ? 'hz-is-added' : '';
						?>
						<a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="hz-cs-wishlist-icon add_to_cart_button <?php echo esc_attr($ajax_class); ?> <?php echo esc_attr($added_class); ?> product_type_<?php echo esc_attr($product_type); ?>" data-product_id="<?php echo esc_attr($product_id); ?>" data-quantity="1" aria-label="Add to cart" rel="nofollow">
							<svg viewBox="0 0 24 24" width="16" height="16" stroke="#555" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
								<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
							</svg>
						</a>

						<?php
						// Fetch first category dynamically
						$terms = get_the_terms($product->get_id(), 'product_cat');
						$material = '';
						if ($terms && !is_wp_error($terms)) {
							$material = $terms[0]->name;
						}
						?>

						<div class="hz-cs-quick-add">
							<a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
								class="hz-cs-quick-add-btn <?php echo esc_attr($ajax_class); ?> product_type_<?php echo esc_attr($product_type); ?>"
								data-product_id="<?php echo esc_attr($product->get_id()); ?>" data-quantity="1"
								aria-label="Add to cart" rel="nofollow">
								<span class="hz-cs-quick-add-text">Add to Bag</span>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
									<line x1="3" y1="6" x2="21" y2="6"></line>
									<path d="M16 10a4 4 0 0 1-8 0"></path>
								</svg>
							</a>
						</div>
						</div> <!-- Close hz-cs-card-img-wrap -->

						<div class="hz-cs-card-content">
							<h3 class="hz-cs-title"><a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
							<?php if (!empty($material)): ?>
								<div class="hz-cs-material"><?php echo esc_html($material); ?></div>
							<?php endif; ?>
							<div class="hz-cs-price"><?php echo $product->get_price_html(); ?></div>
						</div>
					</div>
				<?php
			}
		}

		wp_reset_postdata();

		$html = ob_get_clean();

		wp_send_json_success([
			'html' => $html,
			'max_page' => $products_query->max_num_pages,
		]);
	}
}
