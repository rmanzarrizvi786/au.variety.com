// adds woobox sdk just after opening body tag (if not already added)
jQuery( document ).ready( function ( $ ) {
	jQuery( "body" ).prepend( "<div id='woobox-root'></div>" );
	(
		function ( d, s, id ) {
			var js, fjs = d.getElementsByTagName( s )[0];
			if ( d.getElementById( id ) ) {
				return;
			}
			js = d.createElement( s );
			js.id = id;
			js.src = "//woobox.com/js/plugins/woo.js";
			fjs.parentNode.insertBefore( js, fjs );
		}( document, 'script', 'woobox-sdk' )
	);
} );
