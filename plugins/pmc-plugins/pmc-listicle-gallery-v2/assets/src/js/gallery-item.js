( function ($) {

	var is_mobile = ( $( 'body.pmc-mobile' ).length ) ? true : false;
	var $ns = $( '.pmc-listicle-gallery-v2' );

	initialize_event_handlers();
	set_image_dimensions();
	adjust_header_grid();
	initialize_slick_carousel();

	function initialize_event_handlers() {

		if ( ! is_mobile ) {
			$ns.find( '.slide' ).on( 'click', show_modal_dialog );
			$ns.find( '.gallery-modal' ).on( 'click', close_modal_dialog );
			$ns.find( '.modal-close' ).on( 'click', close_modal_dialog );
			$ns.find( '.modal-content' ).on( 'click', function(e) {
				e.stopPropagation();
			} );
		}

	}

	function close_modal_dialog() {
		$ns.find( '.gallery-modal' ).hide();
	}

	/**
	 * Sets the slide and thumbnail dimensions
	 *
	 */
	function set_image_dimensions() {

		var slide_img = $ns.find( '.slide img' );
		var thumb_img = $ns.find( '.thumbnail img' );

		slide_img.css( 'max-width', settings.slide_width + 'px' );
		slide_img.css( 'max-height', settings.slide_height + 'px' );

		thumb_img.css( 'max-width', settings.thumb_width + 'px' );
		thumb_img.css( 'max-height', settings.thumb_height + 'px' );

	}

	/**
	 * Adjusts the header grid columns for mobile
	 *
	 */
	function adjust_header_grid() {

		if ( is_mobile ) {
			$ns.find( '.gallery-index' ).removeClass( 'col-1' ).addClass( 'col-2' );
			$ns.find( '.gallery-title' ).removeClass( 'col-11' ).addClass( 'col-10' );
		}

	}

	/**
	 * Tells Slick what content to use for the carousel.
	 *
	 * The slides and the thumbnails are setup as separate carousels, but with
	 * interconnected navigation.
	 *
	 */
	function initialize_slick_carousel() {

		// get the number of thumbnails to display

		var thumbs_count = parseInt( settings.thumbs_count, 10 );

		if ( isNaN( thumbs_count ) ) {
			thumbs_count = 0;
		}

		// initialize the carousel

		var slides = $ns.find( '.gallery-slides' );
		var thumbs = $ns.find( '.gallery-thumbs' );

		slides.on( 'beforeChange', function( event, slick, currentSlide, nextSlide ) {
			$ns.find( '.gallery-captions .caption:nth-child(' + ( currentSlide + 1 ) + ')' ).hide();
			$ns.find( '.gallery-captions .caption:nth-child(' + ( nextSlide + 1 ) + ')' ).fadeIn();
		});

		slides.slick( {
			asNavFor: thumbs,
			lazyLoad: 'progressive',  // loads visible images on init, loads the rest on windows.load()
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: true,
			prevArrow: '<div class="slides-nav left"></div>',
			nextArrow: '<div class="slides-nav right"></div>',
		});

		if ( thumbs_count > 0 ) {
			thumbs.slick( {
				asNavFor: slides,
				lazyLoad: 'ondemand',     // loads visible images on init, loads the rest when displayed
				slidesToShow: thumbs_count,
				slidesToScroll: 1,
				arrows: false,
				focusOnSelect: true,
			});
		}

	}

	/**
	 * Displays the clicked slide in a modal, light-boxed dialog, along with its title and credit.
	 *
	 */
	function show_modal_dialog() {

		// get the slide's image url

		var img = $( this ).find( 'img' );

		if ( img.length > 0 ) {
			$ns.find( '.modal-image img' ).attr( 'src', img.attr( 'src' ) );
		}

		// get the slide's title

		var title = $( this ).find( '.slide-title' );

		if ( title.length > 0 ) {
			$ns.find( '.modal-title' ).text( title.text() );
		}

		// get the slide's credit

		var credit = $( this ).find( '.slide-credit' );

		if ( credit.length > 0 ) {
			$ns.find( '.modal-footer' ).text( credit.text() );
		}

		// show the modal dialog

		$ns.find( '.gallery-modal' ).fadeIn();

	}

})( jQuery );
