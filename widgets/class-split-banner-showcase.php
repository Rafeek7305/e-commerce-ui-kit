<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Split_Banner_Showcase extends Widget_Base {

	public function get_name() {
		return 'handzom_split_banner_showcase';
	}

	public function get_title() {
		return esc_html__( 'Premium Split Banner', 'handzom-ui-kit' );
	}

	public function get_icon() {
		return 'eicon-columns';
	}

	public function get_categories() {
		return [ 'handzom-ui-kit' ];
	}

	public function get_style_depends() {
		return [ 'handzom-split-banner-showcase' ];
	}

	public function get_script_depends() {
		return [ 'handzom-split-banner-showcase' ];
	}

	protected function register_controls() {
		/* Content Tab: Main Section */
		$this->start_controls_section(
			'section_main',
			[
				'label' => esc_html__( 'Main Section', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'section_height',
			[
				'label' => esc_html__( 'Section Height', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range' => [
					'px' => [
						'min' => 400,
						'max' => 1200,
					],
					'vh' => [
						'min' => 50,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 600,
				],
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-wrapper' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'gap_between',
			[
				'label' => esc_html__( 'Gap Between Columns', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'global_border_radius',
			[
				'label' => esc_html__( 'Global Border Radius', 'handzom-ui-kit' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-panel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->end_controls_section();

		/* Content Tab: Left Panel */
		$this->start_controls_section(
			'section_left_panel',
			[
				'label' => esc_html__( 'Left Panel', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'left_bg_image',
			[
				'label' => esc_html__( 'Background Image', 'handzom-ui-kit' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'left_overlay_color',
			[
				'label' => esc_html__( 'Overlay Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-panel-left .hz-sbs-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'left_small_label',
			[
				'label' => esc_html__( 'Small Label', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'New Arrival', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'left_main_title',
			[
				'label' => esc_html__( 'Main Title', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Spring Collection', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'left_description',
			[
				'label' => esc_html__( 'Description', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Discover the latest trends in our new spring collection. Minimal, elegant, and timeless.', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'left_button_text',
			[
				'label' => esc_html__( 'Button Text', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Shop Women', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'left_button_link',
			[
				'label' => esc_html__( 'Button Link', 'handzom-ui-kit' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'handzom-ui-kit' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->add_control(
			'left_text_position',
			[
				'label' => esc_html__( 'Text Position', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'top-left' => esc_html__( 'Top Left', 'handzom-ui-kit' ),
					'center-left' => esc_html__( 'Center Left', 'handzom-ui-kit' ),
					'bottom-left' => esc_html__( 'Bottom Left', 'handzom-ui-kit' ),
					'center' => esc_html__( 'Center Center', 'handzom-ui-kit' ),
				],
			]
		);

		$this->end_controls_section();

		/* Content Tab: Right Panel */
		$this->start_controls_section(
			'section_right_panel',
			[
				'label' => esc_html__( 'Right Panel', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'right_bg_image',
			[
				'label' => esc_html__( 'Background Image', 'handzom-ui-kit' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'right_overlay_color',
			[
				'label' => esc_html__( 'Overlay Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-panel-right .hz-sbs-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'right_small_label',
			[
				'label' => esc_html__( 'Small Label', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Essentials', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'right_main_title',
			[
				'label' => esc_html__( 'Main Title', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Modern Classics', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'right_description',
			[
				'label' => esc_html__( 'Description', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Redefining everyday wear with premium materials and perfect tailoring.', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'right_button_text',
			[
				'label' => esc_html__( 'Button Text', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Shop Men', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'right_button_link',
			[
				'label' => esc_html__( 'Button Link', 'handzom-ui-kit' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'handzom-ui-kit' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->add_control(
			'right_text_position',
			[
				'label' => esc_html__( 'Text Position', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'top-right' => esc_html__( 'Top Right', 'handzom-ui-kit' ),
					'center-right' => esc_html__( 'Center Right', 'handzom-ui-kit' ),
					'bottom-right' => esc_html__( 'Bottom Right', 'handzom-ui-kit' ),
					'center' => esc_html__( 'Center Center', 'handzom-ui-kit' ),
				],
			]
		);

		$this->end_controls_section();

		/* Style Tab: Typography */
		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => esc_html__( 'Typography & Colors', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Label Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'label' => esc_html__( 'Label Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-sbs-label',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Title Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Title Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-sbs-title',
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label' => esc_html__( 'Description Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'desc_typography',
				'label' => esc_html__( 'Description Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-sbs-desc',
			]
		);

		$this->end_controls_section();

		/* Style Tab: Button */
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Button Style', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'btn_color',
			[
				'label' => esc_html__( 'Text Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_hover_color',
			[
				'label' => esc_html__( 'Hover Text Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_hover_bg_color',
			[
				'label' => esc_html__( 'Hover Background Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'btn_typography',
				'label' => esc_html__( 'Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-sbs-btn',
			]
		);
		
		$this->add_control(
			'btn_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'handzom-ui-kit' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .hz-sbs-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute('wrapper', 'class', 'hz-sbs-wrapper');

		// Left Panel Attributes
		$this->add_render_attribute('left_panel', 'class', [
			'hz-sbs-panel',
			'hz-sbs-panel-left',
			'hz-sbs-pos-' . $settings['left_text_position']
		]);

		$left_link = $settings['left_button_link']['url'];
		if (!empty($left_link)) {
			$this->add_render_attribute('left_btn', 'href', $left_link);
			if ($settings['left_button_link']['is_external']) {
				$this->add_render_attribute('left_btn', 'target', '_blank');
			}
			if ($settings['left_button_link']['nofollow']) {
				$this->add_render_attribute('left_btn', 'rel', 'nofollow');
			}
		}

		// Right Panel Attributes
		$this->add_render_attribute('right_panel', 'class', [
			'hz-sbs-panel',
			'hz-sbs-panel-right',
			'hz-sbs-pos-' . $settings['right_text_position']
		]);

		$right_link = $settings['right_button_link']['url'];
		if (!empty($right_link)) {
			$this->add_render_attribute('right_btn', 'href', $right_link);
			if ($settings['right_button_link']['is_external']) {
				$this->add_render_attribute('right_btn', 'target', '_blank');
			}
			if ($settings['right_button_link']['nofollow']) {
				$this->add_render_attribute('right_btn', 'rel', 'nofollow');
			}
		}
		?>

		<div <?php $this->print_render_attribute_string('wrapper'); ?>>
			
			<!-- Left Panel -->
			<div <?php $this->print_render_attribute_string('left_panel'); ?>>
				<?php if (!empty($settings['left_bg_image']['url'])) : ?>
					<div class="hz-sbs-bg" style="background-image: url('<?php echo esc_url($settings['left_bg_image']['url']); ?>');"></div>
				<?php endif; ?>
				<div class="hz-sbs-overlay"></div>
				<div class="hz-sbs-content">
					<?php if (!empty($settings['left_small_label'])) : ?>
						<h6 class="hz-sbs-label"><?php echo esc_html($settings['left_small_label']); ?></h6>
					<?php endif; ?>
					
					<?php if (!empty($settings['left_main_title'])) : ?>
						<h2 class="hz-sbs-title"><?php echo esc_html($settings['left_main_title']); ?></h2>
					<?php endif; ?>
					
					<?php if (!empty($settings['left_description'])) : ?>
						<p class="hz-sbs-desc"><?php echo esc_html($settings['left_description']); ?></p>
					<?php endif; ?>
					
					<?php if (!empty($settings['left_button_text'])) : ?>
						<a <?php $this->print_render_attribute_string('left_btn'); ?> class="hz-sbs-btn">
							<span class="hz-sbs-btn-text"><?php echo esc_html($settings['left_button_text']); ?></span>
							<span class="hz-sbs-btn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Right Panel -->
			<div <?php $this->print_render_attribute_string('right_panel'); ?>>
				<?php if (!empty($settings['right_bg_image']['url'])) : ?>
					<div class="hz-sbs-bg" style="background-image: url('<?php echo esc_url($settings['right_bg_image']['url']); ?>');"></div>
				<?php endif; ?>
				<div class="hz-sbs-overlay"></div>
				<div class="hz-sbs-content">
					<?php if (!empty($settings['right_small_label'])) : ?>
						<h6 class="hz-sbs-label"><?php echo esc_html($settings['right_small_label']); ?></h6>
					<?php endif; ?>
					
					<?php if (!empty($settings['right_main_title'])) : ?>
						<h2 class="hz-sbs-title"><?php echo esc_html($settings['right_main_title']); ?></h2>
					<?php endif; ?>
					
					<?php if (!empty($settings['right_description'])) : ?>
						<p class="hz-sbs-desc"><?php echo esc_html($settings['right_description']); ?></p>
					<?php endif; ?>
					
					<?php if (!empty($settings['right_button_text'])) : ?>
						<a <?php $this->print_render_attribute_string('right_btn'); ?> class="hz-sbs-btn">
							<span class="hz-sbs-btn-text"><?php echo esc_html($settings['right_button_text']); ?></span>
							<span class="hz-sbs-btn-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
						</a>
					<?php endif; ?>
				</div>
			</div>

		</div>

		<?php
	}
}
