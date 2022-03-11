// jshint es3: false
// jshint esversion: 6

import * as config from '../../config';
import gulp from 'gulp';
import { join } from 'path';
import watch from 'gulp-watch';
import camelCase from 'camelcase';

gulp.task( 'watch', [ 'browser-sync' ], () => {
	config.watch.watchableTasks.forEach( ( taskName ) => {
		let source, task = config[ camelCase( taskName ) ];

		if ( ! task ) {
			return;
		}

		source = join( config.SRC_DIR, task.src, task.glob );
		watch( source, () => gulp.start( taskName ) );
	} );
} );
