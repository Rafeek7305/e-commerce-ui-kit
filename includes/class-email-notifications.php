<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Handzom Custom Customer Payment Email Notifications
 */
class Email_Notifications
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
		// 1. Success Payment Email Hooks
		add_action('woocommerce_order_status_processing', [$this, 'handle_payment_success'], 20, 1);
		add_action('woocommerce_order_status_completed', [$this, 'handle_payment_success'], 20, 1);
		add_action('woocommerce_payment_complete', [$this, 'handle_payment_success'], 20, 1);

		// 2. Failed Payment Email Hook
		add_action('woocommerce_order_status_failed', [$this, 'handle_payment_failed'], 20, 1);

		// 3. Disable ONLY the duplicate default WooCommerce customer-facing order confirmation emails
		add_filter('woocommerce_email_enabled_customer_processing_order', '__return_false', 999);
		add_filter('woocommerce_email_enabled_customer_on_hold_order', '__return_false', 999);
	}

	/**
	 * Get legitimate sender name & email from site configuration
	 */
	private function get_sender_headers()
	{
		$from_name = get_option('woocommerce_email_from_name');
		if (empty($from_name)) {
			$from_name = get_bloginfo('name') ?: 'Handzom';
		}

		$from_email = get_option('woocommerce_email_from_address');
		if (empty($from_email) || !is_email($from_email)) {
			$from_email = get_option('admin_email');
		}

		if (empty($from_email) || !is_email($from_email)) {
			$domain = sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'] ?? 'handzom.com'));
			$domain = preg_replace('/^www\./i', '', $domain);
			$from_email = 'orders@' . $domain;
		}

		return [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_attr($from_name) . ' <' . sanitize_email($from_email) . '>',
			'Reply-To: ' . esc_attr($from_name) . ' <' . sanitize_email($from_email) . '>',
		];
	}

	/**
	 * Handle Payment Success / Order Confirmed Email
	 */
	public function handle_payment_success($order_id)
	{
		if (!$order_id) {
			return;
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}

		// Prevent duplicate success emails
		$already_sent = $order->get_meta('_handzom_success_email_sent');
		if ('yes' === $already_sent) {
			return;
		}

		$recipient = $order->get_billing_email();
		if (empty($recipient) || !is_email($recipient)) {
			return;
		}

		$subject = sprintf(
			/* translators: %s: Order ID */
			esc_html__('Handzom — Order #%s Confirmed', 'handzom-ui-kit'),
			$order->get_order_number()
		);

		$message = $this->build_success_email_html($order);
		$headers = $this->get_sender_headers();

		$sent = wp_mail($recipient, $subject, $message, $headers);

		if ($sent) {
			$order->update_meta_data('_handzom_success_email_sent', 'yes');
			$order->save();
		}
	}

	/**
	 * Handle Payment Failed Email
	 */
	public function handle_payment_failed($order_id)
	{
		if (!$order_id) {
			return;
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}

		// Prevent duplicate failed emails
		$already_sent = $order->get_meta('_handzom_failed_email_sent');
		if ('yes' === $already_sent) {
			return;
		}

		$recipient = $order->get_billing_email();
		if (empty($recipient) || !is_email($recipient)) {
			return;
		}

		$subject = sprintf(
			/* translators: %s: Order ID */
			esc_html__('Handzom — Payment Failed for Order #%s', 'handzom-ui-kit'),
			$order->get_order_number()
		);

		$message = $this->build_failed_email_html($order);
		$headers = $this->get_sender_headers();

		$sent = wp_mail($recipient, $subject, $message, $headers);

		if ($sent) {
			$order->update_meta_data('_handzom_failed_email_sent', 'yes');
			$order->save();
		}
	}

	/**
	 * Get Handzom Logo HTML
	 */
	private function get_logo_html()
	{
		$logo_url = 'https://www.worthbuy.in/demo/handzom/wp-content/uploads/2026/10/logo.png';
		return '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name') ?: 'Handzom') . '" width="180" style="display: block; width: 180px; max-width: 180px; height: auto; margin: 0 auto; border: 0; outline: none; text-decoration: none;" />';
	}

	/**
	 * Build Items Table HTML dynamically (TEXT ONLY - NO PRODUCT IMAGES)
	 */
	private function build_items_table_html($order)
	{
		$items = $order->get_items();
		if (empty($items)) {
			return '';
		}

		ob_start();
		?>
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin-top: 12px; margin-bottom: 16px;">
			<thead>
				<tr style="border-bottom: 1px solid #e5e5e5;">
					<th align="left" style="padding: 10px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #888888;">
						<?php esc_html_e('PRODUCT', 'handzom-ui-kit'); ?>
					</th>
					<th align="center" style="padding: 10px 8px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #888888; width: 55px; white-space: nowrap;">
						<?php esc_html_e('QTY', 'handzom-ui-kit'); ?>
					</th>
					<th align="right" style="padding: 10px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #888888; width: 90px; white-space: nowrap;">
						<?php esc_html_e('PRICE', 'handzom-ui-kit'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($items as $item_id => $item) {
					$_product  = $item->get_product();
					$item_name = $item->get_name();
					$quantity  = $item->get_quantity();
					$subtotal  = $order->get_formatted_line_subtotal($item);

					// Extract Color and Size
					$color = '';
					$size  = '';
					$meta_data = $item->get_formatted_meta_data('');
					foreach ($meta_data as $meta) {
						$k = strtolower(trim($meta->display_key));
						if (strpos($k, 'color') !== false || strpos($k, 'pa_color') !== false) {
							$color = wp_strip_all_tags($meta->display_value);
						} elseif (strpos($k, 'size') !== false || strpos($k, 'pa_size') !== false) {
							$size = wp_strip_all_tags($meta->display_value);
						}
					}

					if (empty($color) && $_product && $_product->is_type('variation')) {
						$attributes = $_product->get_attributes();
						foreach ($attributes as $attr_key => $attr_val) {
							if (strpos(strtolower($attr_key), 'color') !== false) {
								$term = get_term_by('slug', $attr_val, $attr_key);
								$color = $term ? $term->name : $attr_val;
							} elseif (strpos(strtolower($attr_key), 'size') !== false) {
								$term = get_term_by('slug', $attr_val, $attr_key);
								$size = $term ? $term->name : $attr_val;
							}
						}
					}
					?>
					<tr style="border-bottom: 1px solid #f2f2f2;">
						<td align="left" style="padding: 14px 10px 14px 0; vertical-align: top;">
							<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 600; color: #111111; line-height: 1.4;">
								<?php echo esc_html($item_name); ?>
							</div>
							<?php if (!empty($color) || !empty($size)) : ?>
								<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12.5px; color: #777777; line-height: 1.4; margin-top: 3px;">
									<?php if (!empty($color)) : ?>
										<span><?php esc_html_e('Color:', 'handzom-ui-kit'); ?> <?php echo esc_html($color); ?></span>
									<?php endif; ?>
									<?php if (!empty($color) && !empty($size)) : ?>
										<span> &bull; </span>
									<?php endif; ?>
									<?php if (!empty($size)) : ?>
										<span><?php esc_html_e('Size:', 'handzom-ui-kit'); ?> <?php echo esc_html($size); ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</td>
						<td align="center" style="padding: 14px 8px; vertical-align: top; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; color: #555555; white-space: nowrap;">
							&times;<?php echo esc_html($quantity); ?>
						</td>
						<td align="right" style="padding: 14px 0 14px 8px; vertical-align: top; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 600; color: #111111; white-space: nowrap;">
							<?php echo wp_kses_post($subtotal); ?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get the real Razorpay Payment / Transaction ID from the order
	 */
	private function get_razorpay_payment_id($order)
	{
		if (!$order || !is_a($order, 'WC_Order')) {
			return '';
		}

		$order_id = $order->get_id();

		// 1. Standard WooCommerce transaction ID
		$txn_id = trim((string)$order->get_transaction_id());
		if (!empty($txn_id) && !in_array(strtolower($txn_id), ['null', 'undefined', 'false', 'none'], true)) {
			return $txn_id;
		}

		// 2. Direct order metadata checks for Razorpay
		$meta_keys = [
			'razorpay_payment_id',
			'_razorpay_payment_id',
			'rpay_payment_id',
			'_rpay_payment_id',
			'_transaction_id',
			'transaction_id',
			'rzp_payment_id',
			'_rzp_payment_id',
		];

		foreach ($meta_keys as $key) {
			$val = trim((string)$order->get_meta($key));
			if (!empty($val) && !in_array(strtolower($val), ['null', 'undefined', 'false', 'none'], true)) {
				return $val;
			}

			if ($order_id) {
				$post_val = trim((string)get_post_meta($order_id, $key, true));
				if (!empty($post_val) && !in_array(strtolower($post_val), ['null', 'undefined', 'false', 'none'], true)) {
					return $post_val;
				}
			}
		}

		return '';
	}

	/**
	 * Build Totals & Payment Method Summary HTML
	 */
	private function build_totals_summary_html($order)
	{
		$payment_method_title = $order->get_payment_method_title() ?: __('Razorpay', 'handzom-ui-kit');
		$transaction_id       = $this->get_razorpay_payment_id($order);

		ob_start();
		?>
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin-top: 6px; margin-bottom: 22px;">
			<tbody>
				<!-- Subtotal -->
				<tr>
					<td style="padding: 6px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; color: #666666; text-align: left;">
						<?php esc_html_e('Subtotal', 'handzom-ui-kit'); ?>
					</td>
					<td style="padding: 6px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; color: #111111; font-weight: 500; text-align: right;">
						<?php echo wp_kses_post($order->get_subtotal_to_display()); ?>
					</td>
				</tr>

				<!-- Shipping -->
				<tr>
					<td style="padding: 6px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; color: #666666; text-align: left;">
						<?php esc_html_e('Shipping', 'handzom-ui-kit'); ?>
					</td>
					<td style="padding: 6px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; color: #111111; font-weight: 500; text-align: right;">
						<?php echo wp_kses_post($order->get_shipping_to_display() ?: esc_html__('Free', 'handzom-ui-kit')); ?>
					</td>
				</tr>

				<!-- Total -->
				<tr>
					<td style="padding: 12px 0 6px; border-top: 1px solid #111111; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14.5px; font-weight: 700; color: #111111; text-transform: uppercase; letter-spacing: 0.8px; text-align: left;">
						<?php esc_html_e('Total', 'handzom-ui-kit'); ?>
					</td>
					<td style="padding: 12px 0 6px; border-top: 1px solid #111111; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 700; color: #111111; text-align: right;">
						<?php echo wp_kses_post($order->get_formatted_order_total()); ?>
					</td>
				</tr>

				<!-- Payment Details -->
				<tr>
					<td colspan="2" style="padding: 16px 0 4px; border-top: 1px solid #e5e5e5;">
						<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #888888; margin-bottom: 8px;">
							<?php esc_html_e('PAYMENT DETAILS', 'handzom-ui-kit'); ?>
						</div>
						<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
							<tr>
								<td style="padding: 3px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #666666; width: 130px; vertical-align: top; white-space: nowrap;">
									<?php esc_html_e('Payment Method:', 'handzom-ui-kit'); ?>
								</td>
								<td style="padding: 3px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; color: #111111; font-weight: 500; text-align: right; vertical-align: top;">
									<?php echo esc_html($payment_method_title); ?>
								</td>
							</tr>
							<?php if (!empty($transaction_id)) : ?>
							<tr>
								<td style="padding: 3px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #666666; width: 130px; vertical-align: top; white-space: nowrap;">
									<?php esc_html_e('Transaction ID:', 'handzom-ui-kit'); ?>
								</td>
								<td style="padding: 3px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; color: #111111; font-weight: 600; text-align: right; vertical-align: top; word-break: break-all; word-wrap: break-word; overflow-wrap: anywhere;">
									<?php echo esc_html($transaction_id); ?>
								</td>
							</tr>
							<?php endif; ?>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Build Success Email HTML
	 */
	public function build_success_email_html($order)
	{
		$customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
		if (empty($customer_name)) {
			$customer_name = $order->get_billing_first_name() ?: esc_html__('Customer', 'handzom-ui-kit');
		}

		$order_id   = $order->get_order_number();
		$order_date = $order->get_date_created() ? wc_format_datetime($order->get_date_created(), 'F j, Y') : gmdate('F j, Y');
		$logo_html  = $this->get_logo_html();
		$items_html = $this->build_items_table_html($order);
		$totals_html = $this->build_totals_summary_html($order);

		ob_start();
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<title><?php echo esc_html(sprintf(__('Order #%s Confirmed', 'handzom-ui-kit'), $order_id)); ?></title>
		</head>
		<body style="margin: 0; padding: 0; background-color: #f7f7f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f7f7f7; padding: 35px 15px;">
				<tr>
					<td align="center">
						<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #e5e5e5; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">

							<!-- Top: Handzom Logo Centered -->
							<tr>
								<td align="center" style="padding: 30px 24px 22px; border-bottom: 1px solid #e5e5e5; background-color: #ffffff;">
									<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>
							</tr>

							<!-- Main Content -->
							<tr>
								<td style="padding: 34px 30px 24px;">

									<!-- Subtitle / Tag -->
									<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #111111; margin-bottom: 8px;">
										<?php esc_html_e('ORDER CONFIRMED', 'handzom-ui-kit'); ?>
									</div>

									<!-- Customer Greeting -->
									<h1 style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #111111; margin: 0 0 10px; line-height: 1.3;">
										<?php echo esc_html(sprintf(__('Thank you for your order, %s.', 'handzom-ui-kit'), $customer_name)); ?>
									</h1>

									<p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14.5px; color: #555555; line-height: 1.6; margin: 0 0 26px;">
										<?php esc_html_e('Your payment has been successfully received and your order is now being processed.', 'handzom-ui-kit'); ?>
									</p>

									<!-- Order Summary Header -->
									<div style="border-top: 1px solid #e5e5e5; padding-top: 22px; margin-bottom: 12px;">
										<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12.5px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #111111; margin-bottom: 3px;">
											<?php esc_html_e('ORDER SUMMARY', 'handzom-ui-kit'); ?>
										</div>
										<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #777777;">
											<?php echo esc_html(sprintf(__('Order #%s', 'handzom-ui-kit'), $order_id)); ?> &bull; <?php echo esc_html($order_date); ?>
										</div>
									</div>

									<!-- Items Table (NO IMAGES) -->
									<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

									<!-- Totals & Payment Method Summary -->
									<?php echo $totals_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

									<!-- Payment Status Badge -->
									<div style="background-color: #fafafa; border: 1px solid #e5e5e5; border-left: 3px solid #111111; border-radius: 2px; padding: 14px 18px; margin: 18px 0 22px;">
										<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; color: #888888; margin-bottom: 2px;">
											<?php esc_html_e('PAYMENT STATUS', 'handzom-ui-kit'); ?>
										</div>
										<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; font-weight: 700; color: #111111; letter-spacing: 0.5px;">
											&#x2713; <?php esc_html_e('PAYMENT SUCCESSFUL', 'handzom-ui-kit'); ?>
										</div>
									</div>

									<p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; color: #666666; line-height: 1.6; margin: 0;">
										<?php esc_html_e('Thank you for shopping with Handzom.', 'handzom-ui-kit'); ?>
									</p>

								</td>
							</tr>

							<!-- Footer -->
							<tr>
								<td align="center" style="padding: 24px 30px; background-color: #fafafa; border-top: 1px solid #e5e5e5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
									<div style="font-size: 13px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #111111; margin-bottom: 3px;">
										<?php echo esc_html(get_bloginfo('name') ?: 'Handzom'); ?>
									</div>
									<div style="font-size: 12px; color: #777777; margin-bottom: 12px;">
										<?php esc_html_e('Premium Everyday Wear', 'handzom-ui-kit'); ?>
									</div>
									<div style="font-size: 11px; color: #999999; line-height: 1.5;">
										&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html(get_bloginfo('name') ?: 'Handzom'); ?>. <?php esc_html_e('All rights reserved.', 'handzom-ui-kit'); ?>
									</div>
								</td>
							</tr>

						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Build Failed Email HTML
	 */
	public function build_failed_email_html($order)
	{
		$customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
		if (empty($customer_name)) {
			$customer_name = $order->get_billing_first_name() ?: esc_html__('Customer', 'handzom-ui-kit');
		}

		$order_id   = $order->get_order_number();
		$order_date = $order->get_date_created() ? wc_format_datetime($order->get_date_created(), 'F j, Y') : gmdate('F j, Y');
		$logo_html  = $this->get_logo_html();
		$items_html = $this->build_items_table_html($order);
		$totals_html = $this->build_totals_summary_html($order);
		$pay_url    = $order->get_checkout_payment_url(true);

		ob_start();
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<title><?php echo esc_html(sprintf(__('Payment Failed for Order #%s', 'handzom-ui-kit'), $order_id)); ?></title>
		</head>
		<body style="margin: 0; padding: 0; background-color: #f7f7f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f7f7f7; padding: 35px 15px;">
				<tr>
					<td align="center">
						<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #e5e5e5; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">

							<!-- Top: Handzom Logo Centered -->
							<tr>
								<td align="center" style="padding: 30px 24px 22px; border-bottom: 1px solid #e5e5e5; background-color: #ffffff;">
									<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>
							</tr>

							<!-- Main Content -->
							<tr>
								<td style="padding: 34px 30px 24px;">

									<!-- Subtitle / Tag -->
									<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #b71c1c; margin-bottom: 8px;">
										<?php esc_html_e('PAYMENT UNSUCCESSFUL', 'handzom-ui-kit'); ?>
									</div>

									<!-- Customer Greeting -->
									<h1 style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #111111; margin: 0 0 10px; line-height: 1.3;">
										<?php echo esc_html(sprintf(__('Hi %s,', 'handzom-ui-kit'), $customer_name)); ?>
									</h1>

									<p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14.5px; color: #555555; line-height: 1.6; margin: 0 0 26px;">
										<?php esc_html_e('Unfortunately, your payment could not be completed.', 'handzom-ui-kit'); ?>
									</p>

									<!-- Order Summary Header -->
									<div style="border-top: 1px solid #e5e5e5; padding-top: 22px; margin-bottom: 12px;">
										<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12.5px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #111111; margin-bottom: 3px;">
											<?php esc_html_e('ORDER SUMMARY', 'handzom-ui-kit'); ?>
										</div>
										<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #777777;">
											<?php echo esc_html(sprintf(__('Order #%s', 'handzom-ui-kit'), $order_id)); ?> &bull; <?php echo esc_html($order_date); ?>
										</div>
									</div>

									<!-- Items Table (NO IMAGES) -->
									<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

									<!-- Totals & Payment Method Summary -->
									<?php echo $totals_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

									<!-- Payment Status Badge -->
									<div style="background-color: #fff8f8; border: 1px solid #f5c6cb; border-left: 3px solid #d32f2f; border-radius: 2px; padding: 14px 18px; margin: 18px 0 22px;">
										<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; color: #b71c1c; margin-bottom: 2px;">
											<?php esc_html_e('PAYMENT STATUS', 'handzom-ui-kit'); ?>
										</div>
										<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13.5px; font-weight: 700; color: #b71c1c; letter-spacing: 0.5px;">
											&#x2715; <?php esc_html_e('PAYMENT FAILED', 'handzom-ui-kit'); ?>
										</div>
									</div>

									<p style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #555555; line-height: 1.6; margin: 0 0 24px; text-align: center;">
										<?php esc_html_e('Please try the payment again to complete your order.', 'handzom-ui-kit'); ?>
									</p>

									<!-- Retry Payment CTA Button -->
									<div style="text-align: center; margin: 0 0 10px;">
										<a href="<?php echo esc_url($pay_url); ?>" style="display: inline-block; width: 220px; background-color: #111111; color: #ffffff; text-align: center; text-decoration: none; padding: 14px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight: 700; font-size: 13px; letter-spacing: 2px; text-transform: uppercase; border-radius: 2px; box-sizing: border-box;">
											<?php esc_html_e('TRY PAYMENT AGAIN', 'handzom-ui-kit'); ?>
										</a>
									</div>

								</td>
							</tr>

							<!-- Footer -->
							<tr>
								<td align="center" style="padding: 24px 30px; background-color: #fafafa; border-top: 1px solid #e5e5e5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
									<div style="font-size: 13px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #111111; margin-bottom: 3px;">
										<?php echo esc_html(get_bloginfo('name') ?: 'Handzom'); ?>
									</div>
									<div style="font-size: 12px; color: #777777; margin-bottom: 12px;">
										<?php esc_html_e('Premium Everyday Wear', 'handzom-ui-kit'); ?>
									</div>
									<div style="font-size: 11px; color: #999999; line-height: 1.5;">
										&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html(get_bloginfo('name') ?: 'Handzom'); ?>. <?php esc_html_e('All rights reserved.', 'handzom-ui-kit'); ?>
									</div>
								</td>
							</tr>

						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}
