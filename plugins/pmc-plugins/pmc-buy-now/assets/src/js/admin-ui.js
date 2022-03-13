import './../scss/admin-ui.scss';

( function() {

	if ( ! tinymce ) { // eslint-disable-line
		return;
	}

	let PMCBuyNow = ( () => {

		return {
			init: () => {
				tinymce.create( 'tinymce.plugins.pmc_buy_now_plugin', { // eslint-disable-line
					init: ( editor, url ) => {

						let $modal = jQuery( '#pmc-buy-now-dialog' );
						$modal.selectedContent = false;

						$modal.dialog({
							title: PMC_BUY_NOW_TEXT.title, // eslint-disable-line
							dialogClass: 'pmc-buy-now-dialog',
							autoOpen: false,
							draggable: false,
							width: 'auto',
							modal: true,
							resizable: false,
							closeOnEscape: true,
							position: {
								my: 'center',
								at: 'center',
								of: window
							},
							open: () => {

								// Focus in first input.
								$modal.find( 'input:eq(0)' ).focus();

								// Close dialog by clicking the overlay behind it.
								jQuery( '.ui-widget-overlay' ).on( 'click', () => {
									$modal.dialog( 'close' );
								});
							},
							create: () => {

								// Style fix for WordPress admin.
								jQuery( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
							}
						});

						editor.addCommand( 'pmc_buy_now_insert_shortcode', () => {
							$modal.selectedContent = editor.selection.getContent();
							$modal.dialog( 'open' );
						});

						editor.addButton( 'pmc_buy_now_button', {
							title: PMC_BUY_NOW_TEXT.title, // eslint-disable-line
							cmd: 'pmc_buy_now_insert_shortcode',
							image: url + '/../images/icon.svg'
						});

					}
				});

				tinymce.PluginManager.add( 'pmc_buy_now_button', tinymce.plugins.pmc_buy_now_plugin ); // eslint-disable-line

			},

			events: () => {
				jQuery( function( $ ) {
					let $modal        = $( '#pmc-buy-now-dialog' );
					let defaultButton = $modal.find( 'form input[type="hidden"]:first-child' ).attr( 'name' );

					// Modal form submit.
					$modal.find( 'form' ).on( 'submit', ( e ) => {
						e.preventDefault();

						let data       = $modal.find( 'form' ).serializeArray();
						let dataFormat = $.map( data, ( n ) => {
							let isHiddenElement = !! ( n.name.match( /^_.*/ ) );
							let isVisible       = $modal.find( '*[name="' + n.name + '"]' ).is( ':visible' );
							if ( ! isHiddenElement && isVisible && n.value ) {
								return n.name + '=' + '"' + n.value + '"';
							}
						});

						let content = '[buy-now ' + dataFormat.join( ' ' );

						if ( $modal.selectedContent ) {
							content += ']' + $modal.selectedContent + '[/buy-now]';
						} else {
							content += '/]';
						}

						tinymce.execCommand( 'mceInsertContent', false, content ); // eslint-disable-line

						$modal.dialog( 'close' );
					});

					// Close form modal.
					$modal.find( '.pmc-buy-now-close' ).on( 'click', ( e ) => {
						e.preventDefault();

						$modal.dialog( 'close' );
					});

					$modal.find( 'select[name="button_type"]' ).on( 'change', ( e ) => {
						PMCBuyNow.buttonSetup( e.target.value );
					});

					PMCBuyNow.buttonSetup( defaultButton );
				});

			},

			buttonSetup: ( button ) => {
				jQuery( function( $ ) {

					let $modal = $( '#pmc-buy-now-dialog' );

					if ( '_' !== button.charAt( 0 ) ) {
						button = '_' + button;
					}

					let fields = $( 'input[name="' + button + '"' ).val();

					fields = fields.split( ',' );
					fields.push( 'button_type' );

					$modal.find( 'form > label' ).hide();

					for ( let i = 0; i < fields.length; i++ ) {
						$modal.find( 'form > label *[name="' + fields[ i ] + '"]' ).parent( 'label' ).css( 'display', 'block' );
					}
				});
			}
		};

	})();

	PMCBuyNow.init();
	PMCBuyNow.events();

}() );
