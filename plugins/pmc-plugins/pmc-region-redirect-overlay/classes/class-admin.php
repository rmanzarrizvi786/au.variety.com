<?php
/**
 * Class to set things up for the plugin in wp-admin.
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-11-25
 */

namespace PMC\Region_Redirect_Overlay;

use \PMC\Global_Functions\Traits\Singleton;
use \CheezCapGroup;
use \CheezCapMultipleCheckboxesOption;

class Admin {

	use Singleton;

	const OPTION_GROUP     = 'pmc-reg-rd-overlay-group';
	const OPTION_COUNTRIES = 'pmc-reg-rd-overlay-countries';

	/**
	 * @var \PMC\Region_Redirect_Overlay\Config
	 */
	protected $_config;

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore Ignoring coverage here because this is class constructor. Method calls here have their own individual tests.
	 */
	protected function __construct() {

		$this->_config = Config::get_instance();

		$this->_setup_hooks();

	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		/*
		 * Filters
		 */
		add_filter( 'pmc_cheezcap_groups', [ $this, 'add_settings_ui' ] );

	}

	/**
	 * Method to add settings UI in admin
	 *
	 * @param array $groups
	 *
	 * @return array
	 */
	public function add_settings_ui( array $groups = [] ) : array {

		$countries = $this->_config->get_countries();

		if ( empty( $countries ) ) {
			return $groups;
		}

		$options = [
			new CheezCapMultipleCheckboxesOption(
				__( 'Enable International Redirect Overlay', 'pmc-region-redirect-overlay' ),
				__( 'Visitors from the selected countries will see a banner overlay redirecting them to their respective international site.', 'pmc-region-redirect-overlay' ),
				self::OPTION_COUNTRIES,
				array_keys( (array) $countries ),
				array_values( (array) $countries ),
				'', // No default-selection
				[ '\PMC_Cheezcap', 'sanitize_cheezcap_checkboxes' ]
			),
		];

		$groups[] = new CheezCapGroup(
			__( 'PMC Region Redirect Overlay Options', 'pmc-region-redirect-overlay' ),
			self::OPTION_GROUP,
			$options
		);

		return $groups;

	}

}  // end class

//EOF
