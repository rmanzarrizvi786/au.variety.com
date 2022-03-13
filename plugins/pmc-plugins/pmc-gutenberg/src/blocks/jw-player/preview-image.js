import { Dashicon } from '@wordpress/components';
import { select } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { store } from '@wordpress/viewport';

const PreviewImage = ( {
	imageWidth: requestedWidth,
	isPlaylist,
	videoId,
} ) => {
	const [ imageUrl, setImageUrl ] = useState( '' );

	let imageWidth;
	if ( requestedWidth ) {
		imageWidth = parseInt( requestedWidth, 10 );
	} else {
		const isMobile = select( store ).isViewportMatch( '< small' );
		imageWidth = isMobile ? 320 : 720;
	}

	useEffect( () => {
		setImageUrl(
			`https://assets-jpcust.jwpsrv.com/thumbnails/generic/video-${ imageWidth }.jpg`
		);
	}, [] );

	// React Hooks cannot be used conditionally, hence why this is so late.
	if ( isPlaylist ) {
		return <Dashicon icon="playlist-video" />;
	}

	// Hardcoding the host because content masks (custom hosts) don't support HTTPS, and we have no non-HTTPS wp-admins.
	const urlToTest = `https://content.jwplatform.com/thumbs/${ videoId }-${ imageWidth }.jpg`;

	if ( imageUrl !== urlToTest ) {
		window.fetch( urlToTest, { credentials: 'omit' } ).then( ( res ) => {
			if ( 200 === res.status ) {
				setImageUrl( urlToTest );
			}
		} );
	}

	return (
		<img
			src={ imageUrl }
			width={ imageWidth }
			alt={ __( 'JW Player preview image', 'pmc-gutenberg' ) }
		/>
	);
};

export default PreviewImage;
