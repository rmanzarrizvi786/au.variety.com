#!/usr/bin/env node
'use strict';

const styleguide = require('sc5-styleguide/lib/modules/cli/styleguide-cli');

styleguide({
	// Styleguide title
	title: 'Variety 500 Styleguide',

	// Styleguide overview path
	overviewPath: 'scss/styleguide.md',

	// KSS source material
	kssSource: 'scss/**/*.scss',

	// Stylesheets to include
	// global.css: primary site styles
	// styleguide.css: styleguide-only styles
	styleSource: [
		'css/*.css',
	],

	// Common wrapper class
	commonClass: 'sg-common',

	// Output path
	output: 'styleguide',

	// Watch for changes
	watch: true,

	// Serve
	server: true
});
