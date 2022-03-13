import { BlockControls } from '@wordpress/block-editor';
import { ToolbarButton } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import PreviewImage from './preview-image';

const VideoPreview = ( {
	clearSelectedVideo,
	isPlaylist,
	selectionTitle,
	videoId,
} ) => {
	return (
		<>
			<BlockControls group="other">
				<ToolbarButton onClick={ clearSelectedVideo }>
					{ __( 'Replace', 'pmc-gutenberg' ) }
				</ToolbarButton>
			</BlockControls>

			<p className="pmc-jw-player-preview-wrapper">
				{ ! isPlaylist && (
					<>
						<span>{ selectionTitle }</span>
						<br />
					</>
				) }

				<PreviewImage isPlaylist={ isPlaylist } videoId={ videoId } />

				{ isPlaylist && (
					<>
						<br />
						<span>{ selectionTitle }</span>
					</>
				) }
			</p>
		</>
	);
};

export default VideoPreview;
