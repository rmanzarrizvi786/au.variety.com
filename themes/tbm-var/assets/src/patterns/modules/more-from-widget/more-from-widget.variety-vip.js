const clonedeep = require( 'lodash.clonedeep' );

const more_from_widget_prototype = require( './more-from-widget.prototype.js' );
const more_from_widget = clonedeep( more_from_widget_prototype );

more_from_widget.o_tease_list.o_tease_list_items[0].o_tease_classes = more_from_widget.o_tease_list.o_tease_list_items[0].o_tease_classes.replace( 'lrv-u-align-items-center', 'lrv-u-flex-direction-column@mobile-max' );
more_from_widget.o_tease_list.o_tease_list_items[0].o_tease_secondary_classes = more_from_widget.o_tease_list.o_tease_list_items[0].o_tease_secondary_classes.replace( 'u-width-44p@mobile-max', 'lrv-u-width-100p' );
more_from_widget.o_tease_list.o_tease_list_items[0].o_tease_secondary_classes += ' u-order-n1';
more_from_widget.o_tease_list.o_tease_list_items[0].c_title.c_title_classes += ' lrv-u-margin-t-050@mobile-max';

module.exports = {
	...more_from_widget
};
