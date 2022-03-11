const clonedeep = require( 'lodash.clonedeep' );

const most_popular_sidebar = clonedeep( require( './most-popular-sidebar.prototype' ) );

// Remove max-heights
most_popular_sidebar.most_popular_sidebar_classes = '';

module.exports = most_popular_sidebar;
