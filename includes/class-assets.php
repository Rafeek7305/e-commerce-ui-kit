<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Assets Management Class
 */
class Assets
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
		add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets'], 5);
		add_action('wp_head', [$this, 'print_critical_header_footer_css'], 1);
		add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
		add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_scripts']);
	}

	/**
	 * Enqueue Frontend Assets in <head>
	 */
	public function enqueue_frontend_assets()
	{
		// Register all styles & scripts
		$this->enqueue_styles();
		$this->enqueue_scripts();

		// Enqueue Google Font in <head> without blocking @import
		wp_enqueue_style(
			'handzom-font-ysabeau',
			'https://fonts.googleapis.com/css2?family=Ysabeau+Office:ital,wght@0,100..900;1,100..900&display=swap',
			[],
			null
		);

		// Critical site shell styles (Header & Footer) MUST be enqueued in <head>
		wp_enqueue_style('handzom-premium-glass-header');
		wp_enqueue_style('handzom-premium-luxury-footer');
	}

	/**
	 * Output early critical CSS for Header and Footer to prevent FOUC / layout shift
	 */
	public function print_critical_header_footer_css()
	{
		?>
		<!-- Handzom Critical Header & Footer CSS -->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<style id="handzom-critical-shell-css">
			:root {
				--hz-pgh-font: 'Ysabeau Office', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			}
			.hz-pgh-wrapper {
				position: relative;
				width: 100%;
				z-index: 9999;
				font-family: var(--hz-pgh-font);
			}
			.hz-pgh-wrapper *, .hz-pgh-wrapper *::before, .hz-pgh-wrapper *::after,
			.hz-plf-wrapper *, .hz-plf-wrapper *::before, .hz-plf-wrapper *::after {
				box-sizing: border-box;
			}
			.hz-pgh-wrapper.hz-pgh-overlay {
				position: absolute;
				top: 0;
				left: 0;
			}
			.hz-pgh-announcement {
				background-color: #000;
				color: #fff;
				padding: 10px 40px;
				position: relative;
				display: flex;
				justify-content: center;
				align-items: center;
				overflow: hidden;
				z-index: 10000;
			}
			.hz-pgh-header-inner {
				position: relative;
				width: 100%;
				height: 70px;
				z-index: 10001;
				background: transparent;
				display: flex;
				align-items: center;
			}
			.hz-pgh-container {
				display: flex;
				justify-content: space-between;
				align-items: center;
				width: 100%;
				max-width: 1440px;
				margin: 0 auto;
				padding: 0 40px;
				height: 100%;
			}
			.hz-pgh-logo {
				flex: 1 1 20%;
				display: flex;
				align-items: center;
			}
			.hz-pgh-logo a {
				display: block;
				text-decoration: none;
				color: inherit;
			}
			.hz-pgh-logo img {
				max-height: 40px;
				width: auto;
				display: block;
			}
			.hz-pgh-logo img.hz-pgh-normal-logo {
				display: block !important;
			}
			.hz-pgh-logo img.hz-pgh-sticky-logo,
			.hz-pgh-sticky-logo {
				display: none !important;
			}
			.hz-pgh-wrapper.has-sticky-logo.is-sticky .hz-pgh-logo img.hz-pgh-normal-logo {
				display: none !important;
			}
			.hz-pgh-wrapper.has-sticky-logo.is-sticky .hz-pgh-logo img.hz-pgh-sticky-logo {
				display: block !important;
			}
			.hz-pgh-desktop-nav {
				flex: 1 1 60%;
				display: flex;
				justify-content: center;
				align-items: center;
				height: 100%;
			}
			.hz-pgh-menu-ul {
				list-style: none !important;
				margin: 0 !important;
				padding: 0 !important;
				display: flex;
				gap: 35px;
				align-items: center;
				height: 100%;
			}
			.hz-pgh-menu-ul li {
				position: relative;
				height: 100%;
				display: flex;
				align-items: center;
			}
			.hz-pgh-menu-ul li a {
				text-decoration: none;
				font-size: 14px;
				font-weight: 500;
				letter-spacing: 1px;
				text-transform: uppercase;
			}
			.hz-pgh-icons-group {
				flex: 1 1 20%;
				display: flex;
				justify-content: flex-end;
				align-items: center;
				gap: 20px;
			}
			.hz-pgh-icon-btn {
				display: flex;
				align-items: center;
				justify-content: center;
				text-decoration: none;
				color: inherit;
				position: relative;
			}
			.hz-pgh-hamburger {
				display: none;
				flex-direction: column;
				justify-content: space-between;
				width: 24px;
				height: 18px;
				background: transparent;
				border: none;
				cursor: pointer;
				padding: 0;
				z-index: 10002;
			}
			.hz-pgh-hamburger-line {
				display: block;
				width: 100%;
				height: 2px;
				background-color: currentColor;
			}
			.hz-pgh-mobile-drawer,
			.hz-pgh-search-overlay {
				position: fixed !important;
				top: 0 !important;
				left: 0 !important;
				width: 100% !important;
				height: 100% !important;
				height: 100vh !important;
				opacity: 0 !important;
				visibility: hidden !important;
				pointer-events: none !important;
				z-index: 999999 !important;
			}
			.hz-pgh-mobile-drawer.is-active,
			.hz-pgh-search-overlay.is-active {
				opacity: 1 !important;
				visibility: visible !important;
				pointer-events: auto !important;
			}
			.hz-pgh-drawer-content {
				position: fixed !important;
				top: 0 !important;
				bottom: 0 !important;
				right: -100% !important;
				max-width: 400px;
				width: 100%;
				height: 100% !important;
				height: 100vh !important;
				height: 100dvh !important;
				background: #ffffff !important;
				display: flex !important;
				flex-direction: column !important;
				z-index: 1000000 !important;
				overflow: hidden !important;
			}
			.hz-pgh-mobile-drawer.is-active .hz-pgh-drawer-content {
				right: 0 !important;
			}
			.hz-pgh-drawer-header {
				display: flex !important;
				justify-content: space-between !important;
				align-items: center !important;
				padding: 24px 30px !important;
				border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
				background: #ffffff !important;
				flex-shrink: 0 !important;
			}
			.hz-pgh-mobile-nav {
				flex: 1 1 auto !important;
				overflow-y: auto !important;
				-webkit-overflow-scrolling: touch !important;
				padding: 24px 30px !important;
				background: #ffffff !important;
			}
			.hz-pgh-drawer-footer {
				padding: 20px 30px !important;
				padding-bottom: calc(20px + env(safe-area-inset-bottom, 0px)) !important;
				border-top: 1px solid rgba(0, 0, 0, 0.08) !important;
				margin-top: auto !important;
				background: #ffffff !important;
				flex-shrink: 0 !important;
			}
			.hz-pgh-drawer-icons {
				display: flex !important;
				justify-content: space-around !important;
				align-items: center !important;
				gap: 15px !important;
			}
			.hz-pgh-drawer-icons .hz-pgh-icon-btn {
				display: flex !important;
				visibility: visible !important;
				opacity: 1 !important;
				justify-content: center !important;
				align-items: center !important;
				width: 45px !important;
				height: 45px !important;
				border-radius: 50% !important;
				background: rgba(0, 0, 0, 0.03) !important;
				color: #000000 !important;
				position: relative !important;
				text-decoration: none !important;
			}
			@media (max-width: 1024px) {
				.hz-pgh-desktop-nav {
					display: none !important;
				}
				.hz-pgh-hamburger {
					display: flex !important;
				}
			}
			@media (max-width: 767px) {
				.hz-pgh-container {
					padding: 0 20px;
				}
			}
			/* Critical Footer */
			.hz-plf-wrapper {
				position: relative;
				background: #ffffff;
				padding: 80px 5% 40px;
				color: #666666;
				overflow: hidden;
			}
			.hz-plf-container {
				max-width: 1400px;
				margin: 0 auto;
				position: relative;
			}
			.hz-plf-newsletter-row {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding-bottom: 60px;
				margin-bottom: 60px;
				border-bottom: 1px solid rgba(0, 0, 0, 0.05);
				gap: 40px;
			}
			.hz-plf-nl-content h3 {
				margin: 0 0 10px;
				font-size: 28px;
				font-weight: 300;
				color: #111111;
			}
			.hz-plf-nl-form {
				flex: 1;
				max-width: 500px;
			}
			.hz-plf-form-basic {
				display: flex;
				gap: 10px;
			}
			.hz-plf-form-basic input {
				flex: 1;
				padding: 15px 20px;
				border: 1px solid rgba(0, 0, 0, 0.05);
				border-radius: 4px;
			}
			.hz-plf-form-basic button {
				padding: 15px 30px;
				background: #111111;
				color: #fff;
				border: none;
				border-radius: 4px;
			}
			.hz-plf-main-row {
				display: flex;
				flex-wrap: wrap;
				justify-content: space-between;
				gap: 40px;
				margin-bottom: 60px;
			}
			.hz-plf-brand-col {
				flex: 0 1 300px;
				margin-right: 5%;
			}
			.hz-plf-brand-col .hz-plf-logo {
				max-height: 40px;
				margin-bottom: 25px;
				display: block;
			}
			.hz-plf-col {
				flex: 1;
			}
			.hz-plf-col-title {
				font-size: 14px;
				font-weight: 600;
				letter-spacing: 1px;
				text-transform: uppercase;
				color: #111111;
				margin: 0 0 25px;
			}
			.hz-plf-menu,
			.hz-plf-contact-list {
				list-style: none !important;
				margin: 0 !important;
				padding: 0 !important;
			}
			.hz-plf-menu li,
			.hz-plf-contact-list li {
				list-style: none !important;
				margin-bottom: 15px;
			}
			.hz-plf-menu li a {
				text-decoration: none !important;
				color: #666666;
			}
			.hz-plf-contact-list li {
				display: flex;
				align-items: flex-start;
				gap: 15px;
			}
			.hz-plf-socials {
				display: flex;
				gap: 15px;
			}
			.hz-plf-socials a {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 40px;
				height: 40px;
				border-radius: 50%;
				background: rgba(0, 0, 0, 0.05);
				color: #111111;
				text-decoration: none;
			}
			.hz-plf-bottom-bar {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding-top: 30px;
				border-top: 1px solid rgba(0, 0, 0, 0.05);
			}
			@media (max-width: 1024px) {
				.hz-plf-newsletter-row {
					flex-direction: column;
					text-align: center;
				}
				.hz-plf-main-row {
					justify-content: center;
				}
				.hz-plf-brand-col,
				.hz-plf-contact-col {
					flex: 1 1 100%;
				}
				.hz-plf-brand-col {
					text-align: center;
					margin-right: 0;
				}
				.hz-plf-brand-col .hz-plf-logo {
					margin-left: auto;
					margin-right: auto;
				}
				.hz-plf-socials {
					justify-content: center;
				}
			}
			@media (max-width: 768px) {
				.hz-plf-main-row {
					flex-direction: column;
					align-items: center;
					gap: 30px;
					text-align: center;
				}
				.hz-plf-menu-col,
				.hz-plf-contact-col {
					flex: 1 1 100%;
					width: 100%;
					text-align: left;
				}
				.hz-plf-menu-col .hz-plf-col-title,
				.hz-plf-contact-col .hz-plf-col-title {
					display: flex;
					justify-content: space-between;
					align-items: center;
					border-bottom: 1px solid rgba(0, 0, 0, 0.05);
					padding-bottom: 15px;
					margin-bottom: 0;
				}
				.hz-plf-menu-col .hz-plf-menu,
				.hz-plf-contact-col .hz-plf-contact-list {
					display: none;
					padding-top: 15px;
					padding-bottom: 15px;
				}
				.hz-plf-bottom-bar {
					flex-direction: column;
					gap: 20px;
					text-align: center;
				}
			}
			@media (min-width: 769px) {
				.hz-plf-menu-col .hz-plf-menu {
					display: block !important;
				}
			}
		</style>
		<?php
	}

	/**
	 * Enqueue Styles
	 */
	public function enqueue_styles()
	{
		wp_register_style(
			'handzom-featured-cards',
			HANDZOM_UI_KIT_URL . 'assets/css/featured-cards.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);
		
		wp_register_style(
			'handzom-trending-products',
			HANDZOM_UI_KIT_URL . 'assets/css/trending-products.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-testimonials-carousel',
			HANDZOM_UI_KIT_URL . 'assets/css/testimonials-carousel.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-split-banner-showcase',
			HANDZOM_UI_KIT_URL . 'assets/css/split-banner-showcase.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-luxury-story-scroll',
			HANDZOM_UI_KIT_URL . 'assets/css/luxury-story-scroll.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-premium-glass-header',
			HANDZOM_UI_KIT_URL . 'assets/css/premium-glass-header.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-premium-luxury-footer',
			HANDZOM_UI_KIT_URL . 'assets/css/premium-luxury-footer.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-premium-filter-css',
			HANDZOM_UI_KIT_URL . 'assets/css/premium-filter.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-premium-product-grid-css',
			HANDZOM_UI_KIT_URL . 'assets/css/premium-product-grid.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-our-philosophy',
			HANDZOM_UI_KIT_URL . 'assets/css/our-philosophy.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-journal-sidebar',
			HANDZOM_UI_KIT_URL . 'assets/css/journal-sidebar.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-journal-article-content',
			HANDZOM_UI_KIT_URL . 'assets/css/journal-article-content.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-article-cards',
			HANDZOM_UI_KIT_URL . 'assets/css/article-cards.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-category-showcase',
			HANDZOM_UI_KIT_URL . 'assets/css/category-showcase.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);

		wp_register_style(
			'handzom-collection-showcase',
			HANDZOM_UI_KIT_URL . 'assets/css/collection-showcase.css',
			[],
			HANDZOM_UI_KIT_VERSION
		);
	}

	/**
	 * Enqueue Scripts
	 */
	public function enqueue_scripts()
	{
		wp_register_script(
			'handzom-featured-cards',
			HANDZOM_UI_KIT_URL . 'assets/js/featured-cards.js',
			['jquery', 'elementor-frontend'],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_register_script(
			'handzom-trending-products',
			HANDZOM_UI_KIT_URL . 'assets/js/trending-products.js',
			['jquery', 'elementor-frontend'],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_register_script(
			'handzom-testimonials-carousel',
			HANDZOM_UI_KIT_URL . 'assets/js/testimonials-carousel.js',
			['jquery', 'elementor-frontend'],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_register_script(
			'handzom-split-banner-showcase',
			HANDZOM_UI_KIT_URL . 'assets/js/split-banner-showcase.js',
			[ 'jquery', 'elementor-frontend' ],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_register_script(
			'handzom-luxury-story-scroll',
			HANDZOM_UI_KIT_URL . 'assets/js/luxury-story-scroll.js',
			[ 'jquery', 'elementor-frontend' ],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_register_script(
			'handzom-category-showcase',
			HANDZOM_UI_KIT_URL . 'assets/js/category-showcase.js',
			['jquery', 'elementor-frontend'],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_localize_script('handzom-category-showcase', 'handzom_cs_ajax', [
			'ajax_url' => admin_url('admin-ajax.php')
		]);

		wp_register_script(
			'handzom-collection-showcase',
			HANDZOM_UI_KIT_URL . 'assets/js/collection-showcase.js',
			['jquery', 'elementor-frontend', 'jquery-ui-slider'],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_localize_script('handzom-collection-showcase', 'handzom_cs_ajax', [
			'ajax_url' => admin_url('admin-ajax.php')
		]);

		wp_register_script(
			'handzom-premium-glass-header',
			HANDZOM_UI_KIT_URL . 'assets/js/premium-glass-header.js',
			[ 'jquery', 'elementor-frontend' ],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_register_script(
			'handzom-premium-luxury-footer',
			HANDZOM_UI_KIT_URL . 'assets/js/premium-luxury-footer.js',
			['jquery', 'elementor-frontend'],
			HANDZOM_UI_KIT_VERSION,
			true
		);

		wp_register_script(
			'handzom-premium-filter-js',
			HANDZOM_UI_KIT_URL . 'assets/js/premium-filter.js',
			['jquery', 'jquery-ui-slider', 'elementor-frontend'],
			HANDZOM_UI_KIT_VERSION,
			true
		);
	}
}
