/**
 * Attributes store state for the block that can be changed by the user.
 *
 * In the story block, some of the attributes are updated without user input
 * based on localized configuration provided by the theme.
 */
const attributes = {
	// `className` added to support styles without custom-class input field.
	className: {
		type: 'string',
		default: '',
	},
	postType: {
		type: 'string',
		default: 'post',
	},
	postID: {
		type: 'number',
		default: null,
	},
	taxonomySlug: {
		type: 'string',
		default: null,
	},
	viewMoreText: {
		type: 'string',
		default: null,
	},
	hasDisplayedExcerpt: {
		type: 'boolean',
		default: true,
	},
	hasDisplayedByline: {
		type: 'boolean',
		default: true,
	},
	hasDisplayedPrimaryTerm: {
		type: 'boolean',
		default: true,
	},
	hasFullWidthImage: {
		type: 'boolean',
		default: false,
	},
	alignment: {
		type: 'string',
		default: 'none',
	},
	title: {
		type: 'string',
		default: null,
	},
	excerpt: {
		type: 'string',
		default: null,
	},
	featuredImageID: {
		type: 'number',
		default: null,
	},
	contentOverride: {
		type: 'string',
		default: null,
	},
	hasContentOverride: {
		type: 'boolean',
		default: false,
	},
	backgroundColor: {
		type: 'string',
		default: null,
	},
};

export { attributes };
