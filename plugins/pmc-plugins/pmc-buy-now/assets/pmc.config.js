/**
 * Contains configuration for pmc-build-utils.
 * The config here gets extended in pmc-build-utils.
 */

const path = require( 'path' );

module.exports = {
	reactApp: false,

	webpack: {
		entry: {
			'admin-ui': path.resolve( './src/js/admin-ui.js' )
		},
		performance: {
			maxEntrypointSize: 450000, // bytes
			maxAssetSize: 450000,
			assetFilter: assetFilename => assetFilename.endsWith( '.js' )
		}
	}
};
