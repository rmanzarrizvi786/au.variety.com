const clonedeep = require( 'lodash.clonedeep' );

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype.js' );
const c_icon = clonedeep( c_icon_prototype );

c_icon.c_icon_name = 'vip-plus';
c_icon.c_icon_classes = 'u-width-38 u-height-14';

module.exports = {
	c_icon,
	vip_corporate_subscriptions_submission_text: false
};
