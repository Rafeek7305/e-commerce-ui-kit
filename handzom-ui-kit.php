<?php
/**
 * Plugin Name: Handzom UI Kit
 * Plugin URI:  https://azasoft.in
 * Description: A premium Elementor extension providing infinite collection slider and featured cards.
 * Version:     3.5.0
 * Author:      Azasoft Solutions
 * Author URI:  https://azasoft.in
 * Text Domain: handzom-ui-kit
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Elementor tested up to: 3.20.0
 * Elementor Pro tested up to: 3.20.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('HANDZOM_UI_KIT_VERSION', time());
define('HANDZOM_UI_KIT_FILE', __FILE__);
define('HANDZOM_UI_KIT_PATH', plugin_dir_path(__FILE__));
define('HANDZOM_UI_KIT_URL', plugin_dir_url(__FILE__));

// Require the main loader class.
require_once HANDZOM_UI_KIT_PATH . 'includes/class-loader.php';

// Initialize the plugin.
function handzom_ui_kit_init()
{
	\HandzomUIKit\Loader::instance();
}
add_action('plugins_loaded', 'handzom_ui_kit_init');
