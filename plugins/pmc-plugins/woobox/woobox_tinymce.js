jQuery( document ).ready( function ( $ ) {
	tinymce.create( 'tinymce.plugins.woobox_plugin', {
		init: function ( ed, url ) {
			ed.addButton( 'woobox_button', {
				title: 'Embed Woobox Offer',
				image: url + '/woobox_tinymce_icon.png',
				onclick: function () {
					ed.windowManager.open( {
						title: ' Embed Woobox Offer',
						body: [
							{
								type: 'textbox',
								name: 'offercode',
								label: 'Enter Offer Code'
							}
						],
						onsubmit: function ( e ) {
							ed.insertContent( '[woobox offer=\'' + e.data.offercode + '\']' );
						}
					} )
				}
			} );

		}
	} );
	tinymce.PluginManager.add( 'woobox_button', tinymce.plugins.woobox_plugin );
} );
