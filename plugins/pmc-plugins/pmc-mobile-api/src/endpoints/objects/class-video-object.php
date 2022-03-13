<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Objects\Video_Object class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Objects;

use PMC\Mobile_API\Route_Registrar;

/**
 * Video Object.
 */
class Video_Object extends Article_Object {

	/**
	 * Get video data.
	 *
	 * @return array
	 */
	public function get_video(): array {

		$article_postcard = $this->get_post_card();

		$video_meta = [
			'title'    => $this->post->post_title ?? '',
			'duration' => (string) \get_post_meta( $this->post->ID, 'pmc_top_video_duration', true ),
			'link'     => \rest_url( '/' . Route_Registrar::NAMESPACE . sprintf( '/article/%d', $this->post->ID ) ),
		];

		return array_merge( (array) $article_postcard, (array) $video_meta );
	}
}
