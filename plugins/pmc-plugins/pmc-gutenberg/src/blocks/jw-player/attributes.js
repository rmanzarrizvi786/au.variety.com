/**
 * Attributes store state for the block that can be changed by the user.
 */
const attributes = {
	isPlaylist: {
		type: 'boolean',
		default: false,
	},
	playerId: {
		type: 'string',
		default: '',
	},
	selectionTitle: {
		type: 'string',
		default: 'JW Player Embed',
	},
	videoId: {
		type: 'string',
		default: '',
	},
};

export { attributes };
