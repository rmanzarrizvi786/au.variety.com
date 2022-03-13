import BlockInspectorControls from './block-inspector-controls';
import VideoPreview from './video-preview';
import VideoSelector from './video-selector';

import './edit.scss';

/**
 * Edit function for pmc-jw-player block.
 *
 * @param {Object}   root0
 * @param {Object}   root0.attributes                Object containing block attributes.
 * @param {boolean}  root0.attributes.isPlaylist     Does the video ID represent a playlist.
 * @param {string}   root0.attributes.playerId       ID of JW Player that video is associated with.
 * @param {string}   root0.attributes.selectionTitle Title from JW Player of chosen item, for display in the editor.
 * @param {string}   root0.attributes.videoId        ID of selected JW Player video.
 * @param {Function} root0.setAttributes             Callback to update block attributes.
 * @return {JSX.Element}                                 Edit component.
 */
const Edit = ( {
	attributes: { isPlaylist, playerId, selectionTitle, videoId },
	setAttributes,
} ) => {
	return (
		<>
			{ videoId ? (
				<VideoPreview
					isPlaylist={ isPlaylist }
					playerId={ playerId }
					selectionTitle={ selectionTitle }
					videoId={ videoId }
					clearSelectedVideo={ () =>
						setAttributes( { playerId: '', videoId: '' } )
					}
				/>
			) : (
				<VideoSelector setAttributes={ setAttributes } />
			) }

			<BlockInspectorControls
				playerId={ playerId }
				setAttributes={ setAttributes }
			/>
		</>
	);
};

export { Edit };
