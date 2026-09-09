<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handzom Custom Checkout Module
 */
class Checkout_Module
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
		add_shortcode('handzom_checkout', [$this, 'render_shortcode']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

		// AJAX Handlers for Coupon Management
		add_action('wp_ajax_handzom_checkout_apply_coupon', [$this, 'ajax_apply_coupon']);
		add_action('wp_ajax_nopriv_handzom_checkout_apply_coupon', [$this, 'ajax_apply_coupon']);

		add_action('wp_ajax_handzom_checkout_remove_coupon', [$this, 'ajax_remove_coupon']);
		add_action('wp_ajax_nopriv_handzom_checkout_remove_coupon', [$this, 'ajax_remove_coupon']);

		// WooCommerce AJAX update_checkout Fragments
		add_filter('woocommerce_update_order_review_fragments', [$this, 'add_checkout_fragments']);
	}

	public function enqueue_scripts()
	{
		$style_deps = [];
		if (wp_style_is('select2', 'registered')) {
			$style_deps[] = 'select2';
		}
		if (wp_style_is('woocommerce-general', 'registered')) {
			$style_deps[] = 'woocommerce-general';
		}

		wp_register_style(
			'handzom-checkout',
			HANDZOM_UI_KIT_URL . 'checkout/checkout.css',
			$style_deps,
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_script(
			'handzom-checkout',
			HANDZOM_UI_KIT_URL . 'checkout/checkout.js',
			['jquery', 'wc-checkout'],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_localize_script('handzom-checkout', 'handzom_checkout_ajax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce'    => wp_create_nonce('handzom_checkout_nonce'),
		]);

		if (function_exists('is_checkout') && is_checkout()) {
			wp_enqueue_style('handzom-checkout');
			wp_enqueue_script('handzom-checkout');
		}
	}

	public function render_shortcode()
	{
		wp_enqueue_style('handzom-checkout');
		wp_enqueue_script('handzom-checkout');

		if (function_exists('is_checkout')) {
			wp_enqueue_script('wc-checkout');
			if (class_exists('WC_Frontend_Scripts')) {
				\WC_Frontend_Scripts::load_scripts();
			}
		}

		if (null === WC() || null === WC()->cart) {
			return '';
		}

		ob_start();
		echo '<div class="hz-checkout-page handzom-checkout-wrapper">';

		if (WC()->cart->is_empty()) {
			$this->render_empty_cart();
		} else {
			$this->render_checkout_form();
		}

		echo '</div>';
		return ob_get_clean();
	}

	private function render_checkout_form()
	{
		$checkout = WC()->checkout();
		?>
		<div class="hz-checkout-header">
			<h1 class="hz-checkout-title"><?php esc_html_e('CHECKOUT', 'handzom-ui-kit'); ?></h1>
			<div class="hz-checkout-progress">
				<span class="hz-progress-step active"><?php esc_html_e('Information', 'handzom-ui-kit'); ?></span>
				<span class="hz-progress-sep">&mdash;</span>
				<span class="hz-progress-step"><?php esc_html_e('Payment', 'handzom-ui-kit'); ?></span>
			</div>
		</div>

		<div class="hz-checkout-notices woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">
			<?php wc_print_notices(); ?>
		</div>

		<form name="checkout" method="post" class="checkout woocommerce-checkout hz-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

			<div class="hz-checkout-grid-container">

				<!-- LEFT COLUMN: Contact & Address Details -->
				<div class="hz-checkout-form-column">

					<?php
					// Placeholders definition
					$placeholders = [
						'billing_email'       => esc_attr__('Enter your email address', 'handzom-ui-kit'),
						'billing_phone'       => esc_attr__('Enter your phone number', 'handzom-ui-kit'),
						'billing_first_name'  => esc_attr__('Enter your first name', 'handzom-ui-kit'),
						'billing_last_name'   => esc_attr__('Enter your last name', 'handzom-ui-kit'),
						'billing_company'     => esc_attr__('Company name (optional)', 'handzom-ui-kit'),
						'billing_address_1'   => esc_attr__('House number and street name', 'handzom-ui-kit'),
						'billing_address_2'   => esc_attr__('Apartment, suite, unit, etc. (optional)', 'handzom-ui-kit'),
						'billing_city'        => esc_attr__('Enter your town / city', 'handzom-ui-kit'),
						'billing_postcode'    => esc_attr__('Enter your PIN code', 'handzom-ui-kit'),
						'shipping_first_name' => esc_attr__('Enter your first name', 'handzom-ui-kit'),
						'shipping_last_name'  => esc_attr__('Enter your last name', 'handzom-ui-kit'),
						'shipping_company'    => esc_attr__('Company name (optional)', 'handzom-ui-kit'),
						'shipping_address_1'  => esc_attr__('House number and street name', 'handzom-ui-kit'),
						'shipping_address_2'  => esc_attr__('Apartment, suite, unit, etc. (optional)', 'handzom-ui-kit'),
						'shipping_city'       => esc_attr__('Enter your town / city', 'handzom-ui-kit'),
						'shipping_postcode'   => esc_attr__('Enter your PIN code', 'handzom-ui-kit'),
					];
					$fields = $checkout->get_checkout_fields('billing');
					?>

					<!-- 1. CONTACT INFORMATION -->
					<div class="hz-checkout-section hz-contact-section">
						<h2 class="hz-section-title"><?php esc_html_e('CONTACT INFORMATION', 'handzom-ui-kit'); ?></h2>
						<div class="hz-fields-grid">
							<?php
							if (isset($fields['billing_email'])) {
								$f_config = $fields['billing_email'];
								$f_config['placeholder'] = $placeholders['billing_email'];
								woocommerce_form_field('billing_email', $f_config, $checkout->get_value('billing_email'));
							}
							if (isset($fields['billing_phone'])) {
								$f_config = $fields['billing_phone'];
								$f_config['placeholder'] = $placeholders['billing_phone'];
								woocommerce_form_field('billing_phone', $f_config, $checkout->get_value('billing_phone'));
							}
							?>
						</div>
					</div>

					<!-- 2. BILLING DETAILS / DELIVERY ADDRESS -->
					<div class="hz-checkout-section hz-billing-section">
						<h2 class="hz-section-title"><?php esc_html_e('BILLING DETAILS', 'handzom-ui-kit'); ?></h2>
						<div class="hz-fields-grid">
							<?php
							$billing_fields_order = [
								'billing_first_name',
								'billing_last_name',
								'billing_company',
								'billing_country',
								'billing_address_1',
								'billing_address_2',
								'billing_city',
								'billing_state',
								'billing_postcode',
							];
							foreach ($billing_fields_order as $field_key) {
								if (isset($fields[$field_key])) {
									$f_config = $fields[$field_key];
									if (isset($placeholders[$field_key])) {
										$f_config['placeholder'] = $placeholders[$field_key];
									}
									woocommerce_form_field($field_key, $f_config, $checkout->get_value($field_key));
								}
							}
							?>
						</div>
					</div>

					<!-- 3. SHIPPING ADDRESS -->
					<?php if (WC()->cart->needs_shipping_address()) : ?>
						<div class="hz-checkout-section hz-shipping-section">
							<div class="hz-shipping-toggle-wrapper">
								<label class="hz-checkbox-label">
									<input type="checkbox" id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="ship-to-different-address" value="1" <?php checked(apply_filters('woocommerce_ship_to_different_address_checked', 'shipping' === get_option('woocommerce_ship_to_destination') ? 1 : 0), 1); ?> />
									<span><?php esc_html_e('Ship to a different address?', 'handzom-ui-kit'); ?></span>
								</label>
							</div>

							<div class="shipping_address" style="display: none;">
								<h2 class="hz-section-title"><?php esc_html_e('SHIPPING ADDRESS', 'handzom-ui-kit'); ?></h2>
								<div class="hz-fields-grid">
									<?php
									$shipping_fields = $checkout->get_checkout_fields('shipping');
									$shipping_fields_order = [
										'shipping_first_name',
										'shipping_last_name',
										'shipping_company',
										'shipping_country',
										'shipping_address_1',
										'shipping_address_2',
										'shipping_city',
										'shipping_state',
										'shipping_postcode',
									];
									foreach ($shipping_fields_order as $field_key) {
										if (isset($shipping_fields[$field_key])) {
											$f_config = $shipping_fields[$field_key];
											if (isset($placeholders[$field_key])) {
												$f_config['placeholder'] = $placeholders[$field_key];
											}
											woocommerce_form_field($field_key, $f_config, $checkout->get_value($field_key));
										}
									}
									?>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<!-- 4. ORDER NOTES -->
					<div class="hz-checkout-section hz-notes-section">
						<h2 class="hz-section-title"><?php esc_html_e('ORDER NOTES', 'handzom-ui-kit'); ?> <span class="hz-optional-tag">(<?php esc_html_e('optional', 'handzom-ui-kit'); ?>)</span></h2>
						<div class="hz-fields-grid">
							<?php
							$order_fields = $checkout->get_checkout_fields('order');
							if (isset($order_fields['order_comments'])) {
								$f_config = $order_fields['order_comments'];
								$f_config['placeholder'] = esc_attr__('Notes about your order, e.g. special notes for delivery.', 'handzom-ui-kit');
								woocommerce_form_field('order_comments', $f_config, $checkout->get_value('order_comments'));
							}
							?>
						</div>
					</div>

				</div>

				<!-- RIGHT COLUMN: Order Summary, Coupon, Shipping & Payment -->
				<aside class="hz-checkout-summary-column">
					<div class="hz-checkout-summary-card">

						<h2 class="hz-summary-title"><?php esc_html_e('YOUR ORDER', 'handzom-ui-kit'); ?></h2>

						<!-- Dynamic Cart Items -->
						<div class="hz-checkout-items">
							<?php $this->render_order_items(); ?>
						</div>

						<!-- Coupon Code Area -->
						<div class="hz-checkout-coupon-area">
							<div class="hz-coupon-toggle">
								<span class="hz-coupon-label"><?php esc_html_e('Have a coupon?', 'handzom-ui-kit'); ?></span>
								<button type="button" class="hz-toggle-coupon-btn"><?php esc_html_e('Enter code', 'handzom-ui-kit'); ?></button>
							</div>
							<div class="hz-coupon-form-box" style="display: none;">
								<div class="hz-coupon-input-group">
									<input type="text" id="hz_checkout_coupon_code" class="hz-coupon-input" placeholder="<?php esc_attr_e('Coupon code', 'handzom-ui-kit'); ?>" autocomplete="off">
									<button type="button" class="hz-apply-coupon-btn"><?php esc_html_e('APPLY', 'handzom-ui-kit'); ?></button>
								</div>
								<div class="hz-coupon-message"></div>
							</div>
						</div>

						<!-- Order Totals & Shipping -->
						<div class="hz-checkout-totals-box">
							<?php $this->render_order_totals(); ?>
						</div>

						<!-- Payment Methods -->
						<div class="hz-checkout-payment-box" id="payment">
							<?php $this->render_payment_methods(); ?>
						</div>

						<!-- Place Order Button -->
						<div class="hz-place-order-wrapper">
							<?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
							<button type="submit" class="hz-place-order-btn" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e('PLACE ORDER', 'handzom-ui-kit'); ?>" data-value="<?php esc_attr_e('PLACE ORDER', 'handzom-ui-kit'); ?>"><?php esc_html_e('PLACE ORDER', 'handzom-ui-kit'); ?></button>
						</div>

					</div>
				</aside>

			</div>

		</form>
		<?php
	}

	public function render_order_items()
	{
		if (null === WC() || null === WC()->cart) {
			return;
		}
		?>
		<div class="hz-cart-items-list">
			<?php
			foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
				$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

				if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
					?>
					<div class="hz-summary-item" data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>">
						<div class="hz-summary-item-img-wrap">
							<?php
							$thumbnail_id = $_product->get_image_id();
							if (!$thumbnail_id && $_product->is_type('variation')) {
								$parent = wc_get_product($_product->get_parent_id());
								if ($parent) {
									$thumbnail_id = $parent->get_image_id();
								}
							}
							$thumbnail = $thumbnail_id ? wp_get_attachment_image($thumbnail_id, 'full', false, ['class' => 'hz-summary-thumb']) : $_product->get_image('full', ['class' => 'hz-summary-thumb']);
							echo $thumbnail;
							?>
						</div>
						<div class="hz-summary-item-details">
							<h4 class="hz-summary-item-name">
								<?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)); ?>
							</h4>
							<?php
							// Meta data (Color, Size, variations, etc.)
							echo wc_get_formatted_cart_item_data($cart_item);
							?>
							<div class="hz-summary-item-qty">
								<span><?php esc_html_e('Qty:', 'handzom-ui-kit'); ?> <?php echo esc_html($cart_item['quantity']); ?></span>
							</div>
						</div>
						<div class="hz-summary-item-price">
							<?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
						</div>
					</div>
					<?php
				}
			}
			?>
		</div>
		<?php
	}

	public function render_order_totals()
	{
		if (null === WC() || null === WC()->cart) {
			return;
		}
		?>
		<div class="hz-totals-table">
			<!-- Subtotal -->
			<div class="hz-total-row hz-subtotal-row">
				<span class="hz-total-label"><?php esc_html_e('Subtotal', 'handzom-ui-kit'); ?></span>
				<span class="hz-total-value"><?php wc_cart_totals_subtotal_html(); ?></span>
			</div>

			<!-- Coupons / Discounts -->
			<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
				<div class="hz-total-row hz-discount-row">
					<span class="hz-total-label">
						<?php esc_html_e('Discount', 'handzom-ui-kit'); ?> (<?php echo esc_html($code); ?>)
						<a href="#" class="hz-remove-coupon-link" data-coupon="<?php echo esc_attr($code); ?>"><?php esc_html_e('[Remove]', 'handzom-ui-kit'); ?></a>
					</span>
					<span class="hz-total-value"><?php wc_cart_totals_coupon_html($coupon); ?></span>
				</div>
			<?php endforeach; ?>

			<!-- Shipping -->
			<?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
				<div class="hz-shipping-methods-wrapper">
					<span class="hz-shipping-heading"><?php esc_html_e('Shipping', 'handzom-ui-kit'); ?></span>
					<?php wc_cart_totals_shipping_html(); ?>
				</div>
			<?php endif; ?>

			<!-- Fees -->
			<?php foreach (WC()->cart->get_fees() as $fee) : ?>
				<div class="hz-total-row hz-fee-row">
					<span class="hz-total-label"><?php echo esc_html($fee->name); ?></span>
					<span class="hz-total-value"><?php wc_cart_totals_fee_html($fee); ?></span>
				</div>
			<?php endforeach; ?>

			<!-- Tax -->
			<?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
				<?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
					<?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
						<div class="hz-total-row hz-tax-row">
							<span class="hz-total-label"><?php echo esc_html($tax->label); ?></span>
							<span class="hz-total-value"><?php echo wp_kses_post($tax->formatted_amount); ?></span>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="hz-total-row hz-tax-row">
						<span class="hz-total-label"><?php echo esc_html(WC()->countries->tax_or_vat()); ?></span>
						<span class="hz-total-value"><?php wc_cart_totals_taxes_total_html(); ?></span>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<!-- Total -->
			<div class="hz-total-row hz-grand-total-row">
				<span class="hz-total-label"><?php esc_html_e('Total', 'handzom-ui-kit'); ?></span>
				<span class="hz-total-value"><?php wc_cart_totals_order_total_html(); ?></span>
			</div>
		</div>
		<?php
	}

	public function render_payment_methods()
	{
		if (null === WC() || null === WC()->cart) {
			return;
		}
		?>
		<div class="hz-payment-section-header">
			<h3 class="hz-payment-title"><?php esc_html_e('PAYMENT', 'handzom-ui-kit'); ?></h3>
		</div>
		<?php
		if (WC()->cart->needs_payment()) {
			$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
			WC()->payment_gateways()->set_current_gateway($available_gateways);
		} else {
			$available_gateways = [];
		}

		if (!empty($available_gateways)) {
			?>
			<ul class="wc_payment_methods payment_methods methods hz-payment-list">
				<?php
				$i = 0;
				foreach ($available_gateways as $gateway) {
					$i++;
					?>
					<li class="wc_payment_method payment_method_<?php echo esc_attr($gateway->id); ?> hz-payment-item">
						<input id="payment_method_<?php echo esc_attr($gateway->id); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> data-order_button_text="<?php echo esc_attr($gateway->order_button_text); ?>" />
						<label for="payment_method_<?php echo esc_attr($gateway->id); ?>" class="hz-payment-label">
							<?php echo $gateway->get_title(); ?> <?php echo $gateway->get_icon(); ?>
						</label>
						<?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
							<div class="payment_box payment_method_<?php echo esc_attr($gateway->id); ?> hz-payment-box" <?php if (!$gateway->chosen) : ?>style="display:none;"<?php endif; ?>>
								<?php $gateway->payment_fields(); ?>
							</div>
						<?php endif; ?>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		} else {
			echo '<p class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . esc_html__('Sorry, it seems that there are no available payment methods for your region. Please contact us if you require assistance.', 'handzom-ui-kit') . '</p>';
		}
	}

	private function render_empty_cart()
	{
		?>
		<div class="hz-empty-cart">
			<h2 class="hz-empty-cart-title"><?php esc_html_e('YOUR BAG IS EMPTY', 'handzom-ui-kit'); ?></h2>
			<p class="hz-empty-cart-subtitle"><?php esc_html_e('Discover something you\'ll love.', 'handzom-ui-kit'); ?></p>
			<a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/')); ?>" class="hz-continue-shopping-btn"><?php esc_html_e('CONTINUE SHOPPING', 'handzom-ui-kit'); ?></a>
		</div>
		<?php
	}

	public function ajax_apply_coupon()
	{
		check_ajax_referer('handzom_checkout_nonce', 'nonce');

		$coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';

		if (empty($coupon_code)) {
			wp_send_json_error(['message' => esc_html__('Please enter a coupon code.', 'handzom-ui-kit')]);
		}

		if (WC()->cart->has_discount($coupon_code)) {
			wp_send_json_error(['message' => esc_html__('Coupon code already applied!', 'handzom-ui-kit')]);
		}

		$applied = WC()->cart->apply_coupon($coupon_code);

		if ($applied) {
			WC()->cart->calculate_totals();
			wp_send_json_success(['message' => esc_html__('Coupon code applied successfully.', 'handzom-ui-kit')]);
		} else {
			wc_clear_notices();
			wp_send_json_error(['message' => esc_html__('Invalid coupon code.', 'handzom-ui-kit')]);
		}
	}

	public function ajax_remove_coupon()
	{
		check_ajax_referer('handzom_checkout_nonce', 'nonce');

		$coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';

		if (!empty($coupon_code)) {
			WC()->cart->remove_coupon($coupon_code);
			WC()->cart->calculate_totals();
			wp_send_json_success(['message' => esc_html__('Coupon removed successfully.', 'handzom-ui-kit')]);
		}

		wp_send_json_error(['message' => esc_html__('Could not remove coupon.', 'handzom-ui-kit')]);
	}

	public function add_checkout_fragments($fragments)
	{
		ob_start();
		$this->render_order_items();
		$fragments['.hz-checkout-items'] = ob_get_clean();

		ob_start();
		$this->render_order_totals();
		$fragments['.hz-checkout-totals-box'] = ob_get_clean();

		ob_start();
		$this->render_payment_methods();
		$fragments['.hz-checkout-payment-box'] = ob_get_clean();

		return $fragments;
	}
}
