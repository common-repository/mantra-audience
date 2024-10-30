<?php
/**
 * Plugin Name: Mantra Audience - Lead generation & Popup plugin for WordPress
 * Description: Lead generation & Popup plugin for WordPress. [ma_popup] to show popup by shortcode.
 * Version: 1.0.3
 * Author: Mantrabrain
 * Author URI: https://mantrabrain.com
 * License: GPLv3 or later
 * Text Domain: mantra-audience
 * Domain Path: /languages/
 *
 * @package Mantra_Audience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define MANTRA_AUDIENCE_PLUGIN_FILE.
if ( ! defined( 'MANTRA_AUDIENCE_PLUGIN_FILE' ) ) {
	define( 'MANTRA_AUDIENCE_PLUGIN_FILE', __FILE__ );
}
// Include the main CLASS
if ( ! class_exists( 'Mantra_Audience' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-mantra-audience.php';
}

/**
 * Main instance of mantra audience.
 *
 * Returns the main instance to prevent the need to use globals.
 *
 * @since 1.0.0
 * @return Mantra_Audience
 */
function mantra_audience_callback() {
	return Mantra_Audience::instance();
}

// Global for backwards compatibility.
$GLOBALS['mantra-audience'] = mantra_audience_callback();

