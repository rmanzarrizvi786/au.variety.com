const clonedeep = require( 'lodash.clonedeep' );

const c_critics_pick_label_prototype = require( './c-critics-pick-label.protoype' );
const c_critics_pick_label = clonedeep( c_critics_pick_label_prototype );

const {
	c_span,
} = c_critics_pick_label;

c_span.c_span_classes = c_span.c_span_classes.replace( 'a-glue--t-n125 a-glue--t-n150@tablet', 'a-glue--t-n150@tablet' );
c_span.c_span_classes += ' lrv-a-glue--l-0 a-glue--b-125 a-glue--b-auto@tablet';

module.exports = {
	...c_critics_pick_label,
};
