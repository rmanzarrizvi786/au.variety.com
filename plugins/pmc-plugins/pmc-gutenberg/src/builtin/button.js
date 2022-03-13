/**
 * WordPress dependencies.
 */
import domReady from '@wordpress/dom-ready';
import { unregisterBlockStyle } from '@wordpress/blocks';

const blockName = 'core/button';

/**
 * Disable the “outline” buttonn block style in the editor
 * https://developer.wordpress.org/block-editor/developers/filters/block-filters/#block-style-variations
 */
function setup() {
	unregisterBlockStyle( blockName, 'outline' );
}

/**
 * Run setup on domReady.
 * https://github.com/WordPress/gutenberg/pull/11532#issuecomment-442412409
 */
domReady( setup );
