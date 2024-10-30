<?php
/**
 * Template file to display popup single pages
 * Removes clutter and all theme elements from the page, leaving just the content area
 */
global $mantra_audience_custom_css;
?><!DOCTYPE html>
<html <?php language_attributes(); ?> style="background: #ccc; padding-top: 50px;">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> style="background: #fff; width: <?php echo mantra_audience_get( 'popup_width' ) . mantra_audience_get( 'popup_width_unit', 'px' ); ?>; min-height: <?php echo mantra_audience_get( 'popup_height' ) . mantra_audience_get( 'popup_height_unit', 'px' ); ?>; margin: auto;" id="mantra-audience-<?php the_id(); ?>">

	<?php if( have_posts() ) : while( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>

        <?php $mantra_audience_custom_css.= mantra_audience_get_custom_css(); ?>

	<?php endwhile; endif; ?>

	<?php wp_footer(); ?>
</body>
</html>