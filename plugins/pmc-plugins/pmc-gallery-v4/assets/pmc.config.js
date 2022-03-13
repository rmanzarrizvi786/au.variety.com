/**
 * Contains configuration for pmc-build-utils.
 * The config here gets extended in pmc-build-utils.
 */

const path = require( 'path' );

module.exports = {
	reactApp: true,

	webpack: {
		entry: {
			'gallery': path.resolve( './src/js/gallery.js' ),
			'gallery-inline': path.resolve( './src/js/gallery-inline.js' ),
			'gallery-vertical': path.resolve( './src/js/gallery-vertical.js' ),
			'gallery-runway': path.resolve( './src/js/gallery-runway.js' ),
			'admin-gallery': path.resolve( './src/js/admin-gallery.js' ),
			'admin-list': path.resolve( './src/js/admin-list.js' ),
			'admin-list-match': path.resolve( './src/js/admin/admin-list-match.js' ),
			'admin-bulk-media': path.resolve( './src/js/admin-bulk-media.js' ),
			'admin-ui-improvements': path.resolve( './src/js/admin-ui-improvements.js' ),
			'admin-ui-simplification': path.resolve( './src/js/admin-ui-simplification.js' ),
			'admin-attachment-taxonomy': path.resolve( './src/js/admin-attachment-taxonomy.js' ),
			'admin-caption-shortcode-frame': path.resolve( './src/js/admin-caption-shortcode-frame.js' ),
		},
		performance: {
			maxEntrypointSize: 450000, // bytes
			maxAssetSize: 450000,
			assetFilter: assetFilename => assetFilename.endsWith( '.js' ),
		}
	}
};
