<?php

if( ! class_exists( 'Mantra_Audience_Metabox' ) ) :
class Mantra_Audience_Metabox {

	private static $instance = null;
	var $panel_options;

	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct() {
		$this->includes();
		add_action( 'init', array( $this, 'hooks' ), 100 );
	}

	/**
	 * Setup plugin actions.
	 *
	 * Hooked to init[100] to ensure post types are loaded
	 * @since 1.0.0
	 */
	function hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'pre_post_update', array( $this, 'save_postdata' ), 101 );
		add_action( 'save_post', array( $this, 'save_postdata' ), 101 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 11 );
		add_filter( 'is_protected_meta', array( $this, 'protected_meta' ), 10, 3 );
	}

	function includes() {
		require_once( MANTRA_AUDIENCE_METABOX_DIR . 'includes/mantra-audience-field-types.php' );
		require_once( MANTRA_AUDIENCE_METABOX_DIR . 'includes/mantra-audience-metabox-utils.php' );
		require_once( MANTRA_AUDIENCE_METABOX_DIR . 'includes/mantra-audience-user-fields.php' );
		require_once( MANTRA_AUDIENCE_METABOX_DIR . 'includes/mantra-audience-term-fields.php' );
	}

	/**
	 * Returns a list of all meta boxes registered through mantraAudience Metabox plugin
	 *
	 * @return array
	 * @since 1.0.0
	 */
	function get_meta_boxes() {
		static $meta_boxes = null;

		if( ! isset( $meta_boxes ) ) {
			// mantraAudience Custom Panel by default is added to all post types
			$types = get_post_types( '', 'names' );
			$meta_boxes = apply_filters( 'mantra_audience_metaboxes', array(
				'mantra-audience-meta-boxes' => array(
					'id' => 'mantra-audience-meta-boxes',
					'title' => __( 'Mantra Audience Custom Panel', 'mantra-audience' ),
					'context' => 'normal',
					'priority' => 'high',
					'screen' => $types,
				),
			) );
		}

		return $meta_boxes;
	}

	/**
	 * Returns the parameters for a meta box
	 *
	 * @param $id string the ID of the metabox registered using "mantra_audience_metaboxes" filter hook
	 * @return array
	 * @since 1.0.0
	 */
	public function get_meta_box( $id ) {
		$meta_boxes = $this->get_meta_boxes();
		if( isset( $meta_boxes[$id] ) ) {
			return $meta_boxes[$id];
		}

		return false;
	}

	/**
	 * Returns all the tabs and their fields for a meta box
	 *
	 * @param $meta_box string the ID of the metabox registered using "mantra_audience_metaboxes" filter hook
	 * @param $post_type string optional post_type to filter down the list of tabs displayed in the meta box
	 * @return array
	 * @since 1.0.0
	 */
	function get_meta_box_options( $meta_box, $post_type = null ) {
		if( ! isset( $this->panel_options[$meta_box] ) ) {
			if( 'mantra-audience-meta-boxes' === $meta_box ) {
				// backward compatibility
				global $mantra_audience_write_panels;
				if( ! isset( $mantra_audience_write_panels ) )
					$mantra_audience_write_panels = array();

				$mantra_audience_write_panels = apply_filters( 'mantra_audience_do_metaboxes', $mantra_audience_write_panels );
				$this->panel_options['mantra-audience-meta-boxes'] = array_filter( apply_filters( "mantra_audience_metabox/fields/{$meta_box}", $mantra_audience_write_panels, $post_type ) );
			} else {
				$this->panel_options[$meta_box] = array_filter( apply_filters( "mantra_audience_metabox/fields/{$meta_box}", array(), $post_type ) );
			}
		}

		$meta_box_result = $this->panel_options[$meta_box];

		// filter the panels by post type
		if( $post_type ) {
			$meta_box_result = array();
			foreach( $this->panel_options[$meta_box] as $tab ) {
				if( isset( $tab['pages'] ) && $tab['pages'] != '' ) {
					if( ! is_array( $tab['pages'] ) ) {
						$tab['pages'] = array_map( 'trim', explode( ",", $tab['pages'] ) );
					}
				} else {
					// use whatever the meta box itself uses
					if(isset( $def['screen'] ) && ( $def = $this->get_meta_box( $meta_box ) )  ) {
						$tab['pages'] = $def['screen'];
					}
				}

				in_array( $post_type, $tab['pages'], true ) && ( $meta_box_result[] = $tab );
			}
		}

		return apply_filters( 'mantra_audience_metabox_panel_options', $meta_box_result );
	}

	function admin_menu() {
		foreach( $this->get_meta_boxes() as $meta_box ) {
			add_meta_box( $meta_box['id'], $meta_box['title'], array( $this, 'render' ), $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
		}
	}

	/**
	 * Save Custom Write Panel Data
	 * @param number
	 * @return mixed
	 */
	function save_postdata( $post_id ) {
		global $post;

		if( function_exists( 'icl_object_id' ) && current_filter() === 'save_post' ) {
			wp_cache_delete( $post_id, 'post_meta' );
		}

		if( ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}

		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		if( isset( $_POST['mantra_audience_proper_save'] ) && $_POST['mantra_audience_proper_save'] != '' ) {
			foreach( $this->get_meta_boxes() as $meta_box ) {
				$tabs = $this->get_meta_box_options( $meta_box['id'], $_POST['post_type'] );
				if( empty( $tabs ) )
					continue;

				foreach( $tabs as $tab ) {
					foreach ( $tab['options'] as $field ) {

						if( 'multi' == $field['type'] ) {
							// Grouped fields
							foreach ( $field['meta']['fields'] as $field ) {
								$this->_save_meta( $field, $post_id );
							}
						} else {
							$this->_save_meta( $field, $post_id );
						}
					}
				}
			}
		} else {
			if ( isset( $post ) && isset( $post->ID ) ) {
				return $post->ID;
			}
		}
		return false;
	}

	/**
	 * Helper function that saves the custom field
	 *
	 * @since 1.0.0
	 */
	function _save_meta( $field, $post_id ) {

		$new_meta = isset( $field['name'] ) && isset( $_POST[$field['name']] ) ? $_POST[$field['name']] : '';
		$old_meta = get_post_meta( $post_id, $field['name'], true );


		// when a default value is set for the field and it's the same as $new_meta, do not bother with saving the field
		if( isset( $field['default'] ) && $new_meta == $field['default'] && !empty($old_meta)) {
			$new_meta = '';
		}


		// remove empty meta fields from database
		if( '' == $new_meta && metadata_exists( 'post', $post_id, $field['name'] ) ) {
			delete_post_meta( $post_id, $field['name'] );
		}

		if( $new_meta != '' && $new_meta != $old_meta ) {

			update_post_meta( $post_id, $field['name'], $new_meta );
		}
	}

	function render( $post, $metabox ) {
		global $post, $typenow, $mantra_audience_custom_css;

		$post_id = $post->ID;
		$tabs = $this->get_meta_box_options( $metabox['id'], $typenow );
		if( empty( $tabs ) ) {

			if( $metabox['id'] == 'mantra-audience-meta-boxes' ) {
				// this is a hack to prevent mantraAudience Custom Panel from showing up when it has no options to show
                $mantra_audience_custom_css.= '#mantra-audience-meta-boxes, .metabox-prefs label[for="mantra-audience-meta-boxes-hide"] { display: none !important; }\n';
			}
			return;
		}

		$this->render_tabs( $tabs, $post, $metabox['id'] );
	}

	/**
	 * Output the form and the fields
	 *
	 * @return null
	 */
	function render_tabs( $tabs, $post, $id ) {
		$post_id = $post->ID;

		echo '<div class="mantra-audience-meta-box-tabs" id="' . $id . '-meta-box">';
			echo '<ul class="ilc-htabs mantra-audience-tabs-heading">';
			foreach( $tabs as $tab ) {
				if( isset( $tab['display_callback'] ) && is_callable( $tab['display_callback'] ) ) {
					$show = (bool) call_user_func( $tab['display_callback'], $tab );
					if( ! $show ) { // if display_callback returns "false",
						continue;  // do not output the tab
					}
				}
				$panel_id = isset( $tab['id'] )? $tab['id']: sanitize_title( $tab['name'] );
				echo '<li><span><a id="' . esc_attr( $panel_id . 't' ) . '" href="' . esc_attr( '#' . $panel_id ) . '">' . esc_html( $tab['name'] ) . '</a></span></li>';
			}
			echo '</ul>';
			echo '<div class="ilc-btabs mantra-audience-tabs-body">';
			foreach( $tabs as $tab ) {
				if( isset( $tab['display_callback'] ) && is_callable( $tab['display_callback'] ) ) {
					$show = (bool) call_user_func( $tab['display_callback'], $tab );
					if( ! $show ) { // if display_callback returns "false",
						continue;  // do not output the tab
					}
				}
				$panel_id = isset( $tab['id'] )? $tab['id']: sanitize_title( $tab['name'] );
				?>
				<div id="<?php echo esc_attr( $panel_id ); ?>" class="ilc-tab mantra_audience_write_panel">

				<div class="inside">

					<input type="hidden" name="mantra_audience_proper_save" value="true" />

					<?php $mantra_audience_custom_panel_nonce = wp_create_nonce("mantra-audience-custom-panel"); ?>

					<!-- alerts -->
					<div class="alert"></div>
					<!-- /alerts -->

					<?php
					foreach( $tab['options'] as $field ) :
						$toggle_class = '';
						if( isset( $field['display_callback'] ) && is_callable( $field['display_callback'] ) ) {
							$show = (bool) call_user_func( $field['display_callback'], $field );
							if( ! $show ) { // if display_callback returns "false",
								continue;  // do not output the field
							}
						}

						$meta_value = isset($field['name']) ? get_post_meta( $post_id, $field['name'], true ) : '';
						$ext_attr = '';
						if( isset($field['toggle']) ){
							$toggle_class .= 'mantra-audience-toggle ';
							$toggle_class .= (is_array($field['toggle'])) ? implode(' ', $field['toggle']) : $field['toggle'];
							if ( is_array( $field['toggle'] ) && in_array( '0-toggle', $field['toggle'] ) ) {
								$toggle_class .= ' default-toggle';
							}
						}
						if ( isset( $field['class'] ) ) {
							$toggle_class .= ' ';
							$toggle_class .= is_array( $field['class'] ) ? implode( ' ', $field['class'] ) : $field['class'];
						}
						$data_hide = '';
						if ( isset( $field['hide'] ) ) {
							$data_hide = is_array( $field['hide'] ) ? implode( ' ', $field['hide'] ) : $field['hide'];
						}
						if( isset($field['default_toggle']) && $field['default_toggle'] == 'hidden' ){
							$ext_attr = 'style="display:none;"';
						}
						if( isset($field['enable_toggle']) && $field['enable_toggle'] == true ) {
							$toggle_class .= ' enable_toggle';
						}

						// @todo
						$meta_box = $field;

						echo $this->before_meta_field( compact( 'meta_box', 'toggle_class', 'ext_attr', 'data_hide' ) );

						do_action( "mantra_audience_metabox/field/{$field['type']}", compact( 'meta_box', 'meta_value', 'toggle_class', 'data_hide', 'ext_attr', 'post_id', 'mantra_audience_custom_panel_nonce' ) );

						// backward compatibility: allow custom function calls in the fields array
						if( isset( $field['function'] ) && is_callable( $field['function'] ) ) {
							call_user_func( $field['function'], $field );
						}

						echo $this->after_meta_field();

					endforeach; ?>
				</div>
				</div>
				<?php
			}
		echo '</div>';//end .ilc-btabs
		echo '</div>';//end #mantra-audience-meta-box-tabs
	}

	function before_meta_field( $args = array() ) {
		$meta_box = $args['meta_box'];
		$meta_box_name = isset( $meta_box['name'] ) ? $meta_box['name'] : '';
		$toggle_class = isset( $args['toggle_class'] ) ? $args['toggle_class'] : '';
		$ext_attr = isset( $args['ext_attr'] ) ? $args['ext_attr'] : '';
		$html = '
		<input type="hidden" name="' . esc_attr( $meta_box_name ) . '_noncename" id="' . esc_attr( $meta_box_name ) . '_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />
		<div class="mantra_audience_field_row clearfix ' . esc_attr( $toggle_class ) . '" ' . esc_attr( $ext_attr );
		if ( isset( $args['data_hide'] ) && ! empty( $args['data_hide'] ) ) {
			$html .= ' data-hide="' . esc_attr( $args['data_hide'] ) . '"';
		}
		$html .= '>';
		if ( isset( $meta_box['title'] ) ) {
			$html .= '<div class="mantra_audience_field_title">' . esc_html( $meta_box['title'] ) . '</div>';
		}
		$html .= '<div class="mantra_audience_field mantra_audience_field-' . esc_attr( $meta_box['type'] ) . '">';

		$html .= isset( $meta_box['meta']['before'] ) ? $meta_box['meta']['before'] : '';
		return $html;
	}

	function after_meta_field( $after = null ) {
		$html = isset( $after ) ? $after : '';
		$html .= '
			</div>
		</div><!--/mantra_audience_field_row -->';
		return $html;
	}

	function admin_enqueue_scripts( $page = '' ) {
		global $typenow;

		wp_register_style( 'mantra-audience-datetimepicker', MANTRA_AUDIENCE_METABOX_URI . 'css/jquery-ui-timepicker.min.css', array() );
		wp_register_style( 'mantra-audience-colorpicker', MANTRA_AUDIENCE_METABOX_URI . 'css/jquery.minicolors.css', array() );
		wp_register_style( 'mantra-audience-metabox', MANTRA_AUDIENCE_METABOX_URI . 'css/styles.css', array( 'mantra-audience-colorpicker', 'mantra-audience-datetimepicker' ) );

		wp_register_script( 'meta-box-tabs', MANTRA_AUDIENCE_METABOX_URI . 'js/meta-box-tabs.js', array( 'jquery' ), '1.0', true );
		wp_register_script( 'media-library-browse', MANTRA_AUDIENCE_METABOX_URI . 'js/media-lib-browse.js', array( 'jquery'), '1.0', true );
		wp_register_script( 'mantra-audience-colorpicker', MANTRA_AUDIENCE_METABOX_URI . 'js/jquery.minicolors.min.js', array( 'jquery' ), null, true );
		wp_register_script( 'mantra-audience-datetimepicker', MANTRA_AUDIENCE_METABOX_URI . 'js/jquery-ui-timepicker.min.js', array( 'jquery', 'jquery-ui-datepicker'/*, 'jquery-ui-slider'*/ ), false, true );
		wp_register_script( 'mantra-audience-metabox', MANTRA_AUDIENCE_METABOX_URI . 'js/scripts.js', array( 'jquery', 'meta-box-tabs', 'media-library-browse', 'jquery-ui-tabs', 'mantra-audience-colorpicker', 'mantra-audience-datetimepicker' ), '1.0', true );
		wp_register_script( 'mantra-audience-plupload', MANTRA_AUDIENCE_METABOX_URI . 'js/plupload.js', array( 'jquery', 'mantra-audience-metabox' ), null, true );

		// Inject variable for Plupload
		$global_plupload_init = array(
			'runtimes'				=> 'html5,flash,silverlight,html4',
			'browse_button'			=> 'plupload-browse-button', // adjusted by uploader
			'container' 			=> 'plupload-upload-ui', // adjusted by uploader
			'drop_element' 			=> 'drag-drop-area', // adjusted by uploader
			'file_data_name' 		=> 'async-upload', // adjusted by uploader
			'multiple_queues' 		=> true,
			'max_file_size' 		=> wp_max_upload_size() . 'b',
			'url' 					=> admin_url( 'admin-ajax.php' ),
			'flash_swf_url' 		=> includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' 	=> includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'filters' 				=> array(
				array(
					'title' => __( 'Allowed Files', 'mantra-audience' ),
					'extensions' => 'jpg,jpeg,gif,png,ico,zip,txt,svg',
				),
			),
			'multipart' 			=> true,
			'urlstream_upload' 		=> true,
			'multi_selection' 		=> false, // added by uploader
			 // additional post data to send to our ajax hook
			'multipart_params' 		=> array(
				'_ajax_nonce' => '', // added by uploader
				'imgid' => 0 // added by uploader
			)
		);
		wp_localize_script( 'mantra-audience-metabox', 'global_plupload_init', $global_plupload_init );

		do_action( 'mantra_audience_metabox_register_assets' );

		// attempt to enqueue Metabox API assets automatically when needed
		if( ( $page == 'post.php' || $page == 'post-new.php' ) ) {
			foreach( $this->get_meta_boxes() as $meta_box ) {
				if( isset( $meta_box['screen'] ) && in_array( $typenow, $meta_box['screen'] ) ) {
					$this->enqueue();
					break;
				}
			}
		}
	}

	/**
	 * Enqueues mantraAudience Metabox assets
	 *
	 * @since 1.0.0
	 */
	function enqueue() {
		wp_enqueue_media();
		wp_enqueue_style( 'mantra-audience-metabox' );
		wp_enqueue_script( 'mantra-audience-metabox' );
		wp_enqueue_script( 'mantra-audience-plupload' );

		do_action( 'mantra_audience_metabox_enqueue_assets' );
	}

	/*
	 * Protect $mantra_audience_write_panels fields
	 * This will hide these fields from Custom Fields panel
	 *
	 * @since 1.0.0
	 */
	function protected_meta( $protected, $meta_key, $meta_type ) {
		global $typenow;

		static $protected_metas = array();
		if( $protected_metas == null ) {
			foreach( $this->get_meta_boxes() as $meta_box ) {
				$protected_metas = array_merge( mantra_audience_metabox_get_field_names( $this->get_meta_box_options( $meta_box['id'], $typenow ) ), $protected_metas );
			}
		}

		if( is_array( $protected_metas ) && in_array( $meta_key, $protected_metas ) ) {
			$protected = true;
		}
		$this->panel_options = null;
		
		return $protected;
	}
}
endif;
add_action( 'init', 'Mantra_Audience_Metabox::get_instance', 10 );