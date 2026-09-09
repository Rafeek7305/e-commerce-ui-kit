<?php
namespace HandzomUIKit;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Integration Class
 */
class Elementor_Integration
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
		add_action('elementor/elements/categories_registered', [$this, 'add_elementor_category']);
		add_action('elementor/widgets/register', [$this, 'register_widgets']);
	}

	/**
	 * Add custom category in Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function add_elementor_category($elements_manager)
	{
		$elements_manager->add_category(
			'handzom-ui-kit',
			[
				'title' => esc_html__('Handzom UI Kit', 'handzom-ui-kit'),
				'icon' => 'fa fa-plug',
			]
		);
	}

	/**
	 * Register Widgets
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets($widgets_manager)
	{
		if (!did_action('elementor/loaded')) {
			return;
		}

		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-infinite-slider.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-featured-cards.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-trending-products.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-testimonials-carousel.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-split-banner-showcase.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-luxury-story-scroll.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-premium-glass-header.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-premium-luxury-footer.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-premium-filter.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-premium-product-grid.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-our-philosophy.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-article-cards.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-category-showcase.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-collection-showcase.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-content-section-widget.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-journal-sidebar.php';
		require_once HANDZOM_UI_KIT_PATH . 'widgets/class-journal-article-content.php';

		$widgets_manager->register(new \HandzomUIKit\Widgets\Infinite_Slider());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Featured_Cards());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Trending_Products());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Testimonials_Carousel());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Split_Banner_Showcase());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Luxury_Story_Scroll());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Premium_Glass_Header());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Premium_Luxury_Footer());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Premium_Filter());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Premium_Product_Grid());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Our_Philosophy());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Article_Cards());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Category_Showcase());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Collection_Showcase());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Content_Section_Widget());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Journal_Sidebar());
		$widgets_manager->register(new \HandzomUIKit\Widgets\Journal_Article_Content());
	}
}
