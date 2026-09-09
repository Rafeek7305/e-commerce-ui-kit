<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Testimonials_Carousel extends Widget_Base {

	public function get_name() {
		return 'handzom_testimonials_carousel';
	}

	public function get_title() {
		return esc_html__( 'Premium Testimonials', 'handzom-ui-kit' );
	}

	public function get_icon() {
		return 'eicon-testimonial-carousel';
	}

	public function get_categories() {
		return [ 'handzom-ui-kit' ];
	}

	public function get_style_depends() {
		return [ 'handzom-testimonials-carousel' ];
	}

	public function get_script_depends() {
		return [ 'handzom-testimonials-carousel' ];
	}

	protected function _register_controls() {
		$this->register_controls();
	}

	protected function register_controls() {
		/* Content Tab: Section */
		$this->start_controls_section(
			'section_header',
			[
				'label' => esc_html__( 'Section Header', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'small_heading',
			[
				'label' => esc_html__( 'Small Heading', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'What they say', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'main_heading',
			[
				'label' => esc_html__( 'Main Heading', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Customer Testimonials', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'subtitle',
			[
				'label' => esc_html__( 'Subtitle', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Hear from our satisfied customers around the globe.', 'handzom-ui-kit' ),
			]
		);

		$this->add_control(
			'show_divider',
			[
				'label' => esc_html__( 'Show Divider', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'handzom-ui-kit' ),
				'label_off' => esc_html__( 'No', 'handzom-ui-kit' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'divider_icon',
			[
				'label' => esc_html__( 'Divider Icon', 'handzom-ui-kit' ),
				'type' => Controls_Manager::MEDIA,
				'condition' => [
					'show_divider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'divider_width',
			[
				'label' => esc_html__( 'Divider Width', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 500,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .hz-tc-divider-wrap' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'show_divider' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		/* Content Tab: Testimonials */
		$this->start_controls_section(
			'section_testimonials',
			[
				'label' => esc_html__( 'Testimonials', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		/* Removed global layout_style to make it dynamic per card */

		$repeater = new Repeater();

		$repeater->add_control(
			'customer_image',
			[
				'label' => esc_html__( 'Customer Image', 'handzom-ui-kit' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'customer_name',
			[
				'label' => esc_html__( 'Customer Name', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'John Doe', 'handzom-ui-kit' ),
			]
		);

		$repeater->add_control(
			'customer_designation',
			[
				'label' => esc_html__( 'Designation', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'CEO', 'handzom-ui-kit' ),
			]
		);

		$repeater->add_control(
			'customer_company',
			[
				'label' => esc_html__( 'Company (Optional)', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Company Inc.', 'handzom-ui-kit' ),
			]
		);

		$repeater->add_control(
			'review_text',
			[
				'label' => esc_html__( 'Review Text', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'This product is absolutely amazing. Highly recommended for everyone looking for premium quality.', 'handzom-ui-kit' ),
			]
		);

		$repeater->add_control(
			'star_rating',
			[
				'label' => esc_html__( 'Star Rating', 'handzom-ui-kit' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 5,
				'default' => 5,
			]
		);

		$repeater->add_control(
			'location',
			[
				'label' => esc_html__( 'Location', 'handzom-ui-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'New York, USA', 'handzom-ui-kit' ),
			]
		);

		$repeater->add_control(
			'verified_badge',
			[
				'label' => esc_html__( 'Verified Badge', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'handzom-ui-kit' ),
				'label_off' => esc_html__( 'No', 'handzom-ui-kit' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'testimonials',
			[
				'label' => esc_html__( 'Testimonial Items', 'handzom-ui-kit' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'customer_name' => esc_html__( 'Alice Smith', 'handzom-ui-kit' ),
						'customer_designation' => esc_html__( 'Designer', 'handzom-ui-kit' ),
					],
					[
						'customer_name' => esc_html__( 'Bob Johnson', 'handzom-ui-kit' ),
						'customer_designation' => esc_html__( 'Developer', 'handzom-ui-kit' ),
					],
					[
						'customer_name' => esc_html__( 'Charlie Brown', 'handzom-ui-kit' ),
						'customer_designation' => esc_html__( 'Manager', 'handzom-ui-kit' ),
					],
					[
						'customer_name' => esc_html__( 'Diana Prince', 'handzom-ui-kit' ),
						'customer_designation' => esc_html__( 'Marketing', 'handzom-ui-kit' ),
					],
					[
						'customer_name' => esc_html__( 'Evan Davis', 'handzom-ui-kit' ),
						'customer_designation' => esc_html__( 'Founder', 'handzom-ui-kit' ),
					],
				],
				'title_field' => '{{{ customer_name }}}',
			]
		);

		$this->add_control(
			'avatar_shape',
			[
				'label' => esc_html__( 'Avatar Shape', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'circle',
				'options' => [
					'circle' => esc_html__( 'Circle', 'handzom-ui-kit' ),
					'rounded' => esc_html__( 'Rounded', 'handzom-ui-kit' ),
					'square' => esc_html__( 'Square', 'handzom-ui-kit' ),
				],
			]
		);

		$this->add_control(
			'card_glass_effect',
			[
				'label' => esc_html__( 'Glass Effect Optional', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->end_controls_section();

		/* Content Tab: Carousel Settings */
		$this->start_controls_section(
			'section_carousel',
			[
				'label' => esc_html__( 'Carousel Settings', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'slides_to_show',
			[
				'label' => esc_html__( 'Slides to Show', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
			]
		);

		$this->add_responsive_control(
			'space_between',
			[
				'label' => esc_html__( 'Space Between Cards', 'handzom-ui-kit' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 20,
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => esc_html__( 'Autoplay', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'continuous_scroll',
			[
				'label' => esc_html__( 'Continuous Scroll (Marquee)', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'return_value' => 'yes',
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => esc_html__( 'Autoplay Delay (ms)', 'handzom-ui-kit' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 3000,
				'condition' => [
					'autoplay' => 'yes',
					'continuous_scroll!' => 'yes',
				],
			]
		);

		$this->add_control(
			'loop',
			[
				'label' => esc_html__( 'Loop Infinitely', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => esc_html__( 'Pause on Hover', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'center_mode',
			[
				'label' => esc_html__( 'Center Mode', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'transition_speed',
			[
				'label' => esc_html__( 'Transition Speed (ms)', 'handzom-ui-kit' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 500,
			]
		);

		$this->add_control(
			'drag_mouse',
			[
				'label' => esc_html__( 'Drag Mouse', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'touch_swipe',
			[
				'label' => esc_html__( 'Touch Swipe', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'keyboard_nav',
			[
				'label' => esc_html__( 'Keyboard Navigation', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'navigation',
			[
				'label' => esc_html__( 'Navigation Arrows', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'pagination',
			[
				'label' => esc_html__( 'Pagination', 'handzom-ui-kit' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'bullets',
				'options' => [
					'none' => esc_html__( 'None', 'handzom-ui-kit' ),
					'bullets' => esc_html__( 'Dots', 'handzom-ui-kit' ),
					'fraction' => esc_html__( 'Fraction', 'handzom-ui-kit' ),
					'progressbar' => esc_html__( 'Progress Bar', 'handzom-ui-kit' ),
				],
			]
		);

		$this->end_controls_section();

		/* Style Tab: Header */
		$this->start_controls_section(
			'section_style_header',
			[
				'label' => esc_html__( 'Header Style', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'small_heading_color',
			[
				'label' => esc_html__( 'Small Heading Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tc-small-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'small_heading_typography',
				'label' => esc_html__( 'Small Heading Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-tc-small-heading',
			]
		);

		$this->add_control(
			'main_heading_color',
			[
				'label' => esc_html__( 'Main Heading Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tc-main-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'main_heading_typography',
				'label' => esc_html__( 'Main Heading Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-tc-main-heading',
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label' => esc_html__( 'Subtitle Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tc-subtitle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle_typography',
				'label' => esc_html__( 'Subtitle Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-tc-subtitle',
			]
		);

		$this->end_controls_section();

		/* Style Tab: Cards */
		$this->start_controls_section(
			'section_style_cards',
			[
				'label' => esc_html__( 'Cards Style', 'handzom-ui-kit' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label' => esc_html__( 'Card Background', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tc-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_border_color',
			[
				'label' => esc_html__( 'Card Border Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tc-card' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Review Text Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tc-review' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'name_color',
			[
				'label' => esc_html__( 'Name Text Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tc-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'star_color',
			[
				'label' => esc_html__( 'Stars Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tc-star' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-tc-star.filled' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'review_typography',
				'label' => esc_html__( 'Review Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-tc-review',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'name_typography',
				'label' => esc_html__( 'Name Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-tc-name',
			]
		);

		$this->add_control(
			'designation_color',
			[
				'label' => esc_html__( 'Designation Color', 'handzom-ui-kit' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-tc-designation' => 'color: {{VALUE}};',
					'{{WRAPPER}} .hz-tc-company' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'designation_typography',
				'label' => esc_html__( 'Designation Typography', 'handzom-ui-kit' ),
				'selector' => '{{WRAPPER}} .hz-tc-designation, {{WRAPPER}} .hz-tc-company',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( empty( $settings['testimonials'] ) ) {
			return;
		}

		$slider_options = [
			'autoplay' => isset($settings['autoplay']) ? $settings['autoplay'] : 'yes',
			'autoplay_speed' => isset($settings['autoplay_speed']) ? (int) $settings['autoplay_speed'] : 3000,
			'continuous_scroll' => isset($settings['continuous_scroll']) ? $settings['continuous_scroll'] : 'no',
			'loop' => isset($settings['loop']) ? $settings['loop'] : 'yes',
			'pause_on_hover' => isset($settings['pause_on_hover']) ? $settings['pause_on_hover'] : 'yes',
			'speed' => isset($settings['transition_speed']) ? (int) $settings['transition_speed'] : 500,
			'center_mode' => isset($settings['center_mode']) ? $settings['center_mode'] : 'no',
			'drag_mouse' => isset($settings['drag_mouse']) ? $settings['drag_mouse'] : 'yes',
			'touch_swipe' => isset($settings['touch_swipe']) ? $settings['touch_swipe'] : 'yes',
			'keyboard_nav' => isset($settings['keyboard_nav']) ? $settings['keyboard_nav'] : 'yes',
			'navigation' => isset($settings['navigation']) ? $settings['navigation'] : 'yes',
			'pagination' => isset($settings['pagination']) ? $settings['pagination'] : 'bullets',
			
			'desktop_cols' => !empty($settings['slides_to_show']) ? (int) $settings['slides_to_show'] : 5,
			'tablet_cols' => !empty($settings['slides_to_show_tablet']) ? (int) $settings['slides_to_show_tablet'] : 2,
			'mobile_cols' => !empty($settings['slides_to_show_mobile']) ? (int) $settings['slides_to_show_mobile'] : 1,
			
			'gap' => isset($settings['space_between']) && is_numeric($settings['space_between']) ? (int) $settings['space_between'] : 20,
			'gap_tablet' => isset($settings['space_between_tablet']) && is_numeric($settings['space_between_tablet']) ? (int) $settings['space_between_tablet'] : 20,
			'gap_mobile' => isset($settings['space_between_mobile']) && is_numeric($settings['space_between_mobile']) ? (int) $settings['space_between_mobile'] : 10,
		];

		$this->add_render_attribute('swiper-container', 'class', 'swiper hz-tc-swiper-container');
		if ( 'yes' === $settings['continuous_scroll'] ) {
			$this->add_render_attribute('swiper-container', 'class', 'hz-tc-continuous');
		}
		$this->add_render_attribute('swiper-container', 'data-settings', wp_json_encode($slider_options));
		
		$glass_class = ( 'yes' === $settings['card_glass_effect'] ) ? 'hz-tc-glass' : '';
		$avatar_shape = !empty($settings['avatar_shape']) ? $settings['avatar_shape'] : 'circle';
		?>
		<div class="hz-tc-wrapper">
			<div class="hz-tc-header">
				<?php if ( ! empty( $settings['small_heading'] ) ) : ?>
					<h5 class="hz-tc-small-heading"><?php echo esc_html( $settings['small_heading'] ); ?></h5>
				<?php endif; ?>
				<?php if ( ! empty( $settings['main_heading'] ) ) : ?>
					<h2 class="hz-tc-main-heading"><?php echo esc_html( $settings['main_heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $settings['subtitle'] ) ) : ?>
					<p class="hz-tc-subtitle"><?php echo esc_html( $settings['subtitle'] ); ?></p>
				<?php endif; ?>
				<?php if ( 'yes' === $settings['show_divider'] ) : ?>
					<div class="hz-tc-divider-wrap">
						<?php if ( ! empty( $settings['divider_icon']['url'] ) ) : ?>
							<img src="<?php echo esc_url( $settings['divider_icon']['url'] ); ?>" alt="Divider" class="hz-tc-divider-icon" loading="lazy">
						<?php else: ?>
							<span class="hz-tc-divider-line"></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<div <?php $this->print_render_attribute_string('swiper-container'); ?> aria-label="<?php esc_attr_e( 'Testimonials Carousel', 'handzom-ui-kit' ); ?>">
				<div class="swiper-wrapper">
					<?php foreach ( $settings['testimonials'] as $index => $item ) : 
							$is_simple = empty( $item['customer_image']['url'] );
						?>
						<div class="swiper-slide hz-tc-slide">
							<div class="hz-tc-card <?php echo esc_attr( $is_simple ? 'hz-tc-style-simple' : '' ); ?> <?php echo esc_attr( $glass_class ); ?>">
								
								<div class="hz-tc-stars" aria-label="<?php echo esc_attr( $item['star_rating'] . ' out of 5 stars' ); ?>">
									<?php
									$stars = (int) $item['star_rating'];
									for ( $i = 1; $i <= 5; $i++ ) {
										if ( $i <= $stars ) {
											echo '<span class="hz-tc-star filled">★</span>';
										} else {
											echo '<span class="hz-tc-star">☆</span>';
										}
									}
									?>
								</div>

								<div class="hz-tc-review">
									<?php 
										if ( $is_simple ) {
											echo '"' . esc_html( $item['review_text'] ) . '"';
										} else {
											echo esc_html( $item['review_text'] ); 
										}
									?>
								</div>

								<?php if ( $is_simple ) : ?>
									<div class="hz-tc-customer-info">
										<div class="hz-tc-meta">
											<h4 class="hz-tc-name hz-tc-simple-name">
												- <?php echo esc_html( $item['customer_name'] ); ?><?php if ( ! empty( $item['location'] ) ) : ?>, <?php echo esc_html( $item['location'] ); ?><?php endif; ?>
											</h4>
										</div>
									</div>
								<?php else : ?>
									<div class="hz-tc-customer-info">
										<?php if ( ! empty( $item['customer_image']['url'] ) ) : ?>
											<div class="hz-tc-avatar hz-tc-shape-<?php echo esc_attr( $avatar_shape ); ?>">
												<img src="<?php echo esc_url( $item['customer_image']['url'] ); ?>" alt="<?php echo esc_attr( $item['customer_name'] ); ?>" loading="lazy">
											</div>
										<?php endif; ?>
										
										<div class="hz-tc-meta">
											<h4 class="hz-tc-name">
												<?php echo esc_html( $item['customer_name'] ); ?>
												<?php if ( 'yes' === $item['verified_badge'] ) : ?>
													<svg class="hz-tc-verified" viewBox="0 0 24 24" fill="#62d2a2" width="16" height="16"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
												<?php endif; ?>
											</h4>
											
											<div class="hz-tc-designation">
												<?php echo esc_html( $item['customer_designation'] ); ?>
												<?php if ( ! empty( $item['customer_company'] ) ) : ?>
													<span class="hz-tc-company">@ <?php echo esc_html( $item['customer_company'] ); ?></span>
												<?php endif; ?>
											</div>
											
											<?php if ( ! empty( $item['location'] ) ) : ?>
												<div class="hz-tc-location"><?php echo esc_html( $item['location'] ); ?></div>
											<?php endif; ?>
										</div>
									</div>
								<?php endif; ?>

							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( 'yes' === $settings['navigation'] ) : ?>
					<div class="hz-tc-nav">
						<div class="swiper-button-prev hz-tc-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'handzom-ui-kit' ); ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 18l-6-6 6-6"/></svg>
						</div>
						<div class="swiper-button-next hz-tc-next" aria-label="<?php esc_attr_e( 'Next slide', 'handzom-ui-kit' ); ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 18l6-6-6-6"/></svg>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( 'none' !== $settings['pagination'] ) : ?>
					<div class="swiper-pagination hz-tc-pagination"></div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
