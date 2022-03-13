<?php

namespace PMC\Rollbar;

use \CheezCapTextOption;
use \CheezCapDropdownOption;
use \PMC_Cheezcap;
use PMC\Global_Functions\Traits\Singleton;

class Plugin {
	use Singleton;

	const ACCESS_TOKEN_JS   = 'pmcs_rollbar_access_token_js';
	const ACCESS_TOKEN_PHP  = 'pmcs_rollbar_access_token_php';
	const ENABLE_OR_DISABLE = 'pmcs_rollbar_enable_or_disable';
	const LOG_LEVEL         = 'pmcs_rollbar_log_level';

	/**
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		add_filter( 'pmc_cheezcap_groups', [ $this, 'filter_pmc_cheezcap_groups' ] );
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * Filter to add cheezcap group
	 *
	 * @param array $cheezcap_groups
	 *
	 * @return array
	 */
	public function filter_pmc_cheezcap_groups( $cheezcap_groups = [] ) {

		$cheezcap_options = [
			new CheezCapDropdownOption(
				'Enable or Disable Rollbar?',
				'',
				self::ENABLE_OR_DISABLE,
				[ 'disable', 'enable' ],
				'disable',
				[ 'Disable', 'Enable' ]
			),
			new CheezCapDropdownOption(
				'Minimum log level used for calls to rollbar',
				'',
				self::LOG_LEVEL,
				[ 'debug', 'info', 'warning', 'error', 'critical' ],
				'debug',
				[ 'Debug', 'Info', 'Warning', 'Error', 'Critical' ]
			),

			new CheezCapTextOption(
				'Access Token (JS)', // label
				'Access Token for javascript', // description
				self::ACCESS_TOKEN_JS,
				'', // default text
				false // use text area
			),

			new CheezCapTextOption(
				'Access Token (PHP)', // label
				'Access Token for PHP', // description
				self::ACCESS_TOKEN_PHP,
				'', // default text
				false // use text area
			),
		];

		$cheezcap_group_class = class_exists( 'BGR_CheezCapGroup' ) ? '\BGR_CheezCapGroup' : '\CheezCapGroup';

		$cheezcap_groups[] = new $cheezcap_group_class( 'Rollbar', 'pmc_rollbar', $cheezcap_options );

		return $cheezcap_groups;
	}

	public function get_access_token_js() {
		return PMC_Cheezcap::get_instance()->get_option( self::ACCESS_TOKEN_JS );
	}

	public function get_access_token_php() {
		return PMC_Cheezcap::get_instance()->get_option( self::ACCESS_TOKEN_PHP );
	}

	public function is_rollbar_enabled() {
		return PMC_Cheezcap::get_instance()->get_option( self::ENABLE_OR_DISABLE ) === 'enable';
	}

	public function get_log_level() {
		return PMC_Cheezcap::get_instance()->get_option( self::LOG_LEVEL );
	}
}

