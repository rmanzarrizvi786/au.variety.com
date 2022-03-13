jQuery(document).ready(function($) {
	jQuery("body").on('click', '.closeShare', function(event) {
			event.preventDefault();
			jQuery(event.target).parents(".share-container").find(".shareMore").fadeOut(300);
	});

	jQuery("body").on('click', '.showShareMore', function(event) {
			event.preventDefault();
			jQuery(event.target).parents(".share-container").find(".shareMore").fadeIn(300);
			jQuery(event.target).parents(".share-container").find(".shareMore").css( {'z-index': '99999'} );
	});

	jQuery('.btn-print').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		window.print();
	});

	jQuery("body").on('click', '.btn-whatsapp', function (event) {
			event.preventDefault();
			event.stopPropagation();
			var whatsapp_url = "whatsapp://send?text=" + encodeURIComponent( window.location.href );
			window.location.href = whatsapp_url;
	});
});
