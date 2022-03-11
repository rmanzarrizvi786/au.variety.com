// jshint es3: false
// jshint esversion: 6

import chalk from 'chalk';
import gutil from 'gulp-util';

export default function reportError ( message ) {
	gutil.log( chalk.white.bgRed.bold( message ) );
	process.exit( 1 ); // eslint-disable-line no-magic-numbers
}
