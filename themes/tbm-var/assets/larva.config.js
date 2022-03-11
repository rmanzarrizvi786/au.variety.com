const path = require( 'path' );
const LARVA_PATTERNS_PATH = path.resolve(
	'./node_modules/@penskemediacorp/larva-patterns'
);
const SRC_DIR = path.resolve( './entries' );

module.exports = {
	brand: 'variety',
	webpack: {
		aliases: {
			'@larva-js': path.resolve(
				'./node_modules/@penskemediacorp/larva-js/src'
			),
			'@npm': path.resolve( './node_modules/' ),
			'@js': path.resolve( __dirname, './src/js' ),
			'@one-offs': path.resolve( './src/patterns/one-offs' ),
		},

		entries: {
			admin_single: SRC_DIR + '/admin-single.entry.js',
			author: SRC_DIR + '/author.entry.js',
			category: SRC_DIR + '/category.entry.js',
			common: SRC_DIR + '/common.entry.js',
			frontpage: SRC_DIR + '/frontpage.entry.js',
			'larva-ui': SRC_DIR + '/larva-ui.entry.js',
			page: SRC_DIR + '/page.entry.js',
			single: SRC_DIR + '/single.entry.js',
			variety_non_vip: SRC_DIR + '/variety-non-vip.entry.js',
			variety_vip: SRC_DIR + '/variety-vip.entry.js',
		},
	},

	backstop: {
		// TODO: need a way to pass in this top config to run all tests,
		// vs. the second config which is for individual tests - maybe
		// a CLI for the individual tests, similar to npm run test -- {filter}

		testBaseUrl: 'http://localhost:3000/project/__tests__/',
		// testPaths: patternsToTest,
		testPaths: [
			'homepage',
			// 'homepage?has_side_skins=true',
			'section-front',
			// 'section-front?has_side_skins=true',
			// 'article',
			'article?has_side_skins=true',
			'vip-homepage/variety-vip',
			// 'featured-article',
			'featured-article?is_vertical=true',
			// 'featured-article?has_side_skins=true',
			// 'editorial-hub',
			// 'editorial-hub?has_side_skins=true',
		],

		// testBaseUrl: 'http://localhost:3000/project',
		// larvaModules: [
		// 	'top-stories-with-sidebar'
		// ],
		testScenario: {
			delay: 1000,
			misMatchThreshold: 0.5,
		},
		backstopConfig: {
			engineOptions: {
				args: [
					'--no-sandbox',
					'--proxy-server=127.0.0.1:3000',
					'--proxy-bypass-list=<-loopback>',
				],
			},
		},
	},

	patterns: {
		larvaPatternsDir: LARVA_PATTERNS_PATH,
		projectPatternsDir: path.resolve( './src/patterns' ),
		ignoredModules: [
			'mega-menu-footer',
			'mega-menu-content',
			'mega-menu-item',
		],
		variants: [ 'variety-vip' ],
	},

	parser: {
		isCore: false,

		// Uncomment when you want to parse Larva patterns - should be very rare,
		// only when a Larva pattern is updated.
		// relativeSrcOverride: LARVA_PATTERNS_PATH
	},
};
