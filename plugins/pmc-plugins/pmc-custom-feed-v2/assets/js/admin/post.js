/**
 * Script to show and hide Alternate Heading based on Post title ('amazon' keyword)
 */

jQuery( document ).ready(

	function( $ ) {

		pmc_custom_feed_v2_amazon_ui_toggle_alt_heading();

		$( '#titlediv #titlewrap' ).on( 'input', _.throttle( pmc_custom_feed_v2_amazon_ui_toggle_alt_heading, 2000 ) );

		function pmc_custom_feed_v2_amazon_ui_toggle_alt_heading() {

			var currentValue = $( 'input[name=post_title]' ).val();

			if ( 0 <= currentValue.search( /amazon/i ) ) {
				$( '.alternate-heading' ).show( 500 );
			} else {
				$( '.alternate-heading' ).hide( 500 );
			}

		}

	}

);

//EOF
