<?php
/**
 * Class Interviews
 *
 * @since 2017-08-21 Milind More CDWE-582
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Variety_500;

use \PMC\Global_Functions\Traits\Singleton;
use \Variety\Inc\Carousels;

class Interviews {

	use Singleton;

	/**
	 * Get Interview Videos Carousel Term.
	 *
	 * @var string
	 */
	protected $_carousel_term;

	/**
	 * Count - How many maximum slides to display.
	 *
	 * @var int
	 */
	const COUNT = 10;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		$this->_carousel_term = get_option( 'variety_500_interviews_term' );
	}

	/**
	 * Get carousel posts for interview videos.
	 *
	 * @return array
	 */
	public function get_carousel_videos() {

		if ( ! empty( $this->_carousel_term ) ) {

			$carousel_posts = Carousels::get_carousel_posts( $this->_carousel_term, self::COUNT );

		} else {

			$carousel_posts = false;

		}

		return $carousel_posts;
	}

}
