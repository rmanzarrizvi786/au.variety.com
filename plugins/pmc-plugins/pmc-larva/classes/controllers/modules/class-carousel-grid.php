<?php
/**
 * Controller for Carousel Grid module.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Modules;

use PMC\Global_Functions\Utility\Post as Post_Utilities;
use PMC\Larva\Controllers\Components;
use PMC\Larva\Controllers\Objects;

/**
 * Class Carousel_Grid.
 */
class Carousel_Grid extends Base {
	/**
	 * Pattern to be used with this controller.
	 *
	 * @var string
	 */
	public $pattern_shortpath = 'modules/carousel-grid';

	/**
	 * Size of image passed to `C_Lazy_Image` controller.
	 *
	 * @var string
	 */
	protected $_c_lazy_image_size = 'small';

	/**
	 * The default options structure for the module. This structure
	 * serves as a kind of "contract" for any data that is sent to
	 * the Larva module specified for the class. This "contract" is
	 * enforced before passing rendering the template with data.
	 *
	 * @return array Object to ultimately be passed to the pattern.
	 */
	public function get_default_options(): array {
		return [
			'data'    => [
				'posts' => [
					[
						'ID' => 0,
					],
				],
			],
			'variant' => 'prototype',
		];
	}

	/**
	 * Manually map provided data to the pattern JSON object.
	 *
	 * @param array $pattern The Larva pattern JSON object to plugin data into.
	 * @param array $data Actual data to override placeholder data.
	 *
	 * @return array Object to ultimately be passed to render_template.
	 */
	public function populate_pattern_data( array $pattern, array $data ): array {
		$this->_populate_large_card(
			$pattern['o_card_large'],
			array_shift( $data['posts'] )
		);

		$template                = array_shift( $pattern['o_card_items'] );
		$pattern['o_card_items'] = [];

		foreach ( $data['posts'] as $post_data ) {
			$card_pattern = $template;

			$this->_populate_card(
				$card_pattern,
				$post_data
			);


			$pattern['o_card_items'][] = $card_pattern;
		}

		return $pattern;
	}

	/**
	 * Populate first story's o-card.
	 *
	 * @param array $pattern o-card-large pattern data.
	 * @param array $data    Carousel post data.
	 */
	protected function _populate_large_card(
		array &$pattern,
		array $data
	): void {
		$initial_image_size       = $this->_c_lazy_image_size;
		$this->_c_lazy_image_size = 'large';

		$this->_populate_card(
			$pattern,
			$data
		);

		$this->_c_lazy_image_size = $initial_image_size;

		$term = Post_Utilities::get_primary_term(
			$data['ID'],
			// TODO: put this in the base class?
			apply_filters(
				'pmc_larva_module_controller_primary_taxonomy_slug',
				'category',
				$this->pattern_shortpath,
				$data
			)
		);
		if ( null === $term ) {
			$pattern['c_span'] = false;
		} else {
			(
				new Components\C_Span(
					[
						'text' => $term->name,
						'url'  => get_term_link( $term ),
					]
				)
			)->add_data( $pattern['c_span'] );
		}

		(
			new Objects\O_Author(
				[
					'post_id' => $data['ID'],
				]
			)
		)->add_data( $pattern['o_author'] );
	}

	/**
	 * Populate o-card with data shared by all module variants.
	 *
	 * @param array $pattern o-card pattern data.
	 * @param array $data    Carousel post data.
	 */
	protected function _populate_card( array &$pattern, array $data ): void {
		(
			new Components\C_Title(
				[
					'post_id' => $data['ID'],
					'title'   => $data['title'] ?? null,
					'url'     => $data['url'] ?? null,
				]
			)
		)->add_data( $pattern['c_title'] );

		(
			new Components\C_Lazy_Image(
				[
					'image_id'   => $data['image_id'] ?? get_post_thumbnail_id(
						$data['ID']
					),
					'post_id'    => $data['ID'],
					'image_size' => $this->_c_lazy_image_size,
					'url'        => $data['url'] ?? null,
				]
			)
		)->add_data( $pattern['c_lazy_image'] );

		(
			new Components\C_Timestamp(
				[
					'post_id' => $data['ID'],
				]
			)
		)->add_data( $pattern['c_timestamp'] );
	}
}
