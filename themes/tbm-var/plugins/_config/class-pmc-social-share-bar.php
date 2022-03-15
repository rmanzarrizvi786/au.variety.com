<?php

/**
 * PMC Social Share Bar configuration
 *
 * @package pmc-variety-2017
 *
 * @since 2017.1.0
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class PMC_Social_Share_Bar
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class PMC_Social_Share_Bar
{

	use Singleton;

	const FB_APP_ID = '';

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{

		$this->_setup_hooks();
	}

	/**
	 * Initialize actions and filters.
	 */
	protected function _setup_hooks()
	{

		add_filter('pmc_social_share_bar_primary_list_count', array($this, 'primary_count'));
		add_filter('pmc_social_share_bar_facebook_app_id', array($this, 'get_facebook_app_id'));
	}

	/**
	 * Sets primary social share bar count.
	 *
	 * @return int
	 */
	public function primary_count()
	{

		return 5;
	}

	/**
	 * Returns FB App ID
	 *
	 * @return string
	 */
	public function get_facebook_app_id()
	{
		return self::FB_APP_ID;
	}
}

//EOF
