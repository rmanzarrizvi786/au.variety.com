const clonedeep = require( 'lodash.clonedeep' );

const homepage_lists_prototype = require( '../homepage-lists/homepage-lists.prototype' );
const homepage_lists = clonedeep( homepage_lists_prototype );

const iheart_widget_prototype = require( '../iheart-widget/iheart-widget.prototype' );
const iheart_widget = clonedeep( iheart_widget_prototype );

homepage_lists.lists_classes += ' a-span2@tablet a-span3@desktop-xl';

module.exports = {
	homepage_lists_iheartradio_classes: 'lrv-a-wrapper lrv-a-grid a-cols3@tablet a-cols4@desktop-xl',
	homepage_lists,
	iheart_widget
};
