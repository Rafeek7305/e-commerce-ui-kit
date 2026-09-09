<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
	exit;
}

class Cart_Module
{
	private static $instance = null;

	public static function instance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct()
	{
		add_shortcode('handzom_cart', [$this, 'render_shortcode']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

		// AJAX Actions
		add_action('wp_ajax_handzom_cart_update', [$this, 'ajax_update_cart']);
		add_action('wp_ajax_nopriv_handzom_cart_update', [$this, 'ajax_update_cart']);
		
		add_action('wp_ajax_handzom_cart_remove', [$this, 'ajax_remove_item']);
		add_action('wp_ajax_nopriv_handzom_cart_remove', [$this, 'ajax_remove_item']);

		add_action('wp_ajax_handzom_cart_apply_coupon', [$this, 'ajax_apply_coupon']);
		add_action('wp_ajax_nopriv_handzom_cart_apply_coupon', [$this, 'ajax_apply_coupon']);
	}

	public function enqueue_scripts()
	{
		wp_enqueue_style('handzom-cart', HANDZOM_UI_KIT_URL . 'cart/cart.css', [], HANDZOM_UI_KIT_VERSION);
		wp_enqueue_script('handzom-cart', HANDZOM_UI_KIT_URL . 'cart/cart.js', ['jquery'], HANDZOM_UI_KIT_VERSION, true);

		wp_localize_script('handzom-cart', 'handzom_cart_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('handzom_cart_nonce')
		]);
	}

	public function render_shortcode()
	{
		wp_enqueue_style('handzom-cart');
		wp_enqueue_script('handzom-cart');

		if (null === WC()->cart) {
			return '';
		}

		ob_start();
		echo '<div class="handzom-cart-wrapper">';
		$this->render_cart_content();
		echo '</div>';
		return ob_get_clean();
	}

	private function render_cart_content()
	{
		if (WC()->cart->is_empty()) {
			$this->render_empty_cart();
			return;
		}
		?>
		<div class="hz-cart-header">
			<h2 class="hz-cart-title">YOUR CART</h2>
			<p class="hz-cart-subtitle">Review your selected pieces</p>
		</div>

		<div class="hz-cart-layout">
			<div class="hz-cart-items-section">
				<h3 class="hz-cart-section-title">YOUR ITEMS</h3>
				<div class="hz-cart-items-list">
					<?php
					foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
						$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
						$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

						if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
							$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
							?>
							<div class="hz-cart-item" data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>">
								<div class="hz-cart-item-image">
									<?php
									$thumbnail_id = $_product->get_image_id();
									if (!$thumbnail_id && $_product->is_type('variation')) {
										$parent = wc_get_product($_product->get_parent_id());
										if ($parent) {
											$thumbnail_id = $parent->get_image_id();
										}
									}
									$thumbnail = $thumbnail_id ? wp_get_attachment_image($thumbnail_id, 'full', false, ['class' => 'hz-cart-item-img']) : $_product->get_image('full');
									if (!$product_permalink) {
										echo $thumbnail;
									} else {
										printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
									}
									?>
								</div>
								<div class="hz-cart-item-details">
									<div class="hz-cart-item-header">
										<h4 class="hz-cart-item-name">
											<?php
											if (!$product_permalink) {
												echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;');
											} else {
												echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
											}
											?>
										</h4>
										<div class="hz-cart-item-price">
											<?php
											echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
											?>
										</div>
									</div>

									<?php
									// Meta data (variations)
									echo wc_get_formatted_cart_item_data($cart_item);
									?>

									<div class="hz-cart-item-actions">
										<div class="hz-cart-item-quantity">
											<span class="hz-cart-qty-label">Quantity:</span>
											<div class="hz-cart-qty-controls">
												<button type="button" class="hz-qty-btn hz-qty-minus">−</button>
												<input type="number" class="hz-qty-input" value="<?php echo esc_attr($cart_item['quantity']); ?>" min="1" step="1" readonly>
												<button type="button" class="hz-qty-btn hz-qty-plus">+</button>
											</div>
										</div>
										<button type="button" class="hz-cart-item-remove" data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>">Remove</button>
									</div>
								</div>
							</div>
							<?php
						}
					}
					?>
				</div>
			</div>

			<div class="hz-cart-summary-section">
				<h3 class="hz-cart-section-title">ORDER SUMMARY</h3>
				<div class="hz-cart-summary-box">
					<div class="hz-cart-summary-row">
						<span>Subtotal</span>
						<span><?php wc_cart_totals_subtotal_html(); ?></span>
					</div>

					<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
						<div class="hz-cart-summary-row hz-discount-row">
							<span>Discount (<?php echo esc_html($code); ?>)</span>
							<span><?php wc_cart_totals_coupon_html($coupon); ?></span>
						</div>
					<?php endforeach; ?>

					<?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
						<div class="hz-cart-summary-row">
							<span>Shipping</span>
							<span><?php
									$packages = WC()->shipping()->get_packages();
									$first_package = reset($packages);
									if (!empty($first_package['rates'])) {
										$rate = reset($first_package['rates']);
										echo wc_price($rate->cost);
									} else {
										echo 'Calculated at checkout';
									}
									?></span>
						</div>
					<?php endif; ?>

					<div class="hz-cart-summary-row hz-cart-total-row">
						<span>Total</span>
						<span><?php wc_cart_totals_order_total_html(); ?></span>
					</div>

					<div class="hz-cart-coupon">
						<input type="text" id="hz-coupon-code" class="hz-coupon-input" placeholder="Coupon code">
						<button type="button" class="hz-coupon-btn">APPLY</button>
					</div>
					<div class="hz-cart-coupon-message"></div>

					<a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="hz-cart-checkout-btn">PROCEED TO CHECKOUT</a>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_empty_cart()
	{
		?>
		<div class="hz-empty-cart">
			<h2 class="hz-empty-cart-title">YOUR CART IS EMPTY</h2>
			<p class="hz-empty-cart-subtitle">Discover something you'll love.</p>
			<a href="<?php echo esc_url(home_url('/collection/')); ?>" class="hz-continue-shopping-btn">CONTINUE SHOPPING</a>
		</div>
		<?php
	}

	// AJAX Handlers

	public function ajax_update_cart()
	{
		check_ajax_referer('handzom_cart_nonce', 'nonce');
		
		$cart_item_key = sanitize_text_field($_POST['cart_item_key']);
		$quantity = (int) $_POST['quantity'];

		if ($quantity > 0) {
			WC()->cart->set_quantity($cart_item_key, $quantity);
		}
		
		WC()->cart->calculate_totals();

		ob_start();
		$this->render_cart_content();
		$html = ob_get_clean();

		wp_send_json_success(['html' => $html]);
	}

	public function ajax_remove_item()
	{
		check_ajax_referer('handzom_cart_nonce', 'nonce');
		
		$cart_item_key = sanitize_text_field($_POST['cart_item_key']);

		if ($cart_item_key) {
			WC()->cart->remove_cart_item($cart_item_key);
		}

		WC()->cart->calculate_totals();

		ob_start();
		$this->render_cart_content();
		$html = ob_get_clean();

		wp_send_json_success(['html' => $html]);
	}

	public function ajax_apply_coupon()
	{
		check_ajax_referer('handzom_cart_nonce', 'nonce');
		
		$coupon_code = sanitize_text_field($_POST['coupon_code']);
		
		if (empty($coupon_code)) {
			wp_send_json_error(['message' => 'Please enter a coupon code.']);
		}

		$applied = WC()->cart->add_discount($coupon_code);

		if ($applied) {
			WC()->cart->calculate_totals();
			ob_start();
			$this->render_cart_content();
			$html = ob_get_clean();
			wp_send_json_success(['html' => $html, 'message' => 'Coupon applied successfully.']);
		} else {
			wp_send_json_error(['message' => 'Invalid coupon code.']);
		}
	}
}
