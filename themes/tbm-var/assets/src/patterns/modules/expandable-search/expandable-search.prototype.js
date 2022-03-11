const clonedeep = require( 'lodash.clonedeep' );

const o_icon_button_search_prototype = require( '@penskemediacorp/larva-patterns/objects/o-icon-button/o-icon-button.search' );
const o_icon_button_search = clonedeep( o_icon_button_search_prototype );

const search_form_prototype = require( '../search-form/search-form.prototype' );
const search_form = clonedeep( search_form_prototype );

o_icon_button_search.o_icon_button_classes += ' u-color-brand-primary:hover a-hidden@mobile-max';
o_icon_button_search.c_icon.c_icon_name = 'variety-search';
o_icon_button_search.c_icon.c_icon_classes = o_icon_button_search.c_icon.c_icon_classes.replace( 'lrv-u-width-16', 'u-width-25' );
o_icon_button_search.c_icon.c_icon_classes = o_icon_button_search.c_icon.c_icon_classes.replace( 'lrv-u-height-16', 'u-height-25' );
o_icon_button_search.c_icon.c_icon_classes += ' lrv-u-margin-a-050';

module.exports = {
	expandable_search_classes: 'lrv-u-color-white lrv-u-font-family-secondary u-background-color-brand-accent-100-b',
	expandable_search_inner_classes: '',
	o_icon_button_search: o_icon_button_search,
	search_form: search_form,
};
