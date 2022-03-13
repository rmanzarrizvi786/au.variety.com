/**
 * External dependencies.
 */
import { get } from 'lodash';

/**
 * WordPress dependencies.
 */
import { Spinner } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

const Render = ( { imageAlt, imageUrl } ) => {
	if ( ! imageUrl ) {
		return <Spinner />;
	}

	return (
		<div className="lrv-u-text-align-center">
			<img src={ imageUrl } alt={ imageAlt } />
		</div>
	);
};

const ImagePreview = compose( [
	withSelect( ( select, { imageId } ) => {
		const { getMedia } = select( 'core' );
		let imageAlt, imageUrl;

		if ( imageId ) {
			const image = getMedia( imageId );

			imageAlt = get( image, 'alt_text', '' );

			imageUrl = get(
				image,
				[ 'media_details', 'sizes', 'portrait-medium', 'source_url' ],
				get( image, 'source_url', null )
			);
		}

		return {
			imageAlt,
			imageUrl,
		};
	} ),
] )( Render );

export default ImagePreview;
