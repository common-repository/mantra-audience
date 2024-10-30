var mantraAudienceMediaLib = {};

(function($){

'use strict';

mantraAudienceMediaLib = {
	init: function() {
		this.mediaUploader();
	},

	mediaUploader: function() {

		// Uploading files
		var file_frame = ''; // Set this

		$( 'body' ).on( 'click', '.mantra-audience-media-lib-browse', function( event ) {
			var $el = $(this), $data = $el.data('submit'), type = $el.data('type');

			file_frame = wp.media.frames.file_frame = wp.media({
				title: $(this).data('uploader-title'),
				library: {
					type: type
				},
				button: {
					text: $(this).data('uploader-button-text')
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				// We set multiple to false so only get one image from the uploader
				var attachment = file_frame.state().get('selection').first().toJSON();
				$data.attach_id = attachment.id;

				var data_field = $el.data('fields');
				$('#' + data_field).val(attachment.url);

				// custom event
				$( 'body' ).trigger( 'mantra_audience_metabox_lib_selected', [ $el, attachment, file_frame ] );

				// show image preview, only applicable to "image" picker
				if( type === 'image' ) {
					$.ajax({
						type: "POST",
						url: ajaxurl,
						data: $data,
						dataType: 'json',
						success: function( data ){
							mantraAudienceMediaLib.setPreviewIcon($el.closest( '.mantra_audience_field, .mantra_audience_field_row' ), data.thumb );
						}
					});
				} else if( type === 'audio' || type === 'video' ) {
					$el.closest( '.mantra_audience_field_row' ).find( '.mantra_audience_featimg_remove' ).removeClass('hide');
				}
			});

			// Finally, open the modal
			file_frame.open();
			event.preventDefault();
		});
	},

	setPreviewIcon: function( $field, thumb ) {
		var post_image_preview = $('<a href="' + thumb + '" target="_blank"><img src="' + thumb + '" width="40" /></a>')
			.fadeIn(1000)
			.css('display', 'inline-block');

		if( $field.find('.mantra_audience_upload_preview').find('a').length > 0 ) {
			$field.find('.mantra_audience_upload_preview').find('a').remove();
		}

		$field.find('.mantra_audience_upload_preview').fadeIn().append(post_image_preview);
		$field.find('.mantra_audience_featimg_remove').removeClass('hide');
	}
};

$(document).ready(function(){
	mantraAudienceMediaLib.init();
});

})(jQuery);