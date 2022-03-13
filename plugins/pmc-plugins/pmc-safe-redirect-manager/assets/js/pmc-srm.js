/* global jQuery, ajaxurl, pmcSRM */
( function( $ ) {
	// Edit League Popup, validate user created league and update data.
	$( '.pmc-srm-row-mark-permanent' ).on(
		'click', function() {

			if ( 'undefined' === typeof ajaxurl  || 'object' !== typeof pmcSRM || ! pmcSRM._nonce ) {
				return false;
			}

			const nonce   = pmcSRM._nonce;
			const action  = pmcSRM.row_action;
			const post_id = $( this ).data( 'id' );

			var row    = $( '#post-' + post_id );
			var notice = $( 'td:first-of-type', row );
			var ptag   = $( '</p>' ).addClass( 'pmc-srm' ).hide();

			$( 'p.pmc-srm', notice ).remove();

			var doAjax = function() {

				disableRow();

				$.ajax(
					{
						url: ajaxurl,
						dataType: 'json',
						type: "POST",
						data: {
							action: action,
							post_id: post_id,
							security: nonce,
						},
						success: function( data ) {

							if ( data.success ) {

								showNotification( 'Successfully moved to permanent legacy redirect' );
							} else {

								showNotification( data.data, 'red' )
								disableRow( false );
							}
						},
						error: function( data ) {

							showNotification( 'Something went wrong!', 'red' );
							disableRow( false );
						}
					}
				);

				return false;

			};

			var disableRow = function( disable = true ) {
				row.css( 'opacity', ( disable ? '0.5' : '1' ) );
				row.css( 'pointer-events', 'inherit' );
			}

			var showNotification = function( msg, color = 'green' ) {

				ptag.empty();
				ptag.html( msg );
				ptag.css( 'color', color );

				notice.append( ptag );

				ptag.fadeIn();
			}

			doAjax();

			return false;
		}
	);
}( jQuery ) );
