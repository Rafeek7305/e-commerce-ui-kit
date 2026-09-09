<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Main Loader Class
 */
final class Loader
{

	/**
	 * Instance of this class.
	 */
	private static $instance = null;

	/**
	 * Return an instance of this class.
	 */
	public static function instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct()
	{
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes()
	{
		require_once HANDZOM_UI_KIT_PATH . 'includes/class-assets.php';
		require_once HANDZOM_UI_KIT_PATH . 'includes/class-elementor.php';
		require_once HANDZOM_UI_KIT_PATH . 'includes/class-ajax.php';
		require_once HANDZOM_UI_KIT_PATH . 'cart/class-cart.php';
		require_once HANDZOM_UI_KIT_PATH . 'checkout/class-checkout.php';
		require_once HANDZOM_UI_KIT_PATH . 'content-section/class-content-section.php';
		require_once HANDZOM_UI_KIT_PATH . 'single-product/class-single-product.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks()
	{
		// Check if Elementor is installed and activated
		if (!did_action('elementor/loaded')) {
			add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
			return;
		}

		// Check PHP Version
		if (version_compare(PHP_VERSION, '7.4', '<')) {
			add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
			return;
		}

		// Initialize Assets
		Assets::instance();

		// Initialize Elementor integration
		Elementor_Integration::instance();

		// Initialize AJAX Handler
		Ajax_Handler::instance();

		// Initialize Cart Module
		Cart_Module::instance();

		// Initialize Checkout Module
		Checkout_Module::instance();

		// Initialize Content Section
		Content_Section::instance();

		// Initialize HZ Single Product Module
		HZ_Single_Product::instance();

		// Register shortcode fallback
		add_shortcode('handzom_journal_sidebar', [$this, 'journal_sidebar_shortcode']);
	}

	public function journal_sidebar_shortcode($atts)
	{
		return '<div class="notice notice-info"><p>' . esc_html__('Please use the "Journal Sidebar" Elementor Widget directly from the Elementor panel to control and display this section.', 'handzom-ui-kit') . '</p></div>';
	}

	/**
	 * Admin notice for missing Elementor.
	 */
	public function admin_notice_missing_main_plugin()
	{
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'handzom-ui-kit'),
			'<strong>' . esc_html__('Handzom UI Kit', 'handzom-ui-kit') . '</strong>',
			'<strong>' . esc_html__('Elementor', 'handzom-ui-kit') . '</strong>'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}

	/**
	 * Admin notice for PHP version.
	 */
	public function admin_notice_minimum_php_version()
	{
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'handzom-ui-kit'),
			'<strong>' . esc_html__('Handzom UI Kit', 'handzom-ui-kit') . '</strong>',
			'<strong>' . esc_html__('PHP', 'handzom-ui-kit') . '</strong>',
			'7.4'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}
}
