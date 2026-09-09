<?php
namespace HandzomUIKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Repeater;
use Elementor\Utils;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Editorial_Sidebar extends Widget_Base
{
	public function get_name()
	{
		return 'handzom_editorial_sidebar';
	}

	public function get_title()
	{
		return esc_html__('Editorial Sidebar', 'handzom-ui-kit');
	}

	public function get_icon()
	{
		return 'eicon-sidebar';
	}

	public function get_categories()
	{
		return ['handzom-ui-kit'];
	}

	public function get_style_depends()
	{
		return ['handzom-journal-sidebar'];
	}

	protected function register_controls()
	{
		// ==========================================
		// GENERAL
		// ==========================================
		$this->start_controls_section(
			'section_general',
			[
				'label' => esc_html__('General', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'enable_sidebar',
			[
				'label' => esc_html__('Enable Sidebar', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// BLOCK 1: IN THIS JOURNAL
		// ==========================================
		$this->start_controls_section(
			'section_in_this_journal',
			[
				'label' => esc_html__('Block 1 — In This Journal', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'enable_journal_block',
			[
				'label' => esc_html__('Enable', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'journal_title',
			[
				'label' => esc_html__('Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => 'IN THIS JOURNAL',
				'condition' => ['enable_journal_block' => 'yes'],
			]
		);

		$journal_repeater = new Repeater();
		$journal_repeater->add_control(
			'item_number',
			[
				'label' => esc_html__('Number', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
			]
		);
		$journal_repeater->add_control(
			'item_text',
			[
				'label' => esc_html__('Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
			]
		);
		$journal_repeater->add_control(
			'item_link',
			[
				'label' => esc_html__('Link', 'handzom-ui-kit'),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__('https://your-link.com', 'handzom-ui-kit'),
				'default' => ['url' => ''],
			]
		);

		$this->add_control(
			'journal_items',
			[
				'label' => esc_html__('Journal Items', 'handzom-ui-kit'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $journal_repeater->get_controls(),
				'default' => [
					['item_number' => '01', 'item_text' => 'What Is Natural Fabric?'],
					['item_number' => '02', 'item_text' => 'Why Natural Fabrics Matter'],
				],
				'title_field' => '{{{ item_number }}} - {{{ item_text }}}',
				'condition' => ['enable_journal_block' => 'yes'],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// BLOCK 2: OUR STORY
		// ==========================================
		$this->start_controls_section(
			'section_our_story',
			[
				'label' => esc_html__('Block 2 — Our Story', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'enable_our_story',
			[
				'label' => esc_html__('Enable', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'story_title',
			[
				'label' => esc_html__('Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => 'OUR STORY',
				'condition' => ['enable_our_story' => 'yes'],
			]
		);

		$this->add_control(
			'story_image',
			[
				'label' => esc_html__('Image', 'handzom-ui-kit'),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => ['enable_our_story' => 'yes'],
			]
		);

		$this->add_control(
			'story_description',
			[
				'label' => esc_html__('Description', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => 'Stories on fabric, craftsmanship and simplicity—made to help you choose better and live better.',
				'condition' => ['enable_our_story' => 'yes'],
			]
		);

		$this->add_control(
			'story_button_text',
			[
				'label' => esc_html__('Button Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => 'READ OUR STORY',
				'condition' => ['enable_our_story' => 'yes'],
			]
		);

		$this->add_control(
			'story_button_link',
			[
				'label' => esc_html__('Button URL', 'handzom-ui-kit'),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__('https://your-link.com', 'handzom-ui-kit'),
				'default' => ['url' => ''],
				'condition' => ['enable_our_story' => 'yes'],
			]
		);

		$this->add_control(
			'story_button_arrow',
			[
				'label' => esc_html__('Show Arrow', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => ['enable_our_story' => 'yes'],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// BLOCK 3: LATEST ARTICLES
		// ==========================================
		$this->start_controls_section(
			'section_latest_articles',
			[
				'label' => esc_html__('Block 3 — Latest Articles', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'enable_latest_articles',
			[
				'label' => esc_html__('Enable', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'articles_title',
			[
				'label' => esc_html__('Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => 'LATEST ARTICLES',
				'condition' => ['enable_latest_articles' => 'yes'],
			]
		);

		$this->add_control(
			'articles_source',
			[
				'label' => esc_html__('Article Source', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'manual' => esc_html__('Manual Articles', 'handzom-ui-kit'),
					'posts' => esc_html__('WordPress Posts', 'handzom-ui-kit'),
				],
				'default' => 'posts',
				'condition' => ['enable_latest_articles' => 'yes'],
			]
		);

		// Options for WordPress Posts
		$this->add_control(
			'articles_count',
			[
				'label' => esc_html__('Number of articles', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 4,
				'condition' => [
					'enable_latest_articles' => 'yes',
					'articles_source' => 'posts'
				],
			]
		);

		$this->add_control(
			'articles_category',
			[
				'label' => esc_html__('Category (Slug)', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__('Leave empty for all categories.', 'handzom-ui-kit'),
				'condition' => [
					'enable_latest_articles' => 'yes',
					'articles_source' => 'posts'
				],
			]
		);

		$this->add_control(
			'articles_orderby',
			[
				'label' => esc_html__('Order By', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'date' => esc_html__('Date', 'handzom-ui-kit'),
					'title' => esc_html__('Title', 'handzom-ui-kit'),
					'modified' => esc_html__('Modified', 'handzom-ui-kit'),
					'rand' => esc_html__('Random', 'handzom-ui-kit'),
				],
				'default' => 'date',
				'condition' => [
					'enable_latest_articles' => 'yes',
					'articles_source' => 'posts'
				],
			]
		);

		$this->add_control(
			'articles_order',
			[
				'label' => esc_html__('Order', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'DESC' => esc_html__('Newest First', 'handzom-ui-kit'),
					'ASC' => esc_html__('Oldest First', 'handzom-ui-kit'),
				],
				'default' => 'DESC',
				'condition' => [
					'enable_latest_articles' => 'yes',
					'articles_source' => 'posts'
				],
			]
		);

		// Options for Manual Articles
		$article_repeater = new Repeater();
		$article_repeater->add_control(
			'article_image',
			[
				'label' => esc_html__('Image', 'handzom-ui-kit'),
				'type' => Controls_Manager::MEDIA,
				'default' => ['url' => Utils::get_placeholder_image_src()],
			]
		);
		$article_repeater->add_control(
			'article_title',
			[
				'label' => esc_html__('Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
			]
		);
		$article_repeater->add_control(
			'article_date',
			[
				'label' => esc_html__('Date', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
			]
		);
		$article_repeater->add_control(
			'article_link',
			[
				'label' => esc_html__('Link', 'handzom-ui-kit'),
				'type' => Controls_Manager::URL,
				'default' => ['url' => ''],
			]
		);

		$this->add_control(
			'manual_articles',
			[
				'label' => esc_html__('Manual Articles', 'handzom-ui-kit'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $article_repeater->get_controls(),
				'title_field' => '{{{ article_title }}}',
				'condition' => [
					'enable_latest_articles' => 'yes',
					'articles_source' => 'manual'
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// BLOCK 4: STAY IN THE LOOP
		// ==========================================
		$this->start_controls_section(
			'section_stay_loop',
			[
				'label' => esc_html__('Block 4 — Stay In The Loop', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'enable_stay_loop',
			[
				'label' => esc_html__('Enable', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'loop_title',
			[
				'label' => esc_html__('Title', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => 'STAY IN THE LOOP',
				'condition' => ['enable_stay_loop' => 'yes'],
			]
		);

		$this->add_control(
			'loop_description',
			[
				'label' => esc_html__('Description', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXTAREA,
				'default' => 'Exclusive updates, new arrivals, and stories—straight to your inbox.',
				'condition' => ['enable_stay_loop' => 'yes'],
			]
		);

		$this->add_control(
			'loop_placeholder',
			[
				'label' => esc_html__('Email Placeholder', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => 'Enter your email',
				'condition' => ['enable_stay_loop' => 'yes'],
			]
		);

		$this->add_control(
			'loop_button_text',
			[
				'label' => esc_html__('Button Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => 'SUBSCRIBE',
				'condition' => ['enable_stay_loop' => 'yes'],
			]
		);

		$this->add_control(
			'loop_form_action',
			[
				'label' => esc_html__('Form Action URL', 'handzom-ui-kit'),
				'type' => Controls_Manager::TEXT,
				'default' => '#',
				'condition' => ['enable_stay_loop' => 'yes'],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// BLOCK ORDER
		// ==========================================
		$this->start_controls_section(
			'section_block_order',
			[
				'label' => esc_html__('Block Order', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'block_order_info',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__('Set the order of the blocks (1 is top).', 'handzom-ui-kit'),
			]
		);

		$this->add_control(
			'order_in_this_journal',
			[
				'label' => esc_html__('In This Journal Order', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 1,
			]
		);

		$this->add_control(
			'order_our_story',
			[
				'label' => esc_html__('Our Story Order', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 2,
			]
		);

		$this->add_control(
			'order_latest_articles',
			[
				'label' => esc_html__('Latest Articles Order', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 3,
			]
		);

		$this->add_control(
			'order_stay_loop',
			[
				'label' => esc_html__('Stay In The Loop Order', 'handzom-ui-kit'),
				'type' => Controls_Manager::NUMBER,
				'default' => 4,
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: GLOBAL COLORS
		// ==========================================
		$this->start_controls_section(
			'style_global_colors',
			[
				'label' => esc_html__('Global Colors', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'color_bg_sidebar',
			[
				'label' => esc_html__('Sidebar Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#F4F1EB',
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_text_primary',
			[
				'label' => esc_html__('Primary Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#222222',
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar, {{WRAPPER}} .handzom-editorial-sidebar__desc, {{WRAPPER}} .handzom-editorial-sidebar__story-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_text_muted',
			[
				'label' => esc_html__('Muted Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#66615A',
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__date, {{WRAPPER}} .handzom-editorial-sidebar__number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_text_heading',
			[
				'label' => esc_html__('Heading Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#222222',
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_divider',
			[
				'label' => esc_html__('Divider Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#D9D5CE',
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__section:not(:last-child)' => 'border-bottom-color: {{VALUE}};',
					'{{WRAPPER}} .handzom-editorial-sidebar__divider' => 'border-top-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: GLOBAL TYPOGRAPHY
		// ==========================================
		$this->start_controls_section(
			'style_global_typography',
			[
				'label' => esc_html__('Global Typography', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typo_heading',
				'label' => esc_html__('Heading Font', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .handzom-editorial-sidebar__title',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typo_body',
				'label' => esc_html__('Body Font', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .handzom-editorial-sidebar__desc, {{WRAPPER}} .handzom-editorial-sidebar__story-desc',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typo_nav',
				'label' => esc_html__('Navigation Font', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .handzom-editorial-sidebar__link',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typo_article',
				'label' => esc_html__('Article Font', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .handzom-editorial-sidebar__article-title',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typo_date',
				'label' => esc_html__('Date Font', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .handzom-editorial-sidebar__date',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: SIDEBAR
		// ==========================================
		$this->start_controls_section(
			'style_sidebar_main',
			[
				'label' => esc_html__('Sidebar Layout', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'sidebar_width',
			[
				'label' => esc_html__('Sidebar Width', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => ['min' => 200, 'max' => 600],
					'%' => ['min' => 10, 'max' => 100],
				],
				'default' => [
					'unit' => 'px',
					'size' => 300,
				],
				'mobile_default' => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'sidebar_padding',
			[
				'label' => esc_html__('Sidebar Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'top' => '20',
					'right' => '20',
					'bottom' => '20',
					'left' => '20',
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'sidebar_border',
				'label' => esc_html__('Border', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .handzom-editorial-sidebar',
			]
		);

		$this->add_control(
			'enable_shadow',
			[
				'label' => esc_html__('Enable Shadow', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar' => 'box-shadow: 0px 4px 20px rgba(0,0,0,0.05);',
				],
			]
		);

		$this->add_control(
			'enable_sticky',
			[
				'label' => esc_html__('Sticky Sidebar', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'sticky_offset',
			[
				'label' => esc_html__('Sticky Top Offset', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => ['min' => 0, 'max' => 200],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'condition' => ['enable_sticky' => 'yes'],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar.is-sticky' => 'position: sticky; top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: SECTIONS & DIVIDERS
		// ==========================================
		$this->start_controls_section(
			'style_sections',
			[
				'label' => esc_html__('Sections & Dividers', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'section_padding',
			[
				'label' => esc_html__('Section Padding', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em'],
				'default' => [
					'top' => '20',
					'right' => '0',
					'bottom' => '20',
					'left' => '0',
					'unit' => 'px',
					'isLinked' => false,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'enable_dividers',
			[
				'label' => esc_html__('Enable Dividers', 'handzom-ui-kit'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'divider_style',
			[
				'label' => esc_html__('Divider Style', 'handzom-ui-kit'),
				'type' => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => [
					'solid' => esc_html__('Solid', 'handzom-ui-kit'),
					'dashed' => esc_html__('Dashed', 'handzom-ui-kit'),
					'dotted' => esc_html__('Dotted', 'handzom-ui-kit'),
				],
				'condition' => ['enable_dividers' => 'yes'],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__section:not(:last-child)' => 'border-bottom-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'divider_thickness',
			[
				'label' => esc_html__('Divider Thickness', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'default' => [
					'unit' => 'px',
					'size' => 1,
				],
				'condition' => ['enable_dividers' => 'yes'],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__section:not(:last-child)' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: IN THIS JOURNAL
		// ==========================================
		$this->start_controls_section(
			'style_in_this_journal',
			[
				'label' => esc_html__('In This Journal', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'journal_item_gap',
			[
				'label' => esc_html__('Item Gap', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__list' => 'display: flex; flex-direction: column; gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .handzom-editorial-sidebar__item' => 'margin-bottom: 0;', // reset old margin
				],
			]
		);

		$this->add_control(
			'journal_number_width',
			[
				'label' => esc_html__('Number Min Width', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'default' => [
					'unit' => 'px',
					'size' => 25,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__number' => 'min-width: {{SIZE}}{{UNIT}}; display: inline-block;',
				],
			]
		);

		$this->start_controls_tabs('journal_item_states');

		// Normal
		$this->start_controls_tab('journal_item_normal', ['label' => esc_html__('Normal', 'handzom-ui-kit')]);

		$this->add_control(
			'journal_text_color',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'journal_num_color',
			[
				'label' => esc_html__('Number Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab('journal_item_hover', ['label' => esc_html__('Hover', 'handzom-ui-kit')]);

		$this->add_control(
			'journal_text_color_hover',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__link:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'journal_num_color_hover',
			[
				'label' => esc_html__('Number Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__link:hover .handzom-editorial-sidebar__number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Active
		$this->start_controls_tab('journal_item_active', ['label' => esc_html__('Active', 'handzom-ui-kit')]);

		$this->add_control(
			'journal_text_color_active',
			[
				'label' => esc_html__('Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__item.is-active .handzom-editorial-sidebar__link' => 'color: {{VALUE}}; font-weight: 600;',
				],
			]
		);

		$this->add_control(
			'journal_num_color_active',
			[
				'label' => esc_html__('Number Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__item.is-active .handzom-editorial-sidebar__number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: OUR STORY
		// ==========================================
		$this->start_controls_section(
			'style_our_story',
			[
				'label' => esc_html__('Our Story', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'story_img_aspect',
			[
				'label' => esc_html__('Image Aspect Ratio', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['%'],
				'range' => [
					'%' => ['min' => 20, 'max' => 150],
				],
				'default' => [
					'unit' => '%',
					'size' => 60,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__image-wrap' => 'aspect-ratio: 100 / {{SIZE}};',
					'{{WRAPPER}} .handzom-editorial-sidebar__img' => 'height: 100%; object-fit: cover;',
				],
			]
		);

		$this->add_control(
			'story_img_radius',
			[
				'label' => esc_html__('Image Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_control(
			'story_btn_heading',
			[
				'label' => esc_html__('Button Style', 'handzom-ui-kit'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'story_btn_typo',
				'label' => esc_html__('Button Typography', 'handzom-ui-kit'),
				'selector' => '{{WRAPPER}} .handzom-editorial-sidebar__button',
			]
		);

		$this->add_control(
			'story_btn_color',
			[
				'label' => esc_html__('Button Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__button' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'story_btn_color_hover',
			[
				'label' => esc_html__('Button Hover Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__button:hover' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: LATEST ARTICLES
		// ==========================================
		$this->start_controls_section(
			'style_latest_articles',
			[
				'label' => esc_html__('Latest Articles', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'article_img_size',
			[
				'label' => esc_html__('Image Size', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => ['min' => 30, 'max' => 150],
				],
				'default' => [
					'unit' => 'px',
					'size' => 60,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__article-img-wrap' => 'flex: 0 0 {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; aspect-ratio: 1/1;',
				],
			]
		);

		$this->add_control(
			'article_img_radius',
			[
				'label' => esc_html__('Image Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__article-img-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'article_gap',
			[
				'label' => esc_html__('Gap Between Image & Text', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'default' => [
					'unit' => 'px',
					'size' => 15,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__article' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'articles_list_gap',
			[
				'label' => esc_html__('Gap Between Articles', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__articles-list' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB: STAY IN THE LOOP
		// ==========================================
		$this->start_controls_section(
			'style_newsletter',
			[
				'label' => esc_html__('Stay In The Loop', 'handzom-ui-kit'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'nl_input_bg',
			[
				'label' => esc_html__('Input Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__input' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nl_input_border',
			[
				'label' => esc_html__('Input Border Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__input' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nl_input_radius',
			[
				'label' => esc_html__('Input Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__input' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'nl_btn_bg',
			[
				'label' => esc_html__('Button Background', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#111111',
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__submit' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'nl_btn_color',
			[
				'label' => esc_html__('Button Text Color', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#FFFFFF',
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__submit' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nl_btn_bg_hover',
			[
				'label' => esc_html__('Button Background (Hover)', 'handzom-ui-kit'),
				'type' => Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__submit:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nl_btn_radius',
			[
				'label' => esc_html__('Button Border Radius', 'handzom-ui-kit'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'selectors' => [
					'{{WRAPPER}} .handzom-editorial-sidebar__submit' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		if ($settings['enable_sidebar'] !== 'yes') {
			return;
		}

		$blocks = [];

		// Block 1
		if ($settings['enable_journal_block'] === 'yes') {
			ob_start();
			?>
						<div class="handzom-editorial-sidebar__section handzom-journal-sidebar__nav">
							<?php if (!empty($settings['journal_title'])): ?>
									<h4 class="handzom-editorial-sidebar__title"><?php echo esc_html($settings['journal_title']); ?></h4>
							<?php endif; ?>

							<?php if (!empty($settings['journal_items'])): ?>
									<ul class="handzom-editorial-sidebar__list">
										<?php foreach ($settings['journal_items'] as $item):
											$target = $item['item_link']['is_external'] ? ' target="_blank"' : '';
											$nofollow = $item['item_link']['nofollow'] ? ' rel="nofollow"' : '';
											$link_url = !empty($item['item_link']['url']) ? esc_url($item['item_link']['url']) : '#';

											// Check if active
											$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
											$is_active = ($current_url == $link_url) ? 'is-active' : '';
											?>
												<li class="handzom-editorial-sidebar__item <?php echo esc_attr($is_active); ?>">
													<a href="<?php echo $link_url; ?>" <?php echo $target . $nofollow; ?> class="handzom-editorial-sidebar__link">
														<?php if (!empty($item['item_number'])): ?>
																<span class="handzom-editorial-sidebar__number"><?php echo esc_html($item['item_number']); ?></span>
														<?php endif; ?>
														<span class="handzom-editorial-sidebar__text"><?php echo esc_html($item['item_text']); ?></span>
													</a>
												</li>
										<?php endforeach; ?>
									</ul>
							<?php endif; ?>
						</div>
						<?php
						$blocks[$settings['order_in_this_journal']] = ob_get_clean();
		}

		// Block 2
		if ($settings['enable_our_story'] === 'yes') {
			ob_start();
			?>
						<div class="handzom-editorial-sidebar__section handzom-journal-sidebar__story">
							<?php if (!empty($settings['story_title'])): ?>
									<h4 class="handzom-editorial-sidebar__title"><?php echo esc_html($settings['story_title']); ?></h4>
							<?php endif; ?>

							<?php if (!empty($settings['story_image']['url'])): ?>
									<div class="handzom-editorial-sidebar__image-wrap">
										<img src="<?php echo esc_url($settings['story_image']['url']); ?>"
											alt="<?php echo esc_attr($settings['story_title']); ?>" class="handzom-editorial-sidebar__img">
									</div>
							<?php endif; ?>

							<?php if (!empty($settings['story_description'])): ?>
									<div class="handzom-editorial-sidebar__desc">
										<?php echo wp_kses_post($settings['story_description']); ?>
									</div>
							<?php endif; ?>

							<?php if (!empty($settings['story_button_text'])):
								$target = $settings['story_button_link']['is_external'] ? ' target="_blank"' : '';
								$nofollow = $settings['story_button_link']['nofollow'] ? ' rel="nofollow"' : '';
								$link_url = !empty($settings['story_button_link']['url']) ? esc_url($settings['story_button_link']['url']) : '#';
								?>
									<a href="<?php echo $link_url; ?>" <?php echo $target . $nofollow; ?> class="handzom-editorial-sidebar__button">
										<?php echo esc_html($settings['story_button_text']); ?>
										<?php if ($settings['story_button_arrow'] === 'yes'): ?>
												<span class="handzom-editorial-sidebar__arrow">→</span>
										<?php endif; ?>
									</a>
							<?php endif; ?>
						</div>
						<?php
						$blocks[$settings['order_our_story']] = ob_get_clean();
		}

		// Block 3
		if ($settings['enable_latest_articles'] === 'yes') {
			ob_start();
			?>
						<div class="handzom-editorial-sidebar__section handzom-journal-sidebar__articles">
							<?php if (!empty($settings['articles_title'])): ?>
									<h4 class="handzom-editorial-sidebar__title"><?php echo esc_html($settings['articles_title']); ?></h4>
							<?php endif; ?>

							<div class="handzom-editorial-sidebar__articles-list">
								<?php
								if ($settings['articles_source'] === 'posts') {
									$args = [
										'post_type' => 'post',
										'posts_per_page' => $settings['articles_count'] ? $settings['articles_count'] : 4,
										'orderby' => $settings['articles_orderby'],
										'order' => $settings['articles_order'],
									];

									if (!empty($settings['articles_category'])) {
										$args['category_name'] = $settings['articles_category'];
									}

									$query = new \WP_Query($args);
									if ($query->have_posts()) {
										while ($query->have_posts()) {
											$query->the_post();
											?>
														<a href="<?php the_permalink(); ?>" class="handzom-editorial-sidebar__article">
															<?php if (has_post_thumbnail()): ?>
																	<div class="handzom-editorial-sidebar__article-img-wrap">
																		<?php the_post_thumbnail('thumbnail', ['class' => 'handzom-journal-sidebar__article-img']); ?>
																	</div>
															<?php endif; ?>
															<div class="handzom-editorial-sidebar__article-content">
																<h5 class="handzom-editorial-sidebar__article-title"><?php the_title(); ?></h5>
																<span class="handzom-editorial-sidebar__date"><?php echo get_the_date(); ?></span>
															</div>
														</a>
														<?php
										}
										wp_reset_postdata();
									}
								} else {
									// Manual
									if (!empty($settings['manual_articles'])) {
										foreach ($settings['manual_articles'] as $article) {
											$target = $article['article_link']['is_external'] ? ' target="_blank"' : '';
											$nofollow = $article['article_link']['nofollow'] ? ' rel="nofollow"' : '';
											$link_url = !empty($article['article_link']['url']) ? esc_url($article['article_link']['url']) : '#';
											?>
														<a href="<?php echo $link_url; ?>" <?php echo $target . $nofollow; ?> class="handzom-editorial-sidebar__article">
															<?php if (!empty($article['article_image']['url'])): ?>
																	<div class="handzom-editorial-sidebar__article-img-wrap">
																		<img src="<?php echo esc_url($article['article_image']['url']); ?>"
																			alt="<?php echo esc_attr($article['article_title']); ?>"
																			class="handzom-editorial-sidebar__article-img">
																	</div>
															<?php endif; ?>
															<div class="handzom-editorial-sidebar__article-content">
																<?php if (!empty($article['article_title'])): ?>
																		<h5 class="handzom-editorial-sidebar__article-title"><?php echo esc_html($article['article_title']); ?>
																		</h5>
																<?php endif; ?>
																<?php if (!empty($article['article_date'])): ?>
																		<span class="handzom-editorial-sidebar__date"><?php echo esc_html($article['article_date']); ?></span>
																<?php endif; ?>
															</div>
														</a>
														<?php
										}
									}
								}
								?>
							</div>
						</div>
						<?php
						$blocks[$settings['order_latest_articles']] = ob_get_clean();
		}

		// Block 4
		if ($settings['enable_stay_loop'] === 'yes') {
			ob_start();
			?>
						<div class="handzom-editorial-sidebar__section handzom-journal-sidebar__newsletter">
							<?php if (!empty($settings['loop_title'])): ?>
									<h4 class="handzom-editorial-sidebar__title"><?php echo esc_html($settings['loop_title']); ?></h4>
							<?php endif; ?>

							<?php if (!empty($settings['loop_description'])): ?>
									<div class="handzom-editorial-sidebar__desc">
										<?php echo wp_kses_post($settings['loop_description']); ?>
									</div>
							<?php endif; ?>

							<form class="handzom-editorial-sidebar__form" action="<?php echo esc_url($settings['loop_form_action']); ?>"
								method="POST">
								<input type="email" name="email" class="handzom-editorial-sidebar__input"
									placeholder="<?php echo esc_attr($settings['loop_placeholder']); ?>" required>
								<button type="submit" class="handzom-editorial-sidebar__submit">
									<?php echo esc_html($settings['loop_button_text']); ?>
								</button>
							</form>
						</div>
						<?php
						$blocks[$settings['order_stay_loop']] = ob_get_clean();
		}

		$is_sticky = !empty($settings['enable_sticky']) && $settings['enable_sticky'] === 'yes' ? ' is-sticky' : '';
		?>

				<div class="handzom-editorial-sidebar<?php echo esc_attr($is_sticky); ?>">
					<?php
					foreach ($blocks as $block) {
						echo $block;
					}
					?>
				</div>
				<?php
	}
}
