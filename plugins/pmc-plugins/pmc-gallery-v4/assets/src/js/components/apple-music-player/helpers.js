const PREVIEW_DURATION_SECONDS = 30; // @TODO: Is there a way to get this automatically from Apple?

function getDurationSeconds( { songData, isAuthorized } ) {
	const durationInMillis = songData.attributes.durationInMillis;
	if ( isAuthorized && durationInMillis !== null ) {
		return Math.round( durationInMillis / 1000 );
	}
	return PREVIEW_DURATION_SECONDS;
}

function formattedPlaybackTime( { songData, isAuthorized, playbackTime } ) {
	const timeRemaining = playbackTime ? playbackTime : getDurationSeconds( { songData, isAuthorized } );
	const seconds = timeRemaining % 60;
	const minutes = ( timeRemaining - seconds ) / 60;
	return `${ minutes }:${ seconds < 10 ? '0' : '' }${ seconds }`;
}

export {
	formattedPlaybackTime,
};
