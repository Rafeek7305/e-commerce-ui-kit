<?php
namespace HandzomUIKit\Widgets;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;

/**
 * Article Cards Widget Class
 */
class Article_Cards extends Widget_Base
{
	public function get_name()
	{
		return 'handzom_article_cards';
	}

	public function get_title()
	{
		return esc_html__('Article Cards', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-posts-grid';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-article-cards'];
	}

	protected function register_controls()
	{
		/* Content Section */
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Cards Content', 'handzom-ui-kit'),
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
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'category',
			[
				'label' => esc_html__('Category / Subtitle', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('MATERIALS', 'handzom-ui-kit'),
			]
		);

		$repeater->add_control(
			'title',
			[
				'label' => esc_html__('Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__('Why Fabric Matters More Than Logos', 'handzom-ui-kit'),
			]
		);

		$repeater->add_control(
			'meta_text',
			[
				'label' => esc_html__('Meta Text (e.g. 4 MIN READ)', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('4 MIN READ', 'handzom-ui-kit'),
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => esc_html__('Link', 'handzom-ui-kit'),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__('https://your-link.com', 'handzom-ui-kit'),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->add_control(
			'cards',
			[
				'label' => esc_html__('Article Cards', 'handzom-ui-kit'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'category' => 'MATERIALS',
						'title' => 'Why Fabric Matters More Than Logos',
						'meta_text' => '4 MIN READ',
					],
					[
						'category' => 'STYLE',
						'title' => 'Building a Timeless Wardrobe',
						'meta_text' => '6 MIN READ',
					],
					[
						'category' => 'PHILOSOPHY',
						'title' => 'The Art of Everyday Dressing',
						'meta_text' => '5 MIN READ',
					],
				],
				'title_field' => '{{{ title }}}',
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
				'default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				],
				'selectors' => [
					'{{WRAPPER}} .hz-ac-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				],
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
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}} .hz-ac-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* Style Section - Card */
		$this->start_controls_section(
			'style_card_section',
			[
				'label' => esc_html__('Card Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('tabs_card_style');

		$this->start_controls_tab(
			'tab_card_normal',
			[
				'label' => esc_html__('Normal', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'card_bg',
			[
				'label' => esc_html__('Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#f5f3ef',
				'selectors' => [
					'{{WRAPPER}} .hz-ac-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_shadow',
				'selector' => '{{WRAPPER}} .hz-ac-card',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_card_hover',
			[
				'label' => esc_html__('Hover', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'card_hover_bg',
			[
				'label' => esc_html__('Background Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hz-ac-card:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_hover_shadow',
				'selector' => '{{WRAPPER}} .hz-ac-card:hover',
			]
		);

		$this->add_control(
			'hover_lift',
			[
				'label' => esc_html__('Hover Lift (px)', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .hz-ac-card:hover' => 'transform: translateY(-{{SIZE}}px);',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'card_padding',
			[
				'label' => esc_html__('Content Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'top' => 30,
					'right' => 30,
					'bottom' => 30,
					'left' => 30,
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .hz-ac-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label' => esc_html__('Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .hz-ac-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .hz-ac-image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} 0 0;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'selector' => '{{WRAPPER}} .hz-ac-card',
			]
		);

		$this->end_controls_section();

		/* Style Section - Image */
		$this->start_controls_section(
			'style_image_section',
			[
				'label' => esc_html__('Image Style', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label' => esc_html__('Image Height', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'vh', '%'],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 600,
					],
				],
				'default' => [
					'size' => 280,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .hz-ac-image-wrap' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_hover_zoom',
			[
				'label' => esc_html__('Hover Zoom Effect', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();

		/* Style Section - Typography */
		$this->start_controls_section(
			'style_typography_section',
			[
				'label' => esc_html__('Typography & Colors', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'category_color',
			[
				'label' => esc_html__('Category Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#777777',
				'selectors' => [
					'{{WRAPPER}} .hz-ac-category' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'category_typography',
				'label' => esc_html__('Category Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-ac-category',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__('Title Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#111111',
				'selectors' => [
					'{{WRAPPER}} .hz-ac-title' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__('Title Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-ac-title',
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => esc_html__('Title Bottom Spacing', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 40,
				],
				'selectors' => [
					'{{WRAPPER}} .hz-ac-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label' => esc_html__('Meta Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#555555',
				'selectors' => [
					'{{WRAPPER}} .hz-ac-meta' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_typography',
				'label' => esc_html__('Meta Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .hz-ac-meta',
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__('Icon Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#111111',
				'selectors' => [
					'{{WRAPPER}} .hz-ac-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$hover_zoom_class = ('yes' === $settings['image_hover_zoom']) ? 'hz-hover-zoom' : '';
		?>
		<div class="hz-ac-wrapper">
			<?php if (!empty($settings['cards'])): ?>
				<div class="hz-ac-grid">
					<?php foreach ($settings['cards'] as $index => $item):
						$link_key = 'link_' . $index;
						if (!empty($item['link']['url'])) {
							$this->add_render_attribute($link_key, 'href', esc_url($item['link']['url']));
							if ($item['link']['is_external']) {
								$this->add_render_attribute($link_key, 'target', '_blank');
							}
							if ($item['link']['nofollow']) {
								$this->add_render_attribute($link_key, 'rel', 'nofollow');
							}
						}
						?>
						<div class="hz-ac-card elementor-repeater-item-<?php echo esc_attr($item['_id']); ?>">
							<?php if (!empty($item['link']['url'])): ?>
								<a <?php $this->print_render_attribute_string($link_key); ?> class="hz-ac-stretched-link"
									aria-label="<?php echo esc_attr($item['title']); ?>"></a>
							<?php endif; ?>

							<div class="hz-ac-image-wrap <?php echo esc_attr($hover_zoom_class); ?>">
								<?php if (!empty($item['image']['url'])): ?>
									<img src="<?php echo esc_url($item['image']['url']); ?>" alt="<?php echo esc_attr($item['title']); ?>">
								<?php endif; ?>
							</div>

							<div class="hz-ac-content">
								<?php if (!empty($item['category'])): ?>
									<div class="hz-ac-category"><?php echo esc_html($item['category']); ?></div>
								<?php endif; ?>

								<?php if (!empty($item['title'])): ?>
									<h3 class="hz-ac-title"><?php echo nl2br(esc_html($item['title'])); ?></h3>
								<?php endif; ?>

								<div class="hz-ac-footer">
									<?php if (!empty($item['meta_text'])): ?>
										<span class="hz-ac-meta"><?php echo esc_html($item['meta_text']); ?></span>
									<?php endif; ?>
									<span class="hz-ac-icon">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
											stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
											<line x1="5" y1="12" x2="19" y2="12"></line>
											<polyline points="12 5 19 12 12 19"></polyline>
										</svg>
									</span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
