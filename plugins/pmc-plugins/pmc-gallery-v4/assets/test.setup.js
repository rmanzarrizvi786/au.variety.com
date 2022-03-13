/**
 * Contains scripts which runs before Jest test is setup.
 */

import { configure } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';

configure({ adapter: new Adapter() });

global._ = require( 'underscore' );
global.jQuery = require( 'jquery' );
global.fetch = require( 'jest-fetch-mock' );

// Kind of polyfill for slick js test.
window.matchMedia = window.matchMedia || function () {
	return {
		matches: false,
		addListener() {},
		removeListener() {}
	};
};
