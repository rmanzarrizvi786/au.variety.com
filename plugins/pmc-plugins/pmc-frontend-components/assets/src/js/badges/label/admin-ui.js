/**
 * JS to add TinyMCE functionality for the Label Badge
 *
 * @author Amit Gupta
 */

( function() {

	if ( ! tinymce ) { // eslint-disable-line
		return;
	}

	( function() {

		tinymce.create( 'tinymce.plugins.pmcfcBadgeLabelPlugin', {

			init: ( editor, url ) => {

				editor.addButton( 'pmcfcBadgeLabelButton', {
					title: window.pmcfcBadgeLabelData.buttonTitle,
					cmd: 'pmcfcBadgeLabelCmd',
					image: false
				} );

				editor.addCommand( 'pmcfcBadgeLabelCmd', () => {

					let shortcodeOpeningTag = '[' + window.pmcfcBadgeLabelData.shortcodeTag + ']';
					let shortcodeClosingTag = '[/' + window.pmcfcBadgeLabelData.shortcodeTag + ']';

					let selectedText = editor.selection.getContent( { format: 'text' } );
					selectedText = selectedText.replaceAll( shortcodeOpeningTag, '' );
					selectedText = selectedText.replaceAll( shortcodeClosingTag, '' );

					let modal = editor.windowManager.open( {

						title: window.pmcfcBadgeLabelData.modalTitle,
						body: [
							{
								type: 'textbox',
								name: 'label',
								label: window.pmcfcBadgeLabelData.fieldLabel,
								minWidth: 500,
								value: selectedText
							}
						],
						buttons: [
							{
								text: window.pmcfcBadgeLabelData.buttonOk,
								subtype: 'primary',
								onclick: () => {
									modal.submit();
								}
							},
							{
								text: window.pmcfcBadgeLabelData.buttonCancel,
								onclick: () => {
									modal.close();
								}
							}
						],
						onsubmit: ( e ) => {

							let labeltext = ( 1 > e.data.label.length ) ? selectedText : e.data.label;

							// Lets make sure we use only text
							let doc = new DOMParser().parseFromString( labeltext, 'text/html' );
							labeltext = doc.body.textContent || '';

							let labelShortCode = shortcodeOpeningTag;
							labelShortCode += labeltext;
							labelShortCode += shortcodeClosingTag;

							editor.execCommand( 'mceInsertContent', false, labelShortCode );

						}

					} );

				} );

			}

		} );

		tinymce.PluginManager.add( 'pmcfcBadgeLabelButton', tinymce.plugins.pmcfcBadgeLabelPlugin );

	}() );

}() );


//EOF
