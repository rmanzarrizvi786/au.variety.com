const defaultItems = [];

for ( let i = 0; i <= 5; i++ ) {
	defaultItems[ i ] = {
		postId: null,
		title: null,
	};
}

export const attributes = {
	imageId: {
		type: 'number',
		default: '',
	},
	items: {
		type: 'array',
		default: defaultItems,
	},
};
