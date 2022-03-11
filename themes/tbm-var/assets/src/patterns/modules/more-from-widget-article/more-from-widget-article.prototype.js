const clonedeep = require( 'lodash.clonedeep' );

const more_from_widget_prototype = require( '../more-from-widget/more-from-widget.variety-vip.js' );
const more_from_widget = clonedeep( more_from_widget_prototype );

more_from_widget.more_from_widget_classes += ' lrv-u-margin-lr-auto lrv-u-margin-b-2 u-margin-t-150@tablet u-max-width-618';

module.exports = {
	more_from_widget,
};
