import { debounce } from 'lodash';

import {
	Button,
	Notice,
	RadioControl,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { Search } from './jw-api';
import PreviewImage from './preview-image';

const resultsCSSClass = 'pmc-jw-player-results';

const VideoSearch = ( { closeModal, setAttributes } ) => {
	const [ isSearching, setIsSearching ] = useState( false );
	const [ noticeData, setNoticeData ] = useState( {
		message: '',
		type: 'info',
	} );
	const [ searchResults, setSearchResults ] = useState( [] );
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const [ searchType, setSearchType ] = useState( 'video' );

	const fetchResults = useCallback(
		debounce( () => {
			setIsSearching( true );
			setNoticeData( { message: '' } );
			setSearchResults( [] );

			Search( searchTerm, searchType )
				.then( ( response ) => response.results )
				.then( ( response ) => {
					setSearchResults( response );

					if ( 0 === response.length ) {
						setNoticeData( {
							message: __( 'No results found.', 'pmc-gutenberg' ),
							type: 'warning',
						} );
					}

					setIsSearching( false );
				} )
				.catch( ( { error } ) => {
					setNoticeData( { message: error, type: 'error' } );
					setIsSearching( false );
				} );
		}, 250 ),
		[ searchTerm, searchType ]
	);

	useEffect( () => {
		fetchResults();
		return fetchResults.cancel;
	}, [ searchTerm, searchType, fetchResults ] );

	return (
		<>
			<TextControl
				label={ __( 'Search', 'pmc-gutenberg' ) }
				help={ __(
					'Enter search term, such as ID, title, description, or tag.',
					'pmc-gutenberg'
				) }
				onChange={ setSearchTerm }
			/>

			<RadioControl
				label={ __( 'Type', 'pmc-gutenberg' ) }
				help={ __(
					'Search either videos or playlists.',
					'pmc-gutenberg'
				) }
				selected={ searchType }
				options={ [
					{ label: __( 'Videos', 'pmc-gutenberg' ), value: 'video' },
					{
						label: __( 'Playlists', 'pmc-gutenberg' ),
						value: 'playlist',
					},
				] }
				onChange={ ( type ) => {
					setIsSearching( true );
					setSearchType( type );
				} }
			/>

			<hr />

			{ isSearching && <Spinner /> }

			{ ! isSearching && Boolean( noticeData.message.length ) && (
				<Notice isDismissible={ false } status={ noticeData.type }>
					{ noticeData.message }
				</Notice>
			) }

			{ ! isSearching && Boolean( searchResults ) && (
				<ul className={ resultsCSSClass }>
					{ searchResults.map( ( result ) => (
						<li
							className={ `${ resultsCSSClass }-list` }
							key={ result.key }
						>
							<Button
								className={ `${ resultsCSSClass }-button` }
								onClick={ () => {
									setAttributes( {
										isPlaylist: 'playlist' === searchType,
										selectionTitle: result.title,
										videoId: result.key,
									} );
									closeModal();
								} }
							>
								<p
									className={ `${ resultsCSSClass }-image-wrapper` }
								>
									<PreviewImage
										imageWidth="100"
										isPlaylist={ 'playlist' === searchType }
										videoId={ result.key }
									/>
								</p>
								<p
									className={ `${ resultsCSSClass }-video-title` }
								>
									{ result.title }
								</p>
							</Button>
						</li>
					) ) }
				</ul>
			) }
		</>
	);
};

export default VideoSearch;
