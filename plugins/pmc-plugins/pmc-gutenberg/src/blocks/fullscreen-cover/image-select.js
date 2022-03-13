/**
 * WordPress dependencies.
 */
import { MediaPlaceholder } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const ImageSelect = ( { setAttributes } ) => {
	const labels = {
		title: __( 'Fullscreen Cover', 'pmc-gutenberg' ),
		instructions: __(
			'Upload a cover image file or pick one from the media library. After selecting an image, the title and introductory text can be added.',
			'pmc-gutenberg'
		),
	};

	return (
		<MediaPlaceholder
			onSelect={ ( { id } ) => setAttributes( { imageId: id } ) }
			accept="image/*"
			allowedTypes={ [ 'image' ] }
			labels={ labels }
		/>
	);
};

export default ImageSelect;
