<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handzom My Account Dashboard Module
 *
 * Provides a minimal, editorial luxury UI for WooCommerce My Account
 */
class Account_Module
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
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 20);
		add_filter('woocommerce_locate_template', [$this, 'override_account_template'], 20, 3);
		add_filter('wc_get_template', [$this, 'filter_wc_get_template'], 20, 5);
		add_action('woocommerce_account_navigation', [$this, 'ensure_assets_enqueued'], 5);

		// Remove Downloads completely from navigation
		add_filter('woocommerce_account_menu_items', [$this, 'filter_account_menu_items'], 999);

		// Handle direct access to downloads endpoint safely
		add_action('template_redirect', [$this, 'handle_downloads_endpoint']);
	}

	/**
	 * Enqueue scoped My Account styles strictly for logged-in users on account page
	 */
	public function enqueue_scripts()
	{
		wp_register_style(
			'handzom-account',
			HANDZOM_UI_KIT_URL . 'account/account.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		if (!is_user_logged_in()) {
			return;
		}

		$is_account = false;
		if (function_exists('is_account_page') && is_account_page()) {
			$is_account = true;
		} elseif (function_exists('is_page') && (is_page('my-account') || is_page('account'))) {
			$is_account = true;
		}

		if ($is_account) {
			wp_enqueue_style('handzom-account');
		}
	}

	/**
	 * Ensure assets are enqueued whenever WooCommerce account navigation renders
	 */
	public function ensure_assets_enqueued()
	{
		if (is_user_logged_in()) {
			wp_enqueue_style('handzom-account');
		}
	}

	/**
	 * Override WooCommerce myaccount/dashboard.php template
	 */
	public function override_account_template($template, $template_name, $template_path)
	{
		if ('myaccount/dashboard.php' === $template_name) {
			$custom_template = HANDZOM_UI_KIT_PATH . 'account/templates/dashboard.php';
			if (file_exists($custom_template)) {
				return $custom_template;
			}
		}
		return $template;
	}

	/**
	 * Filter wc_get_template for myaccount/dashboard.php
	 */
	public function filter_wc_get_template($located, $template_name, $args, $template_path, $default_path)
	{
		if ('myaccount/dashboard.php' === $template_name) {
			$custom_template = HANDZOM_UI_KIT_PATH . 'account/templates/dashboard.php';
			if (file_exists($custom_template)) {
				return $custom_template;
			}
		}
		return $located;
	}

	/**
	 * Remove Downloads option completely from the My Account navigation
	 */
	public function filter_account_menu_items($items)
	{
		if (isset($items['downloads'])) {
			unset($items['downloads']);
		}
		return $items;
	}

	/**
	 * Safely handle direct access to downloads endpoint
	 */
	public function handle_downloads_endpoint()
	{
		if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('downloads')) {
			wp_safe_redirect(wc_get_account_endpoint_url('dashboard'));
			exit;
		}
	}
}
