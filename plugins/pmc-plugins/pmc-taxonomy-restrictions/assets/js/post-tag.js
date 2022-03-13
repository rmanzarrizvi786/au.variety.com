/**
 * Hide 'Add New Tag' form
 */

/**
 * To Generate a Minified Version:
 * npm install uglify -g
 * cd pmc-taxonomy-restrictions/assets/js/
 * uglify -s main.js -o main.min.js
 */

( function( $ ) {

	if ( 'post_tag' === $( '#addtag' ).find( 'input[name="taxonomy"]' ).val() ) {

		$( '#addtag' ).parents( '.form-wrap' ).hide();

	}

} )( jQuery );