<?php
/*
Plugin Name:  mantraAudience Metabox API
Version:      1.0.2
Author:       mantraAudience
Author URI:   https://mantrabrain.com/
Description:  Generate custom metaboxes for admin pages easily and efficiently.
Text Domain:  mantra-audience
Domain Path:  /languages
License:      GNU General Public License v2.0
License URI:  http://www.gnu.org/licenses/gpl-2.0.html
*/

if( ! defined( 'MANTRA_AUDIENCE_METABOX_DIR' ) ) {
	define( 'MANTRA_AUDIENCE_METABOX_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}
if( ! defined( 'MANTRA_AUDIENCE_METABOX_URI' ) ) {
	define( 'MANTRA_AUDIENCE_METABOX_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if( ! function_exists( 'mantra_audience_metabox_bootstrap' ) ) :
/**
 * Load and bootstrap mantraAudience Metabox API
 *
 * @since 1.0.0
 */
function mantra_audience_metabox_bootstrap() {
	if( ! class_exists( 'Mantra_Audience_Metabox' ) ) {
		require_once( MANTRA_AUDIENCE_METABOX_DIR . 'includes/mantra-audience-metabox-core.php' );
	}
}
endif;
add_action( 'after_setup_theme', 'mantra_audience_metabox_bootstrap', 20 );