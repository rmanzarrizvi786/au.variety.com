// jshint es3: false
// jshint esversion: 6

import browserSync from 'browser-sync';
import gulp from 'gulp';
import { DEST_DIR, browserSync as _browserSync } from '../../config';

export const bs = browserSync.create();

gulp.task( 'browser-sync', () => {
	bs.init( {

		// A, if you don't have a backend api use the built in server.
		server: {
			baseDir: DEST_DIR
		},

		// B, if you got a backend api proxy the request to it.
		// proxy: 'some-vhost-of-existing-backend.api',

		// Custom middleware for mock api.
		//middleware(req, res, next) {
		//  require(join(ROOT_DIR, 'api', 'api'))(req, res, next);
		//},

		port: _browserSync.port, // Default port.
		notify: false, // Disable notify popup.
		open: false, // Do not open browser on start.
		ui: false // Disable UI completely.
	} );
} );
