<?php

return array(
	'label' => __( 'Button', 'mantra-audience' ),
	'fields' => array(
		array(
			'name' => 'link',
			'type' => 'listbox',
			'values' => $this->get_manual_popup_list(),
			'label' => __( 'Link Button To', 'mantra-audience' ),
		),
		array(
			'name' => 'link_description',
			'type' => 'container',
			'html' => sprintf( __( 'Add new popups at <a href="#">Mantra Audiences > Add new</a>.<br> The popup must select "Manual launch" in order to be launched manually.', 'mantra-audience' ), admin_url( 'post-new.php?post_type=mantra_audience' ) ) . '<hr style="border-bottom: 1px solid #ccc; margin: 5px 0;">',
		),
		array(
			'name' => 'label',
			'type' => 'textbox',
			'label' => __( 'Button Text', 'mantra-audience' ),
			'value' => __( 'Launch Popup', 'mantra-audience' ),
		),
		array(
			'name' => 'color',
			'type' => 'listbox',
			'values' => array(
				array( 'value' => '', 'text' => '' ),
				array( 'value' => 'blue', 'text' => __( 'Blue', 'mantra-audience' ) ),
				array( 'value' => 'green', 'text' => __( 'Green', 'mantra-audience' ) ),
				array( 'value' => 'red', 'text' => __( 'Red', 'mantra-audience' ) ),
				array( 'value' => 'purple', 'text' => __( 'Purple', 'mantra-audience' ) ),
				array( 'value' => 'yellow', 'text' => __( 'Yellow', 'mantra-audience' ) ),
				array( 'value' => 'orange', 'text' => __( 'Orange', 'mantra-audience' ) ),
				array( 'value' => 'pink', 'text' => __( 'Pink', 'mantra-audience' ) ),
				array( 'value' => 'lavender', 'text' => __( 'Lavender', 'mantra-audience' ) ),
				array( 'value' => 'gray', 'text' => __( 'Gray', 'mantra-audience' ) ),
				array( 'value' => 'black', 'text' => __( 'Black', 'mantra-audience' ) ),
				array( 'value' => 'light-yellow', 'text' => __( 'Light Yellow', 'mantra-audience' ) ),
				array( 'value' => 'light-blue', 'text' => __( 'Light Blue', 'mantra-audience' ) ),
				array( 'value' => 'light-green', 'text' => __( 'Light Green', 'mantra-audience' ) ),
			),
			'label' => __( 'Button Color', 'mantra-audience' ),
		),
		array(
			'name' => 'size',
			'type' => 'listbox',
			'values' => array(
				array( 'value' => '', 'text' => __( 'Normal', 'mantra-audience' ) ),
				array( 'value' => 'small', 'text' => __( 'Small', 'mantra-audience' ) ),
				array( 'value' => 'large', 'text' => __( 'Large', 'mantra-audience' ) ),
				array( 'value' => 'xlarge', 'text' => __( 'xLarge', 'mantra-audience' ) ),
			),
			'label' => __( 'Button Size', 'mantra-audience' ),
		),
		array(
			'name' => 'custom_color',
			'type' => 'colorbox',
			'value' => '',
			'label' => __( 'Custom Background Color', 'mantra-audience' ),
			'tooltip' => __( 'Enter color in hexadecimal format. For example, #ddd.', 'mantra-audience' )
		),
		array(
			'name' => 'custom_text_color',
			'type' => 'colorbox',
			'label' => __( 'Custom Button Text Color', 'mantra-audience' ),
			'tooltip' => __( 'Enter color in hexadecimal format. For example, #000.', 'mantra-audience' )
		),
		array(
			'name' => 'block',
			'type' => 'checkbox',
			'label' => __( 'Fullwidth Button', 'mantra-audience' ),
		),
		array(
			'name' => 'style',
			'type' => 'textbox',
			'label' => __( 'Additional Styles', 'mantra-audience' ),
			'tooltip' => __( 'Additional button styles. You can enter one or more of: outline, gradient, flat, rounded, embossed; or a custom CSS classname.', 'mantra-audience' )
		),
	),
	'template' => '[ma_popup<# if ( data.style = [ data.size, data.color, data.style, ( data.block ) ? "block" : "" ].filter( Boolean ).join( " " ) ) { #> style="{{data.style}}"<# } #> link="{{data.link}}"<# if ( data.custom_color ) { #> color="{{data.custom_color}}"<# } #><# if ( data.custom_text_color ) { #> text="{{data.custom_text_color}}"<# } #>]{{{data.label}}}[/ma_popup]',
);