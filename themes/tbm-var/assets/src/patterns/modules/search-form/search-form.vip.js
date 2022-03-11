const clonedeep = require( 'lodash.clonedeep' );

const search_form_prototype = require( './search-form.prototype' );
const search_form_vip = clonedeep( search_form_prototype );

search_form_vip.search_form_classes += ' lrv-u-background-color-brand-primary';

module.exports = search_form_vip;