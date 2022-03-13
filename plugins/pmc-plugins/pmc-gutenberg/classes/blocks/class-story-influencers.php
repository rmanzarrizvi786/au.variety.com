<?php

namespace PMC\Gutenberg\Blocks;

use PMC\Gutenberg\Story_Block_Engine;

class Story_Influencers extends Story_Block_Engine {

	/**
	 * Set configuration and create the influencers story block.
	 *
	 * @see Story_Block_Engine::localize_data
	 */
	protected function __construct() {

		// This module is specific to Indiewire
		$this->larva_module = 'influencer-card';

		// Filterable with pmc_gutenberg_story_influencers_block_config
		$this->story_block_config = [
			'influencers' => [
				'postType'     => 'influencers',
				'taxonomySlug' => 'influencers-category',
				'viewMoreText' => __( 'View Profile', 'pmc-gutenberg' ),
			],
		];

		$this->create_story_block( 'story-influencers' );
	}
}
