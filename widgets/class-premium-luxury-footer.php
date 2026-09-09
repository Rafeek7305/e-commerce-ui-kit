<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Premium_Luxury_Footer extends Widget_Base
{

	public function get_name()
	{
		return 'handzom_premium_luxury_footer';
	}

	public function get_title()
	{
		return esc_html__('Premium Luxury Footer', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-footer';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-premium-luxury-footer'];
	}

	public function get_script_depends()
	{
		return ['handzom-premium-luxury-footer'];
	}

	protected function register_controls()
	{
		$this->register_content_logo_section();
		$this->register_content_menus_section();
		$this->register_content_contact_section();
		$this->register_content_newsletter_section();
		$this->register_content_bottom_bar_section();

		$this->register_style_layout_section();
		$this->register_style_logo_section();
		$this->register_style_menus_section();
		$this->register_style_contact_section();
		$this->register_style_bottom_bar_section();
		$this->register_style_newsletter_section();
	}

	private function register_style_newsletter_section()
	{
		$this->start_controls_section(
			'section_style_newsletter',
			[
				'label' => esc_html__('Newsletter Typography', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'nl_title_typography',
				'label' => esc_html__('Title Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-nl-content h3',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'nl_desc_typography',
				'label' => esc_html__('Description Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-nl-content p',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'nl_input_typography',
				'label' => esc_html__('Input Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-form-basic input',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'nl_btn_typography',
				'label' => esc_html__('Button Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-form-basic button',
			]
		);

		$this->add_control(
			'nl_btn_color',
			[
				'label' => esc_html__('Button Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-form-basic button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nl_btn_bg',
			[
				'label' => esc_html__('Button Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-form-basic button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function get_available_menus()
	{
		$menus = wp_get_nav_menus();
		$options = ['' => esc_html__('— Select Menu —', 'handzom-ui-kit')];
		foreach ($menus as $menu) {
			$options[$menu->slug] = $menu->name;
		}
		return $options;
	}

	private function register_content_logo_section()
	{
		$this->start_controls_section(
			'section_logo',
			[
				'label' => esc_html__('Logo & Brand Info', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'logo_image',
			[
				'label' => esc_html__('Brand Logo', 'handzom-ui-kit'),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'logo_link',
			[
				'label' => esc_html__('Logo Link', 'handzom-ui-kit'),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => esc_html__('https://your-link.com', 'handzom-ui-kit'),
				'default' => [
					'url' => '',
				],
			]
		);

		$this->add_control(
			'brand_description',
			[
				'label' => esc_html__('Brand Description', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('Discover the essence of modern luxury. Thoughtfully crafted for the modern individual.', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'show_social_icons',
			[
				'label' => esc_html__('Show Social Icons', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'social_icon',
			[
				'label' => esc_html__('Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fab fa-instagram',
					'library' => 'fa-brands',
				],
			]
		);

		$repeater->add_control(
			'social_link',
			[
				'label' => esc_html__('Link', 'handzom-ui-kit'),
				'type' => Controls_Manager::URL,
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->add_control(
			'social_networks',
			[
				'label' => esc_html__('Social Networks', 'handzom-ui-kit'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					['social_icon' => ['value' => 'fab fa-instagram', 'library' => 'fa-brands']],
					['social_icon' => ['value' => 'fab fa-facebook-f', 'library' => 'fa-brands']],
					['social_icon' => ['value' => 'fab fa-twitter', 'library' => 'fa-brands']],
					['social_icon' => ['value' => 'fab fa-pinterest-p', 'library' => 'fa-brands']],
				],
				'condition' => [
					'show_social_icons' => 'yes',
				],
				'title_field' => '{{{ social_icon.value }}}',
			]
		);

		$this->end_controls_section();
	}

	private function register_content_menus_section()
	{
		$this->start_controls_section(
			'section_menus',
			[
				'label' => esc_html__('Menus', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		// Menu 1
		$this->add_control(
			'menu_1_title',
			[
				'label' => esc_html__('Menu 1 Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Shop', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'menu_1_slug',
			[
				'label' => esc_html__('Select Menu 1', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_available_menus(),
			]
		);

		$this->add_control('hr_1', ['type' => Controls_Manager::DIVIDER]);

		// Menu 2
		$this->add_control(
			'menu_2_title',
			[
				'label' => esc_html__('Menu 2 Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Company', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'menu_2_slug',
			[
				'label' => esc_html__('Select Menu 2', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_available_menus(),
			]
		);

		$this->add_control('hr_2', ['type' => Controls_Manager::DIVIDER]);

		// Menu 3
		$this->add_control(
			'menu_3_title',
			[
				'label' => esc_html__('Menu 3 Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Support', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'menu_3_slug',
			[
				'label' => esc_html__('Select Menu 3', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_available_menus(),
			]
		);

		$this->end_controls_section();
	}

	private function register_content_contact_section()
	{
		$this->start_controls_section(
			'section_contact',
			[
				'label' => esc_html__('Contact Info', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'contact_title',
			[
				'label' => esc_html__('Contact Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Contact Us', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'contact_phone',
			[
				'label' => esc_html__('Phone Number', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('+1 (800) 123-4567', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'contact_phone_icon',
			[
				'label' => esc_html__('Phone Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-phone-alt',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'contact_email',
			[
				'label' => esc_html__('Email Address', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('support@handzom.com', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'contact_email_icon',
			[
				'label' => esc_html__('Email Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-envelope',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'contact_address',
			[
				'label' => esc_html__('Store Address', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('123 Luxury Avenue, New York, NY 10001', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'contact_address_icon',
			[
				'label' => esc_html__('Address Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-map-marker-alt',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'contact_hours',
			[
				'label' => esc_html__('Working Hours', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Mon - Fri: 9:00 AM - 6:00 PM', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'contact_hours_icon',
			[
				'label' => esc_html__('Hours Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'far fa-clock',
					'library' => 'fa-regular',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_content_newsletter_section()
	{
		$this->start_controls_section(
			'section_newsletter',
			[
				'label' => esc_html__('Newsletter', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_newsletter',
			[
				'label' => esc_html__('Enable Newsletter', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'newsletter_title',
			[
				'label' => esc_html__('Heading', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Join Our Newsletter', 'handzom-ui-kit'),
				'condition' => ['show_newsletter' => 'yes'],
			]
		);

		$this->add_control(
			'newsletter_desc',
			[
				'label' => esc_html__('Description', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('Subscribe to receive updates, access to exclusive deals, and more.', 'handzom-ui-kit'),
				'condition' => ['show_newsletter' => 'yes'],
			]
		);

		$this->add_control(
			'newsletter_shortcode',
			[
				'label' => esc_html__('Form Shortcode (CF7 / Fluent Forms)', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__('Leave blank to use default simple HTML form layout.', 'handzom-ui-kit'),
				'condition' => ['show_newsletter' => 'yes'],
			]
		);

		$this->add_control(
			'newsletter_placeholder',
			[
				'label' => esc_html__('Input Placeholder', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Enter your email address', 'handzom-ui-kit'),
				'condition' => ['show_newsletter' => 'yes', 'newsletter_shortcode' => ''],
			]
		);

		$this->add_control(
			'newsletter_btn_text',
			[
				'label' => esc_html__('Button Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Subscribe', 'handzom-ui-kit'),
				'condition' => ['show_newsletter' => 'yes', 'newsletter_shortcode' => ''],
			]
		);

		$this->end_controls_section();
	}

	private function register_content_bottom_bar_section()
	{
		$this->start_controls_section(
			'section_bottom_bar',
			[
				'label' => esc_html__('Bottom Bar', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'copyright_text',
			[
				'label' => esc_html__('Copyright Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('© {Year} Handzom. All Rights Reserved.', 'handzom-ui-kit'),
				'description' => esc_html__('Use {Year} for dynamic current year.', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'designed_by_text',
			[
				'label' => esc_html__('Designed By Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Designed by Azasoft Solutions.', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'designed_by_link',
			[
				'label' => esc_html__('Designed By Link', 'handzom-ui-kit'),
				'type' => Controls_Manager::URL,
				'default' => [
					'url' => 'http://azasoft.in/',
					'is_external' => true,
				],
			]
		);

		$this->add_control(
			'show_payments',
			[
				'label' => esc_html__('Show Payment Icons', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'payment_icon',
			[
				'label' => esc_html__('Payment Icon', 'handzom-ui-kit'),
				'type' => Controls_Manager::MEDIA,
			]
		);

		$this->add_control(
			'payment_methods',
			[
				'label' => esc_html__('Payment Methods', 'handzom-ui-kit'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'condition' => [
					'show_payments' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_layout_section()
	{
		$this->start_controls_section(
			'section_style_layout',
			[
				'label' => esc_html__('Layout & Background', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'footer_background',
				'label' => esc_html__('Background', 'handzom-ui-kit'),
				'types' => ['classic', 'gradient', 'video'],
				'selector' => '{{WRAPPER}} .hz-plf-wrapper',
			]
		);

		$this->add_control(
			'footer_text_color',
			[
				'label' => esc_html__('Base Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .hz-plf-wrapper' => '--hz-plf-text: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'footer_heading_color',
			[
				'label' => esc_html__('Heading Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#111111',
				'selectors' => [
					'{{WRAPPER}} .hz-plf-wrapper' => '--hz-plf-heading: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'footer_accent_color',
			[
				'label' => esc_html__('Accent / Hover Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#999999',
				'selectors' => [
					'{{WRAPPER}} .hz-plf-wrapper' => '--hz-plf-accent: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'footer_padding',
			[
				'label' => esc_html__('Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .hz-plf-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_logo_section()
	{
		$this->start_controls_section(
			'section_style_logo',
			[
				'label' => esc_html__('Logo & Brand', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'logo_width',
			[
				'label' => esc_html__('Logo Width', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => 50,
						'max' => 400,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .hz-plf-logo' => 'width: {{SIZE}}{{UNIT}}; max-width: 100%;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'brand_desc_typography',
				'label' => esc_html__('Description Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-desc',
			]
		);

		$this->add_control(
			'social_icon_color',
			[
				'label' => esc_html__('Social Icon Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-socials a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'social_icon_bg',
			[
				'label' => esc_html__('Social Icon Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-socials a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'social_icon_hover_color',
			[
				'label' => esc_html__('Social Hover Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-socials a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'social_icon_hover_bg',
			[
				'label' => esc_html__('Social Hover Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-socials a:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_menus_section()
	{
		$this->start_controls_section(
			'section_style_menus',
			[
				'label' => esc_html__('Menus', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'menu_title_typography',
				'label' => esc_html__('Column Title Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-col-title',
			]
		);

		$this->add_control(
			'menu_title_margin',
			[
				'label' => esc_html__('Title Bottom Margin', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-col-title' => 'margin-bottom: {{SIZE}}px;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'menu_link_typography',
				'label' => esc_html__('Menu Link Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-menu li a',
			]
		);

		$this->add_control(
			'menu_item_margin',
			[
				'label' => esc_html__('Menu Item Spacing', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-menu li' => 'margin-bottom: {{SIZE}}px;',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_contact_section()
	{
		$this->start_controls_section(
			'section_style_contact',
			[
				'label' => esc_html__('Contact Section', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'contact_text_typography',
				'label' => esc_html__('Contact Text Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-contact-list li',
			]
		);

		$this->add_control(
			'contact_icon_color',
			[
				'label' => esc_html__('Icon Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-contact-list i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_bottom_bar_section()
	{
		$this->start_controls_section(
			'section_style_bottom_bar',
			[
				'label' => esc_html__('Bottom Bar', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'bottom_bar_typography',
				'label' => esc_html__('Bottom Bar Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-bottom-bar',
			]
		);

		$this->add_control(
			'bottom_bar_border_color',
			[
				'label' => esc_html__('Top Border Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-bottom-bar' => 'border-top-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'designed_by_typography',
				'label' => esc_html__('Designed By Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-plf-designed-by, {{WRAPPER}} .hz-plf-designed-by a',
			]
		);

		$this->add_control(
			'designed_by_color',
			[
				'label' => esc_html__('Designed By Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-designed-by, {{WRAPPER}} .hz-plf-designed-by a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'designed_by_hover_color',
			[
				'label' => esc_html__('Designed By Hover Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-plf-designed-by a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		$year = date('Y');
		$copyright = str_replace('{Year}', $year, $settings['copyright_text']);
		?>
		<div class="hz-plf-wrapper">
			<div class="hz-plf-container">

				<?php if ($settings['show_newsletter'] === 'yes'): ?>
					<div class="hz-plf-newsletter-row">
						<div class="hz-plf-nl-content">
							<h3><?php echo esc_html($settings['newsletter_title']); ?></h3>
							<p><?php echo esc_html($settings['newsletter_desc']); ?></p>
						</div>
						<div class="hz-plf-nl-form">
							<?php if (!empty($settings['newsletter_shortcode'])): ?>
								<?php echo do_shortcode($settings['newsletter_shortcode']); ?>
							<?php else: ?>
								<form class="hz-plf-form-basic" onsubmit="event.preventDefault();">
									<input type="email" placeholder="<?php echo esc_attr($settings['newsletter_placeholder']); ?>"
										required>
									<button type="submit"><?php echo esc_html($settings['newsletter_btn_text']); ?></button>
								</form>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="hz-plf-main-row">
					<!-- Col 1: Brand -->
					<div class="hz-plf-col hz-plf-brand-col">
						<?php if (!empty($settings['logo_image']['url'])): ?>
							<?php if (!empty($settings['logo_link']['url'])): ?>
								<a href="<?php echo esc_url($settings['logo_link']['url']); ?>" <?php echo $settings['logo_link']['is_external'] ? 'target="_blank" rel="noopener"' : ''; ?>>
									<img src="<?php echo esc_url($settings['logo_image']['url']); ?>" alt="Brand Logo"
										class="hz-plf-logo">
								</a>
							<?php else: ?>
								<img src="<?php echo esc_url($settings['logo_image']['url']); ?>" alt="Brand Logo" class="hz-plf-logo">
							<?php endif; ?>
						<?php endif; ?>
						<p class="hz-plf-desc"><?php echo esc_html($settings['brand_description']); ?></p>

						<?php if ($settings['show_social_icons'] === 'yes' && !empty($settings['social_networks'])): ?>
							<div class="hz-plf-socials">
								<?php foreach ($settings['social_networks'] as $social): ?>
									<a href="<?php echo esc_url($social['social_link']['url']); ?>" target="_blank" rel="noopener">
										<?php \Elementor\Icons_Manager::render_icon($social['social_icon'], ['aria-hidden' => 'true']); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<!-- Col 2: Menu 1 -->
					<?php if ( !empty($settings['menu_1_title']) || !empty($settings['menu_1_slug']) ) : ?>
						<div class="hz-plf-col hz-plf-menu-col">
							<?php if ( !empty($settings['menu_1_title']) ) : ?>
								<h4 class="hz-plf-col-title"><?php echo esc_html($settings['menu_1_title']); ?></h4>
							<?php endif; ?>
							<?php
							if (!empty($settings['menu_1_slug'])) {
								wp_nav_menu([
									'menu' => $settings['menu_1_slug'],
									'container' => false,
									'menu_class' => 'hz-plf-menu',
									'fallback_cb' => false,
								]);
							}
							?>
						</div>
					<?php endif; ?>

					<!-- Col 3: Menu 2 -->
					<?php if ( !empty($settings['menu_2_title']) || !empty($settings['menu_2_slug']) ) : ?>
						<div class="hz-plf-col hz-plf-menu-col">
							<?php if ( !empty($settings['menu_2_title']) ) : ?>
								<h4 class="hz-plf-col-title"><?php echo esc_html($settings['menu_2_title']); ?></h4>
							<?php endif; ?>
							<?php
							if (!empty($settings['menu_2_slug'])) {
								wp_nav_menu([
									'menu' => $settings['menu_2_slug'],
									'container' => false,
									'menu_class' => 'hz-plf-menu',
									'fallback_cb' => false,
								]);
							}
							?>
						</div>
					<?php endif; ?>

					<!-- Col 4: Menu 3 -->
					<?php if ( !empty($settings['menu_3_title']) || !empty($settings['menu_3_slug']) ) : ?>
						<div class="hz-plf-col hz-plf-menu-col">
							<?php if ( !empty($settings['menu_3_title']) ) : ?>
								<h4 class="hz-plf-col-title"><?php echo esc_html($settings['menu_3_title']); ?></h4>
							<?php endif; ?>
							<?php
							if (!empty($settings['menu_3_slug'])) {
								wp_nav_menu([
									'menu' => $settings['menu_3_slug'],
									'container' => false,
									'menu_class' => 'hz-plf-menu',
									'fallback_cb' => false,
								]);
							}
							?>
						</div>
					<?php endif; ?>

					<!-- Col 5: Contact -->
					<div class="hz-plf-col hz-plf-contact-col">
						<h4 class="hz-plf-col-title"><?php echo esc_html($settings['contact_title']); ?></h4>
						<ul class="hz-plf-contact-list">
							<?php if (!empty($settings['contact_phone'])): ?>
								<li>
									<div class="hz-plf-contact-icon">
										<?php \Elementor\Icons_Manager::render_icon($settings['contact_phone_icon'], ['aria-hidden' => 'true']); ?>
									</div>
									<span><?php echo esc_html($settings['contact_phone']); ?></span>
								</li>
							<?php endif; ?>
							<?php if (!empty($settings['contact_email'])): ?>
								<li>
									<div class="hz-plf-contact-icon">
										<?php \Elementor\Icons_Manager::render_icon($settings['contact_email_icon'], ['aria-hidden' => 'true']); ?>
									</div>
									<span><?php echo esc_html($settings['contact_email']); ?></span>
								</li>
							<?php endif; ?>
							<?php if (!empty($settings['contact_address'])): ?>
								<li>
									<div class="hz-plf-contact-icon">
										<?php \Elementor\Icons_Manager::render_icon($settings['contact_address_icon'], ['aria-hidden' => 'true']); ?>
									</div>
									<span><?php echo nl2br(esc_html($settings['contact_address'])); ?></span>
								</li>
							<?php endif; ?>
							<?php if (!empty($settings['contact_hours'])): ?>
								<li>
									<div class="hz-plf-contact-icon">
										<?php \Elementor\Icons_Manager::render_icon($settings['contact_hours_icon'], ['aria-hidden' => 'true']); ?>
									</div>
									<span><?php echo esc_html($settings['contact_hours']); ?></span>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				</div>

				<div class="hz-plf-bottom-bar">
					<div class="hz-plf-copyright-wrap">
						<span class="hz-plf-copyright">
							<?php echo esc_html($copyright); ?>
						</span>
						<?php if (!empty($settings['designed_by_text'])): ?>
							<span class="hz-plf-designed-by">
								<?php if (!empty($settings['designed_by_link']['url'])): ?>
									<a href="<?php echo esc_url($settings['designed_by_link']['url']); ?>" <?php echo $settings['designed_by_link']['is_external'] ? 'target="_blank" rel="noopener"' : ''; ?>>
										<?php echo esc_html($settings['designed_by_text']); ?>
									</a>
								<?php else: ?>
									<?php echo esc_html($settings['designed_by_text']); ?>
								<?php endif; ?>
							</span>
						<?php endif; ?>
					</div>

					<?php if ($settings['show_payments'] === 'yes' && !empty($settings['payment_methods'])): ?>
						<div class="hz-plf-payments">
							<?php foreach ($settings['payment_methods'] as $payment): ?>
								<?php if (!empty($payment['payment_icon']['url'])): ?>
									<img src="<?php echo esc_url($payment['payment_icon']['url']); ?>" alt="Payment Method">
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

			</div>
		</div>
		<?php
	}
}
