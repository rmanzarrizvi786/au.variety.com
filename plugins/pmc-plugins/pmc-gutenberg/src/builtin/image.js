/**
 * WordPress dependencies.
 */
import { unregisterBlockStyle } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';

const blockName = 'core/image';

/**
 * Disable the `rounded` image style.
 */
domReady( () => {
	unregisterBlockStyle( blockName, 'rounded' );
} );
