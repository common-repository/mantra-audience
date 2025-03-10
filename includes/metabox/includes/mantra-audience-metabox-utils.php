<?php

/**
 * Takes an array of options and return a one dimensional array of all the field names
 *
 * @return array
 * @since 1.0.0
 */
function mantra_audience_metabox_get_field_names( $arr ) {
	$list = array();
	if( ! empty( $arr ) ) : foreach( $arr as $metabox ) :
		if( ! empty( $metabox['options'] ) ) {
			$list = array_merge( $list, wp_list_pluck( mantra_audience_metabox_make_flat_fields_array( $metabox['options'] ), 'name' ) );
		}
	endforeach; endif;

	return apply_filters( 'mantra_audience_metabox_get_field_names', array_unique( $list ), $arr );
}

/**
 * Takes an options array and returns a one-dimensional list of fields
 *
 * @return array
 * @since 1.0.0
 */
function mantra_audience_metabox_make_flat_fields_array( $arr ) {
	$list = array();
	foreach( $arr as $key => $field ) {
		if( $field['type'] == 'multi' ) {
			foreach( $field['meta']['fields'] as $_key => $_field ) {
				$list[] = $_field;
			}
		} else {
			$list[] = $field;
		}
	}

	return $list;
}

/**
 * Check if assignments are applied in the current context
 *
 * @since 1.0.0
 */
function mantra_audience_verify_assignments( $assignments ) {
	$visible = true;
	$query_object = get_queried_object();

	if ( ! empty( $assignments['roles'] ) ) {
		if ( ! in_array( $GLOBALS['current_user']->roles[0], array_keys( $assignments['roles'] ) ) ) {
			return false; // bail early.
		}
	}
	unset( $assignments['roles'] );

	if ( ! empty($assignments ) ) {
		$visible = false; // if any condition is set for a hook, hide it on all pages of the site except for the chosen ones.

		if (
			( is_front_page() && isset($assignments['general']['home']) )
			|| ( is_page() && isset( $assignments['general']['page'] ) && ! is_front_page() )
			|| ( is_single() && isset($assignments['general']['single']) )
			|| ( is_search() && isset($assignments['general']['search']) )
			|| ( is_author() && isset($assignments['general']['author']) )
			|| ( is_category() && isset($assignments['general']['category']) )
			|| ( is_tag() && isset($assignments['general']['tag']) )
			|| ( is_singular() && isset($assignments['general'][$query_object->post_type]) && $query_object->post_type != 'page' && $query_object->post_type != 'post' )
			|| ( is_tax() && isset($assignments['general'][$query_object->taxonomy]) )
		) {
			$visible = true;
		} else { // let's dig deeper into more specific visibility rules
			if ( ! empty( $assignments['tax'] ) ) {
				if ( is_single() ) {
					if ( isset( $assignments['tax']['category_single'] ) && ! empty( $assignments['tax']['category_single'] ) ) {
						$cat = get_the_category();
						if ( ! empty( $cat ) ) {
							foreach ( $cat as $c ) {
								if ( $c->taxonomy == 'category' && isset( $assignments['tax']['category_single'][$c->slug] ) ) {
									$visible = true;
									break;
								}
							}
						}
					}
				} else {
					foreach ( $assignments['tax'] as $tax => $terms ) {
						$terms = array_keys( $terms );
						if ( ( $tax == 'category' && is_category($terms) ) || ( $tax == 'post_tag' && is_tag( $terms ) ) || ( is_tax( $tax, $terms ) )
						) {
							$visible = true;
							break;
						}
					}
				}
			}
			if ( ! $visible && ! empty( $assignments['post_type'] ) ) {
				foreach ( $assignments['post_type'] as $post_type => $posts ) {
					$posts = array_keys( $posts );
					if ( ( $post_type == 'post' && is_single() && is_single($posts) ) || ( $post_type == 'page' && (
							( is_page() && is_page( $posts ) ) || ( ! is_front_page() && is_home() && in_array( get_post_field( 'post_name', get_option('page_for_posts' ) ), $posts ) ) // check for Posts page
							) ) || ( is_singular( $post_type ) && in_array( $query_object->post_name, $posts ) )
					) {
						$visible = true;
						break;
					}
				}
			}
		}
	}

	return $visible;
}