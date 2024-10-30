<?php
/**
 * Mantra Audience
 *
 * @package Mantra_Audience
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Mantra audience class
 *
 * @class Mantra audience class
 */
final class Mantra_Audience {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.0.2';

	/**
	 * Theme single instance of this class.
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'mantra-audience' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'mantra-audience' ), '1.0.0' );
	}

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		$this->define_constants();
		$this->init_hooks();

		do_action( 'mantra_audience_loaded' );
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {

		$this->define( 'MANTRA_AUDIENCE_ABSPATH', dirname( MANTRA_AUDIENCE_PLUGIN_FILE ) . '/' );
		$this->define( 'MANTRA_AUDIENCE_PLUGIN_BASENAME', plugin_basename( MANTRA_AUDIENCE_PLUGIN_FILE ) );
		$this->define( 'MANTRA_AUDIENCE_VERSION', $this->version );
		$this->define( 'MANTRA_AUDIENCE_URI', trailingslashit( plugin_dir_url( MANTRA_AUDIENCE_PLUGIN_FILE ) ) );
		$this->define( 'MANTRA_AUDIENCE_DIR', trailingslashit( plugin_dir_path( MANTRA_AUDIENCE_PLUGIN_FILE ) ));


    }

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );


		// Register activation hook.
		register_activation_hook( MANTRA_AUDIENCE_PLUGIN_FILE, array( $this, 'install' ) );

        $this->includes();
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

	}

	/**
	 * Include required core files.
	 */
	private function includes() {
		include_once MANTRA_AUDIENCE_ABSPATH.'includes/sample/sample.php';
		include_once MANTRA_AUDIENCE_ABSPATH.'includes/class-mantra-audience-main.php';
		include_once MANTRA_AUDIENCE_ABSPATH.'includes/metabox/mantra-audience-metabox.php';
		include_once MANTRA_AUDIENCE_ABSPATH.'includes/class-mantra-audience-cpt.php';

	}

	/**
	 * Install
	 */
	public function install() {

		// Bypass if filesystem is read-only and/or non-standard upload system is used.
		if ( ! is_blog_installed() || apply_filters( 'mantra_audience_install_skip_create_files', false ) ) {
			return;
		}

        ///Mantra_Audience_Main::get_instance()->register_post_type();
        flush_rewrite_rules();
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/mantra-audience/mantra-audience-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/mantra-audience-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'mantra-audience' );

		unload_textdomain( 'mantra-audience' );
		load_textdomain( 'mantra-audience', WP_LANG_DIR . '/mantra-audience/mantra-audience-' . $locale . '.mo' );
		load_plugin_textdomain( 'mantra-audience', false, plugin_basename( dirname( MANTRA_AUDIENCE_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', MANTRA_AUDIENCE_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( MANTRA_AUDIENCE_PLUGIN_FILE ) );
	}

	/**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions Plugin Action links.
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'importer' => '<a href="' . admin_url( 'themes.php?page=starter-sites' ) . '" aria-label="' . esc_attr( __( 'View Starter Sites', 'mantra-audience' ) ) . '">' . __( 'Starter Sites', 'mantra-audience' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Display row meta in the Plugins list table.
	 *
	 * @param  array  $plugin_meta Plugin Row Meta.
	 * @param  string $plugin_file Plugin Row Meta.
	 * @return array
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( MANTRA_AUDIENCE_PLUGIN_BASENAME === $plugin_file ) {
			$new_plugin_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'mantra_audience_docs_url', 'https://mantrabrain.com/docs/mantra-audience/' ) ) . '" title="' . esc_attr( __( 'View Demo Importer Documentation', 'mantra-audience' ) ) . '">' . __( 'Docs', 'mantra-audience' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'mantra_audience_support_url', 'https://mantrabrain.com/support-forum/' ) ) . '" title="' . esc_attr( __( 'Visit Free Customer Support Forum', 'mantra-audience' ) ) . '">' . __( 'Free Support', 'mantra-audience' ) . '</a>',
			);

			return array_merge( $plugin_meta, $new_plugin_meta );
		}

		return (array) $plugin_meta;
	}

}
