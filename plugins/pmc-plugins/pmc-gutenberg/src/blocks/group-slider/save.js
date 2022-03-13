/**
 * WordPress dependencies.
 */
import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Save inner block content only, as we discard the markup and take the gallery
 * IDs from the block's attributes.
 *
 * @return {JSX.Element} Gallery block's markup.
 */
const Save = () => {
	return <InnerBlocks.Content />;
};

export default Save;
