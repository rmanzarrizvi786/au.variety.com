<?php
/**
 * PMC Gutenberg configuration for Variety.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Gutenberg {

	use Singleton;

	public $enabled_blocks = [];

	public $disabled_blocks = [];

	/**
	 * Class constructor
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_filter( 'pmc_gutenberg_init_blocks', [ $this, 'filter_pmc_gutenberg_init_blocks' ] );
		add_filter( 'pmc_gutenberg_carousel_block_config', [ $this, 'filter_pmc_gutenberg_carousel_block_config' ] );
	}

	/**
	 * Filter the default blocks from the plugin by enabling needed blocks that
	 * are not enabled, and disabling blocks the theme does not need.
	 *
	 * @param array Blocks registered by default from the plugin.
	 */
	public function filter_pmc_gutenberg_init_blocks( $blocks_arr ) {

		$blocks_arr_blocks_removed = array_diff( $blocks_arr, $this->disabled_blocks );
		$blocks_arr_final          = array_merge( $blocks_arr_blocks_removed, $this->enabled_blocks );

		return $blocks_arr_final;
	}

	/**
	 * Set custom post type for videos
	 *
	 * @param array $block_config_arr
	 *
	 * @return array
	 */
	public function filter_pmc_gutenberg_carousel_block_config( array $block_config_arr ) : array {
		$block_config_arr['video']['post_type'] = 'variety_top_video';
		return $block_config_arr;
	}
}

PMC_Gutenberg::get_instance();
