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

		// AJAX Handlers for Email OTP Registration (Stage 1 & 2)
		add_action('wp_ajax_handzom_send_registration_otp', [$this, 'ajax_send_registration_otp']);
		add_action('wp_ajax_nopriv_handzom_send_registration_otp', [$this, 'ajax_send_registration_otp']);
		add_action('wp_ajax_handzom_verify_registration_otp', [$this, 'ajax_verify_registration_otp']);
		add_action('wp_ajax_nopriv_handzom_verify_registration_otp', [$this, 'ajax_verify_registration_otp']);

		// AJAX Handlers for Registration & Login completion flow
		add_action('wp_ajax_handzom_register_account', [$this, 'ajax_register_account']);
		add_action('wp_ajax_nopriv_handzom_register_account', [$this, 'ajax_register_account']);
		add_action('wp_ajax_handzom_login_account', [$this, 'ajax_login_account']);
		add_action('wp_ajax_nopriv_handzom_login_account', [$this, 'ajax_login_account']);

		// AJAX Handlers for OTP Cooldown & Session Management
		add_action('wp_ajax_handzom_check_otp_cooldown', [$this, 'ajax_check_otp_cooldown']);
		add_action('wp_ajax_nopriv_handzom_check_otp_cooldown', [$this, 'ajax_check_otp_cooldown']);
		add_action('wp_ajax_handzom_clear_registration_session', [$this, 'ajax_clear_registration_session']);
		add_action('wp_ajax_nopriv_handzom_clear_registration_session', [$this, 'ajax_clear_registration_session']);
		add_action('wp_ajax_handzom_change_registration_email', [$this, 'ajax_clear_registration_session']);
		add_action('wp_ajax_nopriv_handzom_change_registration_email', [$this, 'ajax_clear_registration_session']);

		// Unhook default notices from appearing above customer login form so errors stay inside the cards
		add_action('init', function () {
			remove_action('woocommerce_before_customer_login_form', 'woocommerce_output_all_notices', 10);
		});

		// Safe redirect to homepage after login
		add_filter('woocommerce_login_redirect', [$this, 'filter_login_redirect'], 10, 2);

		// WooCommerce AJAX update_checkout Fragments
		add_filter('woocommerce_update_order_review_fragments', [$this, 'add_checkout_fragments']);

		// WooCommerce thankyou.php template override for dynamic Razorpay Transaction ID
		add_filter('woocommerce_locate_template', [$this, 'override_thankyou_template'], 20, 3);
		add_filter('wc_get_template', [$this, 'filter_wc_get_template'], 20, 5);

		// Checkout Notices Text Formatting
		add_filter('woocommerce_checkout_login_message', [$this, 'filter_checkout_login_message'], 20);
		add_filter('gettext', [$this, 'filter_checkout_gettext'], 20, 3);

		// Render Create Account notice for guests directly below Login notice
		add_action('woocommerce_before_checkout_form', [$this, 'render_create_account_notice'], 10);

		// Ensure native WooCommerce customer registration is enabled on My Account
		add_filter('option_woocommerce_enable_myaccount_registration', function () {
			return 'yes';
		});
		add_filter('pre_option_woocommerce_enable_myaccount_registration', function () {
			return 'yes';
		});

		// Customer friendly registration email already exists message
		add_filter('woocommerce_registration_error_email_exists', [$this, 'filter_registration_error_email_exists'], 20, 2);

		// Allow customer to set their own password during registration
		add_filter('option_woocommerce_registration_generate_password', function () {
			return 'no';
		});
		add_filter('pre_option_woocommerce_registration_generate_password', function () {
			return 'no';
		});

		// Server-side registration validation: duplicate email, OTP verification, and password match
		add_filter('woocommerce_process_registration_errors', [$this, 'validate_registration_submission'], 10, 4);

		// Post-registration customer setup: authenticate customer, clear OTP state, add Handzom notice
		add_action('woocommerce_created_customer', [$this, 'handle_customer_registration_complete'], 10, 3);

		// Safe redirect to homepage after registration
		add_filter('woocommerce_registration_redirect', [$this, 'filter_registration_redirect'], 10, 2);

		// Clear stale notices on fresh GET visits to login/register form
		add_action('wp', [$this, 'clear_stale_account_notices'], 5);
		add_action('template_redirect', [$this, 'clear_stale_account_notices'], 5);

		// Clear registration state on user logout or login to prevent stale state bleed
		add_action('wp_logout', [$this, 'clear_registration_session_state']);
		add_action('wp_login', [$this, 'clear_registration_session_state']);
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

		$active_otp            = null;
		$verified_session      = null;
		$cooldown_info         = null;
		$email_change_cooldown = null;

		// Check Session-Level Email Change Cooldown
		$reg_session_id     = $this->get_or_create_registration_session_id();
		$em_change_cd_until = (int) get_transient('hz_em_change_cd_' . $reg_session_id);
		if ($em_change_cd_until <= 0) {
			$u_ip               = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
			$em_change_cd_until = (int) get_transient('hz_em_change_cd_ip_' . md5($u_ip));
		}
		if ($em_change_cd_until > 0 && time() >= $em_change_cd_until) {
			delete_transient('hz_em_change_cd_' . $reg_session_id);
			$u_ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
			delete_transient('hz_em_change_cd_ip_' . md5($u_ip));
			delete_transient('hz_em_changes_' . $reg_session_id);
			$em_change_cd_until = 0;
		} elseif ($em_change_cd_until <= 0 && (int) get_transient('hz_em_changes_' . $reg_session_id) >= 3) {
			delete_transient('hz_em_changes_' . $reg_session_id);
		}

		$em_change_cd_remaining = max(0, $em_change_cd_until - time());
		if ($em_change_cd_remaining > 0) {
			$mins                  = (int) floor($em_change_cd_remaining / 60);
			$secs                  = $em_change_cd_remaining % 60;
			$email_change_cooldown = [
				'is_active'          => true,
				'cooldown_until'     => $em_change_cd_until,
				'cooldown_remaining' => $em_change_cd_remaining,
				'formatted_time'     => sprintf('%02d:%02d', $mins, $secs),
				'server_time'        => time(),
			];
		}

		$pending_email = '';
		if (!is_user_logged_in() && function_exists('WC') && isset(WC()->session)) {
			if (!WC()->session->has_session() && !empty($_COOKIE['hz_reg_email'])) {
				WC()->session->set_customer_session_cookie(true);
			}

			$saved_verified = WC()->session->get('hz_reg_verified_data');
			if (is_array($saved_verified) && !empty($saved_verified['expires_at']) && $saved_verified['expires_at'] > time()) {
				$v_email = sanitize_email($saved_verified['email'] ?? '');
				$v_token = sanitize_text_field($saved_verified['token'] ?? '');

				// Verify against server-side transient and check that account doesn't already exist
				$transient_verified = !empty($v_email) ? get_transient('hz_reg_verified_' . md5($v_email)) : null;
				$transient_valid    = is_array($transient_verified) && !empty($transient_verified['token']) && hash_equals($transient_verified['token'], $v_token);

				if (!empty($v_email) && !empty($v_token) && !email_exists($v_email) && $transient_valid) {
					$verified_session = [
						'email' => $v_email,
						'token' => $v_token,
					];
				} else {
					// Invalidated, expired, or user already registered -> purge stale state
					WC()->session->set('hz_reg_verified_data', null);
					if (!empty($v_email)) {
						delete_transient('hz_reg_verified_' . md5($v_email));
					}
				}
			} else {
				// Expired or invalid -> clean up session
				WC()->session->set('hz_reg_verified_data', null);
			}

			if (!$verified_session) {
				$saved_otp = WC()->session->get('hz_registration_otp');
				if (is_array($saved_otp) && !empty($saved_otp['email'])) {
					$pending_email = sanitize_email($saved_otp['email']);
				}
				if (empty($pending_email)) {
					$pending_email = sanitize_email(WC()->session->get('hz_pending_reg_email') ?? '');
				}

				if (!empty($pending_email) && email_exists($pending_email)) {
					$this->clear_registration_session_state($pending_email);
					$pending_email = '';
				}
			}
		}

		if (!is_user_logged_in() && !$verified_session && empty($pending_email) && !empty($_COOKIE['hz_reg_email'])) {
			$cookie_email = sanitize_email($_COOKIE['hz_reg_email']);
			if (!empty($cookie_email) && !email_exists($cookie_email)) {
				$c_hash = md5(strtolower(trim($cookie_email)));
				// Only accept cookie email if an active transient OTP or active cooldown exists for it
				if (get_transient('hz_reg_otp_' . $c_hash) || get_transient('hz_otp_cooldown_' . $c_hash)) {
					$pending_email = $cookie_email;
				}
			} else {
				$cookie_path = COOKIEPATH ? COOKIEPATH : '/';
				$cookie_domain = COOKIE_DOMAIN ? COOKIE_DOMAIN : '';
				if (!headers_sent()) {
					setcookie('hz_reg_email', '', time() - 3600, $cookie_path, $cookie_domain, is_ssl(), true);
				}
				unset($_COOKIE['hz_reg_email']);
			}
		}

		if (!is_user_logged_in() && !$verified_session && !empty($pending_email)) {
			$email_hash = md5(strtolower(trim($pending_email)));
			$otp_data = null;
			if (function_exists('WC') && isset(WC()->session)) {
				$otp_data = WC()->session->get('hz_registration_otp');
			}
			if (empty($otp_data) || !is_array($otp_data)) {
				$otp_data = get_transient('hz_reg_otp_' . $email_hash);
			}

			$resend_data = get_transient('hz_otp_resends_' . $email_hash);
			$cooldown_timestamp = (int) ($resend_data['cooldown_until'] ?? get_transient('hz_otp_cooldown_' . $email_hash));
			if ($cooldown_timestamp > 0 && time() >= $cooldown_timestamp) {
				delete_transient('hz_otp_cooldown_' . $email_hash);
				delete_transient('hz_otp_resends_' . $email_hash);
				$resend_data = null;
				$cooldown_timestamp = 0;
			}
			$resend_count = is_array($resend_data) ? (int) ($resend_data['count'] ?? 0) : 0;
			$cooldown_remaining = max(0, $cooldown_timestamp - time());

			if ($cooldown_remaining > 0) {
				// Email has an active 10-minute cooldown!
				// Return to initial email step with accurate server cooldown info
				$mins = (int) floor($cooldown_remaining / 60);
				$secs = $cooldown_remaining % 60;
				$cooldown_info = [
					'email'                => strtolower(trim($pending_email)),
					'cooldown_remaining'   => $cooldown_remaining,
					'cooldown_until'       => $cooldown_timestamp,
					'formatted_time'       => sprintf('%02d:%02d', $mins, $secs),
					'resend_limit_reached' => true,
					'server_time'          => time(),
				];
				// Do not set active_otp during active cooldown so user is not trapped in OTP screen
			} elseif (is_array($otp_data) && !empty($otp_data['expires_at'])) {
				$is_expired          = time() >= $otp_data['expires_at'];
				$resend_available_at = !empty($otp_data['resend_available_at'])
					? absint($otp_data['resend_available_at'])
					: (absint($otp_data['created_at'] ?? 0) + 60);
				$resend_wait_sec     = max(0, $resend_available_at - time());
				$resend_limit_hit    = ($resend_count >= 3);

				$active_otp = [
					'email'                => strtolower(trim($pending_email)),
					'expires_at'           => absint($otp_data['expires_at']),
					'remaining'            => max(0, absint($otp_data['expires_at']) - time()),
					'is_expired'           => $is_expired,
					'resend_available_at'  => $resend_available_at,
					'resend_remaining'     => $resend_wait_sec,
					'server_time'          => time(),
					'resend_count'         => $resend_count,
					'resends_remaining'    => max(0, 3 - $resend_count),
					'resend_limit_reached' => $resend_limit_hit,
					'cooldown_until'       => $resend_limit_hit ? $cooldown_timestamp : 0,
					'cooldown_remaining'   => $resend_limit_hit ? $cooldown_remaining : 0,
				];
			}
		}

		// Suppress active_otp during email change cooldown so user is kept on Step 1 (email entry step)
		if ($email_change_cooldown && !empty($email_change_cooldown['is_active'])) {
			$active_otp = null;
		}

		wp_localize_script('handzom-checkout', 'handzom_checkout_ajax', [
			'ajax_url'              => admin_url('admin-ajax.php'),
			'nonce'                 => wp_create_nonce('handzom_checkout_nonce'),
			'active_otp'            => $active_otp,
			'verified_session'      => $verified_session,
			'cooldown_info'         => $cooldown_info,
			'email_change_cooldown' => $email_change_cooldown,
		]);

		if (function_exists('is_checkout') && is_checkout()) {
			wp_enqueue_style('handzom-checkout');
			wp_enqueue_script('handzom-checkout');
		}

		if (function_exists('is_account_page') && is_account_page()) {
			wp_dequeue_script('wc-password-toggle');
			wp_deregister_script('wc-password-toggle');
			wp_enqueue_style('handzom-checkout');
			wp_enqueue_script('handzom-checkout');
		}

		// Enqueue checkout.css on front page if notices are present to preserve styling
		if ((is_front_page() || is_home()) && function_exists('wc_notice_count') && wc_notice_count() > 0) {
			wp_enqueue_style('handzom-checkout');
		}
	}

	public function render_shortcode()
	{
		global $wp;

		// 1. Handle WooCommerce order-pay and order-received endpoints (required by Razorpay & other gateways)
		if ((function_exists('is_wc_endpoint_url') && (is_wc_endpoint_url('order-pay') || is_wc_endpoint_url('order-received'))) || isset($wp->query_vars['order-pay']) || isset($wp->query_vars['order-received'])) {
			wp_enqueue_style('handzom-checkout');
			wp_enqueue_script('handzom-checkout');

			$is_pay_page = (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-pay')) || isset($wp->query_vars['order-pay']);

			ob_start();
			if ($is_pay_page) {
				$order_id = isset($wp->query_vars['order-pay']) ? absint($wp->query_vars['order-pay']) : 0;
				$order    = $order_id > 0 ? wc_get_order($order_id) : false;

				echo '<div class="hz-checkout-page hz-order-pay-page handzom-checkout-wrapper">';
				echo '<div class="hz-checkout-header hz-order-pay-header">';
				echo '<h1 class="hz-checkout-title">' . esc_html__('PAY FOR ORDER', 'handzom-ui-kit') . '</h1>';
				echo '<div class="hz-checkout-progress"><span class="hz-progress-step">' . esc_html__('Information', 'handzom-ui-kit') . '</span><span class="hz-progress-sep">&mdash;</span><span class="hz-progress-step active">' . esc_html__('Payment', 'handzom-ui-kit') . '</span></div>';
				echo '</div>';
				echo '<div class="hz-order-pay-card">';

				if ($order) {
					?>
					<div class="hz-order-pay-summary">
						<div class="hz-order-pay-number">
							<span class="hz-pay-order-title"><?php esc_html_e('ORDER', 'handzom-ui-kit'); ?> #<?php echo esc_html($order->get_order_number()); ?></span>
						</div>
						<div class="hz-order-pay-divider"></div>
						<div class="hz-order-pay-meta-grid">
							<div class="hz-order-pay-meta-item">
								<span class="hz-pay-label"><?php esc_html_e('DATE', 'handzom-ui-kit'); ?></span>
								<span class="hz-pay-val"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></span>
							</div>
							<div class="hz-order-pay-meta-item">
								<span class="hz-pay-label"><?php esc_html_e('TOTAL', 'handzom-ui-kit'); ?></span>
								<span class="hz-pay-val hz-pay-total"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
							</div>
							<div class="hz-order-pay-meta-item hz-meta-full">
								<span class="hz-pay-label"><?php esc_html_e('PAYMENT METHOD', 'handzom-ui-kit'); ?></span>
								<span class="hz-pay-val"><?php echo esc_html($order->get_payment_method_title()); ?></span>
							</div>
						</div>
						<div class="hz-order-pay-divider"></div>
					</div>

					<div class="hz-order-pay-secure-heading">
						<h3 class="hz-secure-title"><?php esc_html_e('SECURE PAYMENT', 'handzom-ui-kit'); ?></h3>
						<p class="hz-secure-subtitle"><?php esc_html_e('Your order is ready. Complete your payment securely with Razorpay.', 'handzom-ui-kit'); ?></p>
					</div>
					<?php
				}

				echo '<div class="hz-order-pay-wc-content">';
			} else {
				echo '<div class="hz-checkout-page hz-order-received-page handzom-checkout-wrapper">';
			}

			if (class_exists('WC_Shortcodes')) {
				echo \WC_Shortcodes::shortcode_wrapper(['WC_Shortcode_Checkout', 'output']);
			} else {
				echo do_shortcode('[woocommerce_checkout]');
			}

			if ($is_pay_page) {
				echo '</div>'; // .hz-order-pay-wc-content
				echo '</div>'; // .hz-order-pay-card
			}
			echo '</div>'; // .hz-checkout-page

			return ob_get_clean();
		}

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

		<?php do_action('woocommerce_before_checkout_form', $checkout); ?>

		<form name="checkout" method="post" class="checkout woocommerce-checkout hz-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

			<div class="hz-checkout-grid-container">

				<!-- LEFT COLUMN: Contact & Address Details -->
				<div class="hz-checkout-form-column">

					<?php do_action('woocommerce_checkout_before_customer_details'); ?>

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

					<?php do_action('woocommerce_checkout_after_customer_details'); ?>

				</div>

				<!-- RIGHT COLUMN: Order Summary, Coupon, Shipping & Payment -->
				<aside class="hz-checkout-summary-column">
					<?php do_action('woocommerce_checkout_before_order_review'); ?>
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
							<?php do_action('woocommerce_review_order_before_submit'); ?>
							<?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
							<button type="submit" class="hz-place-order-btn" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e('PLACE ORDER', 'handzom-ui-kit'); ?>" data-value="<?php esc_attr_e('PLACE ORDER', 'handzom-ui-kit'); ?>"><?php esc_html_e('PLACE ORDER', 'handzom-ui-kit'); ?></button>
							<?php do_action('woocommerce_review_order_after_submit'); ?>
						</div>

					</div>
					<?php do_action('woocommerce_checkout_after_order_review'); ?>
				</aside>

			</div>

		</form>

		<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
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

	/**
	 * Override WooCommerce thankyou.php and myaccount form-login.php templates
	 */
	public function override_thankyou_template($template, $template_name, $template_path)
	{
		if ('checkout/thankyou.php' === $template_name) {
			$custom_template = HANDZOM_UI_KIT_PATH . 'checkout/templates/thankyou.php';
			if (file_exists($custom_template)) {
				return $custom_template;
			}
		}
		if ('myaccount/form-login.php' === $template_name) {
			$custom_template = HANDZOM_UI_KIT_PATH . 'checkout/templates/form-login.php';
			if (file_exists($custom_template)) {
				return $custom_template;
			}
		}
		return $template;
	}

	/**
	 * Filter wc_get_template for thankyou.php and myaccount form-login.php
	 */
	public function filter_wc_get_template($located, $template_name, $args, $template_path, $default_path)
	{
		if ('checkout/thankyou.php' === $template_name) {
			$custom_template = HANDZOM_UI_KIT_PATH . 'checkout/templates/thankyou.php';
			if (file_exists($custom_template)) {
				return $custom_template;
			}
		}
		if ('myaccount/form-login.php' === $template_name) {
			$custom_template = HANDZOM_UI_KIT_PATH . 'checkout/templates/form-login.php';
			if (file_exists($custom_template)) {
				return $custom_template;
			}
		}
		return $located;
	}

	/**
	 * Get Razorpay payment / transaction ID from order
	 */
	public static function get_razorpay_payment_id($order)
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
	 * Filter WooCommerce checkout login message
	 */
	public function filter_checkout_login_message($message)
	{
		return esc_html__('Returning customer?', 'handzom-ui-kit');
	}

	/**
	 * Filter gettext strings for checkout and registration notices
	 */
	public function filter_checkout_gettext($translated_text, $text, $domain)
	{
		if ('woocommerce' === $domain) {
			if ('Click here to login' === $text) {
				return esc_html__('Log in to your account', 'handzom-ui-kit');
			}
		}
		return $translated_text;
	}

	/**
	 * Filter registration email already exists error
	 */
	public function filter_registration_error_email_exists($error, $email)
	{
		return __('An account is already registered with this email address. Please <a href="#customer_login" class="hz-notice-link hz-focus-login">log in</a>.', 'handzom-ui-kit');
	}

	/**
	 * Server-side validation during final registration submission:
	 * 1. Checks concurrent double-click / rapid submission lock and absorbs it cleanly.
	 * 2. Checks duplicate email BEFORE account creation (Requirement 4).
	 * 3. Enforces server-side email OTP verification state (Strict Requirement).
	 * 4. Enforces password confirmation and matching.
	 */
	public function validate_registration_submission($errors, $username, $password, $email)
	{
		$email = sanitize_email($email ?: (isset($_POST['email']) ? wp_unslash($_POST['email']) : ''));

		if (empty($email) || !is_email($email)) {
			$errors->add('registration-error-invalid-email', esc_html__('Please enter a valid email address.', 'handzom-ui-kit'));
			return $errors;
		}

		// Prevent duplicate account creation if customer double-clicks Create Account, refreshes, or repeats submission (Requirement 7 & 5)
		$completed_user_id = absint(get_transient('hz_reg_completed_' . md5($email)));
		if ($completed_user_id > 0) {
			$existing_user = get_user_by('id', $completed_user_id);
			if ($existing_user && sanitize_email($existing_user->user_email) === $email) {
				// User was already created by the primary request! Authenticate and redirect directly to homepage.
				wp_set_current_user($completed_user_id);
				wp_set_auth_cookie($completed_user_id, true);
				if (function_exists('wc_set_customer_auth_cookie')) {
					wc_set_customer_auth_cookie($completed_user_id);
				}
				$this->clear_registration_session_state($email);
				if (function_exists('wc_clear_notices')) {
					wc_clear_notices();
				}
				if (function_exists('wc_add_notice')) {
					wc_add_notice(esc_html__('Your Handzom account has been created successfully!', 'handzom-ui-kit'), 'success');
				}
				wp_safe_redirect(home_url('/'));
				exit;
			}
		}

		// Perform duplicate-email validation BEFORE account creation (Requirement 4)
		if (email_exists($email)) {
			$errors->add(
				'registration-error-email-exists',
				__('An account is already registered with this email address. Please <a href="#customer_login" class="hz-notice-link hz-focus-login">log in</a>.', 'handzom-ui-kit')
			);
			return $errors;
		}

		// Enforce server-side OTP verification state (Strict Requirement: Do not bypass OTP verification)
		$submitted_token = isset($_POST['hz_reg_verification_token']) ? sanitize_text_field(wp_unslash($_POST['hz_reg_verification_token'])) : '';
		$verified_data = null;
		if (function_exists('WC') && isset(WC()->session)) {
			$verified_data = WC()->session->get('hz_reg_verified_data');
		}
		if (empty($verified_data) || !is_array($verified_data)) {
			$verified_data = get_transient('hz_reg_verified_' . md5($email));
		}

		$is_otp_valid = false;
		if (is_array($verified_data) && !empty($verified_data['token']) && !empty($verified_data['email'])) {
			if (sanitize_email($verified_data['email']) === $email && !empty($submitted_token) && hash_equals($verified_data['token'], $submitted_token)) {
				if (!empty($verified_data['expires_at']) && time() <= $verified_data['expires_at']) {
					$is_otp_valid = true;
				}
			}
		}

		if (!$is_otp_valid) {
			$errors->add(
				'otp_verification_required',
				esc_html__('Please verify your email address with the verification code before creating an account.', 'handzom-ui-kit')
			);
			return $errors;
		}

		// Validate password confirmation (Requirement 5)
		$pass1 = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';
		$pass2 = isset($_POST['password_confirmation']) ? (string) wp_unslash($_POST['password_confirmation']) : '';

		if (empty($pass1)) {
			$errors->add('password_required', esc_html__('Please enter an account password.', 'handzom-ui-kit'));
		} elseif (empty($pass2)) {
			$errors->add('password_confirmation_required', esc_html__('Please confirm your password.', 'handzom-ui-kit'));
		} elseif ($pass1 !== $pass2) {
			$errors->add('password_mismatch', esc_html__('Passwords do not match. Please confirm your password.', 'handzom-ui-kit'));
		}

		return $errors;
	}

	/**
	 * Handle newly created WooCommerce customer (Requirement 6):
	 * - Authenticate customer securely using WordPress & WooCommerce APIs
	 * - Clear pending OTP and registration state
	 * - Store short-lived idempotency lock to handle duplicate requests
	 */
	public function handle_customer_registration_complete($customer_id, $new_customer_data = [], $password_generated = false)
	{
		if (!$customer_id || is_wp_error($customer_id)) {
			return;
		}

		$email = isset($new_customer_data['user_email']) ? sanitize_email($new_customer_data['user_email']) : '';
		if (empty($email)) {
			$user = get_user_by('id', $customer_id);
			if ($user) {
				$email = sanitize_email($user->user_email);
			}
		}

		// Store completed customer ID for 30s to absorb any concurrent duplicate submissions
		if (!empty($email)) {
			set_transient('hz_reg_completed_' . md5($email), $customer_id, 30);
		}

		// 1. Authenticate the newly created customer securely
		wp_set_current_user($customer_id);
		wp_set_auth_cookie($customer_id, true);
		if (function_exists('wc_set_customer_auth_cookie')) {
			wc_set_customer_auth_cookie($customer_id);
		}

		// 2. Clear pending OTP and registration state
		$this->clear_registration_session_state($email);
	}

	/**
	 * Redirect after registration:
	 * - Clear default WooCommerce notices so nothing shows in header/homepage
	 * - Redirect to homepage
	 */
	public function filter_registration_redirect($redirect, $customer = null)
	{
		if (function_exists('wc_clear_notices')) {
			wc_clear_notices();
		}
		return home_url('/');
	}

	/**
	 * Redirect after login:
	 * - Clear notices
	 * - Redirect to homepage
	 */
	public function filter_login_redirect($redirect, $user = null)
	{
		if (function_exists('wc_clear_notices')) {
			wc_clear_notices();
		}
		return home_url('/');
	}

	/**
	 * Clear all registration session data, transients, and cookies
	 */
	public function clear_registration_session_state($email = '')
	{
		if (function_exists('WC') && isset(WC()->session)) {
			WC()->session->set('hz_registration_otp', null);
			WC()->session->set('hz_reg_verified_data', null);
			WC()->session->set('hz_pending_reg_email', null);
			WC()->session->set('hz_otp_last_sent', null);
			if (method_exists(WC()->session, 'save_data')) {
				WC()->session->save_data();
			}
		}

		if (!empty($email)) {
			$email_hash = md5(strtolower(trim($email)));
			delete_transient('hz_reg_otp_' . $email_hash);
			delete_transient('hz_reg_verified_' . $email_hash);
			delete_transient('hz_otp_resends_' . $email_hash);
			delete_transient('hz_otp_cooldown_' . $email_hash);
			delete_transient('hz_otp_rapid_em_' . $email_hash);
			delete_option('hz_otp_lock_' . $email_hash);
			$ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
			delete_transient('hz_otp_rate_' . md5($ip . '_' . $email));
			delete_transient('hz_otp_rate_em_' . $email_hash);
		}

		$session_id = $this->get_or_create_registration_session_id();
		if (!empty($session_id)) {
			delete_transient('hz_em_changes_' . $session_id);
			delete_transient('hz_em_change_cd_' . $session_id);
			delete_option('hz_lock_change_em_' . $session_id);
		}

		$cookie_path = COOKIEPATH ? COOKIEPATH : '/';
		$cookie_domain = COOKIE_DOMAIN ? COOKIE_DOMAIN : '';
		if (!headers_sent()) {
			setcookie('hz_reg_email', '', time() - 3600, $cookie_path, $cookie_domain, is_ssl(), true);
		}
		unset($_COOKIE['hz_reg_email']);
	}

	/**
	 * Output notices on front page / home page fallback if theme doesn't render them
	 */
	public function output_front_page_notices()
	{
		// Suppressed: errors and success messages are displayed exclusively within the My Account card
		return;
	}

	/**
	 * Render Create an Account notice on checkout for guests
	 */
	public function render_create_account_notice()
	{
		if (is_user_logged_in()) {
			return;
		}

		$myaccount_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : get_permalink(get_option('woocommerce_myaccount_page_id'));
		if ($myaccount_url) {
			$register_url = add_query_arg(['action' => 'register'], $myaccount_url) . '#customer_login';
		} else {
			$register_url = wp_registration_url();
		}

		?>
		<div class="hz-checkout-register-toggle woocommerce-form-register-toggle">
			<div class="woocommerce-info hz-register-notice">
				<?php esc_html_e('New customer?', 'handzom-ui-kit'); ?> <a href="<?php echo esc_url($register_url); ?>" class="showregister hz-showregister-link"><?php esc_html_e('Create an account', 'handzom-ui-kit'); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Clear stale error notices on fresh GET visits to the My Account login/registration page
	 */
	public function clear_stale_account_notices()
	{
		$is_account = false;
		if (function_exists('is_account_page') && is_account_page()) {
			$is_account = true;
		} elseif (function_exists('is_page') && (is_page('my-account') || is_page('account'))) {
			$is_account = true;
		}

		if ($is_account && !is_user_logged_in()) {
			// If it's a fresh GET request (not a form submission) and not an endpoint with specific parameters
			if (isset($_SERVER['REQUEST_METHOD']) && 'GET' === $_SERVER['REQUEST_METHOD']) {
				// Preserve explicit query parameters like reset-link-sent or password-reset
				if (!isset($_GET['reset-link-sent']) && !isset($_GET['password-reset']) && !isset($_GET['action'])) {
					if (function_exists('wc_clear_notices')) {
						wc_clear_notices();
					}
					if (function_exists('WC') && isset(WC()->session) && is_object(WC()->session)) {
						WC()->session->set('wc_notices', null);
						if (method_exists(WC()->session, 'save_data')) {
							WC()->session->save_data();
						}
					}
				}
			}
		}
	}

	/**
	 * AJAX Handler to generate and send Registration OTP email
	 * Strictly enforces:
	 * 1. 5-minute validity period during which new OTP requests are rejected
	 * 2. Maximum 3 successful resends per email address within one verification session (initial send is separate)
	 * 3. 10-minute cooldown once the 3-resend limit is reached
	 * 4. Atomic concurrency locking via WordPress options
	 * 5. Rolling IP-based rate limiting (rapid-fire + hourly cap)
	 * 6. Successful email dispatch confirmation before counting resends
	 */
	public function ajax_send_registration_otp()
	{
		check_ajax_referer('handzom_checkout_nonce', 'nonce');

		$raw_email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
		$email     = strtolower(trim($raw_email));

		if (empty($email) || !is_email($email)) {
			wp_send_json_error([
				'message' => esc_html__('Please enter a valid email address.', 'handzom-ui-kit'),
			]);
		}

		if (email_exists($email)) {
			wp_send_json_error([
				'message' => __('An account is already registered with this email address. Please <a href="#customer_login" class="hz-notice-link hz-focus-login">log in</a>.', 'handzom-ui-kit'),
			]);
		}

		$email_hash = md5($email);
		$ip         = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
		$ip_hash    = md5($ip);

		// Check Session-Level Email Change Cooldown FIRST
		$session_id        = $this->get_or_create_registration_session_id();
		$session_change_cd = (int) get_transient('hz_em_change_cd_' . $session_id);
		if ($session_change_cd <= 0) {
			$session_change_cd = (int) get_transient('hz_em_change_cd_ip_' . $ip_hash);
		}

		if ($session_change_cd > 0) {
			if (time() < $session_change_cd) {
				$remaining_sec  = $session_change_cd - time();
				$mins           = (int) floor($remaining_sec / 60);
				$secs           = $remaining_sec % 60;
				$formatted_time = sprintf('%02d:%02d', $mins, $secs);
				wp_send_json_error([
					'message'                    => sprintf(
						/* translators: %s: MM:SS remaining */
						esc_html__("You've reached the email change limit. Please try again in %s.", 'handzom-ui-kit'),
						$formatted_time
					),
					'email_change_limit_reached' => true,
					'cooldown_until'             => $session_change_cd,
					'cooldown_remaining'         => $remaining_sec,
					'formatted_time'             => $formatted_time,
					'server_time'                => time(),
				]);
			} else {
				delete_transient('hz_em_change_cd_' . $session_id);
				delete_transient('hz_em_change_cd_ip_' . $ip_hash);
				delete_transient('hz_em_changes_' . $session_id);
			}
		} elseif ((int) get_transient('hz_em_changes_' . $session_id) >= 3) {
			delete_transient('hz_em_changes_' . $session_id);
		}

		// 1. Atomic Concurrency Lock to prevent simultaneous double-click / race-condition bypass
		$lock_key      = 'hz_otp_lock_' . $email_hash;
		$lock_acquired = add_option($lock_key, time(), '', 'no');
		if (!$lock_acquired) {
			$existing_lock = (int) get_option($lock_key);
			if ($existing_lock && (time() - $existing_lock) > 10) {
				update_option($lock_key, time());
			} else {
				wp_send_json_error([
					'message' => esc_html__('An OTP request is currently being processed. Please wait a moment.', 'handzom-ui-kit'),
				]);
			}
		}

		// Helper to safely exit releasing the lock
		$release_lock_and_error = function ($data) use ($lock_key) {
			delete_option($lock_key);
			wp_send_json_error($data);
		};

		// 2. Check Server-Side 10-Minute Cooldown FIRST before rapid-fire or attempt limits
		$resend_data        = get_transient('hz_otp_resends_' . $email_hash);
		$cooldown_timestamp = (int) ($resend_data['cooldown_until'] ?? get_transient('hz_otp_cooldown_' . $email_hash));
		if ($cooldown_timestamp > 0) {
			if (time() < $cooldown_timestamp) {
				$wait_seconds   = $cooldown_timestamp - time();
				$mins           = (int) floor($wait_seconds / 60);
				$secs           = $wait_seconds % 60;
				$formatted_time = sprintf('%02d:%02d', $mins, $secs);
				$release_lock_and_error([
					'message'              => sprintf(
						/* translators: %s: MM:SS remaining */
						esc_html__("You've reached the OTP resend limit. Please try again in %s.", 'handzom-ui-kit'),
						$formatted_time
					),
					'resend_limit_reached' => true,
					'cooldown_until'       => $cooldown_timestamp,
					'cooldown_remaining'   => $wait_seconds,
					'formatted_time'       => $formatted_time,
					'email'                => $email,
				]);
			} else {
				// Cooldown period has finished! Clear cooldown and reset resend session counter
				delete_transient('hz_otp_cooldown_' . $email_hash);
				delete_transient('hz_otp_resends_' . $email_hash);
				$resend_data        = null;
				$cooldown_timestamp = 0;
			}
		}

		// 3. Rolling IP Limits (Max 20 requests per hour from a single IP to protect shared networks without blocking legitimate users)
		$ip_cnt = (int) get_transient('hz_otp_ip_cnt_' . $ip_hash);
		if ($ip_cnt >= 20) {
			$release_lock_and_error([
				'message' => esc_html__('Too many verification requests from your network. Please try again later.', 'handzom-ui-kit'),
			]);
		}

		// 4. Rapid-fire rate limiting (Minimum 5s between requests per email, 3s per IP)
		if (get_transient('hz_otp_rapid_em_' . $email_hash)) {
			$release_lock_and_error([
				'message'     => esc_html__('Please wait a few seconds before requesting another code.', 'handzom-ui-kit'),
				'retry_after' => 5,
			]);
		}
		if (get_transient('hz_otp_rapid_ip_' . $ip_hash)) {
			$release_lock_and_error([
				'message'     => esc_html__('Too many requests. Please wait a few seconds.', 'handzom-ui-kit'),
				'retry_after' => 3,
			]);
		}

		// 5. Server-Enforced 1-Minute Resend Interval:
		// User can request a new OTP after 1 minute (60s) has elapsed since the last successfully issued OTP.
		// JavaScript must only display countdown; server strictly authorizes the resend.
		$existing_otp = null;
		if (function_exists('WC') && isset(WC()->session)) {
			$existing_otp = WC()->session->get('hz_registration_otp');
		}
		if (empty($existing_otp) || !is_array($existing_otp)) {
			$existing_otp = get_transient('hz_reg_otp_' . $email_hash);
		}

		if (is_array($existing_otp) && !empty($existing_otp['created_at'])) {
			$last_issued_at      = absint($existing_otp['created_at']);
			$resend_available_at = !empty($existing_otp['resend_available_at'])
				? absint($existing_otp['resend_available_at'])
				: ($last_issued_at + 60);

			if (time() < $resend_available_at) {
				$remaining_resend_sec = $resend_available_at - time();
				$formatted_wait       = sprintf('%02d:%02d', 0, $remaining_resend_sec);
				$release_lock_and_error([
					'message'             => sprintf(
						/* translators: %s: MM:SS remaining */
						esc_html__('Resend available in %s.', 'handzom-ui-kit'),
						$formatted_wait
					),
					'resend_available_at' => $resend_available_at,
					'resend_remaining'    => $remaining_resend_sec,
					'expires_at'          => absint($existing_otp['expires_at'] ?? (time() + 300)),
					'server_time'         => time(),
				]);
			}
		}

		// 6. Check Resend Limit (Max 3 successful resends per verification session, initial send is count 0)
		$is_initial_send = empty($resend_data) || !is_array($resend_data);
		$current_resends = $is_initial_send ? 0 : (int) ($resend_data['count'] ?? 0);

		if (!$is_initial_send && $current_resends >= 3) {
			// 3 successful resends already used! Enforce 10-minute cooldown
			$cooldown_timestamp = (int) get_transient('hz_otp_cooldown_' . $email_hash);
			if ($cooldown_timestamp <= time()) {
				$cooldown_timestamp = time() + 600;
				set_transient('hz_otp_cooldown_' . $email_hash, $cooldown_timestamp, 600);
			}
			if (is_array($resend_data)) {
				$resend_data['cooldown_until'] = $cooldown_timestamp;
				set_transient('hz_otp_resends_' . $email_hash, $resend_data, 1800);
			}
			$remaining_sec  = max(0, $cooldown_timestamp - time());
			$mins           = (int) floor($remaining_sec / 60);
			$secs           = $remaining_sec % 60;
			$formatted_time = sprintf('%02d:%02d', $mins, $secs);
			$release_lock_and_error([
				'message'              => sprintf(
					/* translators: %s: MM:SS remaining */
					esc_html__("You've reached the OTP resend limit. Please try again in %s.", 'handzom-ui-kit'),
					$formatted_time
				),
				'resend_limit_reached' => true,
				'cooldown_until'       => $cooldown_timestamp,
				'cooldown_remaining'   => $remaining_sec,
				'formatted_time'       => $formatted_time,
				'email'                => $email,
			]);
		}

		// 7. Generate cryptographically secure 6-digit OTP
		$otp        = sprintf('%06d', wp_rand(100000, 999999));
		$now        = time();
		$expires_at = $now + 300; // 5 minutes validity
		$resend_available_at = $now + 60; // 1 minute resend cooldown
		$otp_hash   = wp_hash_password($otp);

		// 8. Deliver OTP email to recipient
		// Resend count is incremented ONLY if email delivery succeeds
		$sent = $this->send_otp_email($email, $otp);
		if (!$sent) {
			$release_lock_and_error([
				'message' => esc_html__('Unable to send verification code. Please check your email address or try again later.', 'handzom-ui-kit'),
			]);
		}

		// 9. Successfully sent! Store new OTP (immediately invalidating any previous OTP)
		$otp_data = [
			'email'               => $email,
			'otp_hash'            => $otp_hash,
			'created_at'          => $now,
			'expires_at'          => $expires_at,
			'resend_available_at' => $resend_available_at,
			'attempts'            => 0,
		];

		if (function_exists('WC') && isset(WC()->session)) {
			if (!WC()->session->has_session()) {
				WC()->session->set_customer_session_cookie(true);
			}
			WC()->session->set('hz_registration_otp', $otp_data);
			WC()->session->set('hz_otp_last_sent', $now);
			WC()->session->set('hz_pending_reg_email', $email);
			if (method_exists(WC()->session, 'save_data')) {
				WC()->session->save_data();
			}
		}

		$cookie_path   = COOKIEPATH ? COOKIEPATH : '/';
		$cookie_domain = COOKIE_DOMAIN ? COOKIE_DOMAIN : '';
		if (!headers_sent()) {
			setcookie('hz_reg_email', $email, $now + 900, $cookie_path, $cookie_domain, is_ssl(), true);
		}
		$_COOKIE['hz_reg_email'] = $email;

		set_transient('hz_reg_otp_' . $email_hash, $otp_data, 900);

		// 10. Update resend session counters
		if ($is_initial_send) {
			$new_resend_count = 0;
			$resend_record = [
				'count'      => 0,
				'first_sent' => $now,
				'last_sent'  => $now,
			];
			set_transient('hz_otp_resends_' . $email_hash, $resend_record, 1800);
		} else {
			$new_resend_count = $current_resends + 1;
			$resend_record = [
				'count'      => $new_resend_count,
				'first_sent' => $resend_data['first_sent'] ?? $now,
				'last_sent'  => $now,
			];
			set_transient('hz_otp_resends_' . $email_hash, $resend_record, 1800);
		}

		// 11. Check if the 3-resend limit has now been reached on this issue
		$resend_limit_reached = ($new_resend_count >= 3);
		$cooldown_timestamp   = 0;
		if ($resend_limit_reached) {
			// Trigger 10-minute cooldown for this email
			$cooldown_timestamp = $now + 600;
			set_transient('hz_otp_cooldown_' . $email_hash, $cooldown_timestamp, 600);
			$resend_record['cooldown_until'] = $cooldown_timestamp;
			set_transient('hz_otp_resends_' . $email_hash, $resend_record, 1800);
		}

		// 12. Set rapid-fire and IP rolling limits
		set_transient('hz_otp_rapid_em_' . $email_hash, $now, 5);
		set_transient('hz_otp_rapid_ip_' . $ip_hash, $now, 3);
		set_transient('hz_otp_ip_cnt_' . $ip_hash, $ip_cnt + 1, 3600);

		// 13. Release concurrency lock
		delete_option($lock_key);

		$cooldown_until_ts = $resend_limit_reached ? $cooldown_timestamp : 0;
		$cooldown_rem_sec  = $resend_limit_reached ? 600 : 0;
		$mins              = (int) floor($cooldown_rem_sec / 60);
		$secs              = $cooldown_rem_sec % 60;

		wp_send_json_success([
			'message'              => ($new_resend_count > 0)
				? esc_html__('A new verification code has been sent to your email.', 'handzom-ui-kit')
				: esc_html__('Verification code sent to your email.', 'handzom-ui-kit'),
			'email'                => $email,
			'expires_at'           => $expires_at,
			'expires_in'           => 300,
			'resend_available_at'  => $resend_available_at,
			'resend_wait_seconds'  => 60,
			'server_time'          => $now,
			'resend_count'         => $new_resend_count,
			'resends_remaining'    => max(0, 3 - $new_resend_count),
			'resend_limit_reached' => $resend_limit_reached,
			'cooldown_until'       => $cooldown_until_ts,
			'cooldown_remaining'   => $cooldown_rem_sec,
			'formatted_time'       => sprintf('%02d:%02d', $mins, $secs),
		]);
	}

	/**
	 * AJAX Handler to verify Registration OTP (Stage 2)
	 */
	public function ajax_verify_registration_otp()
	{
		check_ajax_referer('handzom_checkout_nonce', 'nonce');

		$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
		$otp   = isset($_POST['otp']) ? preg_replace('/[^0-9]/', '', (string) wp_unslash($_POST['otp'])) : '';

		if (empty($email) || !is_email($email)) {
			wp_send_json_error([
				'message' => esc_html__('Please enter a valid email address.', 'handzom-ui-kit'),
			]);
		}

		if (strlen($otp) !== 6) {
			wp_send_json_error([
				'message' => esc_html__('Please enter the full 6-digit verification code.', 'handzom-ui-kit'),
			]);
		}

		// Retrieve stored OTP data from session or transient
		$otp_data = null;
		$email_hash = md5(strtolower(trim($email)));
		if (function_exists('WC') && isset(WC()->session)) {
			$otp_data = WC()->session->get('hz_registration_otp');
		}
		if (empty($otp_data) || !is_array($otp_data)) {
			$otp_data = get_transient('hz_reg_otp_' . $email_hash);
		}

		if (empty($otp_data) || !is_array($otp_data)) {
			wp_send_json_error([
				'message' => esc_html__('No pending verification found. Please request a new verification code.', 'handzom-ui-kit'),
				'expired' => true,
			]);
		}

		// Check if email matches
		if (strtolower(trim($otp_data['email'] ?? '')) !== strtolower(trim($email))) {
			wp_send_json_error([
				'message' => esc_html__('Email mismatch. Please request a new verification code for this email.', 'handzom-ui-kit'),
				'expired' => true,
			]);
		}

		// Check expiry
		$expires_at = absint($otp_data['expires_at'] ?? 0);
		if (time() > $expires_at) {
			// Clear expired OTP
			if (function_exists('WC') && isset(WC()->session)) {
				WC()->session->set('hz_registration_otp', null);
			}
			delete_transient('hz_reg_otp_' . $email_hash);

			$resend_data        = get_transient('hz_otp_resends_' . $email_hash);
			$resend_count       = is_array($resend_data) ? (int) ($resend_data['count'] ?? 0) : 0;
			$cooldown_timestamp = (int) ($resend_data['cooldown_until'] ?? get_transient('hz_otp_cooldown_' . $email_hash));
			if ($cooldown_timestamp > 0 && time() >= $cooldown_timestamp) {
				delete_transient('hz_otp_cooldown_' . $email_hash);
				delete_transient('hz_otp_resends_' . $email_hash);
				$cooldown_timestamp = 0;
			}
			$cooldown_remaining = max(0, $cooldown_timestamp - time());
			$resend_limit_hit   = ($resend_count >= 3 || $cooldown_remaining > 0);

			$mins           = (int) floor($cooldown_remaining / 60);
			$secs           = $cooldown_remaining % 60;
			$formatted_time = sprintf('%02d:%02d', $mins, $secs);

			wp_send_json_error([
				'message'              => esc_html__('Your verification code has expired. Please request a new OTP.', 'handzom-ui-kit'),
				'expired'              => true,
				'resend_limit_reached' => $resend_limit_hit,
				'cooldown_until'       => $cooldown_timestamp,
				'cooldown_remaining'   => $cooldown_remaining,
				'formatted_time'       => $formatted_time,
			]);
		}

		// Check attempt limits (max 5 attempts)
		$attempts = absint($otp_data['attempts'] ?? 0) + 1;
		$otp_data['attempts'] = $attempts;

		if ($attempts > 5) {
			// Too many failed attempts -> invalidate OTP
			if (function_exists('WC') && isset(WC()->session)) {
				WC()->session->set('hz_registration_otp', null);
			}
			delete_transient('hz_reg_otp_' . $email_hash);

			wp_send_json_error([
				'message' => esc_html__('Too many failed attempts. Please request a new verification code.', 'handzom-ui-kit'),
				'expired' => true,
			]);
		}

		// Verify OTP hash
		$otp_hash = (string) ($otp_data['otp_hash'] ?? '');
		if (empty($otp_hash) || !wp_check_password($otp, $otp_hash)) {
			// Save incremented attempts
			if (function_exists('WC') && isset(WC()->session)) {
				WC()->session->set('hz_registration_otp', $otp_data);
				if (method_exists(WC()->session, 'save_data')) {
					WC()->session->save_data();
				}
			}
			set_transient('hz_reg_otp_' . $email_hash, $otp_data, 900);

			$remaining_attempts = 5 - $attempts;
			wp_send_json_error([
				'message'            => sprintf(esc_html__('Invalid verification code. Please check and try again (%d attempts remaining).', 'handzom-ui-kit'), $remaining_attempts),
				'remaining_attempts' => $remaining_attempts,
			]);
		}

		// Success! Mark email as verified on server
		$token = wp_generate_password(32, false);
		$verified_data = [
			'email'       => $email,
			'verified_at' => time(),
			'token'       => $token,
			'expires_at'  => time() + 1800, // Valid for 30 minutes to complete registration
		];

		if (function_exists('WC') && isset(WC()->session)) {
			WC()->session->set('hz_reg_verified_data', $verified_data);
			WC()->session->set('hz_registration_otp', null);
			WC()->session->set('hz_pending_reg_email', null);
			if (method_exists(WC()->session, 'save_data')) {
				WC()->session->save_data();
			}
		}
		set_transient('hz_reg_verified_' . $email_hash, $verified_data, 1800);
		delete_transient('hz_reg_otp_' . $email_hash);

		$cookie_path = COOKIEPATH ? COOKIEPATH : '/';
		$cookie_domain = COOKIE_DOMAIN ? COOKIE_DOMAIN : '';
		if (!headers_sent()) {
			setcookie('hz_reg_email', '', time() - 3600, $cookie_path, $cookie_domain, is_ssl(), true);
		}

		wp_send_json_success([
			'message' => esc_html__('Email verified successfully!', 'handzom-ui-kit'),
			'email'   => $email,
			'token'   => $token,
		]);
	}

	/**
	 * AJAX Handler for customer registration completion (Idempotent & Secure)
	 */
	public function ajax_register_account()
	{
		// 1. Nonce validation
		$nonce_valid = false;
		if (check_ajax_referer('handzom_checkout_nonce', 'nonce', false)) {
			$nonce_valid = true;
		} elseif (!empty($_POST['woocommerce-register-nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce-register-nonce'])), 'woocommerce-register')) {
			$nonce_valid = true;
		}

		if (!$nonce_valid) {
			wp_send_json_error([
				'message' => esc_html__('Security verification failed. Please refresh the page and try again.', 'handzom-ui-kit'),
			]);
		}

		$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';

		if (empty($email) || !is_email($email)) {
			wp_send_json_error([
				'message' => esc_html__('Please enter a valid email address.', 'handzom-ui-kit'),
			]);
		}

		// 2. Idempotency Check: if account creation already completed recently for this email, absorb retries cleanly
		$completed_user_id = absint(get_transient('hz_reg_completed_' . md5($email)));
		if ($completed_user_id > 0) {
			$existing_user = get_user_by('id', $completed_user_id);
			if ($existing_user && sanitize_email($existing_user->user_email) === $email) {
				wp_set_current_user($completed_user_id);
				wp_set_auth_cookie($completed_user_id, true);
				if (function_exists('wc_set_customer_auth_cookie')) {
					wc_set_customer_auth_cookie($completed_user_id);
				}
				$this->clear_registration_session_state($email);
				if (function_exists('wc_clear_notices')) {
					wc_clear_notices();
				}
				wp_send_json_success([
					'message'      => esc_html__('Your Handzom account has been created successfully!', 'handzom-ui-kit'),
					'redirect_url' => home_url('/'),
				]);
			}
		}

		// 3. Concurrency Lock: Prevent simultaneous double-click submissions
		$lock_key = 'hz_reg_lock_' . md5($email);
		if (get_transient($lock_key)) {
			wp_send_json_error([
				'message' => esc_html__('Account registration is currently being processed. Please wait a moment.', 'handzom-ui-kit'),
			]);
		}
		set_transient($lock_key, 1, 15);

		// 4. Validate email availability BEFORE customer creation
		if (email_exists($email)) {
			delete_transient($lock_key);
			wp_send_json_error([
				'message' => __('An account is already registered with this email address. Please <a href="#customer_login" class="hz-notice-link hz-focus-login">log in</a>.', 'handzom-ui-kit'),
			]);
		}

		// 5. Enforce server-side email OTP verification state (Strict security)
		$submitted_token = isset($_POST['hz_reg_verification_token']) ? sanitize_text_field(wp_unslash($_POST['hz_reg_verification_token'])) : '';
		$verified_data = null;
		if (function_exists('WC') && isset(WC()->session)) {
			$verified_data = WC()->session->get('hz_reg_verified_data');
		}
		if (empty($verified_data) || !is_array($verified_data)) {
			$verified_data = get_transient('hz_reg_verified_' . md5($email));
		}

		$is_otp_valid = false;
		if (is_array($verified_data) && !empty($verified_data['token']) && !empty($verified_data['email'])) {
			if (sanitize_email($verified_data['email']) === $email && !empty($submitted_token) && hash_equals($verified_data['token'], $submitted_token)) {
				if (!empty($verified_data['expires_at']) && time() <= $verified_data['expires_at']) {
					$is_otp_valid = true;
				}
			}
		}

		if (!$is_otp_valid) {
			delete_transient($lock_key);
			wp_send_json_error([
				'message' => esc_html__('Please verify your email address with the verification code before creating an account.', 'handzom-ui-kit'),
			]);
		}

		// 6. Validate password and password confirmation
		$pass1 = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';
		$pass2 = isset($_POST['password_confirmation']) ? (string) wp_unslash($_POST['password_confirmation']) : '';

		if (empty($pass1)) {
			delete_transient($lock_key);
			wp_send_json_error([
				'message' => esc_html__('Please enter an account password.', 'handzom-ui-kit'),
			]);
		}

		if (empty($pass2)) {
			delete_transient($lock_key);
			wp_send_json_error([
				'message' => esc_html__('Please confirm your password.', 'handzom-ui-kit'),
			]);
		}

		if ($pass1 !== $pass2) {
			delete_transient($lock_key);
			wp_send_json_error([
				'message' => esc_html__('Passwords do not match. Please confirm your password.', 'handzom-ui-kit'),
			]);
		}

		// 7. Resolve username
		$username = '';
		if ('no' === get_option('woocommerce_registration_generate_username') && !empty($_POST['username'])) {
			$username = sanitize_user(wp_unslash($_POST['username']));
		}

		// 8. Create WooCommerce customer exactly once
		$customer_id = wc_create_new_customer($email, $username, $pass1);

		if (is_wp_error($customer_id)) {
			delete_transient($lock_key);
			wp_send_json_error([
				'message' => wp_strip_all_tags($customer_id->get_error_message()),
			]);
		}

		// Security: Enforce customer role (never administrator)
		$user = get_user_by('id', $customer_id);
		if ($user && in_array('administrator', (array) $user->roles, true)) {
			delete_transient($lock_key);
			wp_send_json_error([
				'message' => esc_html__('Registration error: Invalid account role.', 'handzom-ui-kit'),
			]);
		}

		// 9. Store completed customer ID for 60s for idempotency
		set_transient('hz_reg_completed_' . md5($email), $customer_id, 60);
		delete_transient($lock_key);

		// 10. Authenticate newly created customer securely
		wp_set_current_user($customer_id);
		wp_set_auth_cookie($customer_id, true);
		if (function_exists('wc_set_customer_auth_cookie')) {
			wc_set_customer_auth_cookie($customer_id);
		}

		// 11. Clear registration session state & notices
		$this->clear_registration_session_state($email);
		if (function_exists('wc_clear_notices')) {
			wc_clear_notices();
		}

		// 12. Send JSON success response
		wp_send_json_success([
			'message'      => esc_html__('Your Handzom account has been created successfully!', 'handzom-ui-kit'),
			'redirect_url' => home_url('/'),
		]);
	}

	/**
	 * AJAX Handler for customer login
	 */
	public function ajax_login_account()
	{
		// Nonce check
		$nonce_valid = false;
		if (check_ajax_referer('handzom_checkout_nonce', 'nonce', false)) {
			$nonce_valid = true;
		} elseif (!empty($_POST['woocommerce-login-nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce-login-nonce'])), 'woocommerce-login')) {
			$nonce_valid = true;
		}

		if (!$nonce_valid) {
			wp_send_json_error([
				'message' => esc_html__('Security verification failed. Please refresh the page and try again.', 'handzom-ui-kit'),
			]);
		}

		$username = isset($_POST['username']) ? sanitize_text_field(wp_unslash($_POST['username'])) : '';
		$password = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';
		$remember = !empty($_POST['rememberme']);

		if (empty($username)) {
			wp_send_json_error([
				'message' => esc_html__('Please enter your username or email address.', 'handzom-ui-kit'),
			]);
		}

		if (empty($password)) {
			wp_send_json_error([
				'message' => esc_html__('Please enter your password.', 'handzom-ui-kit'),
			]);
		}

		$creds = [
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		];

		$user = wp_signon($creds, is_ssl());

		if (is_wp_error($user)) {
			$error_msg = $user->get_error_message();
			if (strpos($error_msg, 'Lost your password') !== false || strpos($error_msg, 'incorrect') !== false || strpos($error_msg, 'Unknown') !== false) {
				$error_msg = wp_strip_all_tags($error_msg);
			}
			wp_send_json_error([
				'message' => $error_msg,
			]);
		}

		// Authenticate and set WooCommerce cookies
		wp_set_current_user($user->ID);
		if (function_exists('wc_set_customer_auth_cookie')) {
			wc_set_customer_auth_cookie($user->ID);
		}

		if (function_exists('wc_clear_notices')) {
			wc_clear_notices();
		}

		wp_send_json_success([
			'message'      => esc_html__('Login successful! Redirecting...', 'handzom-ui-kit'),
			'redirect_url' => home_url('/'),
		]);
	}

	/**
	 * AJAX Handler to query server-side rate limit & cooldown state for an email
	 */
	public function ajax_check_otp_cooldown()
	{
		check_ajax_referer('handzom_checkout_nonce', 'nonce');

		$raw_email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
		$email     = strtolower(trim($raw_email));

		if (empty($email) || !is_email($email)) {
			wp_send_json_error([
				'message' => esc_html__('Please enter a valid email address.', 'handzom-ui-kit'),
			]);
		}

		$email_hash         = md5($email);
		$resend_data        = get_transient('hz_otp_resends_' . $email_hash);
		$cooldown_timestamp = (int) ($resend_data['cooldown_until'] ?? get_transient('hz_otp_cooldown_' . $email_hash));

		if ($cooldown_timestamp > 0 && time() >= $cooldown_timestamp) {
			delete_transient('hz_otp_cooldown_' . $email_hash);
			delete_transient('hz_otp_resends_' . $email_hash);
			$cooldown_timestamp = 0;
		}

		$cooldown_remaining = max(0, $cooldown_timestamp - time());
		$is_cooldown        = ($cooldown_remaining > 0);

		$mins           = (int) floor($cooldown_remaining / 60);
		$secs           = $cooldown_remaining % 60;
		$formatted_time = sprintf('%02d:%02d', $mins, $secs);

		$resend_count = is_array($resend_data) ? (int) ($resend_data['count'] ?? 0) : 0;

		$existing_otp = get_transient('hz_reg_otp_' . $email_hash);
		$resend_available_at = 0;
		$resend_remaining = 0;
		if (is_array($existing_otp) && !empty($existing_otp['created_at'])) {
			$resend_available_at = !empty($existing_otp['resend_available_at'])
				? absint($existing_otp['resend_available_at'])
				: (absint($existing_otp['created_at']) + 60);
			$resend_remaining = max(0, $resend_available_at - time());
		}

		$session_id        = $this->get_or_create_registration_session_id();
		$ip                = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
		$session_change_cd = (int) get_transient('hz_em_change_cd_' . $session_id);
		if ($session_change_cd <= 0) {
			$session_change_cd = (int) get_transient('hz_em_change_cd_ip_' . md5($ip));
		}
		if ($session_change_cd > 0 && time() >= $session_change_cd) {
			delete_transient('hz_em_change_cd_' . $session_id);
			delete_transient('hz_em_change_cd_ip_' . md5($ip));
			delete_transient('hz_em_changes_' . $session_id);
			$session_change_cd = 0;
		}
		$em_change_cd_rem = max(0, $session_change_cd - time());

		wp_send_json_success([
			'email'                 => $email,
			'is_cooldown'           => $is_cooldown,
			'cooldown_until'        => $cooldown_timestamp,
			'cooldown_remaining'    => $cooldown_remaining,
			'formatted_time'        => $formatted_time,
			'resend_available_at'   => $resend_available_at,
			'resend_remaining'      => $resend_remaining,
			'resend_count'          => $resend_count,
			'resends_remaining'     => max(0, 3 - $resend_count),
			'server_time'           => time(),
			'email_change_cooldown' => [
				'is_active'          => ($em_change_cd_rem > 0),
				'cooldown_until'     => $session_change_cd,
				'cooldown_remaining' => $em_change_cd_rem,
				'formatted_time'     => sprintf('%02d:%02d', floor($em_change_cd_rem / 60), $em_change_cd_rem % 60),
			],
		]);
	}

	/**
	 * AJAX Handler to handle Change Email action with secure session-based rate limiting
	 * Strictly enforces:
	 * 1. Maximum 3 email changes per OTP registration session
	 * 2. 4th change attempt is blocked and triggers a 5-minute cooldown (300 seconds)
	 * 3. Atomic concurrency locking via WordPress options to prevent race conditions
	 * 4. Bound to secure registration session and IP fallback to prevent bypass
	 * 5. Does NOT reset or alter per-email OTP rate limits or resend counters!
	 */
	public function ajax_clear_registration_session()
	{
		check_ajax_referer('handzom_checkout_nonce', 'nonce');

		$session_id = $this->get_or_create_registration_session_id();
		$ip         = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
		$ip_hash    = md5($ip);

		// 1. Atomic Concurrency Lock
		$lock_key      = 'hz_lock_change_em_' . $session_id;
		$lock_acquired = add_option($lock_key, time(), '', 'no');
		if (!$lock_acquired) {
			$existing_lock = (int) get_option($lock_key);
			if ($existing_lock && (time() - $existing_lock) > 5) {
				update_option($lock_key, time());
			} else {
				wp_send_json_error([
					'message' => esc_html__('A change request is currently being processed. Please wait a moment.', 'handzom-ui-kit'),
				]);
			}
		}

		$release_lock = function () use ($lock_key) {
			delete_option($lock_key);
		};

		// 2. Check if Session is already under an active 5-Minute Cooldown
		$cooldown_timestamp = (int) get_transient('hz_em_change_cd_' . $session_id);
		if ($cooldown_timestamp <= 0) {
			$cooldown_timestamp = (int) get_transient('hz_em_change_cd_ip_' . $ip_hash);
		}

		if ($cooldown_timestamp > 0) {
			if (time() < $cooldown_timestamp) {
				$remaining_sec  = $cooldown_timestamp - time();
				$mins           = (int) floor($remaining_sec / 60);
				$secs           = $remaining_sec % 60;
				$formatted_time = sprintf('%02d:%02d', $mins, $secs);

				$this->clear_pending_otp_state();
				$release_lock();

				wp_send_json_error([
					'message'                    => sprintf(
						/* translators: %s: MM:SS remaining */
						esc_html__("You've reached the email change limit. Please try again in %s.", 'handzom-ui-kit'),
						$formatted_time
					),
					'email_change_limit_reached' => true,
					'cooldown_until'             => $cooldown_timestamp,
					'cooldown_remaining'         => $remaining_sec,
					'formatted_time'             => $formatted_time,
					'server_time'                => time(),
					'change_count'               => 3,
				]);
			} else {
				delete_transient('hz_em_change_cd_' . $session_id);
				delete_transient('hz_em_change_cd_ip_' . $ip_hash);
				delete_transient('hz_em_changes_' . $session_id);
				$cooldown_timestamp = 0;
			}
		}

		// 3. Check Change Counter (Max 3 allowed per registration session)
		$change_count = (int) get_transient('hz_em_changes_' . $session_id);

		if ($change_count >= 3) {
			// Fourth change attempt -> block and apply 5-minute cooldown (300 seconds)
			$cooldown_timestamp = time() + 300;
			set_transient('hz_em_change_cd_' . $session_id, $cooldown_timestamp, 3600);
			set_transient('hz_em_change_cd_ip_' . $ip_hash, $cooldown_timestamp, 3600);
			set_transient('hz_em_changes_' . $session_id, 4, 3600);

			$this->clear_pending_otp_state();
			$release_lock();

			$mins           = (int) floor(300 / 60);
			$secs           = 300 % 60;
			$formatted_time = sprintf('%02d:%02d', $mins, $secs);

			wp_send_json_error([
				'message'                    => sprintf(
					/* translators: %s: MM:SS remaining */
					esc_html__("You've reached the email change limit. Please try again in %s.", 'handzom-ui-kit'),
					$formatted_time
				),
				'email_change_limit_reached' => true,
				'cooldown_until'             => $cooldown_timestamp,
				'cooldown_remaining'         => 300,
				'formatted_time'             => $formatted_time,
				'server_time'                => time(),
				'change_count'               => 3,
			]);
		}

		// 4. Change allowed: increment counter and clear pending OTP state
		$new_count = $change_count + 1;
		set_transient('hz_em_changes_' . $session_id, $new_count, 1800);

		$this->clear_pending_otp_state();
		$release_lock();

		wp_send_json_success([
			'cleared'           => true,
			'allowed'           => true,
			'change_count'      => $new_count,
			'changes_remaining' => max(0, 3 - $new_count),
			'server_time'       => time(),
		]);
	}

	/**
	 * Clear pending OTP session data and cookie without resetting rate limits
	 */
	public function clear_pending_otp_state()
	{
		if (function_exists('WC') && isset(WC()->session)) {
			WC()->session->set('hz_registration_otp', null);
			WC()->session->set('hz_pending_reg_email', null);
			if (method_exists(WC()->session, 'save_data')) {
				WC()->session->save_data();
			}
		}

		$cookie_path   = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
		$cookie_domain = defined('COOKIE_DOMAIN') && COOKIE_DOMAIN ? COOKIE_DOMAIN : '';
		if (!headers_sent()) {
			setcookie('hz_reg_email', '', time() - 3600, $cookie_path, $cookie_domain, is_ssl(), true);
		}
		unset($_COOKIE['hz_reg_email']);
	}

	/**
	 * Retrieve or initialize secure registration session ID
	 * Bound to customer's WC session and persistent HTTP-only cookie
	 */
	public function get_or_create_registration_session_id()
	{
		$session_id = '';

		// 1. Check WC session
		if (function_exists('WC') && isset(WC()->session)) {
			if (!WC()->session->has_session()) {
				WC()->session->set_customer_session_cookie(true);
			}
			$stored = WC()->session->get('hz_reg_session_id');
			if (!empty($stored) && is_string($stored) && preg_match('/^[a-zA-Z0-9_-]{20,64}$/', $stored)) {
				$session_id = $stored;
			}
		}

		// 2. Check HTTP-only cookie if session ID not found in WC session
		if (empty($session_id) && !empty($_COOKIE['hz_reg_session_id'])) {
			$cookie_val = sanitize_text_field(wp_unslash($_COOKIE['hz_reg_session_id']));
			if (preg_match('/^[a-zA-Z0-9_-]{20,64}$/', $cookie_val)) {
				$session_id = $cookie_val;
			}
		}

		// 3. If still empty, generate a new secure 32-character random token
		if (empty($session_id)) {
			$session_id = function_exists('wp_generate_password') ? wp_generate_password(32, false) : bin2hex(random_bytes(16));
		}

		// Sync to WC session
		if (function_exists('WC') && isset(WC()->session)) {
			if (WC()->session->get('hz_reg_session_id') !== $session_id) {
				WC()->session->set('hz_reg_session_id', $session_id);
				if (method_exists(WC()->session, 'save_data')) {
					WC()->session->save_data();
				}
			}
		}

		// Sync to cookie
		if (empty($_COOKIE['hz_reg_session_id']) || $_COOKIE['hz_reg_session_id'] !== $session_id) {
			$_COOKIE['hz_reg_session_id'] = $session_id;
			if (!headers_sent()) {
				$cookie_path   = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
				$cookie_domain = defined('COOKIE_DOMAIN') && COOKIE_DOMAIN ? COOKIE_DOMAIN : '';
				setcookie('hz_reg_session_id', $session_id, time() + 7200, $cookie_path, $cookie_domain, is_ssl(), true);
			}
		}

		return $session_id;
	}

	/**
	 * Send clean HTML Verification Code Email to customer
	 */
	private function send_otp_email($recipient, $otp)
	{
		$site_name = get_bloginfo('name') ?: 'Handzom';
		$subject = sprintf(
			/* translators: %s: OTP Code */
			esc_html__('%s is your Handzom verification code', 'handzom-ui-kit'),
			$otp
		);

		$from_name = get_option('woocommerce_email_from_name') ?: $site_name;
		$from_email = get_option('woocommerce_email_from_address') ?: get_option('admin_email');
		if (empty($from_email) || !is_email($from_email)) {
			$domain = sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'] ?? 'handzom.com'));
			$domain = preg_replace('/^www\./i', '', $domain);
			$from_email = 'no-reply@' . $domain;
		}

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_attr($from_name) . ' <' . sanitize_email($from_email) . '>',
			'Reply-To: ' . esc_attr($from_name) . ' <' . sanitize_email($from_email) . '>',
		];

		$logo_url = 'https://www.worthbuy.in/demo/handzom/wp-content/uploads/2026/10/logo.png';

		ob_start();
		?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo esc_html($subject); ?></title>
</head>
<body style="margin: 0; padding: 30px 10px; background-color: #f7f7f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #111111;">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e8e8e8; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.03);">
		<tr>
			<td style="padding: 36px 30px 24px 30px; text-align: center; border-bottom: 1px solid #f2f2f2;">
				<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" width="160" style="display: block; margin: 0 auto; width: 160px; max-width: 160px; height: auto; border: 0;" />
			</td>
		</tr>
		<tr>
			<td style="padding: 36px 32px 28px 32px; text-align: center;">
				<h1 style="font-size: 17px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #111111; margin: 0 0 12px 0;">VERIFY YOUR EMAIL</h1>
				<p style="font-size: 13.5px; line-height: 1.6; color: #555555; margin: 0 0 24px 0;">
					<?php esc_html_e('Use the verification code below to continue creating your Handzom account. This code is valid for 5 minutes.', 'handzom-ui-kit'); ?>
				</p>
				<div style="display: inline-block; background-color: #fafafa; border: 1px solid #111111; border-radius: 3px; padding: 14px 28px; margin: 0 auto 24px auto;">
					<span style="font-family: 'Courier New', Courier, monospace; font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #111111; display: block;"><?php echo esc_html($otp); ?></span>
				</div>
				<p style="font-size: 12px; color: #888888; line-height: 1.5; margin: 0;">
					<?php esc_html_e('If you did not request this verification code, please disregard this email.', 'handzom-ui-kit'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<td style="padding: 18px 30px; background-color: #fafafa; border-top: 1px solid #f0f0f0; text-align: center;">
				<p style="font-size: 11.5px; color: #888888; margin: 0; letter-spacing: 0.5px;">
					&copy; <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html($site_name); ?>. <?php esc_html_e('All rights reserved.', 'handzom-ui-kit'); ?>
				</p>
			</td>
		</tr>
	</table>
</body>
</html>
		<?php
		$message = ob_get_clean();

		return wp_mail($recipient, $subject, $message, $headers);
	}
}
