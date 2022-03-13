import find from 'lodash/find';
import get from 'lodash/get';

class PMC_Review_Admin {

	/**
	 * Class constructor.
	 */
	constructor() {
		this.shouldRun = true;

		this.initElements();
	}

	/**
	 * Finds the necessary HTML elements.
	 */
	initElements() {
		try {
			this.categorySelect = document.getElementById( 'fm-relationships-0-category-0-categories-0' );
			this.metaBox = document.getElementById( 'review-grp' );
			this.reviewTypeSelect = this.metaBox.querySelector( '#pmc-review-type' );
			this.reviewTypeOptions = this.reviewTypeSelect.querySelectorAll( 'option' );
			this.activeReviewType = this.reviewTypeSelect.querySelector( 'option:checked' ).value;
		} catch ( e ) {
			this.shouldRun = false;
		}
	}

	/**
	 * Start.
	 */
	run() {
		this.initDescriptiveTextElements();
		this.initChangeListener();
		this.refreshView();
	}

	/**
	 * Creates an object containing each meta field's label and description elements.
	 */
	initDescriptiveTextElements() {

		const fieldElements = this.metaBox.querySelectorAll( '[data-slug]' );

		this.descriptiveTextElements = [ ...fieldElements ].reduce(
			( descriptiveTextElements, metaField ) => ( {
				...descriptiveTextElements,
				[metaField.getAttribute( 'data-slug' )]: {
					label: metaField.querySelector( 'label' ),
					description: metaField.querySelector( '.description' )
				}
			} ),
			{}
		);

	}

	/**
	 * Updates state and refreshes the view on changes to relevant inputs.
	 */
	initChangeListener() {
		addEventListener( 'change', ( { target } ) => {
			if ( target === this.reviewTypeSelect ) {
				this.activeReviewType = get( target.querySelector( 'option:checked' ), 'value', '' );
				this.refreshView();
				return;
			}

			const categorySelectInputIDs = [
				'fm-relationships-0-category-0-categories-0',
				'fm-relationships-0-category-0-subcategories-0'
			];
			const targetId = target.getAttribute( 'id' );

			// One of the category select inputs has changed.
			if ( 'SELECT' === target.tagName && categorySelectInputIDs.includes( targetId )
			) {
				this.refreshView();
			}
		} );
	}

	/**
	 * Refresh the metabox view.
	 */
	refreshView() {
		this.injectBoxVisibilityCSSClass();
		this.injectReviewTypeCSSClass();
		this.injectFieldDescriptiveText();
	}

	/**
	 * Returns whether the meta box should be visible.
	 */
	shouldShow() {
		if ( ! Object.keys( pmcReviewData.reviewCategories ).includes( this.selectedSubcategory ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets the slug of the selected subcategory.
	 *
	 * @return {string} The slug of the matching review category or an empty string if no match.
	 */
	get selectedSubcategory() {
		try {
			const selectedCategory = document.querySelector( '#fm-relationships-0-category-0-subcategories-0 option:checked' );
			const matchingReviewCategory = find( pmcReviewData.reviewCategories, { term_id: Number( selectedCategory.value ) } );
			return matchingReviewCategory.slug;
		} catch ( e ) {
			return '';
		}
	}

	/**
	 * Gets descriptive text from localized PHP data.
	 *
	 * @param {string} fieldName The meta field name to check.
	 * @return {Object} An object containing a description and label.
	 */
	getDescriptiveText( fieldName ) {
		let textSettings = get(
			pmcReviewData,
			[ 'fieldDescriptiveText', fieldName, this.activeReviewType ]
		);

		if ( ! textSettings ) {

			// On failure to find data for the review type, get default if it exists.
			textSettings = get(
				pmcReviewData,
				[ 'fieldDescriptiveText', fieldName, 'default' ],
				{}
			);
		}

		return {
			descriptionText: get( textSettings, 'description' ),
			labelText: get( textSettings, 'label' )
		};

	}

	/**
	 * Updates inner text of field labels and descriptions.
	 */
	injectFieldDescriptiveText() {
		Object.keys( this.descriptiveTextElements ).forEach( fieldName => {

			const elements = this.descriptiveTextElements[fieldName];
			const { labelText, descriptionText } = this.getDescriptiveText( fieldName );

			if ( labelText && elements.label ) {
				elements.label.textContent = labelText;
			}

			if ( descriptionText && elements.description ) {
				elements.description.textContent = descriptionText;
			}

		} );

	}

	/**
	 * Applies or removes the CSS class controlling whether the metabox is visible.
	 */
	injectBoxVisibilityCSSClass() {
		const shouldBeVisible = this.shouldShow();

		if ( shouldBeVisible ) {
			document.body.classList.add( 'pmc-review-metabox-visible' );
		} else {
			document.body.classList.remove( 'pmc-review-metabox-visible' );
		}
	}

	/**
	 * Applies the CSS class (corresponding to the active review type slug) that toggles
	 * fields on and off using CSS display (see plugin CSS).
	 */
	injectReviewTypeCSSClass() {
		[ ...this.reviewTypeOptions ].forEach( ( { value } ) => {
			if ( ! value.length ) {
				return;
			}

			if ( this.activeReviewType === value ) {
				this.metaBox.classList.add( value );
			} else {
				this.metaBox.classList.remove( value );
			}
		} );
	}
}

window.addEventListener( 'load', () => {
	const pmcReviewAdmin = new PMC_Review_Admin();
	if ( pmcReviewAdmin.shouldRun ) {
		pmcReviewAdmin.run();
	}
} );
