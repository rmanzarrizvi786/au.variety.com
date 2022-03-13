import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

import {
	JW_PLAYER_REST_PROXY_BASE,
	JW_PLAYER_REST_PROXY_DEFAULT_VERSION,
} from './constants';

/**
 * Import a video into JW Player from a given URL.
 *
 * @param {string} url   URL to import.
 * @param {string} title Video title submitted to JW Player.
 * @return {Object} Request result.
 */
const ImportFromURL = async ( url, title ) => {
	const response = {
		success: false,
	};

	await apiFetch( {
		path: `${ JW_PLAYER_REST_PROXY_BASE }/${ JW_PLAYER_REST_PROXY_DEFAULT_VERSION }/import`,
		method: 'POST',
		data: { title, url },
	} )
		.then( ( importData ) => {
			response.videoId = importData.key;
			response.success = true;
		} )
		.catch( ( error ) => {
			response.error = error.message;
		} );

	return response.success ? response : Promise.reject( response );
};

/**
 * Search JW Player for videos or playlists.
 *
 * @param {string} term Search term
 * @param {string} type Type of results to search for: `video` or `playlist`
 * @return {Object} Request result.
 */
const Search = async ( term, type ) => {
	const response = {
		success: false,
	};

	await apiFetch( {
		path: `${ JW_PLAYER_REST_PROXY_BASE }/${ JW_PLAYER_REST_PROXY_DEFAULT_VERSION }/search`,
		method: 'POST',
		data: {
			query: term,
			type,
		},
	} )
		.then( ( results ) => {
			response.results = results;
			response.success = true;
		} )
		.catch( ( error ) => {
			response.error = error.message;
		} );

	return response.success ? response : Promise.reject( response );
};

/**
 * Upload a local video to JW Player.
 *
 * @param {File}   file  File object.
 * @param {string} title Video title submitted to JW Player.
 * @return {Object} Request result.
 */
const UploadFile = async ( file, title ) => {
	const response = {
		success: false,
	};

	await apiFetch( {
		path: `${ JW_PLAYER_REST_PROXY_BASE }/${ JW_PLAYER_REST_PROXY_DEFAULT_VERSION }/upload`,
		method: 'POST',
		data: { filename: file.name, title },
	} )
		.then( async ( uploadData ) => {
			const postData = new window.FormData();
			// JW API requires that form input be named `file`.
			postData.set( 'file', file, file.name );

			await window
				.fetch( uploadData.url, {
					method: 'POST',
					body: postData,
					headers: { 'X-Session-ID': uploadData.session_id },
				} )
				.then( ( res ) => res.json() )
				.then( ( uploadResponse ) => {
					response.videoId = uploadResponse.media.key;
					response.success = true;
				} )
				.catch( ( error ) => {
					response.error = sprintf(
						/* translators: 1. Error text from JW Player API. */
						__(
							'The video could not be uploaded due to an error: %s',
							'pmc-gutenberg'
						),
						error.message
					);
				} );
		} )
		.catch( ( error ) => {
			response.error = error.message;
		} );

	return response.success ? response : Promise.reject( response );
};

export { ImportFromURL, Search, UploadFile };
