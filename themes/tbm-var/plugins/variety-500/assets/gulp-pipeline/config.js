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
		join( DEST_DIR, 'js/*.*' ),
		join( '!', DEST_DIR, 'js/vendor/**' ),
		join( DEST_DIR, 'css/*.*' ),
		join( '!', DEST_DIR, 'css/vendor/**' )
	],
	INCLUDE_PATHS = [ './node_modules/' ],

	assets        = {
		src:       '.',
		dest:      '.',
		glob:      '{favicon.ico,robots.txt,sitemap.xml,*.html}',
		cacheName: 'assets-task'
	},

	js            = {
		src:        'js-src',
		dest:       'js',
		fileName:   'main.js',
		bundleName: 'main.js',
		glob:       '**/*.js',

		/**
		 * Note that noParse is an array which will skip all require() and global parsing for each file in the array. Use this for
		 * giant libs like jquery or threejs that don't have any requires or node-style globals but take forever to parse.
		 * http://stackoverflow.com/a/18543403/1949274
		 */
		noParse: [
			require.resolve( 'jquery' ),
			require.resolve( 'underscore' )
		],

		/**
		 * Extensions is an array of optional extra extensions for the module lookup machinery to use when the extension
		 * has not been specified. By default browserify considers only .js and .json files in such cases.
		 */
		extensions: []
	},

	css           = {
		src:       'scss',
		dest:      'css',
		glob:      '**/*.scss',
		cacheName: 'css-task',

		/**
		 * Autoprefixer uses Browserslist, so you can specify the browsers you want to target in your project by queries
		 * Updated to current @wordpress/browserslist-config
		 */
		autoprefixerBrowsers:   [
			'> 1%',
			'ie >= 11',
			'last 1 Android versions',
			'last 1 ChromeAndroid versions',
			'last 2 Chrome versions',
			'last 2 Firefox versions',
			'last 2 Safari versions',
			'last 2 iOS versions',
			'last 2 Edge versions',
			'last 2 Opera versions',
		],
		postCSSAssetsLoadPaths: [
			join( DEST_DIR, 'images' ),
			join( DEST_DIR, 'fonts' )
		]
	},

	images        = {
		src:       'images',
		dest:      'images',
		glob:      '**/*.+(png|jpg|jpeg|gif|bmp|svg)',
		cacheName: 'images-task'
	},

	cssLint       = {
		src:  'scss',
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

	jsLint        = {
		src:        'js-src',
		glob:       '**/*.js',
		ignoreGlob: '!**/node_modules/**',
		ideSupport: true, // Same as `cssLint.ideSupport`. For this you need `eslint` support.
		cacheName:  'js-lint-task'
	},

	rev           = {
		manifestFile:     'rev-manifest.json',
		assets:           {
			glob:       '**/*',
			ignoreGlob: '**/*.+(html|map|jpg|jpeg|png|gif|svg)'
		},
		updateReferences: {
			glob: '**/*.+(html|css|js)'
		}
	},

	sizeReport    = {
		src:        '.',
		glob:       '**/*',
		ignoreGlob: '!**/node_modules/**'
	},

	watch         = {
		watchableTasks: [ 'assets', 'fonts', 'images', 'css' ] // JS wathing handled in the javascripts task.
	},

	browserSync   = {
		port: argv.port || ( isProd ? 8080 : 3001 ) // eslint-disable-line no-magic-numbers
	},

	checkVersions = {
		npm:  '2.14.2',
		node: '4.0.0'
	},

	prodTasks     = [
		'clean',
		[
			'js-lint',
			'css-lint'
		],
		[
			'images',
			'css',
			'js'
		],
		'assets',
		'size-report'
	],

	devTasks      = [
		'clean',
		[
			'assets',
			'images',
			'css',
			'js'
		],
		'watch'
	];
