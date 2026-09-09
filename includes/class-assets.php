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
		add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
		add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_scripts']);
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
