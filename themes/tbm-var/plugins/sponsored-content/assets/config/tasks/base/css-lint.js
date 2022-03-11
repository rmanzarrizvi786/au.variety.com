// jshint es3: false
// jshint esversion: 6

import { isDev, SRC_DIR, css, cssLint } from '../../config';
import gulp from 'gulp';
import { join } from 'path';
import cache from 'gulp-cached';
import gulpIf from 'gulp-if';
import postcss from 'gulp-postcss';
import reporter from 'postcss-reporter';
import scss from 'postcss-scss';
import stylelint from 'stylelint';
import colorguard from 'colorguard';
import filterStream from 'postcss-filter-stream';

const paths      = {
		  src: join( SRC_DIR, cssLint.src, cssLint.glob )
	  },
	  processors = [
		  stylelint(),
		  reporter( { clearMessages: true } )
	  ];

gulp.task( 'css-lint', () => {
	return gulp
			   .src( paths.src )
			   .pipe( gulpIf( isDev, cache( cssLint.cacheName ) ) )
			   .pipe( postcss( processors, { syntax: scss } ) );
} );
