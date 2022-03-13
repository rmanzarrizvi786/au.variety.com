<?php
/**
 * Story block for PMC Top Videos V2.
 *
 * TODO: port Patterns to `pmc-larva` from `wwd-2021`, introduce module controllers.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Blocks;

use PMC\Digital_Daily\CPT;
use PMC\Gutenberg\Story_Block_Engine;
use PMC\Gutenberg\Interfaces\Block_Base\With_Dependencies;
use PMC\Gutenberg\Traits\Block_Base\Digital_Daily_Common_Elements;
use PMC\Larva;
use PMC\Top_Videos_V2\PMC_Top_Videos;

/**
 * @codeCoverageIgnore Class will be refactored into Larva controllers after
 *                     Patterns are moved to `pmc-larva`.
 */
class Story_Top_Video extends Story_Block_Engine implements With_Dependencies {
	/**
	 * Reusable pattern elements shared across Digital Daily blocks.
	 */
	use Digital_Daily_Common_Elements;

	/**
	 * Configure block.
	 */
	public function __construct() {
		$this->larva_module = 'block-video';

		$this->story_block_config = [
			PMC_Top_Videos::POST_TYPE_NAME => [
				'postType'     => PMC_Top_Videos::POST_TYPE_NAME,
				'taxonomySlug' => 'vcategory',
				'viewMoreText' => '',
			],
		];

		$this->create_story_block( 'story-top-video' );
	}

	/**
	 * Populate pattern from post data.
	 *
	 * @param string $block_content
	 * @param array  $block
	 * @return array|null
	 */
	public function larva_data( string $block_content, array $block ): ?array {
		$data = parent::larva_data( $block_content, $block );

		if ( null === $data ) {
			return null;
		}

		// Remove extraneous data from parent method.
		unset( $data['c_link'], $data['c_span'] );

		$this->_add_common_data( $data );

		switch ( $this->larva_module ) {
			case 'block-video':
				$this->_conditionally_add_c_author( $data );

				if ( $this->_attributes['hasDisplayedPrimaryTerm'] ) {
					$this->_add_c_tag_span( $data['c_tag_span'] );
				} else {
					$data['c_tag_span'] = false;
				}

				$this->_add_c_button( $data['c_button'] );
				break;

			case 'block-video-full':
				$this->_add_c_article_tags( $data['c_article_tags'] );
				$this->_add_c_author( $data['c_author'] );
				$this->_add_c_tag_span( $data['c_tag_span'] );
				$this->_add_c_timestamp( $data['c_timestamp'] );

				$data['c_title']['c_title_url'] = false;

				$data['video_post_id'] = $this->_attributes['postID'];

				$this->_populate_sharing_buttons( $data );
				break;

			default:
				$data = null;
				break;
		}

		return $data;
	}

	/**
	 * Add post data shared across all variations.
	 *
	 * @param array $data Larva Pattern data.
	 */
	protected function _add_common_data( array &$data ): void {
		$data['c_video_card'] =
			Larva\Controllers\Modules\Vlanding_Video_Card::get_instance()->init(
				[
					'data' => [
						'image_id' => $this->_attributes['featuredImageID'],
						'post_id'  => $this->_attributes['postID'],
					],
				]
			)->larva_data();

		$this->_add_c_paragraph( $data['c_paragraph'], 1 );
		$this->_add_c_paragraph( $data['c_paragraph_2'], 2 );

		$this->_add_c_title( $data['c_title'] );
		$this->_set_permalink_attr( $data['video_permalink_attr'] );
		$this->_set_title_attr( $data['video_title_attr'] );

		if ( $this->_attributes['hasDisplayedExcerpt'] ) {
			$this->_add_c_dek( $data['c_dek'] );
		}

		if ( $this->_attributes['backgroundColorClassSuffix'] ) {
			$data['block_video_classes'] .= ' lrv-u-background-color-' .
				$this->_attributes['backgroundColorClassSuffix'];
		}
	}

	/**
	 * Specify classes that must be loaded for this block to be available.
	 *
	 * @return array
	 */
	public function get_dependent_classes(): array {
		return [
			CPT::class,
			PMC_Top_Videos::class,
		];
	}
}
