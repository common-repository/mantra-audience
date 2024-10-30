<?php
/**
 * Adds script files to load assets required by the Popup plugin
 *
 * @package Mantra Audience
 */
?>
<script>
jQuery(function($){
	if( $( '.mantra-audience' ).length ) {
		function load() {
			window.mantraAudiencePopup = { ajaxurl : '<?php echo admin_url( 'admin-ajax.php' ); ?>' };

		}
		load();
	}
});
</script>