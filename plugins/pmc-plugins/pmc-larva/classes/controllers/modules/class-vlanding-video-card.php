<?php
/**
 * Controller for Video Landing Video Card module (vlanding-video-card).
 *
 * @package pmc-larva
 */

namespace PMC\Larva\Controllers\Modules;

use PMC\Global_Functions\Utility\Video as Video_Utilities;
use PMC\Larva\Controllers\Components;

/**
 * Class Vlanding_Video_Card.
 */
class Vlanding_Video_Card extends Base {
	public $pattern_shortpath = 'modules/vlanding-video-card';

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
				'post_id' => 0,
			],
			'variant' => 'prototype',
		];
	}

	/**
	 * Manually map provided data to the pattern JSON object.
	 *
	 * @param array $pattern The Larva pattern JSON object to plugin data into.
	 * @param array $data    Actual data to override placeholder data.
	 *
	 * @return array Object to ultimately be passed to render_template.
	 */
	public function populate_pattern_data( array $pattern, array $data ): array {
		$video_data = Video_Utilities::get_player_data_from_post(
			$data['post_id']
		);

		if ( null === $video_data ) {
			$video_data = [
				'id'     => '',
				'source' => '',
			];
		}

		$pattern['vlanding_video_card_link_showcase_trigger_data_attr'] =
			$video_data['id'];
		$pattern['vlanding_video_card_link_showcase_type_data_attr']    =
			$video_data['source'];

		$pattern['vlanding_video_card_link_showcase_title_data_attr'] =
			get_the_title( $data['post_id'] );

		$pattern['vlanding_video_card_link_showcase_dek_data_attr'] =
			get_the_excerpt( $data['post_id'] );

		$permalink                                    =
			get_permalink( $data['post_id'] );
		$pattern['vlanding_video_card_permalink_url'] = $permalink;
		$pattern['vlanding_video_card_link_showcase_permalink_data_url'] =
			$permalink;

		$pattern['c_span'] = false;

		(
			new Components\C_Title(
				[
					'post_id' => $data['post_id'],
				]
			)
		)->add_data( $pattern['c_title'] );

		(
			new Components\C_Lazy_Image(
				[
					'image_id' => $data['image_id']
						?? get_post_thumbnail_id( $data['post_id'] ),
					'post_id'  => $data['post_id'],
				]
			)
		)->add_data( $pattern['c_lazy_image'] );

		return $pattern;
	}
}
