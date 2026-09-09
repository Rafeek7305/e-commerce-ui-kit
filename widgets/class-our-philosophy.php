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
use Elementor\Group_Control_Background;

/**
 * Our Philosophy Widget Class
 */
class Our_Philosophy extends Widget_Base
{

	public function get_name()
	{
		return 'handzom_our_philosophy';
	}

	public function get_title()
	{
		return esc_html__('Our Philosophy', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-text-area';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-our-philosophy'];
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
				'default' => esc_html__('OUR PHILOSOPHY', 'handzom-ui-kit'),
			]
		);
        
        $this->add_control(
			'show_title_divider',
			[
				'label' => esc_html__('Show Title Divider', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
                'return_value' => 'yes',
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'number',
			[
				'label' => esc_html__('Number', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => '01',
			]
		);

		$repeater->add_control(
			'heading',
			[
				'label' => esc_html__('Heading', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Title', 'handzom-ui-kit'),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'description',
			[
				'label' => esc_html__('Description', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('Add your description here.', 'handzom-ui-kit'),
				'show_label' => false,
			]
		);

		$this->add_control(
			'cards',
			[
				'label' => esc_html__('Philosophy Cards', 'handzom-ui-kit'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'number' => '01',
						'heading' => 'Less.',
						'description' => 'We create fewer pieces and make each one count.',
					],
					[
						'number' => '02',
						'heading' => 'Better.',
						'description' => 'Better fabrics. Better construction. Better for your everyday.',
					],
					[
						'number' => '03',
						'heading' => 'Timeless.',
						'description' => 'Designed beyond trends so you can wear them year after year.',
					],
					[
						'number' => '04',
						'heading' => 'Honest.',
						'description' => 'We choose natural materials with integrity and respect.',
					],
					[
						'number' => '05',
						'heading' => 'Considered.',
						'description' => 'Every detail is considered with purpose and care.',
					],
				],
				'title_field' => '{{{ number }}} - {{{ heading }}}',
			]
		);

		$this->end_controls_section();
        
        /* Layout Options */
        $this->start_controls_section(
			'layout_section',
			[
				'label' => esc_html__('Layout', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
        
        $this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__('Columns', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => '5',
				'tablet_default' => '3',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
                'selectors' => [
                    '{{WRAPPER}} .hz-op-cards-container' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
			]
		);
        
        $this->add_control(
			'show_vertical_dividers',
			[
				'label' => esc_html__('Show Vertical Dividers', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
                'return_value' => 'yes',
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
					'{{WRAPPER}} .hz-op-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'section_title_typography',
				'selector' => '{{WRAPPER}} .hz-op-title',
			]
		);

		$this->add_responsive_control(
			'section_title_margin',
			[
				'label' => esc_html__('Margin', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .hz-op-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
        
        $this->add_control(
			'title_divider_color',
			[
				'label' => esc_html__('Title Divider Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-op-divider-top' => 'background-color: {{VALUE}};',
				],
                'condition' => [
                    'show_title_divider' => 'yes',
                ],
			]
		);

		$this->end_controls_section();

		/* Style Section - Cards */
		$this->start_controls_section(
			'style_cards_section',
			[
				'label' => esc_html__('Cards Container', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
        
        $this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_bg',
				'label' => esc_html__('Background', 'handzom-ui-kit'),
				'types' => ['classic', 'gradient'],
				'selector' => '{{WRAPPER}} .hz-op-wrapper',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__('Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .hz-op-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
        
        $this->add_control(
			'vertical_divider_color',
			[
				'label' => esc_html__('Vertical Divider Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-op-card' => 'border-right-color: {{VALUE}};',
				],
                'condition' => [
                    'show_vertical_dividers' => 'yes',
                ],
			]
		);

		$this->end_controls_section();

		/* Style Section - Typography */
		$this->start_controls_section(
			'style_content_section',
			[
				'label' => esc_html__('Card Typography', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'number_color',
			[
				'label' => esc_html__('Number Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-op-number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'number_typography',
				'selector' => '{{WRAPPER}} .hz-op-number',
			]
		);
        
        $this->add_responsive_control(
			'number_margin',
			[
				'label' => esc_html__('Number Margin', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .hz-op-number' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => esc_html__('Heading Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-op-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'heading_typography',
				'selector' => '{{WRAPPER}} .hz-op-heading',
			]
		);
        
        $this->add_responsive_control(
			'heading_margin',
			[
				'label' => esc_html__('Heading Margin', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .hz-op-heading' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label' => esc_html__('Description Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-op-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'desc_typography',
				'selector' => '{{WRAPPER}} .hz-op-desc',
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
        $show_dividers = 'yes' === $settings['show_vertical_dividers'] ? 'has-dividers' : 'no-dividers';
		?>
		<div class="hz-op-wrapper">
			<div class="hz-op-header">
				<?php if (!empty($settings['section_title'])): ?>
					<h2 class="hz-op-title"><?php echo esc_html($settings['section_title']); ?></h2>
				<?php endif; ?>
                
                <?php if ('yes' === $settings['show_title_divider']): ?>
                    <div class="hz-op-divider-top"></div>
                <?php endif; ?>
			</div>

			<?php if (!empty($settings['cards'])): ?>
				<div class="hz-op-cards-container <?php echo esc_attr($show_dividers); ?>">
					<?php foreach ($settings['cards'] as $index => $item): ?>
						<div class="hz-op-card elementor-repeater-item-<?php echo esc_attr($item['_id']); ?>">
							<?php if (!empty($item['number'])): ?>
								<div class="hz-op-number"><?php echo esc_html($item['number']); ?></div>
							<?php endif; ?>

							<?php if (!empty($item['heading'])): ?>
								<h3 class="hz-op-heading"><?php echo esc_html($item['heading']); ?></h3>
							<?php endif; ?>

							<?php if (!empty($item['description'])): ?>
								<p class="hz-op-desc"><?php echo wp_kses_post($item['description']); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
