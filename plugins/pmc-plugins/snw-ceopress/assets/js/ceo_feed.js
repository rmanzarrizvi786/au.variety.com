( function( $ ) {
	$( document ).on( 'change', '.ceopress-select-js', function() {
		var val = $( this ).val(),
			key = $( this ).data( 'key' ),
			qs  = '';

		if ( val ) {
			qs = '&k=' + key + '&v=' + val;
		}

		window.location.href = 'tools.php?page=ceo-feed' + qs;
	});
}( jQuery ) );
