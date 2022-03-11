// jshint es3: false
// jshint esversion: 6

import gulp from 'gulp';
import del from 'del';
import { CLEAN_DIR } from '../../config';

gulp.task( 'clean', ( done ) => {
	del( CLEAN_DIR ).then( () => done() );
} );
