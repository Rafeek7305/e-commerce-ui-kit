<?php
/**
 * Thankyou page - Handzom Custom Template
 *
 * Displays order confirmation overview with dynamic Razorpay Transaction ID
 *
 * @package HandzomUIKit
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;
?>

<div class="woocommerce-order">

	<?php
	if ($order) :

		do_action('woocommerce_before_thankyou', $order->get_id());
		?>

		<?php if ($order->has_status('failed')) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce'); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
				<?php if (is_user_logged_in()) : ?>
					<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay"><?php esc_html_e('My account', 'woocommerce'); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<?php
			$transaction_id = class_exists('\HandzomUIKit\Checkout_Module') ? \HandzomUIKit\Checkout_Module::get_razorpay_payment_id($order) : '';
			if (empty($transaction_id)) {
				$txn_id = trim((string)$order->get_transaction_id());
				if (!empty($txn_id) && !in_array(strtolower($txn_id), ['null', 'undefined', 'false', 'none'], true)) {
					$transaction_id = $txn_id;
				} else {
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
							$transaction_id = $val;
							break;
						}
						$post_val = trim((string)get_post_meta($order->get_id(), $key, true));
						if (!empty($post_val) && !in_array(strtolower($post_val), ['null', 'undefined', 'false', 'none'], true)) {
							$transaction_id = $post_val;
							break;
						}
					}
				}
			}
			?>

			<?php if (function_exists('wc_get_template')) : ?>
				<?php wc_get_template('checkout/order-received.php', ['order' => $order]); ?>
			<?php else : ?>
				<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters('woocommerce_thankyou_order_received_text', esc_html__('Thank you. Your order has been received.', 'woocommerce'), $order); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

				<li class="woocommerce-order-overview__order order">
					<?php esc_html_e('Order number:', 'woocommerce'); ?>
					<strong><?php echo $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<?php esc_html_e('Date:', 'woocommerce'); ?>
					<strong><?php echo wc_format_datetime($order->get_date_created()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email()) : ?>
					<li class="woocommerce-order-overview__email email">
						<?php esc_html_e('Email:', 'woocommerce'); ?>
						<strong><?php echo $order->get_billing_email(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
					</li>
				<?php endif; ?>

				<li class="woocommerce-order-overview__total total">
					<?php esc_html_e('Total:', 'woocommerce'); ?>
					<strong><?php echo $order->get_formatted_order_total(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<?php if ($order->get_payment_method_title()) : ?>
					<li class="woocommerce-order-overview__payment-method method">
						<?php esc_html_e('Payment method:', 'woocommerce'); ?>
						<strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
					</li>
				<?php endif; ?>

				<?php if (!empty($transaction_id)) : ?>
					<li class="woocommerce-order-overview__transaction-id transaction-id">
						<?php esc_html_e('Transaction ID:', 'handzom-ui-kit'); ?>
						<strong><?php echo esc_html($transaction_id); ?></strong>
					</li>
				<?php endif; ?>

			</ul>

		<?php endif; ?>

		<?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
		<?php do_action('woocommerce_thankyou', $order->get_id()); ?>

	<?php else : ?>

		<?php if (function_exists('wc_get_template')) : ?>
			<?php wc_get_template('checkout/order-received.php', ['order' => false]); ?>
		<?php else : ?>
			<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters('woocommerce_thankyou_order_received_text', esc_html__('Thank you. Your order has been received.', 'woocommerce'), null); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>

	<?php endif; ?>

</div>
