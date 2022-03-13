/* global ajaxurl, pmc_cheezcap_ajax_button */

// This code is loaded and used for the CheezCAP Ajax Button Field
// @see pmc-global-functions/classes/class-pmc-cheezcap-ajax-button.php

( function( $ ) {

	// When the AJAX button is clicked..
	$( document ).on( 'click', 'button.cheezcap-ajax-button', function ( e ) {

		e.preventDefault();

		var $container = $( '.pmc-cheezcap-ajax-button' ),
			$waiting = $container.find( '.waiting' ),
			$output = $container.find( '.output small' );

		// Reveal the waiting animation
		$waiting.removeClass( 'hideme' );

		// POST to the server to fire our ajax callback
		$.post(
			ajaxurl,
			{
				'action'   : 'pmc_cheezcap_ajax_button',
				'nonce'    : pmc_cheezcap_ajax_button.nonce,
				'option_id': pmc_cheezcap_ajax_button.option_id
			},
			function ( response ) {

				// Hide the waiting animation
				$waiting.addClass( 'hideme' );

				// Display the output message
				$output.text( response.message );

				// Was the response successful or not?
				// Events below can be listened to if need be..
				if ( response.success ) {

					var event = new Event( response.option_id + '-success' );

				} else if ( response.error ) {

					var event = new Event( response.option_id + '-failure' );

				}
			}
		);
	} );

} )( jQuery );