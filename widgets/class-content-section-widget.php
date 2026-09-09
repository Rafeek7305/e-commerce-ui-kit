<?php
namespace HandzomUIKit\Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

class Content_Section_Widget extends Widget_Base
{
    public function get_name()
    {
        return 'handzom_content_section';
    }

    public function get_title()
    {
        return esc_html__('Handzom Content Section', 'handzom-ui-kit');
    }

    public function get_icon()
    {
        return 'eicon-text-area';
    }

    public function get_categories()
    {
        return ['handzom-ui-kit'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content Blocks', 'handzom-ui-kit'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'page_title',
            [
                'label' => esc_html__('Page Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('PAGE TITLE', 'handzom-ui-kit'),
                'label_block' => true,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'block_type',
            [
                'label' => esc_html__('Block Type', 'handzom-ui-kit'),
                'type' => Controls_Manager::SELECT,
                'default' => 'subtitle_text',
                'options' => [
                    'section_title' => esc_html__('Section Title', 'handzom-ui-kit'),
                    'subtitle_text' => esc_html__('Subtitle + Text', 'handzom-ui-kit'),
                    'subtitle_bullets' => esc_html__('Subtitle + Bullet Points', 'handzom-ui-kit'),
                    'subtitle_numbers' => esc_html__('Subtitle + Numbered Points', 'handzom-ui-kit'),
                ],
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Title goes here', 'handzom-ui-kit'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'description',
            [
                'label' => esc_html__('Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'rows' => 5,
                'default' => esc_html__('Description goes here...', 'handzom-ui-kit'),
                'condition' => [
                    'block_type' => 'subtitle_text',
                ],
            ]
        );

        $repeater->add_control(
            'points',
            [
                'label' => esc_html__('Points (Enter one per line)', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'rows' => 8,
                'default' => "Point one\nPoint two\nPoint three",
                'description' => esc_html__('Type each point on a new line.', 'handzom-ui-kit'),
                'condition' => [
                    'block_type' => ['subtitle_bullets', 'subtitle_numbers'],
                ],
            ]
        );

        $this->add_control(
            'content_blocks',
            [
                'label' => esc_html__('Content Blocks', 'handzom-ui-kit'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'block_type' => 'section_title',
                        'title' => esc_html__('SECTION 1', 'handzom-ui-kit'),
                    ],
                    [
                        'block_type' => 'subtitle_text',
                        'title' => esc_html__('SUB TITLE', 'handzom-ui-kit'),
                        'description' => esc_html__('Description text goes here.', 'handzom-ui-kit'),
                    ],
                    [
                        'block_type' => 'subtitle_bullets',
                        'title' => esc_html__('SUB TITLE 2', 'handzom-ui-kit'),
                        'points' => "Point one\nPoint two\nPoint three",
                    ],
                ],
                'title_field' => '{{{ title }}} ({{{ block_type }}})',
            ]
        );

        $this->end_controls_section();

        // --------------------------------------------------
        // STYLE TAB
        // --------------------------------------------------

        // Page Title Style
        $this->start_controls_section(
            'style_page_title',
            [
                'label' => esc_html__('Page Title', 'handzom-ui-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'page_title_color',
            [
                'label' => esc_html__('Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-content-section .content-page-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'page_title_typography',
                'selector' => '{{WRAPPER}} .handzom-content-section .content-page-title',
            ]
        );

        $this->end_controls_section();

        // Section Title Style
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
                'label' => esc_html__('Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-content-section .content-section-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'section_title_typography',
                'selector' => '{{WRAPPER}} .handzom-content-section .content-section-title',
            ]
        );

        $this->add_responsive_control(
            'section_title_margin',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-content-section .content-section-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Subtitle Style
        $this->start_controls_section(
            'style_subtitle',
            [
                'label' => esc_html__('Subtitle', 'handzom-ui-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'subtitle_color',
            [
                'label' => esc_html__('Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-content-section .content-subtitle' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_typography',
                'selector' => '{{WRAPPER}} .handzom-content-section .content-subtitle',
            ]
        );

        $this->add_responsive_control(
            'subtitle_margin',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-content-section .content-subtitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Description / Points Style
        $this->start_controls_section(
            'style_description',
            [
                'label' => esc_html__('Description & Lists', 'handzom-ui-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => esc_html__('Text Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-content-section .content-description' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-content-section ul.content-bullet-points' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-content-section ol.content-numbered-points' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-content-section ul.content-bullet-points li' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-content-section ol.content-numbered-points li' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .handzom-content-section .content-description, {{WRAPPER}} .handzom-content-section ul.content-bullet-points, {{WRAPPER}} .handzom-content-section ol.content-numbered-points',
            ]
        );

        $this->add_responsive_control(
            'description_margin',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-content-section .content-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .handzom-content-section ul.content-bullet-points' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .handzom-content-section ol.content-numbered-points' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="handzom-content-section">
            <?php if (!empty($settings['page_title'])): ?>
                <h1 class="content-page-title"><?php echo esc_html($settings['page_title']); ?></h1>
                <hr class="content-divider">
            <?php endif; ?>

            <?php if (!empty($settings['content_blocks'])): ?>
                <?php 
                $is_in_section = false;
                foreach ($settings['content_blocks'] as $block): 
                ?>
                    
                    <?php if ($block['block_type'] === 'section_title'): ?>
                        <?php 
                        if ($is_in_section) echo '</div>'; // Close previous section
                        $is_in_section = true;
                        ?>
                        <div class="content-section">
                        <?php if (!empty($block['title'])): ?>
                            <h2 class="content-section-title"><?php echo esc_html($block['title']); ?></h2>
                        <?php endif; ?>

                    <?php else: ?>
                        <?php if (!$is_in_section): ?>
                            <div class="content-section">
                            <?php $is_in_section = true; ?>
                        <?php endif; ?>

                        <div class="content-subtitle-block">
                            <?php if (!empty($block['title'])): ?>
                                <h3 class="content-subtitle"><?php echo esc_html($block['title']); ?></h3>
                            <?php endif; ?>

                            <?php if ($block['block_type'] === 'subtitle_text' && !empty($block['description'])): ?>
                                <div class="content-description">
                                    <?php echo wp_kses_post(nl2br($block['description'])); ?>
                                </div>
                            <?php elseif (($block['block_type'] === 'subtitle_bullets' || $block['block_type'] === 'subtitle_numbers') && !empty($block['points'])): ?>
                                <?php
                                // Split points by newline
                                $points = array_filter(array_map('trim', explode("\n", $block['points'])));
                                ?>
                                <?php if (count($points) > 0): ?>
                                    <?php if ($block['block_type'] === 'subtitle_bullets'): ?>
                                        <ul class="content-bullet-points">
                                            <?php foreach ($points as $point): ?>
                                                <li><?php echo esc_html($point); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <ol class="content-numbered-points">
                                            <?php foreach ($points as $point): ?>
                                                <li><?php echo esc_html($point); ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php endforeach; ?>
                <?php if ($is_in_section) echo '</div>'; // Close final section ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
