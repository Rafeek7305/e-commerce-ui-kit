<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Premium_Glass_Header extends Widget_Base
{

	public function get_name()
	{
		return 'handzom_premium_glass_header';
	}

	public function get_title()
	{
		return esc_html__('Premium Glass Header', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-header';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-premium-glass-header'];
	}

	public function get_script_depends()
	{
		return ['handzom-premium-glass-header'];
	}

	private function get_available_menus()
	{
		$menus = wp_get_nav_menus();
		$options = ['' => esc_html__('Select Menu', 'handzom-ui-kit')];
		foreach ($menus as $menu) {
			$options[$menu->slug] = $menu->name;
		}
		return $options;
	}

	protected function register_controls()
	{
		// ==========================================
		// LOGO SETTINGS
		// ==========================================
		$this->start_controls_section(
			'section_logo',
			[
				'label' => esc_html__('Logo', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'logo_image',
			[
				'label' => esc_html__('Choose Logo', 'handzom-ui-kit'),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_responsive_control(
			'logo_width',
			[
				'label' => esc_html__('Logo Width', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => ['min' => 20, 'max' => 300],
				],
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-logo img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// MENU SETTINGS
		// ==========================================
		$this->start_controls_section(
			'section_menu',
			[
				'label' => esc_html__('Menu', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'menu_select',
			[
				'label' => esc_html__('Select WordPress Menu', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_available_menus(),
				'default' => '',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'menu_typography',
				'selector' => '{{WRAPPER}} .hz-pgh-nav ul li a',
			]
		);

		$this->add_control(
			'menu_color',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-nav ul li a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-pgh-icons-group a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'menu_hover_color',
			[
				'label' => esc_html__('Hover / Active Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-nav ul li a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-pgh-nav ul li.current-menu-item > a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-pgh-nav ul li a::after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hamburger_color',
			[
				'label' => esc_html__('Hamburger Icon Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-hamburger-line' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// MOBILE DRAWER SETTINGS
		// ==========================================
		$this->start_controls_section(
			'section_mobile_drawer',
			[
				'label' => esc_html__('Mobile Drawer Style', 'handzom-ui-kit'),
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'mobile_menu_typography',
				'selector' => '{{WRAPPER}} .hz-pgh-mobile-ul li a',
			]
		);

		$this->add_control(
			'mobile_menu_color',
			[
				'label' => esc_html__('Mobile Menu Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-mobile-ul li a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mobile_drawer_bg',
			[
				'label' => esc_html__('Drawer Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-drawer-content' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// HEADER STYLE SETTINGS
		// ==========================================
		$this->start_controls_section(
			'section_header_style',
			[
				'label' => esc_html__('Header Styling', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'overlay_header',
			[
				'label' => esc_html__('Overlay on Content', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'description' => esc_html__('Makes the header sit on top of the content (useful for transparent headers over a hero image).', 'handzom-ui-kit'),
				'default' => 'no',
			]
		);

		$this->add_control(
			'header_height',
			[
				'label' => esc_html__('Header Height (px)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => ['px' => ['min' => 40, 'max' => 150]],
				'default' => ['size' => 70],
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-header-inner' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'header_sticky_height',
			[
				'label' => esc_html__('Sticky Height (px)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => ['px' => ['min' => 40, 'max' => 120]],
				'default' => ['size' => 60],
			]
		);

		$this->add_control(
			'glass_bg',
			[
				'label' => esc_html__('Normal Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => 'transparent',
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-header-inner' => 'background: {{VALUE}};',
				],
			]
		);



		$this->add_control(
			'glass_blur',
			[
				'label' => esc_html__('Backdrop Blur (px)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => ['px' => ['min' => 0, 'max' => 50]],
				'default' => ['size' => 0],
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-header-inner' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'header_border',
				'selector' => '{{WRAPPER}} .hz-pgh-header-inner',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'header_shadow',
				'selector' => '{{WRAPPER}} .hz-pgh-header-inner',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STICKY STATE SETTINGS
		// ==========================================
		$this->start_controls_section(
			'section_sticky_state',
			[
				'label' => esc_html__('Sticky State Styles', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'glass_sticky_bg',
			[
				'label' => esc_html__('Sticky Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(255, 255, 255, 0.65)',
				'description' => esc_html__('Important: Use a semi-transparent color so the blur effect is visible.', 'handzom-ui-kit'),
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-wrapper.is-sticky .hz-pgh-header-inner' => 'background: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'sticky_logo_image',
			[
				'label' => esc_html__('Sticky Logo (Optional)', 'handzom-ui-kit'),
				'type' => Controls_Manager::MEDIA,
				'description' => esc_html__('Upload a different logo to show when scrolling.', 'handzom-ui-kit'),
			]
		);

		$this->add_responsive_control(
			'sticky_logo_width',
			[
				'label' => esc_html__('Sticky Logo Width', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => ['min' => 20, 'max' => 300],
				],
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-wrapper.is-sticky .hz-pgh-logo img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'sticky_menu_color',
			[
				'label' => esc_html__('Sticky Menu Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-wrapper.is-sticky .hz-pgh-nav ul li a' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .hz-pgh-wrapper.is-sticky .hz-pgh-icons-group a' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .hz-pgh-wrapper.is-sticky .hz-pgh-hamburger' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'sticky_menu_hover_color',
			[
				'label' => esc_html__('Sticky Hover / Active Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-wrapper.is-sticky .hz-pgh-nav ul li a:hover' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .hz-pgh-wrapper.is-sticky .hz-pgh-nav ul li.current-menu-item > a' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .hz-pgh-wrapper.is-sticky .hz-pgh-nav ul li a::after' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// ELEMENTS (SEARCH, CART, ACCOUNT)
		// ==========================================
		$this->start_controls_section(
			'section_elements',
			[
				'label' => esc_html__('Right Elements', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'show_search',
			[
				'label' => esc_html__('Show Search Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_account',
			[
				'label' => esc_html__('Show Account Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_wishlist',
			[
				'label' => esc_html__('Show Wishlist Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_cart',
			[
				'label' => esc_html__('Show Cart Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'hide_icons_on_mobile',
			[
				'label' => esc_html__('Hide Icons on Mobile', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'description' => esc_html__('Hide all icons (except hamburger) on mobile screens.', 'handzom-ui-kit'),
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// ANNOUNCEMENT BAR
		// ==========================================
		$this->start_controls_section(
			'section_announcement',
			[
				'label' => esc_html__('Announcement Bar', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'show_announcement',
			[
				'label' => esc_html__('Enable Announcement', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'announcement_text',
			[
				'label' => esc_html__('Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => 'Free shipping on all orders over $500',
				'condition' => ['show_announcement' => 'yes'],
			]
		);

		$this->add_control(
			'announcement_bg',
			[
				'label' => esc_html__('Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'condition' => ['show_announcement' => 'yes'],
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-announcement' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'announcement_color',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'condition' => ['show_announcement' => 'yes'],
				'selectors' => [
					'{{WRAPPER}} .hz-pgh-announcement p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$menu_slug = $settings['menu_select'];

		// Setup WooCommerce flags
		$is_woocommerce_active = class_exists('WooCommerce');

		$has_sticky_logo = !empty($settings['sticky_logo_image']['url']) ? 'has-sticky-logo' : '';
		$hide_icons_mobile = ($settings['hide_icons_on_mobile'] === 'yes') ? 'hz-pgh-hide-icons-mobile' : '';
		$is_overlay = ($settings['overlay_header'] === 'yes') ? 'hz-pgh-overlay' : '';
		$wrapper_classes = implode(' ', array_filter([$has_sticky_logo, $hide_icons_mobile, $is_overlay]));

		// HTML Structure
		?>
		<style>
			/* Critical CSS to prevent FOUC (Flash of Unstyled Content) */
			.hz-pgh-wrapper {
				position: relative;
				width: 100%;
				z-index: 9999;
			}
			
			.hz-pgh-wrapper.hz-pgh-overlay {
				position: absolute;
				top: 0;
				left: 0;
			}

			.hz-pgh-mobile-drawer {
				position: fixed;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				width: 100%;
				height: 100%;
				height: 100vh;
				height: 100dvh;
				min-height: 100%;
				min-height: 100vh;
				min-height: 100dvh;
				opacity: 0;
				pointer-events: none;
				transition: opacity 0.4s ease;
				z-index: 999999;
			}

			.hz-pgh-mobile-drawer.is-active {
				opacity: 1;
				pointer-events: auto;
			}

			.hz-pgh-drawer-content {
				position: fixed;
				top: 0;
				bottom: 0;
				right: -100%;
				width: 100%;
				max-width: 400px;
				height: 100%;
				height: 100vh;
				height: 100dvh;
				min-height: 100%;
				min-height: 100vh;
				min-height: 100dvh;
				background: #ffffff;
			}

			.hz-pgh-mobile-drawer,
			.hz-pgh-mobile-drawer *,
			.hz-pgh-mobile-ul li a {
				font-family: 'Ysabeau Office', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
			}

			.hz-pgh-search-overlay {
				position: fixed;
				opacity: 0;
				pointer-events: none;
			}

			.hz-pgh-menu-ul,
			.hz-pgh-mobile-ul {
				list-style: none;
				margin: 0;
				padding: 0;
				display: flex;
			}

			.hz-pgh-mobile-ul {
				flex-direction: column;
			}

			.hz-pgh-container {
				display: flex;
				justify-content: space-between;
				align-items: center;
				width: 100%;
				max-width: 1400px;
				margin: 0 auto;
			}

			.hz-pgh-logo {
				flex: 1 1 20%;
				display: flex;
				align-items: center;
			}

			.hz-pgh-desktop-nav {
				flex: 1 1 60%;
				display: flex;
				justify-content: center;
			}

			.hz-pgh-icons-group {
				flex: 1 1 20%;
				display: flex;
				justify-content: flex-end;
				align-items: center;
				gap: 25px;
			}

			.hz-pgh-sticky-logo {
				display: none;
			}

			.hz-pgh-wrapper.has-sticky-logo.is-sticky .hz-pgh-normal-logo {
				display: none;
			}

			.hz-pgh-wrapper.has-sticky-logo.is-sticky .hz-pgh-sticky-logo {
				display: block;
			}
		</style>
		<div class="hz-pgh-wrapper <?php echo esc_attr($wrapper_classes); ?>"
			data-sticky-height="<?php echo esc_attr($settings['header_sticky_height']['size']); ?>"
			data-sticky-bg="<?php echo esc_attr($settings['glass_sticky_bg']); ?>">

			<?php if ($settings['show_announcement'] === 'yes'): ?>
				<div class="hz-pgh-announcement">
					<div class="hz-pgh-announcement-inner">
						<p><?php echo esc_html($settings['announcement_text']); ?></p>
					</div>
					<button class="hz-pgh-announcement-close" aria-label="Close Announcement">
						<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
							stroke="currentColor" stroke-width="2">
							<path d="M18 6L6 18M6 6l12 12"></path>
						</svg>
					</button>
				</div>
			<?php endif; ?>

			<header class="hz-pgh-header-inner">
				<div class="hz-pgh-container">

					<!-- Left: Logo -->
					<div class="hz-pgh-logo">
						<a href="<?php echo esc_url(home_url('/')); ?>">
							<?php if (!empty($settings['logo_image']['url'])): ?>
								<img src="<?php echo esc_url($settings['logo_image']['url']); ?>" class="hz-pgh-normal-logo"
									alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
								<?php if (!empty($settings['sticky_logo_image']['url'])): ?>
									<img src="<?php echo esc_url($settings['sticky_logo_image']['url']); ?>" class="hz-pgh-sticky-logo"
										alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
								<?php endif; ?>
							<?php else: ?>
								<h2><?php echo esc_html(get_bloginfo('name')); ?></h2>
							<?php endif; ?>
						</a>
					</div>

					<!-- Center: Navigation -->
					<nav class="hz-pgh-nav hz-pgh-desktop-nav">
						<?php
						if (!empty($menu_slug)) {
							wp_nav_menu([
								'menu' => $menu_slug,
								'container' => false,
								'menu_class' => 'hz-pgh-menu-ul',
								'fallback_cb' => false,
							]);
						} else {
							echo '<ul class="hz-pgh-menu-ul"><li><a href="#">Select a Menu in Elementor</a></li></ul>';
						}
						?>
					</nav>

					<!-- Right: Icons -->
					<div class="hz-pgh-icons-group">
						<?php if ($settings['show_search'] === 'yes'): ?>
							<a href="#" class="hz-pgh-icon-btn hz-pgh-search-toggle" aria-label="Search">
								<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
									stroke="currentColor" stroke-width="2">
									<circle cx="11" cy="11" r="8"></circle>
									<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
								</svg>
							</a>
						<?php endif; ?>

						<?php if ($settings['show_account'] === 'yes'): ?>
							<a href="<?php echo $is_woocommerce_active ? esc_url(wc_get_page_permalink('myaccount')) : '#'; ?>"
								class="hz-pgh-icon-btn" aria-label="Account">
								<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
									stroke="currentColor" stroke-width="2">
									<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
									<circle cx="12" cy="7" r="4"></circle>
								</svg>
							</a>
						<?php endif; ?>

						<?php if ($settings['show_wishlist'] === 'yes'): ?>
							<a href="#" class="hz-pgh-icon-btn hz-pgh-wishlist-btn" aria-label="Wishlist">
								<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
									stroke="currentColor" stroke-width="2">
									<path
										d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
									</path>
								</svg>
								<span class="hz-pgh-badge">0</span>
							</a>
						<?php endif; ?>

						<?php if ($settings['show_cart'] === 'yes'):
							$cart_count = 0;
							$cart_url = '#';
							if ($is_woocommerce_active && !is_null(WC()->cart)) {
								$cart_count = WC()->cart->get_cart_contents_count();
								$cart_url = wc_get_cart_url();
							}
							?>
							<a href="<?php echo esc_url($cart_url); ?>" class="hz-pgh-icon-btn" aria-label="Cart">
								<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
									stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
								</svg>
								<span class="hz-pgh-badge hz-pgh-cart-count"><?php echo esc_html($cart_count); ?></span>
							</a>
						<?php endif; ?>

						<!-- Mobile Hamburger -->
						<button class="hz-pgh-hamburger" aria-label="Menu">
							<span class="hz-pgh-hamburger-line top"></span>
							<span class="hz-pgh-hamburger-line middle"></span>
							<span class="hz-pgh-hamburger-line bottom"></span>
						</button>
					</div>

				</div>
			</header>

			<!-- Mobile Drawer Menu -->
			<div class="hz-pgh-mobile-drawer">
				<div class="hz-pgh-drawer-overlay"></div>
				<div class="hz-pgh-drawer-content">
					<div class="hz-pgh-drawer-header">
						<?php if (!empty($settings['logo_image']['url'])): ?>
							<img src="<?php echo esc_url($settings['logo_image']['url']); ?>"
								alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
						<?php else: ?>
							<h3><?php echo esc_html(get_bloginfo('name')); ?></h3>
						<?php endif; ?>
						<button class="hz-pgh-drawer-close">
							<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
								stroke="currentColor" stroke-width="2">
								<path d="M18 6L6 18M6 6l12 12"></path>
							</svg>
						</button>
					</div>
					<nav class="hz-pgh-mobile-nav">
						<?php
						if (!empty($menu_slug)) {
							wp_nav_menu([
								'menu' => $menu_slug,
								'container' => false,
								'menu_class' => 'hz-pgh-mobile-ul',
								'fallback_cb' => false,
							]);
						}
						?>
					</nav>
					<div class="hz-pgh-drawer-footer">
						<div class="hz-pgh-drawer-icons">
							<?php if ($settings['show_search'] === 'yes'): ?>
								<a href="#" class="hz-pgh-icon-btn hz-pgh-search-toggle" aria-label="Search">
									<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
										stroke="currentColor" stroke-width="2">
										<circle cx="11" cy="11" r="8"></circle>
										<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
									</svg>
								</a>
							<?php endif; ?>

							<?php if ($settings['show_account'] === 'yes'): ?>
								<a href="<?php echo esc_url(wp_login_url()); ?>" class="hz-pgh-icon-btn" aria-label="Account">
									<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
										stroke="currentColor" stroke-width="2">
										<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
										<circle cx="12" cy="7" r="4"></circle>
									</svg>
								</a>
							<?php endif; ?>

							<?php if ($settings['show_wishlist'] === 'yes'): ?>
								<a href="#" class="hz-pgh-icon-btn" aria-label="Wishlist">
									<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
										stroke="currentColor" stroke-width="2">
										<path
											d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
										</path>
									</svg>
									<span class="hz-pgh-badge">0</span>
								</a>
							<?php endif; ?>

							<?php if ($settings['show_cart'] === 'yes'):
								$cart_count = 0;
								$cart_url = '#';
								if ($is_woocommerce_active && !is_null(WC()->cart)) {
									$cart_count = WC()->cart->get_cart_contents_count();
									$cart_url = wc_get_cart_url();
								}
								?>
								<a href="<?php echo esc_url($cart_url); ?>" class="hz-pgh-icon-btn" aria-label="Cart">
									<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
										stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
									</svg>
									<span class="hz-pgh-badge hz-pgh-cart-count"><?php echo esc_html($cart_count); ?></span>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<!-- WooCommerce Search Overlay -->
			<?php if ($settings['show_search'] === 'yes'): ?>
				<div class="hz-pgh-search-overlay">
					<div class="hz-pgh-search-container">
						<button class="hz-pgh-search-close">
							<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
								stroke="currentColor" stroke-width="2">
								<path d="M18 6L6 18M6 6l12 12"></path>
							</svg>
						</button>
						<form role="search" method="get" class="hz-pgh-search-form" action="<?php echo esc_url(home_url('/')); ?>">
							<?php if ($is_woocommerce_active): ?>
								<input type="hidden" name="post_type" value="product" />
							<?php endif; ?>
							<input type="search" class="hz-pgh-search-field" placeholder="Search products..."
								value="<?php echo get_search_query(); ?>" name="s" />
							<button type="submit" class="hz-pgh-search-submit">
								<svg style="width: 24px; height: 24px; min-width: 24px;" viewBox="0 0 24 24" fill="none"
									stroke="currentColor" stroke-width="2">
									<circle cx="11" cy="11" r="8"></circle>
									<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
								</svg>
							</button>
						</form>
					</div>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}
}
