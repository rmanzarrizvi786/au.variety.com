/* exported varietyFilmReviewAdmin */
/* eslint-disable prefer-arrow-callback, no-alert, prefer-template, no-var, no-unused-vars */

/**
 * Handles the display of the Review metaboxes and film review snippet button
 * used in the pmc-film-review plugin.
 *
 * This is custom functionality, as pmc-variety-2017 uses Field Manager
 * metabox (entitled, "Relationships") to handle selection of categories, while the original
 * plugin is built to only watch the default WP Categories and tags metaboxes.
 *
 * @summary   Custom handler for pmc-film-review plugin functionality.
 *
 * @since    2017.1.0
 * @requires jquery
 *
 * @see pmc-plugins/pmc-film-review/js/admin-script.js
 */
var varietyFilmReviewAdmin = ( function ( $ ) {
	var self = {
		terms: {
			cat: '',
			tag: '',
		},
		isCat: false,
		isTag: false,
		listen: {},
		contentEditor: false,
		l10n: {
			googleAlert:
				'Please note that the Google review snippet has not been set.',
			googleWarning: 'Google review snippet is missing.',
			snippetLength:
				'The snippet length ( %s ) exceeds the maximum length of 200 characters.',
			selectionLength: 'Selection length:',
		},
	};

	if ( 'undefined' !== typeof _varietyFilmReviewAdminExports ) {
		$.extend( self, _varietyFilmReviewAdminExports );
	}

	/**
	 * @summary Initialize.
	 *
	 * Loads functionality on document.ready.
	 *
	 * @since 2017.1.0
	 * @listens click
	 */
	self.init = function () {
		$( document ).ready( function () {
			self.listen.cat = $(
				'select[name="relationships[category][categories]"]'
			);
			self.listen.tag = '.fm-post_tag'; // Don't cache.
			self.targets = $( '#film-review-grp, #variety_review_credit' );
			self.watchEditor();
			self.watchPublish();
			self.initChecks();
		} );
	};

	/**
	 * @summary Init Checks.
	 *
	 * Registers event listeners for the Field Manager
	 * Categories metabox group.
	 *
	 * @since 2017.1.0
	 * @listens click
	 * @listens change
	 */
	self.initChecks = function () {
		// Run on first document.ready.
		self.checkCat();
		self.checkTag();

		self.listen.cat.on( 'change', function () {
			self.checkCat();
		} );

		// On click remove, add a slight delay for removal to complete.
		$( self.listen.tag ).on( 'click', '.fmjs-remove', function () {
			setTimeout( function () {
				self.checkTag();
			}, 500 );
		} );

		// On click.
		$( self.listen.tag ).on( 'change', 'select', function () {
			self.checkTag();
		} );
	};

	/**
	 * @summary Check for Category.
	 *
	 * Checks to see if the "Reviews" Category is
	 * selected in the Field Manager Category Metabox.
	 *
	 * @since 2017.1.0
	 */
	self.checkCat = function () {
		self.isCat = self.terms.cat.toString() === self.listen.cat.val();
		self.triggerButton();
	};

	/**
	 * @summary Check for Tag.
	 *
	 * Checks to see if the "Reviews" tag is
	 * selected in the Field Manager Metabox.
	 *
	 * @since 2017.1.0
	 */
	self.checkTag = function () {
		var elements = $( self.listen.tag ).find( 'select' );
		self.isTag = false;
		elements.each( function () {
			if ( self.terms.tag.toString() === $( this ).val().toString() ) {
				self.isTag = true;
			}
		} );
		self.triggerButton();
	};

	/**
	 * @summary Is Review.
	 *
	 * Determines if this is a Review.
	 *
	 * @return {boolean} True if it is a review.
	 */
	self.isReview = function () {
		return self.isCat || self.isTag;
	};

	/**
	 * @summary Trigger Button
	 *
	 * Triggers if the Review snippet should display.
	 * Also toggles the display of the Review metaboxes.
	 *
	 * @since 2017.1.0
	 * @fires pmc_film_review_snippet_btn
	 */
	self.triggerButton = function () {
		if ( self.contentEditor ) {
			// Preserve trigger name from original script.
			$( self.contentEditor ).trigger(
				'pmc_film_review_snippet_btn',
				self.isReview()
			);

			if ( self.isReview() ) {
				$( '#feedback-review-snippet' ).show();
			} else {
				$( '#feedback-review-snippet' ).hide();
			}
		}

		if ( self.isReview() ) {
			self.targets.show();
		} else {
			self.targets.hide();
		}
	};

	/**
	 * @summary Watch Editor.
	 *
	 * Watches the TinyMCE Editor for changes, then updates
	 * messages and warnings shown to the author.
	 *
	 * @since 2017.1.0
	 * @listens pmc_film_review_snippet
	 * @listens PostRender
	 * @listens pmc_selection_length
	 */
	self.watchEditor = function () {
		try {
			// Watch for editor event to trigger snippet selection update.
			tinymce.onAddEditor.add( function ( msg, editor ) {
				if ( 'content' === editor.id ) {
					self.contentEditor = editor;

					// Snippet updated.
					$( self.contentEditor ).on(
						'pmc_film_review_snippet',
						function ( e, text ) {
							var message = '';

							if ( 0 === text.length ) {
								message = self.l10n.googleWarning;
							} else if ( 200 < text.length ) {
								message = self.l10n.snippetLength.replace(
									'%s',
									text.length
								);
							}

							if ( 0 < message.length ) {
								if (
									1 > $( '#feedback-review-snippet' ).length
								) {
									$( '#major-publishing-actions' ).after(
										$(
											'<div class="feedback" id="feedback-review-snippet">' +
												message +
												'</div>'
										)
									);

									if ( 200 < text.length ) {
										alert( message );
									}
								} else {
									$( '#feedback-review-snippet' ).text(
										message
									);
								}
							} else {
								$( '#feedback-review-snippet' ).remove();
							}

							$( '#pmc-film-review-snippet' ).val( text );
						}
					);

					// Selection change.
					$( self.contentEditor ).on(
						'pmc_selection_length',
						function ( e, length ) {
							var lengthContainer = $( '#pmc-selection-length' );

							if ( 1 > lengthContainer.length ) {
								$( '#wp-word-count' ).append(
									' | ' +
										self.l10n.selectionLength +
										'<span id="pmc-selection-length"></span>'
								);
							}

							lengthContainer.html( length );
						}
					);

					editor.on( 'PostRender', function () {
						self.initChecks();
					} );
				}
			} );
		} catch ( e ) {
			// continue regardless of error
		}
	};

	/**
	 * @summary Display an alert if the snippet is missing.
	 *
	 * @since 2017.1.0
	 * @listens click
	 */
	self.watchPublish = function () {
		$( '#publish' ).on( 'click', function () {
			try {
				if (
					self.activate &&
					0 < $( '#feedback-review-snippet' ).length
				) {
					alert( self.l10n.googleAlert );
				}
			} catch ( e ) {
				// continue regardless of error
			}
		} );
	};

	return self;
} )( jQuery );

/* eslint-enable */
