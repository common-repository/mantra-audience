<?php
/**
 * Example of how mantraAudience Metabox plugin can be used in themes and plugins.
 *
 * To use this file, enable the mantraAudience Metabox plugin and then copy the contents of this file to
 * your theme's functions.php file, or "include" it.'
 *
 * @package mantraAudience Metabox
 * @since 1.0.0
 */

/**
 * Register a custom meta box to display on Page post type
 *
 * @return array
 */
function mantra_audience_metabox_example_meta_box( $meta_boxes ) {
	$meta_boxes['tm-example'] = array(
		'id' => 'tm-example', // later, to add fields to this metabox we'll use "mantra_audience_metabox/fields/tm-example" filter hook, see function
		'title' => __( 'mantraAudience Metabox Example', 'mantra-audience' ),
		'context' => 'normal',
		'priority' => 'high',
		'screen' => array( 'page' ),
	);

	return $meta_boxes;
}
add_filter( 'mantra_audience_metaboxes', 'mantra_audience_metabox_example_meta_box' );

/**
 * Setup the custom fields for our mantraAudience Metabox Example meta box, added earlier in the mantra_audience_metabox_example_meta_box function
 *
 * @return array
 */
function mantra_audience_metabox_example_meta_box_fields( $fields, $post_type ) {
	$first_tab_options = array(
		array(
			'name' => 'text_field',
			'title' => __( 'Text field', 'mantra-audience' ),
			'description' => __( 'Field description is displayed below the field.', 'mantra-audience' ),
			'type' => 'textbox',
		),
		array(
			'name' => 'textarea_field',
			'title' => __( 'Textarea field', 'mantra-audience' ),
			'type' => 'textarea',
			'size' => 55,
			'rows' => 4,
		),
		array(
			'name' => 'image_field',
			'title' => __( 'Image field', 'mantra-audience' ),
			'description' => '',
			'type' => 'image',
			'meta' => array()
		),
		array(
			'name' => 'dropdown_field',
			'title' => __( 'Dropdown', 'mantra-audience' ),
			'type' => 'dropdown',
			'meta' => array(
				array( 'value' => '', 'name' => '' ),
				array( 'value' => 'yes', 'name' => __( 'Yes', 'mantra-audience' ), 'selected' => true ),
				array( 'value' => 'no', 'name' => __( 'No', 'mantra-audience' ) ),
			),
			'description' => __( 'You can set which option is selected by default. Cool, eh?', 'mantra-audience' ),
			// do not save the custom field when the option is set to Yes
			'default' => 'yes',
		),
		array(
			'name' => 'dropdownbutton_field',
			'title' => __( 'Dropdown Button', 'mantra-audience' ),
			'type' => 'dropdownbutton',
			'states' => array(
				array( 'value' => '', 'title' => __( 'Default', 'mantra-audience' ), 'icon' => '%s/ddbtn-blank.png', 'name' => __( 'Default', 'mantra-audience' ) ),
				array( 'value' => 'yes', 'title' => __( 'Yes', 'mantra-audience' ), 'icon' => '%s/ddbtn-check.png', 'name' => __( 'Yes', 'mantra-audience' ) ),
				array( 'value' => 'no', 'title' => __( 'No', 'mantra-audience' ), 'icon' => '%s/ddbtn-cross.png', 'name' => __( 'No', 'mantra-audience' ) ),
			),
			'description' => __( 'Similar to "dropdown" field, but allows setting custom icons for each state.', 'mantra-audience' ),
		),
		array(
			'name' => 'checkbox_field',
			'title' => __( 'Checkbox', 'mantra-audience' ),
			'label' => __( 'Checkbox label', 'mantra-audience' ),
			'type' => 'checkbox',
		),
		array(
			'name'        => 'radio_field',
			'title'       => __( 'Radio field', 'mantra-audience' ),
			'description' => __( 'You can hide or show option based on how other options are configured', 'mantra-audience' ),
			'type'        => 'radio',
			'meta'        => array(
				array( 'value' => 'yes', 'name' => __( 'Yes', 'mantra-audience' ), 'selected' => true ),
				array( 'value' => 'no', 'name' => __( 'No', 'mantra-audience' ) ),
			),
			'enable_toggle' => true,
			'default' => 'yes',
		),
		array(
			'name' => 'separator_image_size',
			'type' => 'separator',
			//'description' => __( 'Optional text to show after the separator', 'mantra-audience'. )
		),
		array(
			'type' => 'multi',
			'name' => 'multi_field',
			'title' => __( 'Multi fields', 'mantra-audience' ),
			'meta' => array(
				'fields' => array(
					array(
						'name' => 'image_width',
						'label' => __( 'width', 'mantra-audience' ),
						'description' => '',
						'type' => 'textbox',
						'meta' => array( 'size' => 'small' )
					),
					// Image Height
					array(
						'name' => 'image_height',
						'label' => __( 'height', 'mantra-audience' ),
						'type' => 'textbox',
						'meta' => array( 'size' => 'small' )
					),
				),
				'description' => __( '"Multi" field type allows displaying multiple fields together.', 'mantra-audience'),
				'before' => '',
				'after' => '',
				'separator' => ''
			)
		),
		array(
			'name'        => 'color_field',
			'title'       => __( 'Color', 'mantra-audience' ),
			'description' => '',
			'type'        => 'color',
			'meta'        => array( 'default' => null ),
			'class'      => 'yes-toggle'
		),
		array(
			'name'        => 'post_id_info_field',
			'title'       => __( 'Post ID', 'mantra-audience' ),
			'description' => __( 'This field type shows text with the ID of the post, which is: <code>%s</code>', 'mantra-audience' ),
			'type'        => 'post_id_info',
		),
	);

	$second_tab_options = array(
		array(
			'name' 		=> 'audio_field',
			'title' 	=> __( 'Audio field', 'mantra-audience' ),
			'description' => '',
			'type' 		=> 'audio',
			'meta'		=> array(),
		),
		array(
			'name' 		=> 'video_field',
			'title' 	=> __( 'Video field', 'mantra-audience' ),
			'description' => '',
			'type' 		=> 'video',
			'meta'		=> array(),
		),
        array(
			'name' => 'gallery_shortcode_field',
			'title' => __( 'Gallery Shortcode field', 'mantra-audience' ),
			'description' => __( 'Using this field type you can add a gallery manager.', 'mantra-audience' ),
			'type' => 'gallery_shortcode',
        ),
		array(
			'name' => 'date_field',
			'title' => __( 'Date field', 'mantra-audience' ),
			'description' => '',
			'type' => 'date',
			'meta' => array(
				'default' => '',
				'pick' => __( 'Pick Date', 'mantra-audience' ),
				'close' => __( 'Done', 'mantra-audience' ),
				'clear' => __( 'Clear Date', 'mantra-audience' ),
				'time_format' => 'HH:mm:ss',
				'date_format' => 'yy-mm-dd',
				'timeseparator' => ' ',
			)
		),
		array(
			'name' => 'repeater_field',
			'title' => __( 'Repeater', 'mantra-audience' ),
			'type' => 'repeater',
			'fields' => array(
				array(
					'name' => 'text_1',
					'title' => __( 'Text Field', 'mantra-audience' ),
					'type' => 'textbox',
					'class' => 'small'
				),
				array(
					'name'        => 'color_field',
					'title'       => __( 'Color', 'mantra-audience' ),
					'description' => '',
					'type'        => 'color',
				),
				array(
					'name' => 'field_3',
					'title' => __( 'Dropdown', 'mantra-audience' ),
					'type' => 'dropdown',
					'meta' => array(
						array( 'value' => 'yes', 'name' => __( 'Yes', 'mantra-audience' ) ),
						array( 'value' => 'no', 'name' => __( 'No', 'mantra-audience' ) ),
					),
				),
			),
			'add_new_label' => __( 'Add new item', 'mantra-audience' ),
		),
	);

	$fields[] = array(
		'name' => __( 'First Tab', 'mantra-audience' ), // Name displayed in box
		'id' => 'first-tab',
		'options' => $first_tab_options,
	);
	$fields[] = array(
		'name' => __( 'Second Tab', 'mantra-audience' ), // Name displayed in box
		'id' => 'second-tab',
		'options' => $second_tab_options,
	);

	return $fields;
}
add_filter( 'mantra_audience_metabox/fields/tm-example', 'mantra_audience_metabox_example_meta_box_fields', 10, 2 );


/**
 * Add sample fields to the user profile screen
 *
 * @return array
 * @since 1.0.0
 */
function mantra_audience_metabox_example_user_fields( $fields ) {
	$fields['mantra-audience-metabox-sample'] = array(
		'title' => __( 'Sample fields added by mantraAudience Metabox.', 'mantra-audience' ),
		'description' => __( 'Description text about the fields.', 'mantra-audience' ),
		'fields' => array(
			array(
				'name' => 'textbox_field',
				'title' => __( 'Text box', 'mantra-audience' ),
				'type' => 'textbox',
			),
			array(
				'name' => 'image_field',
				'title' => __( 'Image field', 'mantra-audience' ),
				'description' => __( 'This is only to show how field types can be used in user profile pages.', 'mantra-audience' ),
				'type' => 'image',
			),
		),
	);

	return $fields;
}
add_filter( 'mantra_audience_metabox/user/fields', 'mantra_audience_metabox_example_user_fields' );

/**
 * Add a sample Color field to Category taxonomy
 *
 * @since 1.0.0
 * @return array
 */
function mantra_audience_metabox_example_category_fields( $fields ) {
	$new_fields = array(
		array(
			'name'        => 'color_field',
			'title'       => __( 'Color', 'mantra-audience' ),
			'description' => '',
			'type'        => 'color',
			'meta'        => array( 'default' => null ),
		),
	);

	return array_merge( $fields, $new_fields );
}
add_filter( 'mantra_audience_metabox/taxonomy/category/fields', 'mantra_audience_metabox_example_category_fields', 10 );
