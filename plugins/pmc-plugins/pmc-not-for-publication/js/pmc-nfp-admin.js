/*
 * PMC Not For Publication plugin
 *
 * @author Amit Gupta
 * @since 2013-09-12
 */

jQuery( document ).ready( function( $ ) {

	$( '#publish' ).prop( 'disabled', true ).hide();	//disable & hide Publish button
	$( '#visibility' ).hide();							//hide post visibility setting

	//set hidden flag to indicate we need to create a copy
	$( '#' + pmc_nfp.field_prefix + 'copy_post' ).on( 'click', function() {
		$( '#' + pmc_nfp.field_prefix + 'do_copy' ).val( 'yes' );
	} );

} );

//EOF