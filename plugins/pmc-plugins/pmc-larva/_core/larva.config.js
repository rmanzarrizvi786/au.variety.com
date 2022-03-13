const path = require( 'path' );
const LARVA_PATTERNS_PATH = path.resolve( './node_modules/@penskemediacorp/larva-patterns' );

module.exports = {
	webpack: {
		aliases: {
			'@larva-js': path.resolve( './node_modules/@penskemediacorp/larva-js/src' ),
			'@js': path.resolve( './src/js' )
		},
		entries: {
			'pmc-profiles': './entries/pmc-profiles.entry.js' // TODO: if we remove this, we need a noop entrypoint to appease webpack.
		}
	},

	backstop: {
		testBaseUrl: 'http://localhost:3000/larva/__tests__',
		testScenario: {
			'delay': 1000,
			'misMatchThreshold': 0.5,
		},
		testPaths: [
			'/profile',
			'/profile-index?has_side_skins=true',
			'/vlanding?has_side_skins=true',
		],
		backstopConfig: {
			'engineOptions': {
				'args': [ '--no-sandbox', '--proxy-server=127.0.0.1:3000', '--proxy-bypass-list=<-loopback>' ],
			}
		}
	},

	patterns: {
		larvaPatternsDir: LARVA_PATTERNS_PATH,
		projectPatternsDir: path.resolve( './src/patterns' ),

		// Modules that do not need their own JSON
		ignoredModules: [
			'footer-menus',
			'footer-social',
			'footer-tip'
		]
	},

	parser: {
		// After running the parser one time,
		// update twigDir to equal path.resolve( './template-parts/patterns' )
		twigDir: LARVA_PATTERNS_PATH,
		phpDir: path.resolve( './build/patterns' ),
		isUsingPlugin: true,
	}
};
