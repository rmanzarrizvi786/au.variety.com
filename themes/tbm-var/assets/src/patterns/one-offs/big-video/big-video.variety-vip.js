const clonedeep = require( 'lodash.clonedeep' );

const big_video = require( './big-video.prototype.js' );

const a_content = require( '../a-content/a-content.variety-vip.js' );

const more_from_widget = require( '../../modules/more-from-widget-article/more-from-widget-article.prototype.js' );

module.exports = {
	...big_video,
	a_content,
	more_from_widget,
};
