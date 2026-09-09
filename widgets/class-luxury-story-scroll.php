<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Utils;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Luxury_Story_Scroll extends Widget_Base
{

	public function get_name()
	{
		return 'handzom_luxury_story_scroll';
	}

	public function get_title()
	{
		return esc_html__('Luxury Story Scroll', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-scroll';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-luxury-story-scroll'];
	}

	public function get_script_depends()
	{
		return ['handzom-luxury-story-scroll'];
	}

	protected function register_controls()
	{
		/* Content Tab: Stories */
		$this->start_controls_section(
			'section_stories',
			[
				'label' => esc_html__('Stories', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'label' => esc_html__('Image', 'handzom-ui-kit'),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'image_position',
			[
				'label' => esc_html__('Image Position', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'center center',
				'options' => [
					'center center' => esc_html__('Center Center', 'handzom-ui-kit'),
					'center top' => esc_html__('Center Top', 'handzom-ui-kit'),
					'center bottom' => esc_html__('Center Bottom', 'handzom-ui-kit'),
					'left center' => esc_html__('Left Center', 'handzom-ui-kit'),
					'right center' => esc_html__('Right Center', 'handzom-ui-kit'),
				],
			]
		);

		$repeater->add_control(
			'category',
			[
				'label' => esc_html__('Category / Subtitle', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Collection', 'handzom-ui-kit'),
			]
		);

		$repeater->add_control(
			'heading',
			[
				'label' => esc_html__('Heading', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Story Heading', 'handzom-ui-kit'),
			]
		);

		$repeater->add_control(
			'description',
			[
				'label' => esc_html__('Description', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('Write a beautiful description for this particular story to engage your audience.', 'handzom-ui-kit'),
			]
		);

		$repeater->add_control(
			'button_text',
			[
				'label' => esc_html__('Button Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Discover More', 'handzom-ui-kit'),
			]
		);

		$repeater->add_control(
			'button_link',
			[
				'label' => esc_html__('Button Link', 'handzom-ui-kit'),
				'type' => Controls_Manager::URL,
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->add_control(
			'stories',
			[
				'label' => esc_html__('Story Items', 'handzom-ui-kit'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'category' => esc_html__('Chapter I', 'handzom-ui-kit'),
						'heading' => esc_html__('The Beginning', 'handzom-ui-kit'),
					],
					[
						'category' => esc_html__('Chapter II', 'handzom-ui-kit'),
						'heading' => esc_html__('The Journey', 'handzom-ui-kit'),
					],
					[
						'category' => esc_html__('Chapter III', 'handzom-ui-kit'),
						'heading' => esc_html__('The Destination', 'handzom-ui-kit'),
					],
				],
				'title_field' => '{{{ heading }}}',
			]
		);

		$this->end_controls_section();

		/* Content Tab: Layout */
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__('Layout Options', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'section_height',
			[
				'label' => esc_html__('Section Height', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'vh'],
				'range' => [
					'px' => [
						'min' => 400,
						'max' => 1200,
					],
					'vh' => [
						'min' => 40,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'vh',
					'size' => 100,
				],
				'selectors' => [
					'{{WRAPPER}} .hz-lss-sticky' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_size',
			[
				'label' => esc_html__('Image Fit', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => [
					'cover' => esc_html__('Cover (Fills Area)', 'handzom-ui-kit'),
					'contain' => esc_html__('Contain (Shows Full Image)', 'handzom-ui-kit'),
				],
				'selectors' => [
					'{{WRAPPER}} .hz-lss-bg-item' => 'background-size: {{VALUE}};',
				],
			]
		);

		// Removed global image_position as it's now in the repeater

		$this->end_controls_section();

		/* Content Tab: Animations */
		$this->start_controls_section(
			'section_animations',
			[
				'label' => esc_html__('Animations', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'animation_speed',
			[
				'label' => esc_html__('Transition Speed (Seconds)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['s'],
				'range' => [
					's' => [
						'min' => 0.1,
						'max' => 3.0,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 's',
					'size' => 0.8,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--hz-lss-speed: {{SIZE}}s;',
				],
			]
		);

		$this->add_control(
			'scroll_sensitivity',
			[
				'label' => esc_html__('Scroll Sensitivity (Speed)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'description' => esc_html__('Lower numbers = Faster slide changes. Higher numbers = Requires more scrolling.', 'handzom-ui-kit'),
				'range' => [
					'px' => [
						'min' => 0.1,
						'max' => 2.0,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.2,
				],
			]
		);

		$this->add_control(
			'animation_type',
			[
				'label' => esc_html__('Text Entrance Animation', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'fade-up',
				'options' => [
					'fade-up' => esc_html__('Fade Up & Blur', 'handzom-ui-kit'),
					'fade-down' => esc_html__('Fade Down & Blur', 'handzom-ui-kit'),
					'zoom-in' => esc_html__('Zoom In', 'handzom-ui-kit'),
					'slide-left' => esc_html__('Slide Left', 'handzom-ui-kit'),
				],
			]
		);

		$this->end_controls_section();

		/* Style Tab */
		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => esc_html__('Typography & Colors', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'overlay_color',
			[
				'label' => esc_html__('Overlay Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-lss-overlay' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'category_color',
			[
				'label' => esc_html__('Category Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-lss-category' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'category_typography',
				'label' => esc_html__('Category Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-lss-category',
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => esc_html__('Heading Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-lss-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'heading_typography',
				'label' => esc_html__('Heading Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-lss-heading',
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => esc_html__('Description Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-lss-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'label' => esc_html__('Description Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-lss-desc',
			]
		);

		$this->end_controls_section();

		/* Style Tab: Button */
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__('Button Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'btn_color',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-lss-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_border_color',
			[
				'label' => esc_html__('Border Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-lss-btn' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_hover_bg',
			[
				'label' => esc_html__('Hover Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-lss-btn:hover' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_hover_color',
			[
				'label' => esc_html__('Hover Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-lss-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'btn_typography',
				'label' => esc_html__('Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-lss-btn',
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		if (empty($settings['stories'])) {
			return;
		}

		$stories_count = count($settings['stories']);
		$anim_type = $settings['animation_type'] ? $settings['animation_type'] : 'fade-up';

		$this->add_render_attribute('wrapper', 'class', 'hz-lss-wrapper');
		$this->add_render_attribute('wrapper', 'class', 'hz-lss-anim-' . $anim_type);
		$this->add_render_attribute('wrapper', 'data-scroll-sensitivity', isset($settings['scroll_sensitivity']['size']) ? $settings['scroll_sensitivity']['size'] : 0.2);
		// We will let JS calculate the dynamic height of wrapper based on sticky height
		$this->add_render_attribute('wrapper', 'data-stories', $stories_count);

		?>
		<div <?php $this->print_render_attribute_string('wrapper'); ?>>
			<div class="hz-lss-sticky">

				<!-- Unified View for Desktop, Tablet, and Mobile -->
				<div class="hz-lss-desktop-view">

					<!-- Background Images Layer -->
					<div class="hz-lss-bg-layer">
						<?php foreach ($settings['stories'] as $index => $story):
							$active_class = $index === 0 ? 'active' : '';
							$bg_position = !empty($story['image_position']) ? $story['image_position'] : 'center center';
							?>
							<div class="hz-lss-bg-item <?php echo esc_attr($active_class); ?>"
								data-index="<?php echo esc_attr($index); ?>"
								style="background-image: url('<?php echo esc_url($story['image']['url']); ?>'); background-position: <?php echo esc_attr($bg_position); ?>;">
							</div>
						<?php endforeach; ?>
						<div class="hz-lss-overlay"></div>
					</div>

					<!-- Foreground Text Layer -->
					<div class="hz-lss-text-layer">
						<?php foreach ($settings['stories'] as $index => $story):
							$active_class = $index === 0 ? 'active' : '';
							?>
							<div class="hz-lss-text-item <?php echo esc_attr($active_class); ?>"
								data-index="<?php echo esc_attr($index); ?>">
								<div class="hz-lss-content-inner">
									<?php if (!empty($story['category'])): ?>
										<h6 class="hz-lss-category"><?php echo esc_html($story['category']); ?></h6>
									<?php endif; ?>

									<?php if (!empty($story['heading'])): ?>
										<h2 class="hz-lss-heading"><?php echo esc_html($story['heading']); ?></h2>
									<?php endif; ?>

									<?php if (!empty($story['description'])): ?>
										<p class="hz-lss-desc"><?php echo esc_html($story['description']); ?></p>
									<?php endif; ?>

									<?php if (!empty($story['button_text'])): ?>
										<a href="<?php echo esc_url($story['button_link']['url']); ?>" class="hz-lss-btn">
											<span><?php echo esc_html($story['button_text']); ?></span>
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
												<path d="M5 12h14M12 5l7 7-7 7" />
											</svg>
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- Progress Indicators -->
					<div class="hz-lss-progress">
						<?php foreach ($settings['stories'] as $index => $story):
							$active_class = $index === 0 ? 'active' : '';
							?>
							<div class="hz-lss-dot <?php echo esc_attr($active_class); ?>"
								data-index="<?php echo esc_attr($index); ?>"></div>
						<?php endforeach; ?>
					</div>

				</div> <!-- End View -->

			</div>
		</div>
		<?php
	}
}
