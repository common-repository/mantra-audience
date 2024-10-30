<?php
/**
 * Data for the sample popup post created on activation of the plugin
 *
 * @package Mantra Audience
 * @since 1.0.0
 */

// if there are existing popup posts, don't create any
$query = get_posts( array(
	'post_type' => 'mantra_audience',
	'posts_per_page' => 1,
	'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' )
) );
if( ! empty( $query ) ) {
	return;
}

$data = array(
	'post_type' => 'mantra_audience',
	'post_title' => __( 'Basic Lead Generation & Popup', 'mantra-audience' ),
	'post_content' => '<div class="welcome-popup">
<h2>Thank you for using <br>
Mantra Audience</h2>
<p>This is pop up plugin. <a href="https://mantrabrain.com/">Mantra Audience</a> or <a href="https://wordpress.org/plugins/mantra-audience/">Mantra Audience</a> (free) plugin is recommended to design the pop up layouts.</p>

</div>',
	'post_status' => 'publish',
	'meta_input' => array(
		'popup_start_at' => '1970-01-01 00:00:00',
		'popup_end_at' => '3015-01-01 00:00:00',
		'popup_width' => 500,
		'_ma_popup_sample_post' => 'yes', // flag, may come handy in the future,
		'custom_css' => '%POPUP% .welcome-popup {
	background: #fff;
	color: #777;
	font-size: 1em;
	line-height: 1.6em;
	text-align: center;
	padding: 75px 10% 60px;
}
%POPUP% .welcome-popup h1,
%POPUP% .welcome-popup h2,
%POPUP% .welcome-popup h3 {
	color: #000;
}
%POPUP% .welcome-popup a {
	color: #755ebb;
	text-decoration: none;
}
%POPUP% .action-buttons {
	margin: 30px 0 10px;
}
%POPUP% .action-buttons a {
	font-size: 1.2em;
	line-height: 1;
	background: #755ebb;
	color: #fff;
	display: inline-block; 
	vertical-align: middle;
	padding: 11px 26px;
	margin: 5px 3px;
	text-decoration: none;
	border-radius: 40px;
}
%POPUP% .action-buttons a.outline {
	background: none;
	color: #755ebb;
	border: solid 2px;
}
',
	)
);

wp_insert_post( $data );