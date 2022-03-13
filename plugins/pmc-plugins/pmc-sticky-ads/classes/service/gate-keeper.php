<?php
/**
 * Gate Keeper service class for PMC Sticky Ads plugin.
 * It determines when/if to allow initialization of a service.
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2016-11-08
 */

namespace PMC\Sticky_Ads\Service;


use \PMC;
use \CheezCapGroup;
use \CheezCapDropdownOption;
use \PMC\Global_Functions\Traits\Singleton;

class Gate_Keeper {

	use Singleton;

	/**
	 * @var String ID for CheezCap Group
	 */
	const ID = 'pmc-sticky-ads-czg';

	/**
	 * @var String Label for CheezCap Group
	 */
	const LABEL = 'Sticky Ads';

	/**
	 * Plugin initialization method
	 *
	 * @return void
	 */
	protected function __construct() {

		$this->_setup_hooks();
		$this->maybe_setup_services();

	}

	/**
	 * Method to setup listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'pmc_cheezcap_groups', array( $this, 'add_cheezcap_group' ) );

	}

	/**
	 * Method called by 'pmc_cheezcap_groups' to allow addition of CheezCap option groups
	 *
	 * @param array $groups An array of CheezCapGroup objects
	 * @return array An array of CheezCapGroup objects
	 */
	public function add_cheezcap_group( $groups = array() ) {

		if ( ! is_array( $groups ) ) {
			$groups = array();
		}

		$groups[] = new CheezCapGroup( self::LABEL, self::ID, array(

			new CheezCapDropdownOption(
				'Enable on Desktop?',
				'Should the sticky ad be enabled on Desktop devices?',
				sprintf( '%s-enabled-cco', Desktop::ID ),
				array( 0, 1 ),
				0, // Default index is 0, 0 == 'No'
				array( 'No', 'Yes' )
			),
			new CheezCapDropdownOption(
				'Enable on Mobile?',
				'Should the sticky ad be enabled on Mobile devices?',
				sprintf( '%s-enabled-cco', Mobile::ID ),
				array( 0, 1 ),
				0, // Default index is 0, 0 == 'No'
				array( 'No', 'Yes' )
			),

		) );

		return $groups;

	}

	/**
	 * Conditional method to check whether Sticky Ads on Mobile are enabled or not
	 *
	 * @return boolean Returns TRUE if current request should get sticky ads on mobile else FALSE
	 */
	public function allows_on_mobile() {

		if ( PMC::is_mobile() && intval( get_option( sprintf( 'cap_%s-enabled-cco', Mobile::ID ), 0 ) ) === 1 ) {
			return true;
		}

		return false;

	}

	/**
	 * Conditional method to check whether Sticky Ads on Desktop are enabled or not
	 *
	 * @return boolean Returns TRUE if current request should get sticky ads on desktop else FALSE
	 */
	public function allows_on_desktop() {

		if ( ! PMC::is_mobile() && intval( get_option( sprintf( 'cap_%s-enabled-cco', Desktop::ID ), 0 ) ) === 1 ) {
			return true;
		}

		return false;

	}

	/**
	 * Method which sets up Sticky Ads services
	 *
	 * @return void
	 */
	public function maybe_setup_services() {

		// Setup Mobile Service if it has a pass
		if ( $this->allows_on_mobile() || is_admin() ) {
			Mobile::get_instance();
		}

		// Setup Desktop Service if it has a pass
		if ( $this->allows_on_desktop() || is_admin() ) {
			Desktop::get_instance();
		}

	}

}	//end of class


//EOF
