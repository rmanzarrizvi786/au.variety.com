<?php
/**
 * This file contains the PMC\WWD\Mobile_API\Endpoints\VY_Video_Latest class
 *
 * @package VY_Mobile_API
 */

namespace PMC\VY\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Latest_Video;

/**
 * Custom Latest Video endpoint class.
 */
class VY_Video_Latest extends Latest_Video {

	/**
	 * Adding our video post type.
	 *
	 * @var string|array
	 */
	protected $post_type = 'variety_top_video';
}
