<?php
/**
 * Class Injection.
 *
 * @package pmc-variety
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Injection
 */
class Injection {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		// Inject to VIP posts.
		\PMC_Inject_Content::get_instance()->register_post_type( Content::VIP_POST_TYPE );

	}

}
