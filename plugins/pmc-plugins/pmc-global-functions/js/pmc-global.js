/* global jQuery, __cmp */
jQuery( document ).ready( function( $ ) {

	// Trigger Consent preference modal.
	$( '.privacy-consent' ).on( 'click', function( e ) {
		
		/**
		 * Consent Manager call, provided by Quantcast Choice.
		 *
		 * This triggers the consent modal to show, for user to update any privacy
		 * preferences.
		 *
		 * @see https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/CMP%20JS%20API%20v1.1%20Final.md#CMP-JS-API
		 * @see https://quantcast.zendesk.com/hc/en-us/articles/360003814853-Technical-Implementation-Guide
		 *
		 * @since 2018-05-25 PMCEED-477
		 */

		if ( 'function' === typeof __cmp  ) {
			e.preventDefault();
			__cmp( 'displayConsentUi' );
		}
	} );

});
