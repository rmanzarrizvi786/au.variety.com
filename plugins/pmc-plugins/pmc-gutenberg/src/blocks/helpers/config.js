/**
 * Get the name of the key of localized configuration from the theme to be
 * accessed on the window object.
 *
 * @param {string} blockName The name used to register the block e.g. pmc/story
 * @see localize_data in pmc-gutenberg/classes/class-story-block-engine.php
 * @return {Object} Key for configuration.
 */

const getBlockConfigKey = ( blockName ) => {
	const blockSlug = blockName.split( '/' )[ 1 ];
	const snakeSlug = blockSlug.replace( /-/g, '_' );

	return `pmc_${ snakeSlug }_block_config`;
};

/**
 * Get block configuration by block name
 * accessed on the window object.
 *
 * @param {string} blockName The name used to register the block e.g. pmc/story
 * @return {Object} block configuration.
 */
const getBlockConfig = ( blockName ) => {
	return window.pmc_gutenberg_blocks_config[ getBlockConfigKey( blockName ) ];
};

export { getBlockConfigKey, getBlockConfig };
