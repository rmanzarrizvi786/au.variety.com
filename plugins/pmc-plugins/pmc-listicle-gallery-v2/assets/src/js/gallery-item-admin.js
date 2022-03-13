( function ( $ ) {

	var PmcGalleryItemAdmin = {

		LastEditorId: null,

		SetupCaptionEditor: function () {

			// Bind click event for gallery thumbnails

			$( document ).on( 'click', '.attachments li.attachment', function ( e ) {

				// Remove the last editor from the EditorManager since it has been removed from the DOM. Otherwise
				// we can't attach it again.

				if ( PmcGalleryItemAdmin.LastEditorId ) {
					tinyMCE.EditorManager.execCommand( 'mceRemoveEditor', true, PmcGalleryItemAdmin.LastEditorId );
				}

				// Get the data-id of the clicked thumbnail

				var dataId = $( this ).data( 'id' );

				// Locate the corresponding caption textarea and assign an id to it so we can attach a tinyMCE editor

				var textareaSelector = '.media-sidebar .attachment-details[data-id=' + dataId + '] .setting[data-setting=caption] textarea';
				var textareaId = 'pmc-image-caption-' + dataId;
				$( textareaSelector ).attr( 'id', textareaId );
				PmcGalleryItemAdmin.LastSelector = textareaId;

				// Attach the editor, if not already attached

				PmcGalleryItemAdmin.AttachCaptionEditor( textareaId );

				// Invoke the thumbnail's click event before the "Update Gallery" button is clicked.
				// This is a workaround for ensuring that caption updates are displayed after we exit
				// the media edit dialog.

				$( document ).on( 'focus', '.media-button-insert', function ( e ) {
					$( 'li.attachment[data-id=' + dataId + '] .thumbnail' ).click();
				} );

			} );

		},

		AttachCaptionEditor: function ( selector ) {

			tinyMCE.init( {
				selector: '#' + selector,
				branding: false,
				elementpath: false,
				height: 250,
				menubar: false,
				plugins: 'wordpress wplink',
				toolbar: 'bold, italic, wp_link_advanced',
				setup: function ( editor ) {
					editor.on( 'change', function ( e ) {
						editor.save();
					} );
					PmcGalleryItemAdmin.LastEditorId = editor.id;
					try {
						// workaround issue with wplink tinymce plugin
						if ( !editor.wp && tinyMCE.editors && tinyMCE.editors[ 0 ] && tinyMCE.editors[ 0 ].wp ) {
							editor.wp = tinyMCE.editors[ 0 ].wp;
						} else {
							if ( !editor.wp ) {
								editor.wp = {
									_createToolbar: function () {
										return {
											on: function () {
											}
										}
									}
								};
							}
						}
					}
					catch ( ignore ) {
					}
				}
			} );

		}

	};

	$( document ).ready( function () {
		PmcGalleryItemAdmin.SetupCaptionEditor();
	} );

})( jQuery );
