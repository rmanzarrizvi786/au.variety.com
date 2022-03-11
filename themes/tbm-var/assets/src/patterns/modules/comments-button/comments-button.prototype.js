const clonedeep = require( 'lodash.clonedeep' );

const o_comments_button_prototype = require( '../../objects/o-comments-button/o-comments-button.prototype' );
const o_comments_button = clonedeep( o_comments_button_prototype );

o_comments_button.c_link.c_link_classes += ' a-content-ignore';
o_comments_button.c_link.c_link_text = '16 Comments';

module.exports = {
	o_comments_button
}
