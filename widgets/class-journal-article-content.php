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
use Elementor\Icons_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Journal_Article_Content extends Widget_Base
{

    public function get_name()
    {
        return 'handzom_journal_article_content';
    }

    public function get_title()
    {
        return esc_html__('Journal Article Content', 'handzom-ui-kit');
    }

    public function get_icon()
    {
        return 'eicon-document-file';
    }

    public function get_categories()
    {
        return ['handzom-ui-kit'];
    }

    public function get_keywords()
    {
        return ['journal', 'article', 'content', 'editorial', 'handzom'];
    }

    public function get_style_depends()
    {
        return ['handzom-journal-article-content'];
    }

    protected function register_controls()
    {

        // ==========================================
        // CONTENT TAB: BLOCKS
        // ==========================================
        $this->start_controls_section(
            'section_content_blocks',
            [
                'label' => esc_html__('Content Blocks', 'handzom-ui-kit'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'block_type',
            [
                'label' => esc_html__('Block Type', 'handzom-ui-kit'),
                'type' => Controls_Manager::SELECT,
                'default' => 'intro',
                'options' => [
                    'intro' => esc_html__('Introduction', 'handzom-ui-kit'),
                    'text_image' => esc_html__('Text + Image', 'handzom-ui-kit'),
                    'feature_grid' => esc_html__('Feature Grid (Image + Features)', 'handzom-ui-kit'),
                    'process_steps' => esc_html__('Process Steps (4 Cards)', 'handzom-ui-kit'),
                    'icon_features' => esc_html__('Icon Features Grid', 'handzom-ui-kit'),
                    'table' => esc_html__('Table', 'handzom-ui-kit'),
                    'table_image' => esc_html__('Table + Image', 'handzom-ui-kit'),
                    'comparison' => esc_html__('Comparison', 'handzom-ui-kit'),
                    'text_only' => esc_html__('Text Only', 'handzom-ui-kit'),
                    'image_only' => esc_html__('Image Only', 'handzom-ui-kit'),
                    'dual_features' => esc_html__('Dual Features (Fabric & Colours)', 'handzom-ui-kit'),
                    'explore_banner' => esc_html__('Explore Banner (CTA)', 'handzom-ui-kit'),
                    'choice_cards' => esc_html__('Choice Cards (3 Columns)', 'handzom-ui-kit'),
                    'list_and_note' => esc_html__('List & Note (2 Columns)', 'handzom-ui-kit'),
                    'triple_section' => esc_html__('Triple Section (Features + Image + Text)', 'handzom-ui-kit'),
                    'final_thought' => esc_html__('Final Thought', 'handzom-ui-kit'),
                ],
            ]
        );

        // --- Introduction Block Fields ---
        $repeater->add_control(
            'intro_block_number',
            [
                'label' => esc_html__('Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '01',
                'condition' => ['block_type' => 'intro'],
            ]
        );

        $repeater->add_control(
            'intro_section_label',
            [
                'label' => esc_html__('Section Label', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'INTRODUCTION',
                'condition' => ['block_type' => 'intro'],
            ]
        );

        $repeater->add_control(
            'intro_main_heading',
            [
                'label' => esc_html__('Main Heading', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => "What we wear\nbegins with what\ntouches the skin.",
                'description' => 'Use new lines for multiple heading lines.',
                'condition' => ['block_type' => 'intro'],
            ]
        );

        $repeater->add_control(
            'intro_description',
            [
                'label' => esc_html__('Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::WYSIWYG,
                'default' => '<p>Add your description paragraphs here.</p>',
                'condition' => ['block_type' => 'intro'],
            ]
        );

        // --- Text + Image Block Fields ---
        $repeater->add_control(
            'ti_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'What Is a Natural Fabric?',
                'condition' => ['block_type' => 'text_image'],
            ]
        );

        $repeater->add_control(
            'ti_description',
            [
                'label' => esc_html__('Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::WYSIWYG,
                'default' => '<p>Common natural fabrics include:</p><ul><li>Plant-based: Cotton, Linen (Flax), Hemp, Jute</li><li>Animal-based: Wool, Silk, Cashmere</li></ul>',
                'condition' => ['block_type' => 'text_image'],
            ]
        );

        $repeater->add_control(
            'ti_image',
            [
                'label' => esc_html__('Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => ['block_type' => 'text_image'],
            ]
        );

        $repeater->add_control(
            'ti_image_position',
            [
                'label' => esc_html__('Image Position', 'handzom-ui-kit'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'handzom-ui-kit'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'handzom-ui-kit'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'right',
                'toggle' => false,
                'condition' => ['block_type' => 'text_image'],
            ]
        );

        // --- Image + Text Block Fields ---
        $repeater->add_control(
            'it_image',
            [
                'label' => esc_html__('Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => ['block_type' => 'feature_grid'],
            ]
        );

        $repeater->add_control(
            'it_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Why Natural Fabrics Matter',
                'condition' => ['block_type' => 'feature_grid'],
            ]
        );

        $repeater->add_control(
            'it_description',
            [
                'label' => esc_html__('Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::WYSIWYG,
                'default' => '<p>Natural fabrics offer more than just comfort...</p>',
                'condition' => ['block_type' => 'feature_grid'],
            ]
        );

        $repeater->add_control(
            'it_image_width',
            [
                'label' => esc_html__('Image Area Width (%)', 'handzom-ui-kit'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 20,
                        'max' => 80,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 50,
                ],
                'condition' => ['block_type' => 'feature_grid'],
            ]
        );

        // Simulated Repeater for Image + Text Features (Up to 6 features)
        for ($i = 1; $i <= 6; $i++) {
            $repeater->add_control(
                "it_feature_{$i}_title",
                [
                    'label' => sprintf(esc_html__('Feature %d Title', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::TEXT,
                    'condition' => ['block_type' => 'feature_grid'],
                    'separator' => $i === 1 ? 'before' : '',
                ]
            );
            $repeater->add_control(
                "it_feature_{$i}_desc",
                [
                    'label' => sprintf(esc_html__('Feature %d Description', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::TEXTAREA,
                    'condition' => ['block_type' => 'feature_grid'],
                ]
            );
            $repeater->add_control(
                "it_feature_{$i}_icon",
                [
                    'label' => sprintf(esc_html__('Feature %d Icon', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::ICONS,
                    'condition' => ['block_type' => 'feature_grid'],
                ]
            );
        }

        // --- Process Steps Block Fields ---
        $repeater->add_control(
            'ps_block_number',
            [
                'label' => esc_html__('Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '02',
                'condition' => ['block_type' => 'process_steps'],
            ]
        );

        $repeater->add_control(
            'ps_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'FROM FLAX TO LINEN',
                'condition' => ['block_type' => 'process_steps'],
            ]
        );

        $repeater->add_control(
            'ps_subtitle',
            [
                'label' => esc_html__('Subtitle', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'A journey from field to fabric.',
                'condition' => ['block_type' => 'process_steps'],
            ]
        );

        for ($i = 1; $i <= 4; $i++) {
            $repeater->add_control(
                "ps_step_{$i}_heading",
                [
                    'label' => sprintf(esc_html__('Step %d', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before',
                    'condition' => ['block_type' => 'process_steps'],
                ]
            );

            $repeater->add_control(
                "ps_step_{$i}_image",
                [
                    'label' => esc_html__('Image', 'handzom-ui-kit'),
                    'type' => Controls_Manager::MEDIA,
                    'default' => [
                        'url' => Utils::get_placeholder_image_src(),
                    ],
                    'condition' => ['block_type' => 'process_steps'],
                ]
            );

            $repeater->add_control(
                "ps_step_{$i}_number",
                [
                    'label' => esc_html__('Number', 'handzom-ui-kit'),
                    'type' => Controls_Manager::TEXT,
                    'default' => '0' . $i,
                    'condition' => ['block_type' => 'process_steps'],
                ]
            );

            $repeater->add_control(
                "ps_step_{$i}_title",
                [
                    'label' => esc_html__('Title', 'handzom-ui-kit'),
                    'type' => Controls_Manager::TEXT,
                    'default' => 'STEP TITLE',
                    'condition' => ['block_type' => 'process_steps'],
                ]
            );

            $repeater->add_control(
                "ps_step_{$i}_desc",
                [
                    'label' => esc_html__('Description', 'handzom-ui-kit'),
                    'type' => Controls_Manager::TEXTAREA,
                    'default' => 'Description for this step goes here.',
                    'condition' => ['block_type' => 'process_steps'],
                ]
            );
        }

        // --- Icon Features Block Fields ---
        $repeater->add_control(
            'if_block_number',
            [
                'label' => esc_html__('Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '03',
                'condition' => ['block_type' => 'icon_features'],
            ]
        );

        $repeater->add_control(
            'if_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'WHY LINEN IS PERFECT FOR WARM WEATHER',
                'condition' => ['block_type' => 'icon_features'],
            ]
        );

        for ($i = 1; $i <= 4; $i++) {
            $repeater->add_control(
                "if_feature_{$i}_heading",
                [
                    'label' => sprintf(esc_html__('Feature %d', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before',
                    'condition' => ['block_type' => 'icon_features'],
                ]
            );

            $repeater->add_control(
                "if_feature_{$i}_icon",
                [
                    'label' => esc_html__('Icon', 'handzom-ui-kit'),
                    'type' => Controls_Manager::ICONS,
                    'condition' => ['block_type' => 'icon_features'],
                ]
            );

            $repeater->add_control(
                "if_feature_{$i}_title",
                [
                    'label' => esc_html__('Title', 'handzom-ui-kit'),
                    'type' => Controls_Manager::TEXT,
                    'default' => 'Feature Title',
                    'condition' => ['block_type' => 'icon_features'],
                ]
            );

            $repeater->add_control(
                "if_feature_{$i}_desc",
                [
                    'label' => esc_html__('Description', 'handzom-ui-kit'),
                    'type' => Controls_Manager::TEXTAREA,
                    'default' => 'Feature description goes here.',
                    'condition' => ['block_type' => 'icon_features'],
                ]
            );
        }

        // --- Table Block Fields ---
        $repeater->add_control(
            'tb_title',
            [
                'label' => esc_html__('Table Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Popular Natural Fabrics for Men',
                'condition' => ['block_type' => 'table'],
            ]
        );

        $repeater->add_control(
            'tb_description',
            [
                'label' => esc_html__('Table Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::WYSIWYG,
                'default' => '<p>Here is a breakdown of common fabrics.</p>',
                'condition' => ['block_type' => 'table'],
            ]
        );

        for ($i = 1; $i <= 5; $i++) {
            $repeater->add_control(
                "tb_col_{$i}_name",
                [
                    'label' => sprintf(esc_html__('Column %d Name', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::TEXT,
                    'condition' => ['block_type' => 'table'],
                    'separator' => $i === 1 ? 'before' : '',
                ]
            );
        }

        for ($r = 1; $r <= 10; $r++) {
            $heading_condition = ['block_type' => 'table'];
            if ($r > 1) {
                $prev_row = $r - 1;
                $heading_condition["tb_row_{$prev_row}_col_1!"] = '';
            }

            $repeater->add_control(
                "tb_row_{$r}_heading",
                [
                    'label' => sprintf(esc_html__('--- ROW %d ---', 'handzom-ui-kit'), $r),
                    'type' => Controls_Manager::HEADING,
                    'condition' => $heading_condition,
                    'separator' => 'before',
                ]
            );

            for ($c = 1; $c <= 5; $c++) {
                $condition = ['block_type' => 'table'];

                // Hide if previous row is empty
                if ($r > 1) {
                    $prev_row = $r - 1;
                    $condition["tb_row_{$prev_row}_col_1!"] = '';
                }

                // Hide if previous column in this row is empty
                if ($c > 1) {
                    $prev = $c - 1;
                    $condition["tb_row_{$r}_col_{$prev}!"] = '';
                }

                $repeater->add_control(
                    "tb_row_{$r}_col_{$c}",
                    [
                        'label' => sprintf(esc_html__('Column %d Value', 'handzom-ui-kit'), $c),
                        'type' => Controls_Manager::TEXT,
                        'condition' => $condition,
                    ]
                );
            }
        }

        // --- Table + Image Block Fields ---
        $repeater->add_control(
            'tblimg_block_number',
            [
                'label' => esc_html__('Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '04',
                'condition' => ['block_type' => 'table_image'],
            ]
        );

        $repeater->add_control(
            'tblimg_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'LINEN VS COTTON AT A GLANCE',
                'condition' => ['block_type' => 'table_image'],
            ]
        );

        $repeater->add_control(
            'tblimg_image',
            [
                'label' => esc_html__('Side Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => ['block_type' => 'table_image'],
            ]
        );

        $tblimg_cols = new Repeater();
        $tblimg_cols->add_control(
            'col_name',
            [
                'label' => esc_html__('Column Name', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'COLUMN',
            ]
        );

        $repeater->add_control(
            'tblimg_columns',
            [
                'label' => esc_html__('Table Columns', 'handzom-ui-kit'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $tblimg_cols->get_controls(),
                'title_field' => '{{{ col_name }}}',
                'condition' => ['block_type' => 'table_image'],
                'default' => [
                    ['col_name' => 'FEATURE'],
                    ['col_name' => 'COTTON'],
                    ['col_name' => 'LINEN'],
                ],
            ]
        );

        $tblimg_rows = new Repeater();
        for ($c = 1; $c <= 6; $c++) {
            $tblimg_rows->add_control(
                "col_{$c}_value",
                [
                    'label' => sprintf(esc_html__('Column %d Value', 'handzom-ui-kit'), $c),
                    'type' => Controls_Manager::TEXT,
                ]
            );
        }

        $repeater->add_control(
            'tblimg_rows',
            [
                'label' => esc_html__('Table Rows', 'handzom-ui-kit'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $tblimg_rows->get_controls(),
                'title_field' => 'Row (Col 1: {{{ col_1_value }}}) ',
                'condition' => ['block_type' => 'table_image'],
            ]
        );

        // --- Dual Features Block Fields ---
        $repeater->add_control(
            'df_left_number',
            [
                'label' => esc_html__('Left Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '05',
                'condition' => ['block_type' => 'dual_features'],
            ]
        );

        $repeater->add_control(
            'df_left_title',
            [
                'label' => esc_html__('Left Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'LINEN FABRIC WEIGHTS',
                'condition' => ['block_type' => 'dual_features'],
            ]
        );

        $repeater->add_control(
            'df_left_subtitle',
            [
                'label' => esc_html__('Left Subtitle', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Linen comes in different weights. Choose what suits your climate and lifestyle.',
                'condition' => ['block_type' => 'dual_features'],
            ]
        );

        for ($i = 1; $i <= 3; $i++) {
            $repeater->add_control(
                "df_left_card_{$i}_heading",
                [
                    'label' => sprintf(esc_html__('Left Card %d', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before',
                    'condition' => ['block_type' => 'dual_features'],
                ]
            );

            $repeater->add_control(
                "df_left_card_{$i}_image",
                [
                    'label' => esc_html__('Image', 'handzom-ui-kit'),
                    'type' => Controls_Manager::MEDIA,
                    'default' => [
                        'url' => Utils::get_placeholder_image_src(),
                    ],
                    'condition' => ['block_type' => 'dual_features'],
                ]
            );

            $repeater->add_control(
                "df_left_card_{$i}_title",
                [
                    'label' => esc_html__('Title', 'handzom-ui-kit'),
                    'type' => Controls_Manager::TEXT,
                    'default' => 'LIGHTWEIGHT',
                    'condition' => ['block_type' => 'dual_features'],
                ]
            );

            $repeater->add_control(
                "df_left_card_{$i}_desc",
                [
                    'label' => esc_html__('Description', 'handzom-ui-kit'),
                    'type' => Controls_Manager::TEXTAREA,
                    'default' => 'Best for peak summer, vacations and maximum breathability.',
                    'condition' => ['block_type' => 'dual_features'],
                ]
            );
        }

        $repeater->add_control(
            'df_right_number',
            [
                'label' => esc_html__('Right Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '06',
                'condition' => ['block_type' => 'dual_features'],
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'df_right_title',
            [
                'label' => esc_html__('Right Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'BEST LINEN COLOURS',
                'condition' => ['block_type' => 'dual_features'],
            ]
        );

        $repeater->add_control(
            'df_right_subtitle',
            [
                'label' => esc_html__('Right Subtitle', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Natural tones highlight the true beauty of linen.',
                'condition' => ['block_type' => 'dual_features'],
            ]
        );

        for ($i = 1; $i <= 6; $i++) {
            $repeater->add_control(
                "df_right_icon_{$i}_heading",
                [
                    'label' => sprintf(esc_html__('Right Icon %d', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::HEADING,
                    'separator' => 'before',
                    'condition' => ['block_type' => 'dual_features'],
                ]
            );

            $repeater->add_control(
                "df_right_icon_{$i}_color",
                [
                    'label' => esc_html__('Icon Background Color', 'handzom-ui-kit'),
                    'type' => Controls_Manager::COLOR,
                    'default' => '#e6e4df',
                    'condition' => ['block_type' => 'dual_features'],
                ]
            );

            $repeater->add_control(
                "df_right_icon_{$i}_title",
                [
                    'label' => esc_html__('Title', 'handzom-ui-kit'),
                    'type' => Controls_Manager::TEXT,
                    'default' => 'Ecru',
                    'condition' => ['block_type' => 'dual_features'],
                ]
            );
        }

        // --- Explore Banner Block Fields ---
        $repeater->add_control(
            'eb_block_number',
            [
                'label' => esc_html__('Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '07',
                'condition' => ['block_type' => 'explore_banner'],
            ]
        );

        $repeater->add_control(
            'eb_subtitle',
            [
                'label' => esc_html__('Subtitle', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'EXPLORE HANDZOM',
                'condition' => ['block_type' => 'explore_banner'],
            ]
        );

        $repeater->add_control(
            'eb_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'NATURAL FABRIC SHIRTS',
                'condition' => ['block_type' => 'explore_banner'],
            ]
        );

        $repeater->add_control(
            'eb_description',
            [
                'label' => esc_html__('Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Thoughtfully designed. Carefully made. Timeless pieces that get better with every wear.',
                'condition' => ['block_type' => 'explore_banner'],
            ]
        );

        $repeater->add_control(
            'eb_button_text',
            [
                'label' => esc_html__('Button Text', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'VIEW SHIRTS',
                'condition' => ['block_type' => 'explore_banner'],
            ]
        );

        $repeater->add_control(
            'eb_button_link',
            [
                'label' => esc_html__('Button Link', 'handzom-ui-kit'),
                'type' => Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'handzom-ui-kit'),
                'default' => [
                    'url' => '#',
                ],
                'condition' => ['block_type' => 'explore_banner'],
            ]
        );

        $repeater->add_control(
            'eb_image',
            [
                'label' => esc_html__('Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => ['block_type' => 'explore_banner'],
            ]
        );

        // --- Choice Cards Block Fields ---
        $repeater->add_control(
            'cc_block_number',
            [
                'label' => esc_html__('Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '05',
                'condition' => ['block_type' => 'choice_cards'],
            ]
        );

        $repeater->add_control(
            'cc_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'WHICH SHOULD YOU CHOOSE?',
                'condition' => ['block_type' => 'choice_cards'],
            ]
        );

        for ($i = 1; $i <= 3; $i++) {
            $repeater->add_control(
                "cc_card_{$i}_icon",
                [
                    'label' => sprintf(esc_html__('Card %d Icon', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::ICONS,
                    'condition' => ['block_type' => 'choice_cards'],
                    'separator' => $i === 1 ? 'before' : '',
                ]
            );
            $repeater->add_control(
                "cc_card_{$i}_title",
                [
                    'label' => sprintf(esc_html__('Card %d Title', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::TEXT,
                    'condition' => ['block_type' => 'choice_cards'],
                ]
            );
            $repeater->add_control(
                "cc_card_{$i}_desc",
                [
                    'label' => sprintf(esc_html__('Card %d Description', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::TEXTAREA,
                    'condition' => ['block_type' => 'choice_cards'],
                ]
            );
        }

        // --- List & Note Block Fields ---
        $repeater->add_control(
            'ln_left_number',
            [
                'label' => esc_html__('Left Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '04',
                'condition' => ['block_type' => 'list_and_note'],
            ]
        );

        $repeater->add_control(
            'ln_left_title',
            [
                'label' => esc_html__('Left Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'GSM: WEIGHT MATTERS, BUT NOT EVERYTHING',
                'condition' => ['block_type' => 'list_and_note'],
            ]
        );

        $ln_items = new Repeater();
        $ln_items->add_control(
            'item_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'LIGHTWEIGHT',
            ]
        );
        $ln_items->add_control(
            'item_subtitle',
            [
                'label' => esc_html__('Subtitle', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '120 - 160 GSM',
            ]
        );
        $ln_items->add_control(
            'item_desc',
            [
                'label' => esc_html__('Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
            ]
        );
        $ln_items->add_control(
            'item_image',
            [
                'label' => esc_html__('Background Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
            ]
        );

        $repeater->add_control(
            'ln_left_items',
            [
                'label' => esc_html__('Left Items', 'handzom-ui-kit'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $ln_items->get_controls(),
                'title_field' => '{{{ item_title }}}',
                'condition' => ['block_type' => 'list_and_note'],
                'default' => [
                    ['item_title' => 'LIGHTWEIGHT', 'item_subtitle' => '120 - 160 GSM'],
                    ['item_title' => 'MEDIUM WEIGHT', 'item_desc' => 'and city life.'],
                    ['item_title' => 'HEAVIER WEIGHT', 'item_subtitle' => '200+ GSM', 'item_desc' => 'Better for cooler months and structure.'],
                ]
            ]
        );

        $repeater->add_control(
            'ln_right_number',
            [
                'label' => esc_html__('Right Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '05',
                'condition' => ['block_type' => 'list_and_note'],
            ]
        );

        $repeater->add_control(
            'ln_right_title',
            [
                'label' => esc_html__('Right Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'REMEMBER',
                'condition' => ['block_type' => 'list_and_note'],
            ]
        );

        $repeater->add_control(
            'ln_right_desc',
            [
                'label' => esc_html__('Right Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'GSM only tells you the weight. Weave, yarn, finishing and fit are equally important.',
                'condition' => ['block_type' => 'list_and_note'],
            ]
        );

        $repeater->add_control(
            'ln_right_image',
            [
                'label' => esc_html__('Right Side Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'condition' => ['block_type' => 'list_and_note'],
            ]
        );

        // --- Triple Section Block Fields ---
        $repeater->add_control(
            'ts_block_number',
            [
                'label' => esc_html__('Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '04',
                'condition' => ['block_type' => 'triple_section'],
            ]
        );

        $repeater->add_control(
            'ts_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'WHICH FABRIC IS BETTER FOR INDIAN WEATHER?',
                'condition' => ['block_type' => 'triple_section'],
            ]
        );

        for ($i = 1; $i <= 5; $i++) {
            $repeater->add_control(
                "ts_feature_{$i}_title",
                [
                    'label' => sprintf(esc_html__('Feature %d Title', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::TEXT,
                    'condition' => ['block_type' => 'triple_section'],
                    'separator' => $i === 1 ? 'before' : '',
                ]
            );
            $repeater->add_control(
                "ts_feature_{$i}_desc",
                [
                    'label' => sprintf(esc_html__('Feature %d Description', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::TEXTAREA,
                    'condition' => ['block_type' => 'triple_section'],
                ]
            );
        }

        $repeater->add_control(
            'ts_middle_image',
            [
                'label' => esc_html__('Middle Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => ['block_type' => 'triple_section'],
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'ts_middle_image_width',
            [
                'label' => esc_html__('Image Width (%)', 'handzom-ui-kit'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 20,
                        'max' => 60,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 40,
                ],
                'condition' => ['block_type' => 'triple_section'],
            ]
        );

        $repeater->add_control(
            'ts_right_subtitle',
            [
                'label' => esc_html__('Right Subtitle', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'OUR TAKE',
                'condition' => ['block_type' => 'triple_section'],
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'ts_right_content',
            [
                'label' => esc_html__('Right Content', 'handzom-ui-kit'),
                'type' => Controls_Manager::WYSIWYG,
                'default' => '<p>For everyday ease, cotton is your go-to.</p><p>For breathable comfort and natural character, linen leads the way.</p><p>Why not have both?</p>',
                'condition' => ['block_type' => 'triple_section'],
            ]
        );

        // --- Final Thought Block Fields ---
        $repeater->add_control(
            'ft_block_number',
            [
                'label' => esc_html__('Block Number', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => '08',
                'condition' => ['block_type' => 'final_thought'],
            ]
        );

        $repeater->add_control(
            'ft_subtitle',
            [
                'label' => esc_html__('Subtitle', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'FINAL THOUGHT',
                'condition' => ['block_type' => 'final_thought'],
            ]
        );

        $repeater->add_control(
            'ft_title',
            [
                'label' => esc_html__('Title (Large Text)', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => "Good linen isn't just about how it looks. It's about how it lives with you.",
                'condition' => ['block_type' => 'final_thought'],
            ]
        );

        $repeater->add_control(
            'ft_description',
            [
                'label' => esc_html__('Description (Right Side Text)', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => "It wrinkles, it softens, it adapts.\nLinen is not perfect—and that's exactly why it's perfect.",
                'condition' => ['block_type' => 'final_thought'],
            ]
        );

        $repeater->add_control(
            'ft_signature_image',
            [
                'label' => esc_html__('Signature Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => ['block_type' => 'final_thought'],
            ]
        );

        $repeater->add_control(
            'ft_signature_text',
            [
                'label' => esc_html__('Signature Subtext', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'WEAR CONFIDENCE',
                'condition' => ['block_type' => 'final_thought'],
            ]
        );

        // --- Comparison Block Fields ---
        $repeater->add_control(
            'cp_title',
            [
                'label' => esc_html__('Block Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Natural vs Synthetic Fabrics',
                'condition' => ['block_type' => 'comparison'],
            ]
        );

        $repeater->add_control(
            'cp_description',
            [
                'label' => esc_html__('Block Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::WYSIWYG,
                'condition' => ['block_type' => 'comparison'],
            ]
        );

        $repeater->add_control(
            'cp_left_title',
            [
                'label' => esc_html__('Left Item Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'NATURAL FABRICS',
                'condition' => ['block_type' => 'comparison'],
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'cp_left_image',
            [
                'label' => esc_html__('Left Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'condition' => ['block_type' => 'comparison'],
            ]
        );

        $repeater->add_control(
            'cp_image_width',
            [
                'label' => esc_html__('Image Area Width (%)', 'handzom-ui-kit'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 20,
                        'max' => 80,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 50,
                ],
                'condition' => ['block_type' => 'comparison'],
            ]
        );

        for ($i = 1; $i <= 6; $i++) {
            $condition = ['block_type' => 'comparison'];
            if ($i > 1) {
                $prev = $i - 1;
                $condition["cp_left_point_{$prev}!"] = '';
            }
            $repeater->add_control(
                "cp_left_point_{$i}",
                [
                    'label' => sprintf(esc_html__('Left Point %d', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::TEXT,
                    'condition' => $condition,
                ]
            );
        }

        $repeater->add_control(
            'cp_right_title',
            [
                'label' => esc_html__('Right Item Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'SYNTHETIC FABRICS',
                'condition' => ['block_type' => 'comparison'],
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'cp_right_image',
            [
                'label' => esc_html__('Right Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'condition' => ['block_type' => 'comparison'],
            ]
        );

        for ($i = 1; $i <= 6; $i++) {
            $condition = ['block_type' => 'comparison'];
            if ($i > 1) {
                $prev = $i - 1;
                $condition["cp_right_point_{$prev}!"] = '';
            }
            $repeater->add_control(
                "cp_right_point_{$i}",
                [
                    'label' => sprintf(esc_html__('Right Point %d', 'handzom-ui-kit'), $i),
                    'type' => Controls_Manager::TEXT,
                    'condition' => $condition,
                ]
            );
        }

        $repeater->add_control(
            'cp_bottom_desc',
            [
                'label' => esc_html__('Bottom Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::WYSIWYG,
                'condition' => ['block_type' => 'comparison'],
                'separator' => 'before',
            ]
        );

        // --- Text Only Block Fields ---
        $repeater->add_control(
            'to_title',
            [
                'label' => esc_html__('Title', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Section Title',
                'condition' => ['block_type' => 'text_only'],
            ]
        );

        $repeater->add_control(
            'to_description',
            [
                'label' => esc_html__('Description', 'handzom-ui-kit'),
                'type' => Controls_Manager::WYSIWYG,
                'default' => '<p>Add your text here.</p>',
                'condition' => ['block_type' => 'text_only'],
            ]
        );

        // --- Image Only Block Fields ---
        $repeater->add_control(
            'io_image',
            [
                'label' => esc_html__('Image', 'handzom-ui-kit'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => ['block_type' => 'image_only'],
            ]
        );

        $repeater->add_control(
            'io_caption',
            [
                'label' => esc_html__('Caption', 'handzom-ui-kit'),
                'type' => Controls_Manager::TEXT,
                'condition' => ['block_type' => 'image_only'],
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
                        'block_type' => 'intro',
                    ],
                ],
                'title_field' => '{{{ block_type.toUpperCase().replace("_", " ") }}}',
            ]
        );

        $this->end_controls_section();


        // ==========================================
        // STYLE TAB: MAIN SECTION
        // ==========================================
        $this->start_controls_section(
            'style_main_section',
            [
                'label' => esc_html__('Main Section', 'handzom-ui-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'section_max_width',
            [
                'label' => esc_html__('Maximum Width', 'handzom-ui-kit'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => 300, 'max' => 2000],
                    '%' => ['min' => 10, 'max' => 100],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1200,
                ],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content' => 'max-width: {{SIZE}}{{UNIT}}; margin-left: auto; margin-right: auto;',
                ],
            ]
        );

        $this->add_control(
            'section_bg_color',
            [
                'label' => esc_html__('Background Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'default' => '#F4F1EB',
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'section_padding',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'block_gap',
            [
                'label' => esc_html__('Block Gap', 'handzom-ui-kit'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 200],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 80,
                ],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content' => 'display: flex; flex-direction: column; gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: TYPOGRAPHY & COLORS
        // ==========================================
        $this->start_controls_section(
            'style_typography',
            [
                'label' => esc_html__('Typography & Colors', 'handzom-ui-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'heading_style_label',
            [
                'label' => esc_html__('Headings', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_headings',
                'label' => esc_html__('Typography', 'handzom-ui-kit'),
                'selector' => '{{WRAPPER}} .handzom-journal-content__heading, {{WRAPPER}} .handzom-journal-content__intro-heading, {{WRAPPER}} .handzom-journal-content__ps-title, {{WRAPPER}} .handzom-journal-content__ps-step-title, {{WRAPPER}} .handzom-journal-content__if-title, {{WRAPPER}} .handzom-journal-content__if-item-title, {{WRAPPER}} .handzom-journal-content__tblimg-title, {{WRAPPER}} .handzom-journal-content__df-title, {{WRAPPER}} .handzom-journal-content__df-card-title, {{WRAPPER}} .handzom-journal-content__eb-title, {{WRAPPER}} .handzom-journal-content__ft-title',
            ]
        );

        $this->add_control(
            'color_headings',
            [
                'label' => esc_html__('Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__heading' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__intro-heading' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ps-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ps-step-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__if-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__if-item-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__tblimg-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__df-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__df-card-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__eb-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ft-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'body_style_label',
            [
                'label' => esc_html__('Body / Paragraphs', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_body',
                'label' => esc_html__('Typography', 'handzom-ui-kit'),
                'selector' => '{{WRAPPER}} .handzom-journal-content, {{WRAPPER}} .handzom-journal-content__description, {{WRAPPER}} .handzom-journal-content__table td, {{WRAPPER}} .handzom-journal-content__cp-list li, {{WRAPPER}} .handzom-journal-content__feature-desc, {{WRAPPER}} .handzom-journal-content__ps-subtitle, {{WRAPPER}} .handzom-journal-content__ps-step-desc, {{WRAPPER}} .handzom-journal-content__if-item-desc, {{WRAPPER}} .handzom-journal-content__df-subtitle, {{WRAPPER}} .handzom-journal-content__df-card-desc, {{WRAPPER}} .handzom-journal-content__eb-desc, {{WRAPPER}} .handzom-journal-content__ft-desc',
            ]
        );

        $this->add_control(
            'color_body',
            [
                'label' => esc_html__('Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__description' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__table td' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__cp-list li' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__feature-desc' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ps-subtitle' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ps-step-desc' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__if-item-desc' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__df-subtitle' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__df-card-desc' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__eb-desc' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ft-desc' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'accent_style_label',
            [
                'label' => esc_html__('Accents & Small Text', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography_accent',
                'label' => esc_html__('Typography', 'handzom-ui-kit'),
                'selector' => '{{WRAPPER}} .handzom-journal-content__intro-label, {{WRAPPER}} .handzom-journal-content__table th, {{WRAPPER}} .handzom-journal-content__cp-title, {{WRAPPER}} .handzom-journal-content__ps-step-num, {{WRAPPER}} .handzom-journal-content__df-icon-title, {{WRAPPER}} .handzom-journal-content__eb-subtitle, {{WRAPPER}} .handzom-journal-content__eb-btn, {{WRAPPER}} .handzom-journal-content__ft-subtitle, {{WRAPPER}} .handzom-journal-content__ft-sig-text',
            ]
        );

        $this->add_control(
            'color_accent',
            [
                'label' => esc_html__('Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__intro-label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__table th' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__cp-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ps-step-num' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__df-icon-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__eb-subtitle' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__eb-btn' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ft-subtitle' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ft-sig-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: SPECIFIC BLOCKS
        // ==========================================
        $this->start_controls_section(
            'style_blocks',
            [
                'label' => esc_html__('Specific Blocks', 'handzom-ui-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'color_intro_number',
            [
                'label' => esc_html__('Big Number Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__intro-num' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ps-num' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__if-num' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__tblimg-num' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__df-num' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__eb-num' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__ft-num' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'color_table_header_bg',
            [
                'label' => esc_html__('Table Header BG', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__table th' => 'background-color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'color_comparison_bg',
            [
                'label' => esc_html__('Comparison Card BG', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__cp-item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'heading_block_bgs',
            [
                'label' => esc_html__('Block Backgrounds', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'bg_color_intro',
            [
                'label' => esc_html__('Introduction BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__intro' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_text_image',
            [
                'label' => esc_html__('Text + Image BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__text-image' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_feature_grid',
            [
                'label' => esc_html__('Feature Grid BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__feature-grid' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_process_steps',
            [
                'label' => esc_html__('Process Steps BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__process-steps' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_icon_features',
            [
                'label' => esc_html__('Icon Features BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__icon-features' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_dual_features',
            [
                'label' => esc_html__('Dual Features BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__dual-features' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_explore_banner',
            [
                'label' => esc_html__('Explore Banner BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__eb-wrap' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_final_thought',
            [
                'label' => esc_html__('Final Thought BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ft-wrap' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'color_feature_icons',
            [
                'label' => esc_html__('Feature Icons Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__feature-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__feature-icon svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__if-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .handzom-journal-content__if-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_table',
            [
                'label' => esc_html__('Table Block BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__table' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_table_image',
            [
                'label' => esc_html__('Table + Image BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__table-image' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_comparison',
            [
                'label' => esc_html__('Comparison Block BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__comparison' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_text_only',
            [
                'label' => esc_html__('Text Only BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__text-only' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color_image_only',
            [
                'label' => esc_html__('Image Only BG Color', 'handzom-ui-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__image-only' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ==========================================
        // STYLE TAB: INDIVIDUAL BLOCK SPACING
        // ==========================================
        $this->start_controls_section(
            'style_block_spacing',
            [
                'label' => esc_html__('Block Spacing (Padding/Margin)', 'handzom-ui-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Intro Block
        $this->add_control(
            'spacing_intro_heading',
            [
                'label' => esc_html__('Introduction Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
            ]
        );
        $this->add_responsive_control(
            'margin_intro',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__intro-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_intro',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__intro-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Text + Image
        $this->add_control(
            'spacing_ti_heading',
            [
                'label' => esc_html__('Text + Image Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_ti',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ti-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_ti',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ti-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Feature Grid
        $this->add_control(
            'spacing_it_heading',
            [
                'label' => esc_html__('Feature Grid Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_it',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__it-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_it',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__it-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Table
        $this->add_control(
            'spacing_table_heading',
            [
                'label' => esc_html__('Table Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_table',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__table-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_table',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__table-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Process Steps
        $this->add_control(
            'spacing_ps_heading',
            [
                'label' => esc_html__('Process Steps Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_ps',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ps-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_ps',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ps-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Icon Features Grid
        $this->add_control(
            'spacing_if_heading',
            [
                'label' => esc_html__('Icon Features Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_if',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__if-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_if',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__if-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Table + Image
        $this->add_control(
            'spacing_tblimg_heading',
            [
                'label' => esc_html__('Table + Image Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_tblimg',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__tblimg-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_tblimg',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__tblimg-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Dual Features
        $this->add_control(
            'spacing_df_heading',
            [
                'label' => esc_html__('Dual Features Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_df',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__df-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_df',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__df-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Comparison
        $this->add_control(
            'spacing_cp_heading',
            [
                'label' => esc_html__('Comparison Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_cp',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__cp-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_cp',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__cp-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Explore Banner
        $this->add_control(
            'spacing_eb_heading',
            [
                'label' => esc_html__('Explore Banner Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_eb',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__eb-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_eb',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__eb-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Choice Cards
        $this->add_control(
            'spacing_cc_heading',
            [
                'label' => esc_html__('Choice Cards Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_cc',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__cc-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_cc',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__cc-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Triple Section
        $this->add_control(
            'spacing_ts_heading',
            [
                'label' => esc_html__('Triple Section Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_ts',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ts-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_ts',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ts-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // List & Note
        $this->add_control(
            'spacing_ln_heading',
            [
                'label' => esc_html__('List & Note Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_ln',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ln-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_ln',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ln-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Final Thought
        $this->add_control(
            'spacing_ft_heading',
            [
                'label' => esc_html__('Final Thought Block', 'handzom-ui-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'margin_ft',
            [
                'label' => esc_html__('Margin', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ft-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'padding_ft',
            [
                'label' => esc_html__('Padding', 'handzom-ui-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .handzom-journal-content__ft-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $blocks = $settings['content_blocks'];

        if (empty($blocks)) {
            return;
        }

        echo '<div class="handzom-journal-content">';

        foreach ($blocks as $block) {
            $type = $block['block_type'];
            echo '<div class="handzom-journal-content__block handzom-journal-content__' . esc_attr(str_replace('_', '-', $type)) . ' elementor-repeater-item-' . esc_attr($block['_id']) . '">';

            switch ($type) {
                case 'intro':
                    $this->render_intro_block($block);
                    break;
                case 'text_image':
                    $this->render_text_image_block($block);
                    break;
                case 'feature_grid':
                    $this->render_image_text_block($block);
                    break;
                case 'process_steps':
                    $this->render_process_steps_block($block);
                    break;
                case 'icon_features':
                    $this->render_icon_features_block($block);
                    break;
                case 'table':
                    $this->render_table_block($block);
                    break;
                case 'table_image':
                    $this->render_table_image_block($block);
                    break;
                case 'comparison':
                    $this->render_comparison_block($block);
                    break;
                case 'dual_features':
                    $this->render_dual_features_block($block);
                    break;
                case 'explore_banner':
                    $this->render_explore_banner_block($block);
                    break;
                case 'choice_cards':
                    $this->render_choice_cards_block($block);
                    break;
                case 'list_and_note':
                    $this->render_list_and_note_block($block);
                    break;
                case 'triple_section':
                    $this->render_triple_section_block($block);
                    break;
                case 'final_thought':
                    $this->render_final_thought_block($block);
                    break;
                case 'text_only':
                    $this->render_text_only_block($block);
                    break;
                case 'image_only':
                    $this->render_image_only_block($block);
                    break;
            }

            echo '</div>'; // End block
        }

        echo '</div>'; // End content
    }

    private function render_intro_block($block)
    {
        ?>
        <div class="handzom-journal-content__intro-wrap">
            <div class="handzom-journal-content__intro-left">
                <?php if (!empty($block['intro_block_number'])): ?>
                    <div class="handzom-journal-content__intro-num-wrap">
                        <span
                            class="handzom-journal-content__intro-num"><?php echo esc_html($block['intro_block_number']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="handzom-journal-content__intro-title-wrap">
                    <?php if (!empty($block['intro_section_label'])): ?>
                        <span
                            class="handzom-journal-content__intro-label"><?php echo esc_html($block['intro_section_label']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($block['intro_main_heading'])): ?>
                        <h2 class="handzom-journal-content__intro-heading">
                            <?php echo nl2br(esc_html($block['intro_main_heading'])); ?>
                        </h2>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($block['intro_description'])): ?>
                <div class="handzom-journal-content__intro-right">
                    <div class="handzom-journal-content__description">
                        <?php echo wp_kses_post($block['intro_description']); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_text_image_block($block)
    {
        $pos = isset($block['ti_image_position']) ? $block['ti_image_position'] : 'right';
        $class_suffix = $pos === 'left' ? ' image-left' : ' image-right';
        ?>
        <div class="handzom-journal-content__ti-wrap<?php echo esc_attr($class_suffix); ?>">
            <div class="handzom-journal-content__ti-content">
                <?php if (!empty($block['ti_title'])): ?>
                    <h3 class="handzom-journal-content__heading"><?php echo esc_html($block['ti_title']); ?></h3>
                <?php endif; ?>
                <?php if (!empty($block['ti_description'])): ?>
                    <div class="handzom-journal-content__description">
                        <?php echo wp_kses_post($block['ti_description']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($block['ti_image']['url'])): ?>
                <div class="handzom-journal-content__ti-image-wrap">
                    <img src="<?php echo esc_url($block['ti_image']['url']); ?>" alt="<?php echo esc_attr($block['ti_title']); ?>"
                        class="handzom-journal-content__image">
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_image_text_block($block)
    {
        $image_width = (isset($block['it_image_width']['size']) && is_numeric($block['it_image_width']['size'])) ? (float) $block['it_image_width']['size'] : 50;
        $text_width = 100 - $image_width;
        ?>
        <div class="handzom-journal-content__it-wrap"
            style="--it-image-width: <?php echo esc_attr($image_width); ?>%; --it-text-width: <?php echo esc_attr($text_width); ?>%;">
            <?php if (!empty($block['it_image']['url'])): ?>
                <div class="handzom-journal-content__it-image-wrap">
                    <img src="<?php echo esc_url($block['it_image']['url']); ?>" alt="<?php echo esc_attr($block['it_title']); ?>"
                        class="handzom-journal-content__image">
                </div>
            <?php endif; ?>
            <div class="handzom-journal-content__it-content">
                <?php if (!empty($block['it_title'])): ?>
                    <h3 class="handzom-journal-content__heading"><?php echo esc_html($block['it_title']); ?></h3>
                <?php endif; ?>
                <?php if (!empty($block['it_description'])): ?>
                    <div class="handzom-journal-content__description">
                        <?php echo wp_kses_post($block['it_description']); ?>
                    </div>
                <?php endif; ?>

                <div class="handzom-journal-content__features-grid">
                    <?php
                    for ($i = 1; $i <= 6; $i++) {
                        if (!empty($block["it_feature_{$i}_title"])) {
                            ?>
                            <div class="handzom-journal-content__feature">
                                <?php if (!empty($block["it_feature_{$i}_icon"]['value'])): ?>
                                    <div class="handzom-journal-content__feature-icon">
                                        <?php Icons_Manager::render_icon($block["it_feature_{$i}_icon"], ['aria-hidden' => 'true']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="handzom-journal-content__feature-content">
                                    <h5 class="handzom-journal-content__feature-title">
                                        <?php echo esc_html($block["it_feature_{$i}_title"]); ?>
                                    </h5>
                                    <?php if (!empty($block["it_feature_{$i}_desc"])): ?>
                                        <div class="handzom-journal-content__feature-desc">
                                            <?php echo esc_html($block["it_feature_{$i}_desc"]); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_icon_features_block($block)
    {
        ?>
        <div class="handzom-journal-content__if-wrap">
            <div class="handzom-journal-content__if-header">
                <div class="handzom-journal-content__if-title-wrap">
                    <?php if (!empty($block['if_block_number'])): ?>
                        <span class="handzom-journal-content__if-num"><?php echo esc_html($block['if_block_number']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($block['if_title'])): ?>
                        <h3 class="handzom-journal-content__if-title"><?php echo esc_html($block['if_title']); ?></h3>
                    <?php endif; ?>
                </div>
            </div>

            <div class="handzom-journal-content__if-grid">
                <?php
                for ($i = 1; $i <= 4; $i++) {
                    if (!empty($block["if_feature_{$i}_title"])) {
                        ?>
                        <div class="handzom-journal-content__if-item">
                            <?php if (!empty($block["if_feature_{$i}_icon"]['value'])): ?>
                                <div class="handzom-journal-content__if-icon">
                                    <?php Icons_Manager::render_icon($block["if_feature_{$i}_icon"], ['aria-hidden' => 'true']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="handzom-journal-content__if-content">
                                <h4 class="handzom-journal-content__if-item-title">
                                    <?php echo esc_html($block["if_feature_{$i}_title"]); ?>
                                </h4>
                                <?php if (!empty($block["if_feature_{$i}_desc"])): ?>
                                    <div class="handzom-journal-content__if-item-desc">
                                        <?php echo nl2br(esc_html($block["if_feature_{$i}_desc"])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }

    private function render_process_steps_block($block)
    {
        ?>
        <div class="handzom-journal-content__ps-wrap">
            <div class="handzom-journal-content__ps-header">
                <div class="handzom-journal-content__ps-title-wrap">
                    <?php if (!empty($block['ps_block_number'])): ?>
                        <span class="handzom-journal-content__ps-num"><?php echo esc_html($block['ps_block_number']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($block['ps_title'])): ?>
                        <h3 class="handzom-journal-content__ps-title"><?php echo esc_html($block['ps_title']); ?></h3>
                    <?php endif; ?>
                </div>
                <?php if (!empty($block['ps_subtitle'])): ?>
                    <p class="handzom-journal-content__ps-subtitle"><?php echo nl2br(esc_html($block['ps_subtitle'])); ?></p>
                <?php endif; ?>
            </div>

            <div class="handzom-journal-content__ps-grid">
                <?php
                $valid_steps = 0;
                for ($i = 1; $i <= 4; $i++) {
                    if (!empty($block["ps_step_{$i}_title"])) {
                        $valid_steps++;
                    }
                }

                $current_step = 0;
                for ($i = 1; $i <= 4; $i++) {
                    if (!empty($block["ps_step_{$i}_title"])) {
                        $current_step++;
                        ?>
                        <div class="handzom-journal-content__ps-step">
                            <div class="handzom-journal-content__ps-image-wrap">
                                <?php if (!empty($block["ps_step_{$i}_image"]['url'])): ?>
                                    <img src="<?php echo esc_url($block["ps_step_{$i}_image"]['url']); ?>"
                                        alt="<?php echo esc_attr($block["ps_step_{$i}_title"]); ?>">
                                <?php endif; ?>
                                <?php if ($current_step < $valid_steps): ?>
                                    <div class="handzom-journal-content__ps-arrow">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="handzom-journal-content__ps-content">
                                <?php if (!empty($block["ps_step_{$i}_number"])): ?>
                                    <span
                                        class="handzom-journal-content__ps-step-num"><?php echo esc_html($block["ps_step_{$i}_number"]); ?></span>
                                <?php endif; ?>
                                <h4 class="handzom-journal-content__ps-step-title"><?php echo esc_html($block["ps_step_{$i}_title"]); ?>
                                </h4>
                                <?php if (!empty($block["ps_step_{$i}_desc"])): ?>
                                    <div class="handzom-journal-content__ps-step-desc">
                                        <?php echo nl2br(esc_html($block["ps_step_{$i}_desc"])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }

    private function render_text_only_block($block)
    {
        ?>
        <div class="handzom-journal-content__to-wrap">
            <?php if (!empty($block['to_title'])): ?>
                <h3 class="handzom-journal-content__heading"><?php echo esc_html($block['to_title']); ?></h3>
            <?php endif; ?>
            <?php if (!empty($block['to_description'])): ?>
                <div class="handzom-journal-content__description">
                    <?php echo wp_kses_post($block['to_description']); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_image_only_block($block)
    {
        if (empty($block['io_image']['url'])) {
            return;
        }
        ?>
        <div class="handzom-journal-content__io-wrap">
            <img src="<?php echo esc_url($block['io_image']['url']); ?>" alt="<?php echo esc_attr($block['io_caption']); ?>"
                class="handzom-journal-content__image">
            <?php if (!empty($block['io_caption'])): ?>
                <span class="handzom-journal-content__image-caption"><?php echo esc_html($block['io_caption']); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }
    private function render_table_block($block)
    {
        ?>
        <div class="handzom-journal-content__table-wrap">
            <?php if (!empty($block['tb_title'])): ?>
                <h3 class="handzom-journal-content__heading"><?php echo esc_html($block['tb_title']); ?></h3>
            <?php endif; ?>
            <?php if (!empty($block['tb_description'])): ?>
                <div class="handzom-journal-content__description">
                    <?php echo wp_kses_post($block['tb_description']); ?>
                </div>
            <?php endif; ?>

            <div class="handzom-journal-content__table-wrapper">
                <table class="handzom-journal-content__table">
                    <thead>
                        <tr>
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if (!empty($block["tb_col_{$i}_name"])) {
                                    echo '<th>' . esc_html($block["tb_col_{$i}_name"]) . '</th>';
                                }
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($r = 1; $r <= 10; $r++) {
                            if (!empty($block["tb_row_{$r}_col_1"])) {
                                echo '<tr>';
                                for ($c = 1; $c <= 5; $c++) {
                                    if (isset($block["tb_row_{$r}_col_{$c}"])) {
                                        echo '<td>' . esc_html(trim($block["tb_row_{$r}_col_{$c}"])) . '</td>';
                                    }
                                }
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function render_table_image_block($block)
    {
        ?>
        <div class="handzom-journal-content__tblimg-wrap">
            <div class="handzom-journal-content__tblimg-header">
                <div class="handzom-journal-content__tblimg-title-wrap">
                    <?php if (!empty($block['tblimg_block_number'])): ?>
                        <span
                            class="handzom-journal-content__tblimg-num"><?php echo esc_html($block['tblimg_block_number']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($block['tblimg_title'])): ?>
                        <h3 class="handzom-journal-content__tblimg-title"><?php echo esc_html($block['tblimg_title']); ?></h3>
                    <?php endif; ?>
                </div>
            </div>

            <div class="handzom-journal-content__tblimg-grid">
                <div class="handzom-journal-content__tblimg-left">
                    <div class="handzom-journal-content__table-wrapper">
                        <table class="handzom-journal-content__table">
                            <thead>
                                <tr>
                                    <?php
                                    if (!empty($block['tblimg_columns'])) {
                                        foreach ($block['tblimg_columns'] as $col) {
                                            if (!empty($col['col_name'])) {
                                                echo '<th>' . esc_html($col['col_name']) . '</th>';
                                            }
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($block['tblimg_rows'])) {
                                    $col_count = !empty($block['tblimg_columns']) ? count($block['tblimg_columns']) : 3;
                                    $col_count = min($col_count, 6);
                                    foreach ($block['tblimg_rows'] as $row) {
                                        echo '<tr>';
                                        for ($c = 1; $c <= $col_count; $c++) {
                                            $val = isset($row["col_{$c}_value"]) ? $row["col_{$c}_value"] : '';
                                            echo '<td>' . esc_html(trim($val)) . '</td>';
                                        }
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (!empty($block['tblimg_image']['url'])): ?>
                    <div class="handzom-journal-content__tblimg-right">
                        <img src="<?php echo esc_url($block['tblimg_image']['url']); ?>"
                            alt="<?php echo esc_attr($block['tblimg_title']); ?>" class="handzom-journal-content__image">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_comparison_block($block)
    {
        $image_width = isset($block['cp_image_width']['size']) ? $block['cp_image_width']['size'] : 50;
        $text_width = 100 - $image_width;

        ?>
        <div class="handzom-journal-content__cp-wrap"
            style="--cp-image-width: <?php echo esc_attr($image_width); ?>%; --cp-text-width: <?php echo esc_attr($text_width); ?>%;">
            <?php if (!empty($block['cp_title'])): ?>
                <h3 class="handzom-journal-content__heading"><?php echo esc_html($block['cp_title']); ?></h3>
            <?php endif; ?>
            <?php if (!empty($block['cp_description'])): ?>
                <div class="handzom-journal-content__description">
                    <?php echo wp_kses_post($block['cp_description']); ?>
                </div>
            <?php endif; ?>

            <div class="handzom-journal-content__cp-grid">
                <div class="handzom-journal-content__cp-item handzom-journal-content__cp-item--left">
                    <div class="handzom-journal-content__cp-text-content">
                        <?php if (!empty($block['cp_left_title'])): ?>
                            <h4 class="handzom-journal-content__cp-title"><?php echo esc_html($block['cp_left_title']); ?></h4>
                        <?php endif; ?>
                        <ul class="handzom-journal-content__cp-list">
                            <?php
                            for ($i = 1; $i <= 6; $i++) {
                                if (!empty($block["cp_left_point_{$i}"])) {
                                    echo '<li><span class="handzom-journal-content__cp-icon">✓</span>' . esc_html($block["cp_left_point_{$i}"]) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <?php if (!empty($block['cp_left_image']['url'])): ?>
                        <div class="handzom-journal-content__cp-image-wrap">
                            <img src="<?php echo esc_url($block['cp_left_image']['url']); ?>" alt="Comparison Left"
                                class="handzom-journal-content__image">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="handzom-journal-content__cp-item handzom-journal-content__cp-item--right">
                    <div class="handzom-journal-content__cp-text-content">
                        <?php if (!empty($block['cp_right_title'])): ?>
                            <h4 class="handzom-journal-content__cp-title"><?php echo esc_html($block['cp_right_title']); ?></h4>
                        <?php endif; ?>
                        <ul class="handzom-journal-content__cp-list">
                            <?php
                            for ($i = 1; $i <= 6; $i++) {
                                if (!empty($block["cp_right_point_{$i}"])) {
                                    echo '<li><span class="handzom-journal-content__cp-icon">×</span>' . esc_html($block["cp_right_point_{$i}"]) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <?php if (!empty($block['cp_right_image']['url'])): ?>
                        <div class="handzom-journal-content__cp-image-wrap">
                            <img src="<?php echo esc_url($block['cp_right_image']['url']); ?>" alt="Comparison Right"
                                class="handzom-journal-content__image">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($block['cp_bottom_desc'])): ?>
                <div class="handzom-journal-content__description handzom-journal-content__cp-bottom-desc">
                    <?php echo wp_kses_post($block['cp_bottom_desc']); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_dual_features_block($block)
    {
        ?>
        <div class="handzom-journal-content__df-wrap">
            <div class="handzom-journal-content__df-grid">

                <!-- Left Side -->
                <div class="handzom-journal-content__df-left">
                    <div class="handzom-journal-content__df-header">
                        <div class="handzom-journal-content__df-title-wrap">
                            <?php if (!empty($block['df_left_number'])): ?>
                                <span
                                    class="handzom-journal-content__df-num"><?php echo esc_html($block['df_left_number']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($block['df_left_title'])): ?>
                                <h3 class="handzom-journal-content__df-title"><?php echo esc_html($block['df_left_title']); ?></h3>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($block['df_left_subtitle'])): ?>
                            <p class="handzom-journal-content__df-subtitle"><?php echo esc_html($block['df_left_subtitle']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="handzom-journal-content__df-cards">
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <?php if (!empty($block["df_left_card_{$i}_title"])): ?>
                                <div class="handzom-journal-content__df-card">
                                    <?php if (!empty($block["df_left_card_{$i}_image"]['url'])): ?>
                                        <div class="handzom-journal-content__df-card-img-wrap">
                                            <img src="<?php echo esc_url($block["df_left_card_{$i}_image"]['url']); ?>"
                                                alt="<?php echo esc_attr($block["df_left_card_{$i}_title"]); ?>"
                                                class="handzom-journal-content__image">
                                        </div>
                                    <?php endif; ?>
                                    <h4 class="handzom-journal-content__df-card-title">
                                        <?php echo esc_html($block["df_left_card_{$i}_title"]); ?>
                                    </h4>
                                    <?php if (!empty($block["df_left_card_{$i}_desc"])): ?>
                                        <p class="handzom-journal-content__df-card-desc">
                                            <?php echo esc_html($block["df_left_card_{$i}_desc"]); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="handzom-journal-content__df-right">
                    <div class="handzom-journal-content__df-header">
                        <div class="handzom-journal-content__df-title-wrap">
                            <?php if (!empty($block['df_right_number'])): ?>
                                <span
                                    class="handzom-journal-content__df-num"><?php echo esc_html($block['df_right_number']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($block['df_right_title'])): ?>
                                <h3 class="handzom-journal-content__df-title"><?php echo esc_html($block['df_right_title']); ?></h3>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($block['df_right_subtitle'])): ?>
                            <p class="handzom-journal-content__df-subtitle"><?php echo esc_html($block['df_right_subtitle']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="handzom-journal-content__df-icons">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <?php if (!empty($block["df_right_icon_{$i}_title"])):
                                $color = !empty($block["df_right_icon_{$i}_color"]) ? $block["df_right_icon_{$i}_color"] : '#e6e4df';
                                ?>
                                <div class="handzom-journal-content__df-icon-item">
                                    <div class="handzom-journal-content__df-icon-circle"
                                        style="background-color: <?php echo esc_attr($color); ?>;"></div>
                                    <span
                                        class="handzom-journal-content__df-icon-title"><?php echo esc_html($block["df_right_icon_{$i}_title"]); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    private function render_explore_banner_block($block)
    {
        $btn_url = !empty($block['eb_button_link']['url']) ? esc_url($block['eb_button_link']['url']) : '#';
        $btn_target = !empty($block['eb_button_link']['is_external']) ? ' target="_blank"' : '';
        $btn_nofollow = !empty($block['eb_button_link']['nofollow']) ? ' rel="nofollow"' : '';
        ?>
        <div class="handzom-journal-content__eb-wrap">
            <div class="handzom-journal-content__eb-grid">

                <!-- Left Content -->
                <div class="handzom-journal-content__eb-content">
                    <div class="handzom-journal-content__eb-subtitle-wrap">
                        <?php if (!empty($block['eb_block_number'])): ?>
                            <span class="handzom-journal-content__eb-num"><?php echo esc_html($block['eb_block_number']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($block['eb_subtitle'])): ?>
                            <span class="handzom-journal-content__eb-subtitle"><?php echo esc_html($block['eb_subtitle']); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($block['eb_title'])): ?>
                        <h2 class="handzom-journal-content__eb-title"><?php echo esc_html($block['eb_title']); ?></h2>
                    <?php endif; ?>

                    <?php if (!empty($block['eb_description'])): ?>
                        <p class="handzom-journal-content__eb-desc"><?php echo nl2br(esc_html($block['eb_description'])); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($block['eb_button_text'])): ?>
                        <a href="<?php echo $btn_url; ?>" <?php echo $btn_target . $btn_nofollow; ?>
                            class="handzom-journal-content__eb-btn">
                            <?php echo esc_html($block['eb_button_text']); ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Right Image -->
                <?php if (!empty($block['eb_image']['url'])): ?>
                    <div class="handzom-journal-content__eb-image-wrap">
                        <img src="<?php echo esc_url($block['eb_image']['url']); ?>"
                            alt="<?php echo esc_attr($block['eb_title']); ?>" class="handzom-journal-content__image">
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <?php
    }

    private function render_choice_cards_block($block)
    {
        ?>
        <div class="handzom-journal-content__cc-wrap">
            <div class="handzom-journal-content__cc-header">
                <?php if (!empty($block['cc_block_number'])): ?>
                    <span class="handzom-journal-content__cc-num"><?php echo esc_html($block['cc_block_number']); ?></span>
                <?php endif; ?>
                <?php if (!empty($block['cc_title'])): ?>
                    <h3 class="handzom-journal-content__cc-title"><?php echo esc_html($block['cc_title']); ?></h3>
                <?php endif; ?>
            </div>
            <div class="handzom-journal-content__cc-grid">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <?php if (!empty($block["cc_card_{$i}_title"]) || !empty($block["cc_card_{$i}_icon"]['value'])): ?>
                        <div class="handzom-journal-content__cc-card">
                            <?php if (!empty($block["cc_card_{$i}_icon"]['value'])): ?>
                                <div class="handzom-journal-content__cc-icon-wrap">
                                    <?php \Elementor\Icons_Manager::render_icon($block["cc_card_{$i}_icon"], ['aria-hidden' => 'true']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="handzom-journal-content__cc-card-content">
                                <?php if (!empty($block["cc_card_{$i}_title"])): ?>
                                    <h4 class="handzom-journal-content__cc-card-title"><?php echo esc_html($block["cc_card_{$i}_title"]); ?></h4>
                                <?php endif; ?>
                                <?php if (!empty($block["cc_card_{$i}_desc"])): ?>
                                    <div class="handzom-journal-content__cc-card-desc">
                                        <?php echo nl2br(esc_html($block["cc_card_{$i}_desc"])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>
        <?php
    }

    private function render_triple_section_block($block)
    {
        ?>
        <div class="handzom-journal-content__ts-wrap">
            <div class="handzom-journal-content__ts-header">
                <?php if (!empty($block['ts_block_number'])): ?>
                    <span class="handzom-journal-content__ts-num"><?php echo esc_html($block['ts_block_number']); ?></span>
                <?php endif; ?>
                <?php if (!empty($block['ts_title'])): ?>
                    <h3 class="handzom-journal-content__ts-title"><?php echo esc_html($block['ts_title']); ?></h3>
                <?php endif; ?>
            </div>
            <div class="handzom-journal-content__ts-grid">
                <!-- Left (Icons) -->
                <div class="handzom-journal-content__ts-left">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if (!empty($block["ts_feature_{$i}_title"])): ?>
                            <div class="handzom-journal-content__ts-feature">
                                <div class="handzom-journal-content__ts-icon-wrap">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path><path d="M12 8v4l3 3"></path></svg>
                                </div>
                                <div class="handzom-journal-content__ts-feature-content">
                                    <h4 class="handzom-journal-content__ts-feature-title"><?php echo esc_html($block["ts_feature_{$i}_title"]); ?></h4>
                                    <?php if (!empty($block["ts_feature_{$i}_desc"])): ?>
                                        <p class="handzom-journal-content__ts-feature-desc"><?php echo esc_html($block["ts_feature_{$i}_desc"]); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <!-- Middle (Image) -->
                <?php 
                $mid_width = !empty($block['ts_middle_image_width']['size']) ? $block['ts_middle_image_width']['size'] : 40;
                ?>
                <div class="handzom-journal-content__ts-middle" style="flex: 0 0 <?php echo esc_attr($mid_width); ?>%;">
                    <?php if (!empty($block['ts_middle_image']['url'])): ?>
                        <div class="handzom-journal-content__ts-image-inner">
                            <img src="<?php echo esc_url($block['ts_middle_image']['url']); ?>" alt="<?php echo esc_attr($block['ts_title']); ?>" class="handzom-journal-content__image">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right (Text) -->
                <div class="handzom-journal-content__ts-right" style="flex: 1;">
                    <div class="handzom-journal-content__ts-right-inner">
                        <?php if (!empty($block['ts_right_subtitle'])): ?>
                            <div class="handzom-journal-content__ts-right-sub-wrap">
                                <span class="handzom-journal-content__ts-right-subtitle"><?php echo esc_html($block['ts_right_subtitle']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($block['ts_right_content'])): ?>
                            <div class="handzom-journal-content__ts-right-content">
                                <?php echo wp_kses_post($block['ts_right_content']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_final_thought_block($block)
    {
        ?>
        <div class="handzom-journal-content__ft-wrap">
            <div class="handzom-journal-content__ft-grid">

                <div class="handzom-journal-content__ft-left">
                    <div class="handzom-journal-content__ft-subtitle-wrap">
                        <?php if (!empty($block['ft_block_number'])): ?>
                            <span class="handzom-journal-content__ft-num"><?php echo esc_html($block['ft_block_number']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($block['ft_subtitle'])): ?>
                            <span class="handzom-journal-content__ft-subtitle"><?php echo esc_html($block['ft_subtitle']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($block['ft_title'])): ?>
                        <h2 class="handzom-journal-content__ft-title"><?php echo nl2br(esc_html($block['ft_title'])); ?></h2>
                    <?php endif; ?>
                </div>

                <div class="handzom-journal-content__ft-right">
                    <?php if (!empty($block['ft_description'])): ?>
                        <p class="handzom-journal-content__ft-desc"><?php echo nl2br(esc_html($block['ft_description'])); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($block['ft_signature_image']['url']) || !empty($block['ft_signature_text'])): ?>
                        <div class="handzom-journal-content__ft-sig-wrap">
                            <?php if (!empty($block['ft_signature_image']['url'])): ?>
                                <img src="<?php echo esc_url($block['ft_signature_image']['url']); ?>" alt="Signature"
                                    class="handzom-journal-content__ft-sig-img">
                            <?php endif; ?>
                            <?php if (!empty($block['ft_signature_text'])): ?>
                                <span
                                    class="handzom-journal-content__ft-sig-text"><?php echo esc_html($block['ft_signature_text']); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php
    }
    private function render_list_and_note_block($block)
    {
        ?>
        <div class="handzom-journal-content__ln-wrap">
            <div class="handzom-journal-content__ln-grid">
                
                <!-- Left Section: List -->
                <div class="handzom-journal-content__ln-left">
                    <div class="handzom-journal-content__ln-header">
                        <?php if (!empty($block['ln_left_number'])): ?>
                            <span class="handzom-journal-content__ln-num"><?php echo esc_html($block['ln_left_number']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($block['ln_left_title'])): ?>
                            <h3 class="handzom-journal-content__ln-title"><?php echo esc_html($block['ln_left_title']); ?></h3>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($block['ln_left_items'])): ?>
                        <div class="handzom-journal-content__ln-list">
                            <?php foreach ($block['ln_left_items'] as $item): ?>
                                <div class="handzom-journal-content__ln-item">
                                    <div class="handzom-journal-content__ln-item-content">
                                        <?php if (!empty($item['item_title'])): ?>
                                            <h4 class="handzom-journal-content__ln-item-title"><?php echo esc_html($item['item_title']); ?></h4>
                                        <?php endif; ?>
                                        <?php if (!empty($item['item_subtitle'])): ?>
                                            <div class="handzom-journal-content__ln-item-subtitle"><?php echo esc_html($item['item_subtitle']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($item['item_desc'])): ?>
                                            <div class="handzom-journal-content__ln-item-desc"><?php echo nl2br(esc_html($item['item_desc'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($item['item_image']['url'])): ?>
                                        <div class="handzom-journal-content__ln-item-image">
                                            <img src="<?php echo esc_url($item['item_image']['url']); ?>" alt="<?php echo esc_attr($item['item_title']); ?>">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Right Section: Note -->
                <div class="handzom-journal-content__ln-right">
                    <div class="handzom-journal-content__ln-header">
                        <?php if (!empty($block['ln_right_number'])): ?>
                            <span class="handzom-journal-content__ln-num"><?php echo esc_html($block['ln_right_number']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="handzom-journal-content__ln-note-wrap">
                        <?php if (!empty($block['ln_right_title'])): ?>
                            <h3 class="handzom-journal-content__ln-title" style="margin-bottom: 20px;"><?php echo esc_html($block['ln_right_title']); ?></h3>
                        <?php endif; ?>
                        
                        <div class="handzom-journal-content__ln-note-body">
                            <?php if (!empty($block['ln_right_desc'])): ?>
                                <div class="handzom-journal-content__ln-note-desc">
                                    <?php echo wp_kses_post($block['ln_right_desc']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($block['ln_right_image']['url'])): ?>
                                <div class="handzom-journal-content__ln-note-img">
                                    <img src="<?php echo esc_url($block['ln_right_image']['url']); ?>" alt="Note image">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }
}
