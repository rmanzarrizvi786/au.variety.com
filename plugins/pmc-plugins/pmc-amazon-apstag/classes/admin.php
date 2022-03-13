<?php

namespace PMC\Amazon_Apstag;

/**
 * Render admin UI elements to enable / disable Amazon Apstag header bidding
 */

use \PMC\Global_Functions\Traits\Singleton;

class Admin {

	use Singleton;

	/**
	 * Class instantiation.
	 *
	 * Hook into WordPress.
	 */
	protected function __construct() {

		// Only proceed if we're in the admin
		if ( ! is_admin() ) {
		//	return;
		}

		add_filter( 'pmc_cheezcap_groups', array( $this, 'filter_pmc_cheezcap_groups' ) );
	}

	/**
	 * Added a cheezcap to enable the plugin
	 *
	 * @param array $cheezcap_groups List of cheezcap options.
	 *
	 * @return array $cheezcap_groups
	 */
	public function filter_pmc_cheezcap_groups( array $cheezcap_groups = array() ) {

		// Add an Amazon Apstag cheezcap group
		$cheezcap_groups[] = new \CheezCapGroup( __( 'Amazon Apstag', 'pmc-amazon-apstag' ), 'pmc-amazon-apstag', array(

			// Enable / Disable Apstag header bidding from admin
			new \CheezCapDropdownOption(
				__( 'Amazon Apstag Header Bidding', 'pmc-amazon-apstag' ),
				__( 'This option will enable Amazon Apstag header bidding', 'pmc-amazon-apstag' ),
				'pmc_amazon_apstag_enable',
				array( 'disabled', 'enabled' ),
				0,
				array( __( 'Disabled', 'pmc-amazon-apstag' ), __( 'Enabled', 'pmc-amazon-apstag' ) )
			),
			// Enable / Disable Apstag header bidding on Gallery pages
			new \CheezCapDropdownOption(
				__( 'Enable on gallery', 'pmc-amazon-apstag' ),
				__( 'This option will enable Amazon Apstag header bidding on Gallery', 'pmc-amazon-apstag' ),
				'pmc_amazon_apstag_for_gallery',
				array( 'disabled', 'enabled' ),
				0,
				array( __( 'Disabled', 'pmc-amazon-apstag' ), __( 'Enabled', 'pmc-amazon-apstag' ) )
			),
			// Enable / Disable Apstag header bidding on Video
			new \CheezCapDropdownOption(
				__( 'Enable for Video', 'pmc-amazon-apstag' ),
				__( 'This option will enable Amazon Apstag header bidding for video player', 'pmc-amazon-apstag' ),
				'pmc_amazon_apstag_for_video',
				array( 'disabled', 'enabled' ),
				0,
				array( __( 'Disabled', 'pmc-amazon-apstag' ), __( 'Enabled', 'pmc-amazon-apstag' ) )
			),
		) );

		return $cheezcap_groups;
	}
}
