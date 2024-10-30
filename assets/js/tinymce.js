(function($) {
	'use strict';

	if( typeof mcemantraAudiencePopup !== 'object' )
		return;

	$( '<script type="text/html" id="tmpl-mantra-audience-shortcode">' + mcemantraAudiencePopup.fields.template + '</script>' ).appendTo( 'body' );

	tinymce.PluginManager.add( 'mcemantraAudiencePopup', function( editor, url ) {

		function createColorPickAction() {
			var colorPickerCallback = editor.settings.color_picker_callback;

			if ( colorPickerCallback ) {
				return function() {
					var self = this;

					colorPickerCallback.call(
						editor,
						function( value ) {
							self.value( value ).fire( 'change' );
						},
						self.value()
					);
				};
			}
		}

		editor.addButton( 'mcemantraAudiencePopup', {
			title: mcemantraAudiencePopup.labels.menuName,
			image: url + '/../images/icon.svg',
			onclick: function(){
				var fields = [];
				jQuery.each( mcemantraAudiencePopup.fields.fields, function( i, field ){
					if( field.type == 'colorbox' ) {
						field.onaction = createColorPickAction()
					}
					fields.push( field );
				} );

				editor.windowManager.open({
					'title' : mcemantraAudiencePopup.labels.menuName,
					'body' : fields,
					onSubmit : function( e ){
						var values = this.toJSON(); // get form field values
						values.selectedContent = editor.selection.getContent();
						var template = wp.template( 'mantra-audience-shortcode' );
						editor.insertContent( template( values ) );
					}
				});
			}
		});
	});
})(jQuery);