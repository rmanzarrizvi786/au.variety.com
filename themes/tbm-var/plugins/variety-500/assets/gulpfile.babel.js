// jshint es3: false
// jshint esversion: 6

import requireDir from 'require-dir';
import gulp from 'gulp';
import runSequence from 'run-sequence';
import taskList from './gulp-pipeline/utils/base/get-tasks';

/*
 Rather than manage one giant configuration file responsible
 for creating multiple tasks, each task has been broken out into
 its own file in gulp-pipeline/tasks/base && /project. Any file in those directories gets
 automatically loaded.
 */
requireDir( './gulp-pipeline/tasks', { recurse: true } );

/*
 To add a new task, simply create a new task file in asset-pipeline/tasks/project. As you can see
 there are two folders base and project. The base folder contains tasks provided by the repo. If you
 want to create a new task or override an existing one (f.e. css task to use less/stylus) you should create it
 under /project scope. This way you can update this project without breaking anything later on.
 */
gulp.task( 'default', ( done ) => {
	runSequence( ...taskList, done );
} );
