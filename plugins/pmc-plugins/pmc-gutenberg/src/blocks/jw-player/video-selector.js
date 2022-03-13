import { Button, Modal, Placeholder } from '@wordpress/components';
import { select } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';
import { video } from '@wordpress/icons';
import { store } from '@wordpress/viewport';

import VideoAdd from './video-add';
import VideoSearch from './video-search';

const VideoSelector = ( { setAttributes } ) => {
	const [ selectModalIsOpen, setSelectModalOpen ] = useState( false );
	const [ addModalIsOpen, setAddModalOpen ] = useState( false );

	const modalStyles = {};
	if ( select( store ).isViewportMatch( '>= medium' ) ) {
		modalStyles.minHeight = '600px';
		modalStyles.minWidth = '450px';
	}

	const openSelectModal = () => setSelectModalOpen( true );
	const closeSelectModal = () => setSelectModalOpen( false );
	const selectVideoText = _x(
		'Select video',
		'Select JW Player video from existing items',
		'pmc-gutenberg'
	);

	const openAddModal = () => setAddModalOpen( true );
	const closeAddModal = () => setAddModalOpen( false );
	const addVideoText = _x(
		'Add new video',
		'Add video to JW Player',
		'pmc-gutenberg'
	);

	return (
		<Placeholder
			icon={ video }
			label={ __( 'JW Player Video', 'pmc-gutenberg' ) }
			instructions={ __(
				'Embed a video hosted in JW Player, or add a new video to JW Player.',
				'pmc-gutenberg'
			) }
		>
			<Button isPrimary={ true } onClick={ openSelectModal }>
				{ selectVideoText }
			</Button>
			{ selectModalIsOpen && (
				<Modal
					onRequestClose={ closeSelectModal }
					style={ modalStyles }
					title={ selectVideoText }
				>
					<VideoSearch
						closeModal={ closeSelectModal }
						setAttributes={ setAttributes }
					/>
				</Modal>
			) }

			<Button onClick={ openAddModal }>{ addVideoText }</Button>
			{ addModalIsOpen && (
				<Modal onRequestClose={ closeAddModal } title={ addVideoText }>
					<VideoAdd
						closeModal={ closeAddModal }
						setAttributes={ setAttributes }
					/>
				</Modal>
			) }
		</Placeholder>
	);
};

export default VideoSelector;
