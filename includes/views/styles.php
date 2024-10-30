<?php
/**
 * Template to render popups on frontend
 *
 * @var $id
 */

$styles = array();
global $post;

if( mantra_audience_check( 'popup_width' ) ) {

	if( $style == 'classic' ) {
		$styles[] = "body.mantra-audience-showing-{$id} .mfp-wrap .mfp-inline-holder .mfp-content { width: " . mantra_audience_get( 'popup_width' ) . mantra_audience_get( 'popup_width_unit', 'px' ) . " !important; }";
	} elseif( $style == 'slide-out' ) {
		$styles[] = "#mantra-audience-{$id} { width: " . mantra_audience_get( 'popup_width' ) . mantra_audience_get( 'popup_width_unit', 'px' ) . " !important; }";
	}
}
if( mantra_audience_check( 'popup_height' ) && ! mantra_audience_check( 'popup_auto_height' ) ) {
	if( $style == 'classic' ) {
		$styles[] = "body.mantra-audience-showing-{$id} .mfp-wrap .mfp-inline-holder .mfp-content { height: " . mantra_audience_get( 'popup_height' ) . mantra_audience_get( 'popup_height_unit', 'px' ) . " !important; }";
	} elseif( $style == 'slide-out' ) {
		$styles[] = "#mantra-audience-{$id} { height: " . mantra_audience_get( 'popup_height' ) . mantra_audience_get( 'popup_height_unit', 'px' ) . " !important; }";
	}
}
if( mantra_audience_check( 'popup_overlay_color' ) ) {
	$styles[] = "body.mantra-audience-showing-{$id} .mfp-bg { background-color: " . mantra_audience_get( 'popup_overlay_color' ) . "; }";
}
$styles[] = mantra_audience_get_custom_css();

if( ! empty( $styles ) ) {
    $mantra_audience_custom_css.= join( "\n", $styles );
}