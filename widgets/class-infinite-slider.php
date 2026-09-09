<?php
namespace HandzomUIKit\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Infinite Slider Widget Class
 */
class Infinite_Slider extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name() {
		return 'handzom_infinite_slider';
	}

	/**
	 * Get widget title.
	 */
	public function get_title() {
		return esc_html__( 'Infinite Slider', 'handzom-ui-kit' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon() {
		return 'eicon-slider-push';
	}

	/**
	 * Get widget categories.
	 */
	public function get_categories() {
		return [ 'handzom-ui-kit' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {
		// Controls will be added here.
	}

	/**
	 * Render widget output on the frontend.
	 */
	protected function render() {
		// Render output will be added here.
	}
}
