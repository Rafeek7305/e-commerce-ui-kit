<?php
namespace HandzomUIKit\Widgets;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;

/**
 * Featured Cards Widget Class
 */
class Featured_Cards extends Widget_Base
{

	public function get_name()
	{
		return 'handzom_featured_cards';
	}

	public function get_title()
	{
		return esc_html__('Featured Cards', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-slider-album';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-featured-cards'];
	}

	public function get_script_depends()
	{
		return ['handzom-featured-cards'];
	}

	protected function register_controls()
	{

		/* Content Section */
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Content', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'section_title',
			[
				'label' => esc_html__('Section Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('New Arrivals', 'handzom-ui-kit'),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'label' => esc_html__('Image', 'handzom-ui-kit'),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'subtitle',
			[
				'label' => esc_html__('Subtitle', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Category', 'handzom-ui-kit'),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'title',
			[
				'label' => esc_html__('Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Card Title', 'handzom-ui-kit'),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'description',
			[
				'label' => esc_html__('Description', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('Add a brief description here.', 'handzom-ui-kit'),
				'show_label' => false,
			]
		);

		$repeater->add_control(
			'price',
			[
				'label' => esc_html__('Price (Optional)', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'button_text',
			[
				'label' => esc_html__('Button Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => '',
			]
		);

		$repeater->add_control(
			'button_link',
			[
				'label' => esc_html__('Card / Button Link', 'handzom-ui-kit'),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__('https://your-link.com', 'handzom-ui-kit'),
				'default' => [
					'url' => '#',
				],
			]
		);

		$repeater->add_control(
			'badge',
			[
				'label' => esc_html__('Badge (Optional)', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'overlay_toggle',
			[
				'label' => esc_html__('Background Overlay', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Show', 'handzom-ui-kit'),
				'label_off' => esc_html__('Hide', 'handzom-ui-kit'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'cards',
			[
				'label' => esc_html__('Featured Cards', 'handzom-ui-kit'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'title' => esc_html__('Card 1', 'handzom-ui-kit'),
						'subtitle' => esc_html__('New', 'handzom-ui-kit'),
					],
					[
						'title' => esc_html__('Card 2', 'handzom-ui-kit'),
						'subtitle' => esc_html__('Popular', 'handzom-ui-kit'),
					],
					[
						'title' => esc_html__('Card 3', 'handzom-ui-kit'),
						'subtitle' => esc_html__('Trending', 'handzom-ui-kit'),
					],
					[
						'title' => esc_html__('Card 4', 'handzom-ui-kit'),
						'subtitle' => esc_html__('Featured', 'handzom-ui-kit'),
					],
					[
						'title' => esc_html__('Card 5', 'handzom-ui-kit'),
						'subtitle' => esc_html__('Top Rated', 'handzom-ui-kit'),
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();

		/* Slider Settings Section */
		$this->start_controls_section(
			'slider_settings_section',
			[
				'label' => esc_html__('Slider Settings', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__('Cards Per View', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => '4',
				'tablet_default' => '3',
				'mobile_default' => '2',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => esc_html__('Autoplay', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => esc_html__('Autoplay Speed (ms)', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 3000,
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'infinite',
			[
				'label' => esc_html__('Infinite Loop', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => esc_html__('Pause on Hover', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'speed',
			[
				'label' => esc_html__('Transition Speed (ms)', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 500,
			]
		);

		$this->add_control(
			'arrows',
			[
				'label' => esc_html__('Show Arrows', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'dots',
			[
				'label' => esc_html__('Show Dots', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();

		/* Layout Section */
		$this->start_controls_section(
			'layout_section',
			[
				'label' => esc_html__('Layout', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label' => esc_html__('Gap', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 2,
				]
			]
		);

		$this->add_responsive_control(
			'card_height',
			[
				'label' => esc_html__('Card Height', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 800,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					]
				],
				'size_units' => ['px', 'vh'],
				'default' => [
					'size' => 450,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-featured-card' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'content_alignment',
			[
				'label' => esc_html__('Content Alignment', 'handzom-ui-kit'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__('Left', 'handzom-ui-kit'),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__('Center', 'handzom-ui-kit'),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__('Right', 'handzom-ui-kit'),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .handzom-card-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'vertical_alignment',
			[
				'label' => esc_html__('Vertical Alignment', 'handzom-ui-kit'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__('Top', 'handzom-ui-kit'),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__('Middle', 'handzom-ui-kit'),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__('Bottom', 'handzom-ui-kit'),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'flex-end',
				'selectors' => [
					'{{WRAPPER}} .handzom-card-content' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_position',
			[
				'label' => esc_html__('Button Position (Hover Slide Up)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Slide', 'handzom-ui-kit'),
				'label_off' => esc_html__('Static', 'handzom-ui-kit'),
				'return_value' => 'slide',
				'default' => 'slide',
				'prefix_class' => 'handzom-btn-',
			]
		);

		$this->end_controls_section();

		/* Style Section - Section Title */
		$this->start_controls_section(
			'style_section_title',
			[
				'label' => esc_html__('Section Title', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'section_title_color',
			[
				'label' => esc_html__('Title Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-section-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'section_title_typography',
				'selector' => '{{WRAPPER}} .handzom-section-title',
			]
		);

		$this->add_responsive_control(
			'section_title_margin',
			[
				'label' => esc_html__('Margin', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .handzom-section-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* Style Section - Cards */
		$this->start_controls_section(
			'style_cards_section',
			[
				'label' => esc_html__('Card Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_aspect_ratio',
			[
				'label' => esc_html__('Image Aspect Ratio (Height)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0.5,
						'max' => 2.5,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-card-image-wrap' => 'aspect-ratio: 1 / {{SIZE}};',
				],
			]
		);

		$this->start_controls_tabs('tabs_card_bg_style');

		$this->start_controls_tab(
			'tab_card_bg_normal',
			[
				'label' => esc_html__('Normal', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label' => esc_html__('Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-featured-card' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .handzom-card-image-wrap' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_card_bg_hover',
			[
				'label' => esc_html__('Hover', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'card_hover_bg_color',
			[
				'label' => esc_html__('Hover Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-featured-card:hover' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .handzom-featured-card:hover .handzom-card-image-wrap' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'selector' => '{{WRAPPER}} .handzom-featured-card',
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label' => esc_html__('Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .handzom-featured-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .handzom-card-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_shadow',
				'selector' => '{{WRAPPER}} .handzom-featured-card',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_hover_shadow',
				'label' => esc_html__('Hover Shadow', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .handzom-featured-card:hover',
			]
		);

		$this->add_control(
			'card_hover_lift',
			[
				'label' => esc_html__('Hover Lift (px)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-featured-card' => '--hz-lift: -{{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'overlay_color',
			[
				'label' => esc_html__('Overlay Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-card-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'overlay_opacity',
			[
				'label' => esc_html__('Overlay Opacity', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-card-overlay' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'image_hover_zoom',
			[
				'label' => esc_html__('Image Hover Zoom', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 2,
						'step' => 0.05,
					],
				],
				'default' => [
					'size' => 1.1,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-featured-card' => '--hz-zoom: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'transition_duration',
			[
				'label' => esc_html__('Transition Duration (ms)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 2000,
						'step' => 50,
					],
				],
				'default' => [
					'size' => 400,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-featured-card' => '--hz-duration: {{SIZE}}ms;',
				],
			]
		);

		$this->end_controls_section();

		/* Style Section - Typography & Colors */
		$this->start_controls_section(
			'style_content_section',
			[
				'label' => esc_html__('Content Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__('Title Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-card-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .handzom-card-title',
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label' => esc_html__('Subtitle Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-card-subtitle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle_typography',
				'selector' => '{{WRAPPER}} .handzom-card-subtitle',
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => esc_html__('Price Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-card-price' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'selector' => '{{WRAPPER}} .handzom-card-price',
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label' => esc_html__('Description Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-card-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'desc_typography',
				'selector' => '{{WRAPPER}} .handzom-card-desc',
			]
		);

		$this->end_controls_section();

		/* Style Section - Button */
		$this->start_controls_section(
			'style_button_section',
			[
				'label' => esc_html__('Button Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .handzom-card-button',
			]
		);

		$this->start_controls_tabs('tabs_button_style');

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__('Normal', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-card-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label' => esc_html__('Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-card-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .handzom-card-button',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_shadow',
				'selector' => '{{WRAPPER}} .handzom-card-button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__('Hover', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'button_hover_color',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-card-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_bg_color',
			[
				'label' => esc_html__('Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-card-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_hover_border',
				'selector' => '{{WRAPPER}} .handzom-card-button:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_hover_shadow',
				'selector' => '{{WRAPPER}} .handzom-card-button:hover',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__('Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .handzom-card-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .handzom-card-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		if (empty($settings['cards'])) {
			return;
		}

		$slider_options = [
			'autoplay' => isset($settings['autoplay']) ? $settings['autoplay'] : 'yes',
			'autoplay_speed' => isset($settings['autoplay_speed']) ? (int) $settings['autoplay_speed'] : 3000,
			'infinite' => isset($settings['infinite']) ? $settings['infinite'] : 'yes',
			'pause_on_hover' => isset($settings['pause_on_hover']) ? $settings['pause_on_hover'] : 'yes',
			'speed' => isset($settings['speed']) ? (int) $settings['speed'] : 500,
			'arrows' => isset($settings['arrows']) ? $settings['arrows'] : 'yes',
			'dots' => isset($settings['dots']) ? $settings['dots'] : 'yes',
			'desktop_cols' => !empty($settings['columns']) ? (int) $settings['columns'] : 4,
			'tablet_cols' => !empty($settings['columns_tablet']) ? (int) $settings['columns_tablet'] : 3,
			'mobile_cols' => !empty($settings['columns_mobile']) ? (int) $settings['columns_mobile'] : 2,
			'gap' => isset($settings['gap']['size']) && is_numeric($settings['gap']['size']) ? (int) $settings['gap']['size'] : 2,
			'gap_tablet' => isset($settings['gap_tablet']['size']) && is_numeric($settings['gap_tablet']['size']) ? (int) $settings['gap_tablet']['size'] : 2,
			'gap_mobile' => isset($settings['gap_mobile']['size']) && is_numeric($settings['gap_mobile']['size']) ? (int) $settings['gap_mobile']['size'] : 2,
		];

		$this->add_render_attribute('swiper-container', 'class', 'swiper handzom-swiper-container');
		$this->add_render_attribute('swiper-container', 'data-settings', wp_json_encode($slider_options));
		?>
		<div class="handzom-featured-cards-wrapper">

			<?php if (!empty($settings['section_title']) || 'yes' === $settings['arrows']): ?>
				<div class="handzom-cards-header">
					<?php if (!empty($settings['section_title'])): ?>
						<h2 class="handzom-section-title"><?php echo esc_html($settings['section_title']); ?></h2>
					<?php endif; ?>

					<?php if ('yes' === $settings['arrows']): ?>
						<div class="handzom-swiper-nav">
							<div class="swiper-button-prev handzom-swiper-prev">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
									stroke-linejoin="round">
									<path d="M19 12H5M12 19l-7-7 7-7" />
								</svg>
							</div>
							<div class="swiper-button-next handzom-swiper-next">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
									stroke-linejoin="round">
									<path d="M5 12h14M12 5l7 7-7 7" />
								</svg>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div <?php $this->print_render_attribute_string('swiper-container'); ?>>
				<div class="swiper-wrapper">
					<?php foreach ($settings['cards'] as $index => $item):
						$card_key = 'card_' . $index;
						$this->add_render_attribute($card_key, 'class', 'swiper-slide handzom-featured-card elementor-repeater-item-' . esc_attr($item['_id']));
						?>
						<div <?php $this->print_render_attribute_string($card_key); ?>>

							<?php if (!empty($item['image']['url'])): ?>
								<div class="handzom-card-image-wrap">
									<img src="<?php echo esc_url($item['image']['url']); ?>"
										alt="<?php echo esc_attr($item['title']); ?>" class="handzom-card-image">
									
									<?php if ('yes' === $item['overlay_toggle']): ?>
										<div class="handzom-card-overlay"></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if (!empty($item['badge'])): ?>
								<div class="handzom-card-badge"><?php echo esc_html($item['badge']); ?></div>
							<?php endif; ?>
							
							<?php if (!empty($item['button_link']['url'])):
								$stretched_link_key = 'stretched_link_' . $index;
								$this->add_render_attribute($stretched_link_key, 'class', 'handzom-card-stretched-link');
								$this->add_render_attribute($stretched_link_key, 'href', esc_url($item['button_link']['url']));
								if (!empty($item['button_link']['is_external'])) {
									$this->add_render_attribute($stretched_link_key, 'target', '_blank');
								}
								if (!empty($item['button_link']['nofollow'])) {
									$this->add_render_attribute($stretched_link_key, 'rel', 'nofollow');
								}
								// Accessibility: prevent screen readers from reading this twice if button also exists
								if (!empty($item['button_text'])) {
									$this->add_render_attribute($stretched_link_key, 'aria-hidden', 'true');
									$this->add_render_attribute($stretched_link_key, 'tabindex', '-1');
								}
								?>
								<a <?php $this->print_render_attribute_string($stretched_link_key); ?>></a>
							<?php endif; ?>

							<div class="handzom-card-content">
								<div class="handzom-card-content-inner">
									<?php if (!empty($item['subtitle'])): ?>
										<div class="handzom-card-subtitle"><?php echo esc_html($item['subtitle']); ?></div>
									<?php endif; ?>

									<?php if (!empty($item['title']) || !empty($item['price'])): ?>
										<div class="handzom-card-title-price-wrap">
											<?php if (!empty($item['title'])): ?>
												<h3 class="handzom-card-title"><?php echo esc_html($item['title']); ?></h3>
											<?php endif; ?>

											<?php if (!empty($item['price'])): ?>
												<div class="handzom-card-price"><?php echo esc_html($item['price']); ?></div>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<?php if (!empty($item['description'])): ?>
										<div class="handzom-card-desc"><?php echo esc_html($item['description']); ?></div>
									<?php endif; ?>
								</div>

								<?php if (!empty($item['button_text'])):
									$btn_key = 'button_' . $index;
									$btn_url = !empty($item['button_link']['url']) ? $item['button_link']['url'] : '#';
									
									$this->add_render_attribute($btn_key, 'class', 'handzom-card-button');
									$this->add_render_attribute($btn_key, 'href', esc_url($btn_url));

									if (!empty($item['button_link']['is_external'])) {
										$this->add_render_attribute($btn_key, 'target', '_blank');
									}
									if (!empty($item['button_link']['nofollow'])) {
										$this->add_render_attribute($btn_key, 'rel', 'nofollow');
									}
									?>
									<div class="handzom-card-button-wrap">
										<a <?php $this->print_render_attribute_string($btn_key); ?>>
											<?php echo esc_html($item['button_text']); ?>
										</a>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ('yes' === $settings['dots']): ?>
					<div class="swiper-pagination handzom-swiper-pagination"></div>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}
}
