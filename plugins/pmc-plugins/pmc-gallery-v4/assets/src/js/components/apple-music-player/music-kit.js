// https://developer.apple.com/documentation/musickitjs/

import { useEffect } from 'react';

let musicKitAdded = false;

const pmcgalleryamapi = window.pmcgalleryamapi || {};
const developerToken = pmcgalleryamapi.tkn || '';
const app = pmcgalleryamapi.appinfo || {};

/**
 * This function is needed to set an object and its properties
 * in window scope because for some reason Apple's JS expects it
 * and does not account for them being not there. Since these
 * are not available in browsers, the JS breaks.
 *
 * In essence, this is a band-aid on Apple's broken code to prevent
 * it from breaking things on our end.
 */
const fixAppleStupidity = () => {
	if ( 'undefined' === typeof window.process ) {
		window.process = {};
	}

	if ( 'undefined' === typeof window.process.versions ) {
		window.process.versions = {};
	}

	if ( 'undefined' === typeof window.process.versions.node ) {
		window.process.versions.node = null;
	}
};

function addMusicKit() {
	if ( musicKitAdded ) return;

	document.addEventListener( 'musickitloaded', function() {
		window.MusicKit.configure( {
			developerToken: developerToken,
			app: app,
		} );
	} );

	fixAppleStupidity();

	const script = document.createElement( 'script' );
	script.setAttribute(
		'src',
		'https://js-cdn.music.apple.com/musickit/v1/musickit.js'
	);
	document.head.appendChild( script );

	musicKitAdded = true;
}

function loadMusicKit() {
	return new Promise( ( resolve ) => {
		addMusicKit();

		if ( window.MusicKit ) {
			resolve();
		} else {
			document.addEventListener( 'musickitloaded', resolve );
		}
	} );
}

function useMusicKit( {
	song,
	send,
} ) {
	const music = window.MusicKit ? window.MusicKit.getInstance() : null;
	const isAuthorized = music ? music.isAuthorized : null;

	// @TODO: Handle errors
	//	A. Slow connection:
	//		 https://developer.apple.com/documentation/musickitjs/musickit/events
	//		 https://developer.apple.com/documentation/musickitjs/musickit/playbackstates
	//	B. MusicKit isn’t available:
	//		 https://developer.apple.com/documentation/musickitjs/musickit/mkerror
	useEffect( () => {
		loadMusicKit().then( () => {
			send( { type: 'MUSIC_KIT_LOAD' } );
		} );
	}, [ loadMusicKit ] );

	useEffect( () => {
		if ( music ) {
			const { Events, PlaybackStates } = window.MusicKit;
			const { player } = music;
			music.addEventListener(
				Events.playbackTimeDidChange,
				() =>
					( player.nowPlayingItem && song === player.nowPlayingItem.id )
					&& send( {
						type: 'SONG_DATA_LOAD',
						songData: player.nowPlayingItem,
					} )
			);
			music.addEventListener(
				Events.playbackTimeDidChange,
				() => send( {
					type: 'TIME_CHANGE',
					playbackData: {
						time: player.currentPlaybackTime,
						progress: player.currentPlaybackProgress,
					},
				} )
			);
			music.addEventListener(
				Events.playbackTimeDidChange,
				() =>
					( 30 === player.currentPlaybackTime )
					&& send( { type: 'THIRTY_SECONDS' } )
			);
			music.addEventListener(
				Events.playbackStateDidChange,
				() =>
					( PlaybackStates.ended === player.playbackState )
					&& send( { type: 'SONG_END' } )
			);
			music.addEventListener(
				Events.authorizationStatusDidChange,
				() => send( {
					type: 'AUTHORIZE_CHANGE',
					isAuthorized: music.isAuthorized,
				} )
			);
			music.addEventListener(
				Events.mediaItemDidChange,
				() =>
					( player.nowPlayingItem && song !== player.nowPlayingItem.id )
					&& send( { type: 'SONG_CHANGE' } )
			);
		}
	}, [ music, song ] );

	return [
		isAuthorized,
	];
}

export {
	useMusicKit,
};
