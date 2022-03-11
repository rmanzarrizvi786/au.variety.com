<?php
/**
 * Config for PMC floating Player Plugin
 *
 * @author Vinod Tella <vtella@pmc.com>
 *
 * @since 2019-08-10
 *
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Floating_Player {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		add_filter( 'pmc_floating_video_mobile', '__return_true' ); //enable floating player for mobile

	}

} //end of class

//EOF
