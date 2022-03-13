/**
 * Main JS file.
 *
 * @author Kelin Chauhan <kelin.chauhan@rtcamp.com>
 *
 * To Generate a Minified Version:
 * npm install uglify -g
 * cd pmc-excerpt/assets/
 * uglify -s src/js/main.js -o build/js/main.js
 */

 /*global pmcExcerptConfig*/

( function( $ ) {


	var pmc_excerpt_limit = {

		/**
		 * Initializes the functionality of excerpt limit.
		 *
		 * Adds html element that displays the limit also adds a callback to excerpt field for
		 * displaying the characters inserted. Conditionally restricts user from typing more chars
		 * than limit specified via Theme Setting.
		 */
		init: function() {

			var excerptEl = $( '[name=excerpt]' ),
				maxCharLimit,
				preventTyping,
				countDiv,
				charCountEl,
				maxLimitEl;

			// Return if can't find the configuration for excerpt set via Theme Setting.
			if ( 'undefined' === typeof pmcExcerptConfig || 0 === excerptEl.length ) {
				return;
			}

			maxCharLimit  = pmcExcerptConfig.pmc_excerpt_limit;
			preventTyping = pmcExcerptConfig.pmc_excerpt_prevent;

			countDiv      = $( '<div/>' ).css({'width': '99%'});

			charCountEl   = $( '<span/>' )
								.addClass( 'excerpt-chars' )
								.text( '0' );

			maxLimitEl    = $( '<span/>' ).text( maxCharLimit );

			countDiv.append( charCountEl )
					.append( ' / ' )
					.append( maxLimitEl )
					.append( ' Characters' );

			countDiv.addClass( 'excerpt-char-count' ).insertAfter( excerptEl );

			charCountEl.text( excerptEl.val().length );

			// Stop user from entering more chars than max limit.
			if ( 'enable' === preventTyping ) {
				excerptEl.attr( 'maxlength', maxCharLimit );
			}

			excerptEl.on( 'input propertychange', function() {

				var excerpLength = excerptEl.val().length;

				// Don't increment the character count if Theme Setting is set for preventing user from adding more chars than limit.
				if ( 'enable' === preventTyping && excerpLength > maxCharLimit ) {
					return;
				}

				charCountEl.text( excerpLength );

			});

		}

	};

	$( document ).ready( function() {

		pmc_excerpt_limit.init();

	});

}( jQuery ) );
