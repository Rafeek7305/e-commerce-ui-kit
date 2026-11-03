<?php
/**
 * Login & Registration Form - Handzom Custom Template
 *
 * @package HandzomUIKit
 */

defined('ABSPATH') || exit;

// Retrieve any error notices to display inside the respective cards instead of floating at the top of page
$all_errors = function_exists('wc_get_notices') ? wc_get_notices('error') : [];
$login_errors = [];
$reg_errors = [];

if (!empty($all_errors)) {
	foreach ($all_errors as $err_notice) {
		$notice_text = is_array($err_notice) ? ($err_notice['notice'] ?? '') : (string)$err_notice;
		if (
			stripos($notice_text, 'register') !== false ||
			stripos($notice_text, 'verification') !== false ||
			stripos($notice_text, 'code') !== false ||
			(stripos($notice_text, 'password') !== false && !empty($_POST['register']))
		) {
			$reg_errors[] = $notice_text;
		} else {
			$login_errors[] = $notice_text;
		}
	}
	if (function_exists('wc_clear_notices')) {
		wc_clear_notices();
	}
}

$is_email_change_cooldown = false;
if (class_exists('\\HandzomUIKit\\Checkout_Module')) {
	$checkout_mod   = \HandzomUIKit\Checkout_Module::instance();
	$reg_session_id = method_exists($checkout_mod, 'get_or_create_registration_session_id') ? $checkout_mod->get_or_create_registration_session_id() : '';
	if (!empty($reg_session_id)) {
		$em_cd_until = (int) get_transient('hz_em_change_cd_' . $reg_session_id);
		if ($em_cd_until <= 0) {
			$u_ip        = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
			$em_cd_until = (int) get_transient('hz_em_change_cd_ip_' . md5($u_ip));
		}
		if ($em_cd_until > time()) {
			$is_email_change_cooldown = true;
			$rem_sec        = $em_cd_until - time();
			$mins           = (int) floor($rem_sec / 60);
			$secs           = $rem_sec % 60;
			$formatted_time = sprintf('%02d:%02d', $mins, $secs);
			$reg_errors[]   = sprintf(
				/* translators: %s: MM:SS remaining */
				esc_html__("You've reached the email change limit. Please try again in %s.", 'handzom-ui-kit'),
				$formatted_time
			);
		}
	}
}

do_action('woocommerce_before_customer_login_form'); ?>

<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>

<div class="u-columns col2-set hz-account-auth-container" id="customer_login">

	<div class="u-column1 col-1 hz-auth-card hz-login-card">

<?php endif; ?>

		<div class="hz-auth-card-header">
			<h2 class="hz-auth-title"><?php esc_html_e('Welcome back', 'handzom-ui-kit'); ?></h2>
			<p class="hz-auth-subtitle"><?php esc_html_e('Sign in to access your orders, saved items, and details.', 'handzom-ui-kit'); ?></p>
		</div>

		<form class="woocommerce-form woocommerce-form-login login hz-auth-form" method="post">

			<?php do_action('woocommerce_login_form_start'); ?>

			<!-- In-card feedback notice for Login -->
			<div class="hz-auth-card-notice hz-login-card-notice<?php echo empty($login_errors) ? ' is-hidden' : ' is-error'; ?>" id="hz-login-notice" style="<?php echo !empty($login_errors) ? '' : 'display: none;'; ?>" role="alert"><?php echo !empty($login_errors) ? wp_kses_post(implode('<br>', $login_errors)) : ''; ?></div>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide hz-form-row">
				<label for="username"><?php esc_html_e('Username or email address', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text hz-input" name="username" id="username" autocomplete="username" placeholder="<?php esc_attr_e('Enter your username or email', 'handzom-ui-kit'); ?>" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide hz-form-row hz-password-field-wrapper">
				<label for="password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
				<div class="hz-input-password-wrap">
					<input class="woocommerce-Input woocommerce-Input--text input-text hz-input hz-password-input" type="password" name="password" id="password" autocomplete="current-password" placeholder="<?php esc_attr_e('Enter your password', 'handzom-ui-kit'); ?>" required />
					<button type="button" class="hz-password-toggle-btn" aria-label="<?php esc_attr_e('Show password', 'handzom-ui-kit'); ?>" aria-pressed="false" tabindex="-1">
						<span class="hz-eye-icon hz-eye-closed">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
						</span>
						<span class="hz-eye-icon hz-eye-open" style="display: none;">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
						</span>
					</button>
				</div>
			</p>

			<?php do_action('woocommerce_login_form'); ?>

			<div class="hz-auth-actions-row">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce_form_check_box hz-rememberme-label">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox hz-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e('Remember me', 'woocommerce'); ?></span>
				</label>
				<p class="woocommerce-LostPassword lost_password hz-lost-password">
					<a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Lost your password?', 'woocommerce'); ?></a>
				</p>
			</div>

			<p class="form-row hz-submit-row">
				<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
				<button type="submit" class="woocommerce-button button woocommerce-form-login__submit hz-auth-submit-btn" name="login" value="<?php esc_attr_e('Log in', 'woocommerce'); ?>"><?php esc_html_e('Log in', 'woocommerce'); ?></button>
			</p>

			<?php do_action('woocommerce_login_form_end'); ?>

		</form>

<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>

	</div>

	<div class="u-column2 col-2 hz-auth-card hz-register-card">

		<div class="hz-auth-card-header">
			<h2 class="hz-auth-title"><?php esc_html_e('Create your account', 'handzom-ui-kit'); ?></h2>
			<p class="hz-auth-subtitle"><?php esc_html_e('Join Handzom for faster checkout, order tracking, and exclusive updates.', 'handzom-ui-kit'); ?></p>
		</div>

		<form method="post" class="woocommerce-form woocommerce-form-register register hz-auth-form hz-register-form" id="hz-register-form" <?php do_action('woocommerce_register_form_tag'); ?> >

			<?php do_action('woocommerce_register_form_start'); ?>

			<!-- In-card feedback notice -->
			<div class="hz-auth-card-notice<?php echo empty($reg_errors) ? ' is-hidden' : ' is-error'; ?>" id="hz-reg-notice" style="<?php echo !empty($reg_errors) ? '' : 'display: none;'; ?>" role="alert"><?php echo !empty($reg_errors) ? wp_kses_post(implode('<br>', $reg_errors)) : ''; ?></div>

			<!-- Step 1: Email Address Entry -->
			<div class="hz-reg-step hz-reg-step-email" id="hz-reg-step-email">
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide hz-form-row">
					<label for="reg_email"><?php esc_html_e('Email Address', 'handzom-ui-kit'); ?>&nbsp;<span class="required">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text hz-input" name="email" id="reg_email" autocomplete="email" placeholder="<?php esc_attr_e('Enter your email address', 'handzom-ui-kit'); ?>" value="" required />
				</p>

				<p class="form-row hz-submit-row hz-send-otp-row">
					<button type="button" class="woocommerce-Button woocommerce-button button hz-auth-submit-btn hz-btn-send-otp<?php echo $is_email_change_cooldown ? ' is-disabled' : ''; ?>" id="hz-btn-send-otp"<?php echo $is_email_change_cooldown ? ' disabled' : ''; ?>><?php esc_html_e('SEND OTP TO EMAIL', 'handzom-ui-kit'); ?></button>
				</p>
			</div>

			<!-- Step 2: OTP Verification & Expiry Countdown (Revealed after OTP dispatch) -->
			<div class="hz-reg-step hz-reg-step-otp" id="hz-reg-step-otp" style="display: none;">
				<div class="hz-otp-info-box">
					<span class="hz-otp-sent-text"><?php esc_html_e('Verification code sent to', 'handzom-ui-kit'); ?> <strong id="hz-otp-target-email"></strong></span>
					<button type="button" class="hz-otp-change-email-btn" id="hz-otp-change-email-btn"><?php esc_html_e('Change email', 'handzom-ui-kit'); ?></button>
				</div>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide hz-form-row hz-otp-field-row">
					<label for="reg_otp"><?php esc_html_e('Enter Verification Code', 'handzom-ui-kit'); ?>&nbsp;<span class="required">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text hz-input hz-otp-input" name="reg_otp" id="reg_otp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" placeholder="<?php esc_attr_e('6-digit code', 'handzom-ui-kit'); ?>" />
				</p>

				<div class="hz-otp-timer-row">
					<span class="hz-otp-timer-text" id="hz-otp-timer-text"><?php esc_html_e('Expires in:', 'handzom-ui-kit'); ?> <strong class="hz-otp-countdown" id="hz-otp-countdown">05:00</strong></span>
					<button type="button" class="hz-otp-resend-btn" id="hz-btn-resend-otp" disabled>
						<span class="hz-resend-btn-text" id="hz-resend-btn-text"><?php esc_html_e('Resend available in 01:00', 'handzom-ui-kit'); ?></span>
					</button>
				</div>

				<p class="form-row hz-submit-row hz-verify-otp-row">
					<button type="button" class="woocommerce-Button woocommerce-button button hz-auth-submit-btn hz-btn-verify-otp" id="hz-btn-verify-otp"><?php esc_html_e('VERIFY OTP', 'handzom-ui-kit'); ?></button>
				</p>
			</div>

			<input type="hidden" name="hz_reg_verification_token" id="hz_reg_verification_token" value="" />

			<!-- Hidden fields for Stage 2 (Password & Account creation) -->
			<div class="hz-reg-stage2-fields" style="display: none;" aria-hidden="true">
				<?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide hz-form-row">
						<label for="reg_username"><?php esc_html_e('Username', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text hz-input" name="username" id="reg_username" autocomplete="username" placeholder="<?php esc_attr_e('Choose a username', 'handzom-ui-kit'); ?>" value="" />
					</p>
				<?php endif; ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide hz-form-row hz-password-field-wrapper hz-new-password-row">
					<label for="reg_password"><?php esc_html_e('New Password', 'handzom-ui-kit'); ?>&nbsp;<span class="required">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text hz-input hz-password-input" name="password" id="reg_password" autocomplete="new-password" placeholder="<?php esc_attr_e('Create a password', 'handzom-ui-kit'); ?>" />
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide hz-form-row hz-password-field-wrapper hz-confirm-password-row">
					<label for="reg_password_confirmation"><?php esc_html_e('Confirm Password', 'handzom-ui-kit'); ?>&nbsp;<span class="required">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text hz-input hz-password-input" name="password_confirmation" id="reg_password_confirmation" autocomplete="new-password" placeholder="<?php esc_attr_e('Confirm your password', 'handzom-ui-kit'); ?>" />
				</p>
				<p class="woocommerce-form-row form-row hz-submit-row">
					<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
					<input type="hidden" name="register" value="1" />
					<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit hz-auth-submit-btn" name="register" value="<?php esc_attr_e('CREATE ACCOUNT', 'handzom-ui-kit'); ?>"><?php esc_html_e('CREATE ACCOUNT', 'handzom-ui-kit'); ?></button>
				</p>
			</div>

			<?php do_action('woocommerce_register_form'); ?>
			<?php do_action('woocommerce_register_form_end'); ?>

		</form>

	</div>

</div>
<?php endif; ?>

<?php do_action('woocommerce_after_customer_login_form'); ?>
