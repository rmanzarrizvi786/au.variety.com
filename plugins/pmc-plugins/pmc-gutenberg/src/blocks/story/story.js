/**
 * External dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Block dependencies
 */
import { getStoryBlockRegistrationObject } from './utils';

/**
 * The Story block has no overrides.
 */
const storyBlock = getStoryBlockRegistrationObject();

registerBlockType( 'pmc/story', storyBlock );

export { storyBlock };
