// jscs:disable
// jshint es3: false
// jshint esversion: 6

import { join, normalize } from 'path';
import minimist from 'minimist';

let argv = minimist( process.argv.slice( 2 ) ); // eslint-disable-line no-magic-numbers
export const isDev         = argv.dev,
	isProd        = argv.prod,

	ROOT_DIR      = normalize( join( __dirname, '..' ) ),
	SRC_DIR       = ROOT_DIR,
	DEST_DIR      = ROOT_DIR,
	CLEAN_DIR     = [
		join( DEST_DIR, 'build/js/*.*' ),
		join( '!', DEST_DIR, 'build/js/vendor/**' ),
		join( DEST_DIR, 'build/css/*.*' ),
		join( '!', DEST_DIR, 'build/css/vendor/**' )
	],
	INCLUDE_PATHS = [ './node_modules/', './node_modules/scss-query' ],

	css           = {
		src:       'src/scss',
		dest:      'build/css',
		glob:      '**/*.scss',
		cacheName: 'css-task',

		/**
		 * Autoprefixer uses Browserslist, so you can specify the browsers you want to target in your project by queries
		 * like last 2 versions or > 5%. For more info check out https://github.com/ai/browserslist#browsers and
		 * https://github.com/ai/browserslist#queries
		 */
		autoprefixerBrowsers:   [
			'Edge >= 13',
			'Explorer >= 11',
			'ExplorerMobile >= 11',
			'Firefox >= 50',
			'Chrome >= 55',
			'ChromeAndroid >= 55',
			'Android >= 55',
			'Safari >= 10',
			'Opera >= 42',
			'iOS >= 9'
		],
		postCSSAssetsLoadPaths: [
			join( DEST_DIR, 'build/images' ),
			join( DEST_DIR, 'fonts' )
		]
	},

	cssLint       = {
		src:  'src/scss',
		glob: '**/*.scss',

		/**
		 * If you have built-in ide support for `stylelint` feel free to set this to true, if `ideSupport` is true.
		 * The `css-lint` task won't run after each css modification if true.
		 */
		ideSupport: false,
		cacheName:  'css-lint-task',

		/**
		 * Ignore files / folders from being linted. Note for `stylelint` you have to edit `.stylelintrc`
		 * ignoreFiles attribute.
		 */
		ignoreGlob: '**/css/vendor/**'
	},

	watch         = {
		watchableTasks: [ 'css' ] // JS watching handled in the javascripts task.
	},

	browserSync   = {
		port: argv.port || ( isProd ? 8080 : 3001 ) // eslint-disable-line no-magic-numbers
	},

	prodTasks     = [
		'clean',
		[
			'css-lint'
		],
		[
			'css'
		]
	],

	devTasks      = [
		'clean',
		[
			'css'
		],
		'watch'
	];
