const clonedeep = require( 'lodash.clonedeep' );

const c_title = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' ) );

c_title.c_title_classes = 'a-font-secondary-bold-s';

module.exports = c_title;
