<?php
/**
 * Block controller for Fullscreen Cover block.
 *
 * @package pmc-gutenberg
 */
namespace PMC\Gutenberg\Blocks;

use PMC\Digital_Daily\Full_View;
use PMC\Gutenberg\Block_Base;
use PMC\Gutenberg\Interfaces\Block_Base\With_Larva_Data;
use PMC\Larva;

/**
 * Class Fullscreen_Cover.
 *
 * @codeCoverageIgnore Class will be refactored into Larva controllers after
 *                     Patterns are moved to `pmc-larva`.
 */
class Fullscreen_Cover extends Block_Base implements With_Larva_Data {
	/**
	 * Larva template for this block.
	 *
	 * @var string
	 */
	public $template = 'modules/block-landing-page';

	/**
	 * Fullscreen_Cover constructor.
	 */
	public function __construct() {
		$this->_block = 'fullscreen-cover';

		// Registered here to allow themes to filter as needed.
		$this->_block_args['supports'] = [
			'multiple' => false,
			'pmc'      => [
				'coverAdOverlay' => false,
			],
		];
	}

	/**
	 * Populate Larva data.
	 *
	 * @param string $block_content Block's inner content, generally empty.
	 * @param array  $block Block details, including attributes.
	 * @return array|null
	 */
	public function larva_data( string $block_content, array $block ): ?array {
		$variant = Full_View::is() ? 'full-view' : 'prototype';
		$data    = Larva\Pattern::get_json_data(
			$this->template . '.' . $variant
		);

		$url  = get_permalink( get_the_ID() );
		$url .= '#jump-to-content';

		if ( empty( $block['attrs']['imageId'] ) ) {
			$data['o_figure'] = false;
		} else {
			Larva\add_controller_data(
				Larva\Controllers\Objects\O_Figure::class,
				[
					'image_id'   => $block['attrs']['imageId'],
					'image_size' => 'digital-daily-3-4-no-crop',
					'post_id'    => get_the_ID(),
				],
				$data['o_figure']
			);

			$data['o_figure']['c_lazy_image']['c_lazy_image_link_url'] = $url;
			$data['o_figure']['o_figure_link_url']                     = $url;
		}

		if ( empty( $block['attrs']['items'] ) ) {
			$data['list']                 = [];
			$data['content_col_classes'] .= ' lrv-a-hidden';
		} else {
			$item_template = array_shift( $data['list'] );
			$data['list']  = [];

			foreach ( $block['attrs']['items'] as $post_data ) {
				if ( empty( $post_data['postId'] ) ) {
					continue;
				}

				$item = $item_template;

				Larva\add_controller_data(
					Larva\Controllers\Components\C_Link::class,
					[
						'text' => $post_data['title'] ?? get_the_title(
							$post_data['postId']
						),
						'url'  => get_permalink( $post_data['postId'] ),
					],
					$item['c_link']
				);

				$data['list'][] = $item;
			}
		}

		return $data;
	}
}
