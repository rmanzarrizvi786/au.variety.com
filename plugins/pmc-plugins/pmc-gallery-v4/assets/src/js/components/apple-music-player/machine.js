import { Machine, assign } from 'xstate';

const LOADING_TRANSITION_DURATION = 200; // milliseconds
const LOADING_TRANSITION_DELAY = 400; // milliseconds

// https://xstate.js.org/viz/?gist=5f70218520f8ec60a69cb10b1611ab1d
const musicMachine = Machine(
	{
		id: 'music',
		context: {
			song: null,
			songData: null,
			playbackData: null,
			isVisible: null,
			wasPlaying: null,
		},
		initial: 'loading',
		states: {
			loading: {
				on: {
					MUSIC_KIT_LOAD: {
						target: 'ready',
					},
				},
			},
			ready: {
				on: {
					VISIBLE: {
						actions: [ 'setVisible' ],
					},
					SONG_DATA_LOAD: {
						actions: [ 'setSongData' ],
					},
					AUTHORIZE: {
						actions: [ 'authorize' ],
					},
					UNAUTHORIZE: {
						actions: [ 'unauthorize' ],
					},
					AUTHORIZE_CHANGE: {
						actions: [ 'stopSong', 'clearWasPlaying' ],
						target: 'ready',
					},
					SONG_CHANGE: {
						target: 'ready',
					},
				},
				initial: 'paused',
				states: {
					paused: {
						on: {
							PLAY: {
								target: 'preparing',
								actions: [ 'queueSong' ],
							},
						},
					},
					preparing: {
						on: {
							PAUSE: {
								actions: [ 'stopSong' ],
								target: 'paused',
							},
						},
						initial: 'queuing_song',
						states: {
							queuing_song: {
								on: {
									TIME_CHANGE: [
										{ target: 'restoring_playback', cond: 'wasPlaying' },
										{ target: 'transitioning' },
									],
								},
							},
							restoring_playback: {
								entry: [ 'restorePlayback' ],
								on: {
									TIME_CHANGE: [
										{ target: 'transitioning' },
									],
								},
							},
							transitioning: {
								after: [
									{
										delay: LOADING_TRANSITION_DURATION + LOADING_TRANSITION_DELAY,
										target: "done",
									},
								],
							},
							done: {
								type: 'final',
							},
						},
						onDone: 'playing',
					},
					playing: {
						on: {
							TIME_CHANGE: {
								actions: [ 'setPlaybackData', 'setWasPlaying' ],
							},
							PAUSE: {
								actions: [ 'stopSong' ],
								target: 'paused',
							},
							SONG_END: {
								actions: [ 'clearWasPlaying' ],
								target: 'paused',
							},
						},
						initial: 'counting',
						states: {
							counting: {
								on: {
									THIRTY_SECONDS: 'played_thirty_seconds',
								},
							},
							played_thirty_seconds: {},
						},
					},
				},
			},
		},
	},
	{
		actions: {
			setVisible: assign( { isVisible: () => true } ),
			setWasPlaying: assign( { wasPlaying: () => true } ),
			setSongData: assign( {
				songData: ( context, event ) => {
					return event.songData || context.songData || null;
				},
			} ),
			setPlaybackData: assign( {
				playbackData: ( context, event ) => {
					return event.playbackData || context.playbackData || null;
				},
			} ),
			clearWasPlaying: assign( { wasPlaying: () => null } ),
			queueSong: ( context ) => {
				if ( window.MusicKit ) {
					// https://developer.apple.com/documentation/musickitjs/musickit/setqueueoptions
					// https://developer.apple.com/documentation/musickitjs/musickit/mediaitemoptions
					window.MusicKit.getInstance().setQueue( { song: context.song } ).then( () => {
						window.MusicKit.getInstance().player.play();
					} );
				}
			},
			restorePlayback: ( context ) => {
				if ( window.MusicKit && context.playbackData ) {
					window.MusicKit.getInstance().player.seekToTime( context.playbackData.time );
				}
			},
			stopSong: () => window.MusicKit && window.MusicKit.getInstance().player.stop(),
			authorize: () => window.MusicKit && window.MusicKit.getInstance().authorize(),
			unauthorize: () => window.MusicKit && window.MusicKit.getInstance().unauthorize(),
		},
		guards: {
			wasPlaying: ( context ) => context.wasPlaying,
		},
	},
);

export {
	musicMachine,
	LOADING_TRANSITION_DURATION,
	LOADING_TRANSITION_DELAY,
};
