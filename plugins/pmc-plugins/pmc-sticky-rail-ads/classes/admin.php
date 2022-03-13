<?php

/**
 * Sticky rail ads
 *
 * @author Vinod Tella <vtella@pmc.com>
 */

namespace PMC\Sticky_Rail_Ads;

use PMC\Global_Functions\Traits\Singleton;

class Admin {

	use Singleton;

	/**
	 * Initialising Sticky rail ads admin options
	 */
	protected function __construct() {
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
		$cheezcap_groups[] = new \CheezCapGroup( wp_strip_all_tags( __( 'Sticky Rail Ads', 'pmc-sticky-rail-ads' ), true ), 'pmc-sticky-rail-ads', array(
			// Enable/disable
			new \CheezCapDropdownOption(
				wp_strip_all_tags( __( 'Enable Sticky Rail Ads?', 'pmc-sticky-rail-ads' ), true ),
				wp_strip_all_tags( __( 'This option will enable Sticky Rail Ads', 'pmc-sticky-rail-ads' ), true ),
				'pmc_sticky_rail_ads',
				array( 'no', 'yes' ),
				0,
				array( wp_strip_all_tags( __( 'No', 'pmc-sticky-rail-ads' ), true ), wp_strip_all_tags( __( 'Yes', 'pmc-sticky-rail-ads' ), true ) )
			),
			new \CheezCapTextOption(
				wp_strip_all_tags( __( 'Sidebar div selector', 'pmc-sticky-rail-ads' ), true ),
				wp_strip_all_tags( __( 'Id or the class name of the sidebar container', 'pmc-sticky-rail-ads' ), true ),
				'pmc_sticky_rail_ads_parent',
				''//default
			),
			new \CheezCapTextOption(
				wp_strip_all_tags( __( 'First Ad div selector', 'pmc-sticky-rail-ads' ), true ),
				wp_strip_all_tags( __( 'Id or the class name of the first Ad container', 'pmc-sticky-rail-ads' ), true ),
				'pmc_sticky_rail_ads_first',
				''//default
			),
			new \CheezCapTextOption(
				wp_strip_all_tags( __( 'Second Ad div selector', 'pmc-sticky-rail-ads' ), true ),
				wp_strip_all_tags( __( 'Id or the class name of the second ad container', 'pmc-sticky-rail-ads' ), true ),
				'pmc_sticky_rail_ads_second',
				''//default
			),
			new \CheezCapTextOption(
				wp_strip_all_tags( __( 'Sticky Nav div selector', 'pmc-sticky-rail-ads' ), true ),
				wp_strip_all_tags( __( 'Id or the class name of the sticky nav container', 'pmc-sticky-rail-ads' ), true ),
				'pmc_sticky_rail_ads_nav_bar',
				''//default
			),
			new \CheezCapTextOption(
				wp_strip_all_tags( __( 'WP Admin Nav div selector', 'pmc-sticky-rail-ads' ), true ),
				wp_strip_all_tags( __( 'Id or the class name of the admin nav container', 'pmc-sticky-rail-ads' ), true ),
				'pmc_sticky_rail_ads_admin_bar',
				''//default
			),
			new \CheezCapTextOption(
				wp_strip_all_tags( __( 'First Ad scroll limit', 'pmc-sticky-rail-ads' ), true ),
				wp_strip_all_tags( __( 'Scroll limit ', 'pmc-sticky-rail-ads' ), true ),
				'pmc_sticky_rail_ads_first_ad_scroll',
				'0'//default
			),
			new \CheezCapTextOption(
				wp_strip_all_tags( __( 'Ad container width', 'pmc-sticky-rail-ads' ), true ),
				wp_strip_all_tags( __( 'Enter Ad container width', 'pmc-sticky-rail-ads' ), true ),
				'pmc_sticky_rail_ads_ad_container_width',
				'320'//default
			),
		));

		return $cheezcap_groups;
	}

}