const clonedeep = require( 'lodash.clonedeep' );

const issue_item = clonedeep( require( '../issue-item/issue-item.prototype.js' ) );

module.exports = {
	issue_item_list_classes: 'lrv-a-unstyle-list lrv-a-grid lrv-a-cols2 lrv-a-cols3@tablet',
	issue_item_list: [
		issue_item,
		issue_item,
		issue_item,
	]
}