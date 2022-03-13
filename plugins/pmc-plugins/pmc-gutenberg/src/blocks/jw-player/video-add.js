import { URLInput, URLPopover } from '@wordpress/block-editor';
import {
	Button,
	FormFileUpload,
	Notice,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';
import { keyboardReturn } from '@wordpress/icons';

import { UploadFile, ImportFromURL } from './jw-api';

const rowCSSClass = 'pmc-jw-player-button-row';

const VideoAdd = ( { closeModal, setAttributes } ) => {
	const [ errorText, setErrorText ] = useState( '' );
	const [ isUploading, setIsUploading ] = useState( false );
	const [ showPopover, setShowPopover ] = useState( false );
	const [ title, setTitle ] = useState( '' );
	const [ urlToImport, setUrlToImport ] = useState( '' );

	const handleImport = ( url ) => {
		setErrorText( '' );
		setIsUploading( true );

		ImportFromURL( url, title )
			.then( processSuccess )
			.catch( processError );
	};

	const handleUpload = ( file ) => {
		setErrorText( '' );
		setIsUploading( true );

		UploadFile( file, title )
			.then( processSuccess )
			.catch( processError );
	};

	const processError = ( response ) => {
		setErrorText( response.error );
		setIsUploading( false );
	};

	const processSuccess = ( response ) => {
		setAttributes( {
			isPlaylist: false,
			selectionTitle: title,
			videoId: response.videoId,
		} );

		closeModal();
	};

	return (
		<>
			<TextControl
				label={ _x(
					'Title',
					'Video title submitted to JW Player',
					'pmc-gutenberg'
				) }
				help={ __(
					'Enter video title. If omitted, the filename is used.',
					'pmc-gutenberg'
				) }
				onChange={ setTitle }
			/>

			{ isUploading && <Spinner /> }

			{ ! isUploading && (
				<div className={ rowCSSClass }>
					<FormFileUpload
						accept="video/*"
						icon="cloud-upload"
						isPrimary
						multiple="false"
						onChange={ ( event ) => {
							handleUpload( event.target.files[ 0 ] );
						} }
					>
						{ _x(
							'Upload file',
							'Upload video file to JW Player',
							'pmc-gutenberg'
						) }
					</FormFileUpload>

					<Button
						isSecondary
						onClick={ () => setShowPopover( true ) }
						icon="admin-links"
					>
						{ _x(
							'Add from URL',
							'Add video to JW Player from an existing URL.',
							'pmc-gutenberg'
						) }
					</Button>

					<Button
						className={ `${ rowCSSClass }-cancel` }
						isTertiary
						onClick={ closeModal }
					>
						{ _x(
							'Cancel',
							'Cancel JW Player upload and close modal',
							'pmc-gutenberg'
						) }
					</Button>
				</div>
			) }

			{ showPopover && ! isUploading && (
				<URLPopover onClose={ () => setShowPopover( false ) }>
					<form
						className="block-editor-url-popover__link-editor"
						onSubmit={ ( event ) => {
							event.preventDefault();
							setShowPopover( false );
							handleImport( urlToImport );
						} }
					>
						<div className="block-editor-url-input">
							<URLInput
								value={ urlToImport }
								onChange={ setUrlToImport }
								placeholder={ __(
									'Enter video URL',
									'pmc-gutenberg'
								) }
								disableSuggestions={ true }
							/>
						</div>

						<Button
							icon={ keyboardReturn }
							label={ _x(
								'Import',
								'Import video URL into JW Player',
								'pmc-gutenberg'
							) }
							type="submit"
						/>
					</form>
				</URLPopover>
			) }

			{ ! isUploading && Boolean( errorText.length ) && (
				<Notice isDismissible={ false } status="error">
					{ errorText }
				</Notice>
			) }
		</>
	);
};

export default VideoAdd;
