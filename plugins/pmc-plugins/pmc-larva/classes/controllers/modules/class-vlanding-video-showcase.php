<?php
/**
 * Controller for Video Landing Showcase module (vlanding-video-showcase).
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Modules;

use ErrorException;
use PMC\Global_Functions\Utility\Video as Video_Utilities;
use PMC\Larva\Controllers\Components;

/**
 * Class Vlanding_Video_Showcase.
 */
class Vlanding_Video_Showcase extends Base {
	/**
	 * Pattern to be used with this controller.
	 *
	 * @var string
	 */
	public $pattern_shortpath = 'modules/vlanding-video-showcase';

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
				'post_ids' => [
					0,
					0,
					0,
					0,
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
	public function populate_pattern_data(
		array $pattern,
		array $data
	): array {
		$video_cards   = $this->_get_valid_video_cards( $data );
		$first_post_id = array_key_first( $video_cards );

		$pattern['vlanding_video_showcase_video_cards'] = array_values(
			$video_cards
		);

		$this->_add_player_data(
			$pattern['vlanding_video_card_player'],
			$first_post_id
		);

		$pattern['c_span']       = false;
		$pattern['social_share'] = false;

		(
			new Components\C_Title(
				[
					'post_id' => $first_post_id,
				]
			)
		)->add_data( $pattern['c_title'] );

		(
			new Components\C_Dek(
				[
					'post_id' => $first_post_id,
				]
			)
		)->add_data( $pattern['c_dek'] );

		$pattern['c_heading']['c_heading_text']    = '';
		$pattern['c_heading']['c_heading_classes'] = 'lrv-a-hidden';

		return $pattern;
	}

	/**
	 * Validate post IDs and get the Larva data needed to render each post's
	 * video.
	 *
	 * @param array $data Controller data.
	 * @return array
	 */
	protected function _get_valid_video_cards( array $data ): array {
		$video_cards = [];

		foreach ( $data['post_ids'] as $id ) {
			$card = Vlanding_Video_Card::get_instance()->init(
				[
					'data' => [
						'post_id' => $id,
					],
				]
			)->larva_data();

			if (
				! empty(
					$card['vlanding_video_card_link_showcase_trigger_data_attr']
				)
			) {
				$video_cards[ $id ] = $card;
			}

			unset( $card );
		}

		return $video_cards;
	}

	/**
	 * Populate player data from first video post.
	 *
	 * @param array $pattern Pattern data to fill.
	 * @param int   $post_id ID of video post.
	 */
	protected function _add_player_data(
		array &$pattern,
		int $post_id
	): void {
		$permalink  = get_permalink( $post_id );
		$video_data = Video_Utilities::get_player_data_from_post( $post_id );

		$pattern['vlanding_video_card_link_showcase_trigger_data_attr'] =
			$video_data['id'];
		$pattern['vlanding_video_card_link_showcase_type_data_attr']    =
			$video_data['source'];

		$pattern['vlanding_video_card_link_showcase_title_data_attr']    =
			get_the_title( $post_id );
		$pattern['vlanding_video_card_link_showcase_dek_data_attr']      =
			get_the_excerpt( $post_id );
		$pattern['vlanding_video_card_permalink_url']                    =
			$permalink;
		$pattern['vlanding_video_card_link_showcase_permalink_data_url'] =
			$permalink;

		(
			new Components\C_Lazy_Image(
				[
					'image_id' => get_post_thumbnail_id( $post_id ),
					'post_id'  => $post_id,
				]
			)
		)->add_data( $pattern['c_lazy_image'] );
		$pattern['c_lazy_image']['c_lazy_image_crop_class'] = '';
	}
}
