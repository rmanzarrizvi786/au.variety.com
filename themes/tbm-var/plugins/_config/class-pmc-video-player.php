<?php
/**
 * pmc-video-player plugin config file.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-09-21 READS-1224
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Video_Player {

	use Singleton;

	/**
	 * PMC_Video_Player constructor.
	 *
	 *
	 */
	protected function __construct() {

		add_filter( 'pmc_video_player_indexechange_params', [ $this, 'add_indexexchange_bidder_params' ] );
		add_filter( 'pmc_video_player_remove_jw_scripts', '__return_false' );
	}

	/**
	 * Add video player indexechange bidder configurations.
	 *
	 * @param array $params list of bidder params.
	 *
	 * @return array
	 */
	public function add_indexexchange_bidder_params( array $params = [] ) : array {

		if ( ! empty( $params ) && is_array( $params ) ) {
			$params['site_id'] = 290295;
		}

		return $params;
	}

}
