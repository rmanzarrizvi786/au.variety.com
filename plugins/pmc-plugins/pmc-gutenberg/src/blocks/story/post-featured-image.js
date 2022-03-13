import { get } from 'lodash';

const featuredImageSizeSlug = 'large';

// KUDOS: https://github.com/WordPress/gutenberg/blob/master/packages/block-library/src/latest-posts/edit.js
const postWithFeaturedImage = ( { post, getMedia } ) => {
	if ( post && post.featured_media ) {
		const image = getMedia( post.featured_media );
		let url = get(
			image,
			[ 'media_details', 'sizes', featuredImageSizeSlug, 'source_url' ],
			null
		);
		if ( ! url ) {
			url = get( image, 'source_url', null );
		}
		return {
			...post,
			featuredImageSourceUrl: url,
			featuredImageAltText: image && image.alt_text ? image.alt_text : '',
		};
	}
	return post;
};

export { postWithFeaturedImage };
