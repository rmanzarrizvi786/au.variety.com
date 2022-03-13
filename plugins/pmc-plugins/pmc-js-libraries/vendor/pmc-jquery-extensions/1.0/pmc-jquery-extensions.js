( function( $ ) {

	/**
	 * Add some custom jQuery selectors
	 */
	$.extend( $.expr[':'], {
	
		/**
		 * New jQuery :pmc-inview selector
		 *
		 * Usage $( 'p.my-class:pmc-inview' );
		 *
		 * @return bool True when the given element is in view/seen by the user.
		 *              False otherwise.
		 */
		'pmc-inview': function( element, index, match ) {
			var elementIsVisible = element.offsetHeight,
				elementPosition = element.getBoundingClientRect(),
				elementMiddlePosition = ( elementPosition.height / 2 ) + elementPosition.top;

			return ( elementIsVisible > 0 ) && ( elementMiddlePosition >= 0 ) && ( elementMiddlePosition <= window.innerHeight );
		},
		
		/**
		 * New jQuery :pmc-middle-child selector
		 *
		 * Similar to :first-child or :last-child, but matches
		 * the middle child.
		 *
		 * Usage $( 'div#container p:pmc-middle-child' );
		 *
		 * @return bool True when the given element is the middle child.
		 *              False otherwise.
		 */
		'pmc-middle-child': function( element, index, match ) {
			
			// Within the context of this callback function we have
			// no idea what the full jQuery selector was, e.g. if the
			// selector was $( 'div#container p:pmc-middle-child' )
			// we have no way of getting the 'div#container' part. This
			// is unfortunate because we need to know the parent to determine
			// if the current <p> element is the middle child. Here we're
			// simply getting the immediate .parent() though that will not
			// always be accurate.
			var $items = $( element ).parent().find( element.localName );
			
			// 3 is the fewest number of items to warrant a 'middle'
			if ( 3 <= $items.length ) {
				return element === $items[ Math.round( $items.length / 2 ) ];
			}
			
			return false;
		}
	} );
} )( jQuery );
