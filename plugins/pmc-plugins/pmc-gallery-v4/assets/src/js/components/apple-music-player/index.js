// https://developer.apple.com/documentation/musickitjs/
// https://developer.apple.com/documentation/musickitjs/musickit

import React, { useState, useEffect, useRef } from 'react'; // eslint-disable-line

import { useMachine } from '@xstate/react';
import { useMusicKit } from './music-kit';
import { useIntersectionObserver } from './intersection-observer';
import { useAnalytics } from './analytics';

import { formattedPlaybackTime } from './helpers';
import Icon from './icon';

import { 
	musicMachine,
	LOADING_TRANSITION_DURATION,
	LOADING_TRANSITION_DELAY,
} from './machine';

const AppleMusicPlayer = ( {
	song,
	enableAnalytics,
} ) => {
	const musicPlayerElement = useRef( null );

	const [ state, send ] = useMachine( musicMachine, { context: { song } } );
	const [ isAuthorized ] = useMusicKit( { song, send } );

	const { songData, playbackData } = state.context;
	const playbackTime = playbackData ? playbackData.time : null;

	useIntersectionObserver( {
		musicPlayerElement,
		onVisible: () => {
			send( { type: 'VISIBLE' } );
		},
		enabled: true !== state.context.isVisible,
	} );

	useAnalytics( {
		category: 'music-player',
		label: song,
		value: playbackTime,
		valueEnabled: {
			PAUSE: true,
			SONG_END: true,
		},
		actions: {
			PLAY: 'press-play',
			PAUSE: 'press-pause',
			SONG_END: 'song-ended',
			AUTHORIZE: 'login',
			VISIBLE: 'inView',
			THIRTY_SECONDS: '30-seconds-played',
		},
		actionTarget: state.event.type, // 'PLAY'
		enabled: enableAnalytics,
	} );

	// https://medium.com/@DavidKPiano/css-animations-with-finite-state-machines-7d596bb2914a
	const stateStrings = state.toStrings();
	const stateValue = stateStrings[ stateStrings.length - 1 ];
	
	const ready = stateValue.includes( 'ready' );
	const isPlaying =
		stateValue.includes( 'playing' )
		|| stateValue.includes( 'preparing' );

	const progressPercentage = playbackData ? playbackData.progress * 100 : 0;

	return (
		<figure
			className="c-gallery-apple-music-player"
			data-state={ stateValue }
			ref={ musicPlayerElement }
		>
			{ songData && (
				<figcaption className="c-gallery-apple-music-player__title">
					{ songData.attributes.name }
				</figcaption>
			) }
			<button
				className="c-gallery-apple-music-player__button c-gallery-apple-music-player__button-play"
				type="button"
				disabled={ ( ! ready ) }
				onClick={
					() => true === isPlaying
						? send( { type: 'PAUSE', playbackTime } )
						: send( { type: 'PLAY' } )
				}
			>
				{ true === isPlaying ? (
					<Icon shape="pause" label="Pause" />
				) : (
					<Icon shape="play" label="Play" />
				) }
				<Icon
					shape="loading"
					label="Loading"
					transitionDuration={ LOADING_TRANSITION_DURATION }
					transitionDelay={ LOADING_TRANSITION_DELAY } />
			</button>
			{ songData && (
				<div className="c-gallery-apple-music-player__progress">
					<div
						className="c-gallery-apple-music-player__progress-value"
						role="progressbar"
						aria-valuemin="0"
						aria-valuemax="100"
						aria-valuenow={ Math.round( progressPercentage ) }
						style={ { width: `${ progressPercentage }%` } }
					>
						{ Math.round( progressPercentage ) }%
					</div>
				</div>
			) }
			{ ready && songData && (
				<time className="c-gallery-apple-music-player__time">
					{ formattedPlaybackTime( { songData, isAuthorized, playbackTime } ) }
				</time>
			) }
			<small className="c-gallery-apple-music-player__powered-by">
				Powered by
				<img
					alt="Apple Music"
					className="c-gallery-apple-music-player__logo"
					src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAM4AAAAyCAAAAAAxJLt6AAAE8UlEQVR4Ad3YIeyrOhvH8Z9PNV7UJ/V4hU+wk/jU4SZxODQWicMhcSRLUIiJORwK87wbZWPPoR1n5M1Nzj7iJncHgG9Z+2eA3K5JSf8Yd86UKMQ/k1N4AM6/kqPxkP9Izhmz62/kNJgp+o0ciVn1GzkFZgH9Rk6AB2/6kRwPd6Kj38i54s7v6UdyblIGOdG/nTPWeZq39DJWSRRlDf1HLkWiT1Gcf3HC7r5LpNPqts25RB4eVDwXTUUoMJPxjZjRVw9S08agjIxmjTKu7NYro2YtWuJJRryoUsZATB8rPPnJyHNirPw0X9oMkfFrxoJdJTtKRLMSRktvWhgFvYwRuPBGqwxGT+TexUvfcwJ8FLEcYfnUEDBimlUw2BLZwSjpqfHwJ1HSS85Hz7VLML1yfOwIbTliIi7HkZwWNvWnnBoWalpyNGx4uiUHKXHySM7kwUaM7pwbrHyT02JPQmTL8YipcCRHYxEkVVOnARYnd45ao1Wg2GWCyN+vseegpHf+kZxRgC8NlQdjcOWUWHjZY5tbKrAYCdRhR0CuHJ+tnDiSU8DIN8fJXTkShj+SMcjXuGN/5gzOHFxodTqUk2AmaRVhFjpyWhjetJlM6oar2l/V7Dn830YcytHbb0DriTvv5MjR7O6xP3iiRiXwWeHK4XfufCwn2t4dmsa7iRw5yrIO9TAyJNjRf8qJ6ck7lhPDyMjCljPylW8B44QIn4nRnrP8l5/UE9/m5Hz53M9prP06mGU44TNvsueczY45W25K+W3ODU9S1wPt55QwKrLZzcFgz8n69y99vawM+DaHAqyEr4tuJ6eA0R3Mae05mry3UfLxcDmQ04GDDIu/yentOQl2ZPaciNL1T+nzTg3f51CKDZkezqkFPvNdOZN4XWy0hPcHcmw98Ie9nKs9p5PYcXHkkH6umOPyg+FYDjUBNrzxWM4YYId05QzPtSJZZtORHBOkJTj4x3IowZ6TI4dCPKTLonBz5VzsOdwljxTelYfmDnXYFTlyLmYczQkDOnB3uFuh16Tgc07jyCGJXX5rzSEfq8aZ0+/kMNUzSEzWnApG4co5Yx9aa06FF0XOnPabnHWMLtaczvpQFPmzFERX7MNgzSGJp3KbU/Mr540VuTTrFiyHv1sI7Y+g7DnDSZM9J+PvDXhOb5t6mo19HcxSejPCqC0569UK/lbdSGBGcc/VkUMCRmrJIY+fmX02sWtlCwKMzp6TwDhvf8CKEnSnDrxni/jvwMmWE7Ld2Uc+v4EZrVLHUsBq2ZRs1x/X5tu6Y3DmjFj+z5ZTYxFNfBjX6/c28+siWDDLYc/goiKjEq+Dgo2Zw5ksOeyNx22bw1YKL64ubRVLgA89xVicGjP2CRaFK6fHU1i0fZsHb/MXfApYSfqQ0/t3muw5FRwKyxsTGZxCxU5qzyENu9Lk7K0GV2cOx3PcZ2Y757Dr3TmknAcFGWc4FXQ8h6Ldh0D7mSv6kDMp10FBixMcYjqYY6S2qchk2FAtuXKM0PHmGZtxVOeybctYrZtxA3tC5DpsU/sTmPBCxF1PYGRGb1LrI3Tp81vTEfEcKnwhZFTToo6k8MKG/jSd9UNFFmOsH2piblmoBO6EDFM2yGwLMW8RxDUxnZ7FI3GN9r15Fy9YDwpaXcuS/zSpmpH+L8a+zrXOq278sEVx1nnVDvTXbk2V6qR43+V/EDAN+fI2MiwAAAAASUVORK5CYII="
				/>
			</small>
			{ ready && (
				<button
					className="c-gallery-apple-music-player__button c-gallery-apple-music-player__button-sign-in"
					type="button"
					onClick={
						() => true === isAuthorized
							? send( { type: 'UNAUTHORIZE' } )
							: send( { type: 'AUTHORIZE' } )
					}
				>
					{ true === isAuthorized ? 'Sign Out' : 'Play the Full Song' }
				</button>
			) }
		</figure>
	);
};

export default AppleMusicPlayer;
