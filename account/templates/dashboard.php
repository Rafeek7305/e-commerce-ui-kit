<?php
/**
 * HANDZOM Premium WooCommerce My Account Dashboard Template
 *
 * Editorial Luxury 2026 Fashion Interface
 *
 * @version 4.4.0
 */

if (!defined('ABSPATH')) {
	exit;
}

$current_user = wp_get_current_user();
$customer_name = !empty($current_user->first_name) ? $current_user->first_name : $current_user->display_name;
$full_name = !empty($current_user->display_name) ? $current_user->display_name : $customer_name;
$logout_url = wc_logout_url();

$orders_url = wc_get_endpoint_url('orders');
$addresses_url = wc_get_endpoint_url('edit-address');
$account_url = wc_get_endpoint_url('edit-account');
?>

<div class="hz-account-overview">
	<!-- Editorial Greeting Header -->
	<header class="hz-overview-header">
		<div class="hz-overview-greeting">
			<span class="hz-overview-kicker"><?php esc_html_e('Personal Dashboard', 'handzom-ui-kit'); ?></span>
			<h2 class="hz-overview-title">
				<?php
				printf(
					/* translators: %s: Customer Name */
					esc_html__('Hello, %s', 'handzom-ui-kit'),
					'<span class="hz-customer-name">' . esc_html($full_name) . '</span>'
				);
				?>
			</h2>
			<p class="hz-overview-desc">
				<?php esc_html_e('Welcome to your personal account. Manage your wardrobe orders, customize your delivery addresses, and update your profile details.', 'handzom-ui-kit'); ?>
			</p>
		</div>

		<div class="hz-overview-signout">
			<span class="hz-signout-text"><?php printf(esc_html__('Not %s?', 'handzom-ui-kit'), esc_html($full_name)); ?></span>
			<a href="<?php echo esc_url($logout_url); ?>" class="hz-signout-link"><?php esc_html_e('Log out', 'handzom-ui-kit'); ?></a>
		</div>
	</header>

	<!-- Quick-Access Cards -->
	<div class="hz-overview-cards">
		<!-- Orders Card -->
		<a href="<?php echo esc_url($orders_url); ?>" class="hz-overview-card" id="hz-card-orders">
			<div class="hz-card-top">
				<div class="hz-card-icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
						<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
						<line x1="3" y1="6" x2="21" y2="6"></line>
						<path d="M16 10a4 4 0 0 1-8 0"></path>
					</svg>
				</div>
				<span class="hz-card-arrow" aria-hidden="true">&rarr;</span>
			</div>
			<div class="hz-card-content">
				<h3 class="hz-card-heading"><?php esc_html_e('Orders', 'handzom-ui-kit'); ?></h3>
				<p class="hz-card-text"><?php esc_html_e('Track active shipments, view past purchases, and download official invoices.', 'handzom-ui-kit'); ?></p>
			</div>
			<div class="hz-card-action">
				<span><?php esc_html_e('View orders', 'handzom-ui-kit'); ?></span>
			</div>
		</a>

		<!-- Addresses Card -->
		<a href="<?php echo esc_url($addresses_url); ?>" class="hz-overview-card" id="hz-card-addresses">
			<div class="hz-card-top">
				<div class="hz-card-icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
						<circle cx="12" cy="10" r="3"></circle>
					</svg>
				</div>
				<span class="hz-card-arrow" aria-hidden="true">&rarr;</span>
			</div>
			<div class="hz-card-content">
				<h3 class="hz-card-heading"><?php esc_html_e('Addresses', 'handzom-ui-kit'); ?></h3>
				<p class="hz-card-text"><?php esc_html_e('Manage your default billing details and primary shipping destinations.', 'handzom-ui-kit'); ?></p>
			</div>
			<div class="hz-card-action">
				<span><?php esc_html_e('Manage addresses', 'handzom-ui-kit'); ?></span>
			</div>
		</a>

		<!-- Account Details Card -->
		<a href="<?php echo esc_url($account_url); ?>" class="hz-overview-card" id="hz-card-details">
			<div class="hz-card-top">
				<div class="hz-card-icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
				</div>
				<span class="hz-card-arrow" aria-hidden="true">&rarr;</span>
			</div>
			<div class="hz-card-content">
				<h3 class="hz-card-heading"><?php esc_html_e('Account details', 'handzom-ui-kit'); ?></h3>
				<p class="hz-card-text"><?php esc_html_e('Edit your personal contact info, change email, and update password.', 'handzom-ui-kit'); ?></p>
			</div>
			<div class="hz-card-action">
				<span><?php esc_html_e('Edit account', 'handzom-ui-kit'); ?></span>
			</div>
		</a>
	</div>

	<?php
	/**
	 * Standard WooCommerce dashboard hooks
	 */
	do_action('woocommerce_account_dashboard');
	do_action('woocommerce_before_my_account');
	do_action('woocommerce_after_my_account');
	?>
</div>
