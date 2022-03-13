// Adding a temporary fix for line breaks, later on needs to add unix line endings.
/*eslint linebreak-style: ["error", "windows"]*/
/*global pmcExcerptConfig*/

jQuery(function() {
	var $ = jQuery,
		overrides = $('#overrides');

	if (!overrides.length) {
		return;
	}

	FO.initialize();

	var title = $('#title'),
		excerpt = $('#excerpt'),
		oTitle = $('#override_post_title'),
		oExcerpt = $('#override_post_excerpt'),
		form = $('#post');

	// On page load
	/*FO.update(oTitle, title.val());
	FO.update(oExcerpt, excerpt.val());

	// On key up
	title.keyup(function() {
		FO.update(oTitle, this.value);
	});

	excerpt.keyup(function() {
		FO.update(oExcerpt, this.value);
	});*/

	// Submit
	form.submit(function() {
		if (title.length && title.val().trim() == oTitle.val().trim()) {
			oTitle.val('');
		}

		if (excerpt.length && excerpt.val().trim() == oExcerpt.val().trim()) {
			oExcerpt.val('');
		}

		return true;
	})
});

var FO = {

	// Creates the char/word count divs/events
	initialize: function() {
		jQuery('#overrides').find('input[type="text"], textarea').each(function() {

			var self = jQuery( this ),
			maxCharLimit = '',
			preventTyping = '',
			countDiv,
			charCountEl,
			wordCountEl,
			maxLimitEl;

			if ( 'undefined' !== typeof pmcExcerptConfig ) {
				maxCharLimit  = pmcExcerptConfig['pmc_excerpt_limit'];
				preventTyping = pmcExcerptConfig['pmc_excerpt_prevent'];
			}

			if ( self.val().length ) {
				self.data( 'override', true );
			}

			countDiv    = jQuery( '<div/>' ).css({'width': '99%'});

			charCountEl = jQuery( '<span/>' )
							.addClass( 'chars' )
							.text( '0' );

			wordCountEl = jQuery( '<span/>' )
							.addClass( 'words' )
							.text( '0' );

			maxLimitEl  = jQuery( '<span/>' )
							.css({'float': 'right'})
							.text( 'Max Characters: ' + maxCharLimit );

			countDiv.append( charCountEl )
				.append( ' characters, ' )
				.append( wordCountEl )
				.append( ' words' );

			if ( '' !== maxCharLimit && self.is( 'textarea' ) ) {

				countDiv.append( maxLimitEl );

				if ( 'enable' ===  preventTyping ) {
					self.attr( 'maxlength', maxCharLimit );
				}

			}

			countDiv.addClass( 'char-count' ).insertAfter( self );

			self.keyup(function() {
				FO.update(self);
			});
		});
	},

	update: function(input, value) {
		// Manual set
		if (value) {
			// Only set if override is off
			if (!input.data('override')) {
				input.val(value);
			}

		// Auto
		} else {
			// Toggle override on/off based on input length
			input.data('override', input.val().length);
		}

		FO.charCount(input);
	},

	charCount: function(input) {
		var value = input.val(),
			c = value.length,
			w = c;

		// Returns 1 even if there is nothing
		if (c) {
			w = value.split(' ').length;
		}

		input.siblings('.char-count')
			.find('.chars').text(c).end()
			.find('.words').text(w).end();
	}

};