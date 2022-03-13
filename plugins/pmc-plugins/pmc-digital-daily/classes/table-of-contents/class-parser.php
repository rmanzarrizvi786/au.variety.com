<?php
/**
 * Parse post IDs from blocks in post content.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily\Table_of_Contents;

use PMC\Digital_Daily\Block_Flattener;

/**
 * Class Parser.
 */
class Parser extends Block_Flattener {
	/**
	 * Post data parsed from block content.
	 *
	 * @var array
	 */
	protected array $_data = [];

	/**
	 * Retrieve Digital Daily's block data.
	 *
	 * @return array|null
	 */
	public function get(): ?array {
		return ! empty( $this->_data ) ? $this->_data : null;
	}

	/**
	 * Perform parsing.
	 *
	 * @codeCoverageIgnore Parent class and extract method are both covered.
	 */
	protected function _do(): void {
		parent::_do();

		$this->_extract_data();
	}

	/**
	 * Extract post data from parsed blocks.
	 */
	protected function _extract_data(): void {
		foreach ( $this->_blocks as $block ) {
			switch ( $block['blockName'] ?? null ) {
				case 'pmc/story':
				case 'pmc/story-digital-daily':
				case 'pmc/story-gallery':
				case 'pmc/story-runway-review':
				case 'pmc/story-top-video':
					$data = [
						'ID' => $block['attrs']['postID'],
					];

					if ( ! empty( $block['attrs']['title'] ) ) {
						$data['title'] = $block['attrs']['title'];
					}

					if ( ! empty( $block['attrs']['excerpt'] ) ) {
						$data['excerpt'] = wp_strip_all_tags(
							$block['attrs']['excerpt']
						);
					}

					if ( ! empty( $block['attrs']['contentOverride'] ) ) {
						$data['content'] = $block['attrs']['contentOverride'];
					}

					if ( ! empty( $block['attrs']['featuredImageID'] ) ) {
						$data['featured_image'] = $block['attrs']['featuredImageID'];
					}

					$this->_data[] = $data;
					break;

				default:
					continue 2;
			}
		}
	}
}
