<?php
/**
 * Controller for Story Grid module.
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Modules;

use PMC\Global_Functions\Utility\Post as Post_Utilities;
use PMC\Larva\Controllers\Components;

/**
 * Class Story_Grid.
 */
class Story_Grid extends Base {
	/**
	 * Larva Module prefix.
	 *
	 * @var string
	 */
	public $pattern_shortpath = 'modules/story-grid';

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
	 * @return array Object to ultimately be passed to render_template.
	 */
	public function populate_pattern_data(
		array $pattern,
		array $data
	): array {
		$template                          = array_shift(
			$pattern['story_grid_story_cards']
		);
		$pattern['story_grid_story_cards'] = [];

		foreach ( $data['posts'] as $datum ) {
			$post_data = $template;

			(
				new Components\C_Title(
					[
						'post_id' => $datum['ID'],
						'title'   => $datum['title'] ?? null,
					]
				)
			)->add_data( $post_data['c_title'] );

			$term = Post_Utilities::get_primary_term(
				$datum['ID'],
				apply_filters(
					'pmc_larva_module_controller_primary_taxonomy_slug',
					'category',
					$this->pattern_shortpath,
					$datum
				)
			);
			if ( null === $term ) {
				$post_data['c_span'] = false;
			} else {
				(
					new Components\C_Span(
						[
							'text' => $term->name,
							'url'  => get_term_link( $term ),
						]
					)
				)->add_data( $post_data['c_span'] );
			}

			(
				new Components\C_Dek(
					[
						'post_id' => $datum['ID'],
					]
				)
			)->add_data( $post_data['c_dek'] );

			(
				new Components\C_Tagline_Author(
					[
						'post_id' => $datum['ID'],
					]
				)
			)->add_data( $post_data['c_tagline_author'] );

			(
				new Components\C_Timestamp(
					[
						'post_id' => $datum['ID'],
					]
				)
			)->add_data( $post_data['c_timestamp'] );

			(
				new Components\C_Lazy_Image(
					[
						'image_id' => get_post_thumbnail_id( $datum['ID'] ),
						'post_id'  => $datum['ID'],
					]
				)
			)->add_data( $post_data['c_lazy_image'] );

			if ( $post_data['c_link_bottom'] ) {
				(
					new Components\C_Link(
						[
							'text' => __( 'Read the Story', 'pmc-larva' ),
							'url'  => get_permalink( $datum['ID'] ),
						]
					)
				)->add_data( $post_data['c_link_bottom'] );
			}

			$pattern['story_grid_story_cards'][] = $post_data;
		}

		return $pattern;
	}
}
