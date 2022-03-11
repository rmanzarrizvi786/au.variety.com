'use strict';
( function ( window ) {
	const ReviewSnippet = function () {
		const self = this;

		const el = document.querySelector( '#category-all' );

		/**
		 * Init function.
		 */
		self.init = function () {
			const categorsList = el.querySelectorAll( 'input' );

			if ( null !== categorsList ) {
				categorsList.forEach( function ( cat ) {
					cat.addEventListener(
						'change',
						self.injectBoxVisibilityCSSClass.bind( this )
					);
				} );
			}

			this.injectBoxVisibilityCSSClass();
		};

		/**
		 * Applies or removes the CSS class controlling whether the Snippet is visible.
		 */
		self.injectBoxVisibilityCSSClass = function () {
			const shouldBeVisible = self.shouldShow();

			if ( shouldBeVisible ) {
				document.body.classList.add( 'pmc-review-metabox-visible' );
			} else {
				document.body.classList.remove( 'pmc-review-metabox-visible' );
			}
		};

		/**
		 * Returns whether the Snippet should be visible.
		 */
		self.shouldShow = function () {
			const shouldShow = Object.keys(
				pmcReviewData.reviewCategories
			).includes( this.getSelectedCategory() ); // eslint-disable-line no-undef

			return shouldShow;
		};

		/**
		 * Gets the slug of the selected category.
		 *
		 * @return {string} The slug of the matching review category or an empty string if no match.
		 */
		self.getSelectedCategory = function () {
			const selectedCategory = el.querySelectorAll( 'input:checked' );

			let matching = '',
				matchingReviewCategory = '';

			selectedCategory.forEach( function ( cat ) {
				// Ignored eslint for camelcase and space-in-parens.
				matchingReviewCategory = _.find( pmcReviewData.reviewCategories, { term_id: Number( cat.value ) } );  // eslint-disable-line

				if ( undefined !== matchingReviewCategory ) {
					matching = matchingReviewCategory.slug;
				}
			} );

			return matching;
		};

		// Initialize!
		self.init();
	};

	window.addEventListener( 'load', function () {
		new ReviewSnippet();
	} );
} )( window, jQuery );
