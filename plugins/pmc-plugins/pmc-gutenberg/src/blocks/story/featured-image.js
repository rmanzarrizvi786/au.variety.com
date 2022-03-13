import { get } from 'lodash';

import { Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

const featuredImageSizeSlug = 'large';

// KUDOS: https://github.com/WordPress/gutenberg/blob/master/packages/block-library/src/latest-posts/edit.js
const FeaturedImage = ( { imageID, hasFullWidthImage } ) => {
	const { src, alt } = useSelect(
		( select ) => {
			const data = {
				src: null,
				alt: null,
			};

			// This is here because `useSelect()` cannot be called conditionally.
			if ( imageID < 1 ) {
				return data;
			}

			const { getMedia } = select( 'core' );
			const image = getMedia( imageID );
			let url = get(
				image,
				[
					'media_details',
					'sizes',
					featuredImageSizeSlug,
					'source_url',
				],
				null
			);
			if ( ! url ) {
				url = get( image, 'source_url', null );
			}

			data.src = url;
			data.alt = image && image.alt_text ? image.alt_text : '';

			return data;
		},
		[ imageID ]
	);

	if ( ! src ) {
		return <Spinner />;
	}

	return (
		<img
			src={ src }
			alt={ alt }
			style={ {
				width: hasFullWidthImage ? 'auto' : '50%',
			} }
			className="pmc-story-card-preview__featured-image"
		/>
	);
};

export { FeaturedImage };
