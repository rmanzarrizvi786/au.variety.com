<?php
namespace PMC\Gallery;

/*
 * Add Options to Media Settings Page
 *
 * @package PMC Gallery Plugin
 * @since 1/1/2013 Vicky Biswas
 *
 * Holds code needed to add options to the Media Settings Page
 */

use CheezCapDropdownOption;
use CheezCapTextOption;
use PMC\Global_Functions\Traits\Singleton;
use PMC_Cheezcap;

/**
 * Manages the Settings for PMC Gallery
 */
class Settings {

	use Singleton;

	/**
	 * Initializes the class.
	 */
	protected function __construct() {
		add_action( 'pmc_cheezcap_groups', [ $this, 'action_pmc_cheezcap_groups' ] );
		add_action( 'pmc_cheezcap_groups', [ $this, 'add_runway_cheezcap_groups' ] );
		add_filter( 'pmc_adm_locations', [ $this, 'add_gallery_ad_locations' ] );
	}

	public function action_pmc_cheezcap_groups( $cheezcap_groups = array() ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		// Needed for compatibility with BGR_CheezCap
		// @codeCoverageIgnoreStart
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';
		}
		// @codeCoverageIgnoreEnd

		$cheezcap_options = array(
			new CheezCapDropdownOption(
				esc_html__( 'Add photo to beginning of gallery', 'pmc-gallery' ),
				esc_html__( 'When enabled, new photos will be added to beginning of gallery in gallery admin tools.', 'pmc-gallery' ),
				'pmc_gallery_prepend',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new CheezCapDropdownOption(
				esc_html__( 'Enable gallery interstitial', 'pmc-gallery' ),
				esc_html__( 'When enabled, gallery interstitial ads will render see PMC Ad manager for ad placement', 'pmc-gallery' ),
				'pmc_gallery_interstitial',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new CheezCapDropdownOption(
				esc_html__( 'Vertical Gallery Ad Frequency', 'pmc-gallery' ),
				esc_html__( 'This number will determine after how many items in the gallery ads will show. 1 is default', 'pmc-gallery' ),
				'pmc_vertical_ad_frequency',
				array( 1, 2, 3, 4 ),
				0, // First option => Disabled
				array( 1, 2, 3, 4 )
			),
			new CheezCapTextOption(
				esc_html__( 'Vertical Gallery Ad Limit', 'pmc-gallery' ),
				esc_html__( 'This number will determine maximum number of ads to show in the gallery . Default is No limit;', 'pmc-gallery' ),
				'pmc_vertical_ad_limit_count',
				0
			),
			new CheezCapTextOption(
				esc_html__( 'Number of clicks before ad refresh', 'pmc-gallery' ),
				esc_html__( 'Enter number > 0 to enable ad refresh', 'pmc-gallery' ),
				'pmc_gallery_ad_refresh_clicks',
				2
			),
			new CheezCapTextOption(
				esc_html__( 'Number of clicks before desktop sticky rail bottom ad refresh', 'pmc-gallery' ),
				esc_html__( 'Enter number > 0 to enable desktop sticky rail bottom ad refresh', 'pmc-gallery' ),
				'pmc_gallery_rail_bottom_ad_refresh_clicks',
				2
			),
			new CheezCapTextOption(
				esc_html__( 'Number of clicks before mobile adhesion ad refresh', 'pmc-gallery' ),
				esc_html__( 'Enter number > 0 to enable mobile adhesion ad refresh', 'pmc-gallery' ),
				'pmc_gallery_adhesion_ad_refresh_clicks',
				2
			),
			new CheezCapTextOption(
				esc_html__( 'Number of clicks before interstitial ad is refresh', 'pmc-gallery' ),
				esc_html__( 'Enter number > 0 to enable interstitial ad refresh', 'pmc-gallery' ),
				'pmc_gallery_interstitial_ad_refresh_clicks',
				25
			),
			new CheezCapTextOption(
				esc_html__( "Don't show interstitials on these galleries", 'pmc-gallery' ),
				esc_html__( 'Comma delimited post IDs, e.g.: 123,456,789', 'pmc-gallery' ),
				'pmc_gallery_interstitial_no_ads',
				null
			),
			new CheezCapDropdownOption(
				'Enable Pinterest Description for images',
				'When enabled, creators can define a Pinterest Description for each image in the gallery.',
				'pmc_gallery_enable_pinterest_description',
				array( 'no', 'yes' ),
				0, // First option => Disabled
				array( 'No', 'Yes' )
			),
			new CheezCapDropdownOption(
				esc_html__( 'Show zoom icon', 'pmc-gallery' ),
				esc_html__( 'When enabled, shows zoom icon on horizontal gallery.', 'pmc-gallery' ),
				'pmc_gallery_enable_zoom',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new CheezCapDropdownOption(
				esc_html__( 'Show Pinit icon', 'pmc-gallery' ),
				esc_html__( 'When enabled, shows pinit icon on horizontal gallery on top left of each slide.', 'pmc-gallery' ),
				'pmc_gallery_enable_pinit',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new CheezCapDropdownOption(
				esc_html__( 'Force all gallery endings to be the same', 'pmc-gallery' ),
				esc_html__( 'When enabled, shows next gallery link and related galleries thumbnail at the end of the slide.', 'pmc-gallery' ),
				'pmc_gallery_force_same_ending',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
		);

		$cheezcap_options  = apply_filters( 'pmc_gallery_cheezcap_options', $cheezcap_options );
		$cheezcap_groups[] = new $cheezcap_group_class( 'Gallery Options', 'pmc_gallery_cheezcap', $cheezcap_options );

		return $cheezcap_groups;
	}

	/**
	 * Add runway cheezcap group.
	 *
	 * @param array $cheezcap_groups Cheezcap group.
	 *
	 * @return array
	 */
	public function add_runway_cheezcap_groups( $cheezcap_groups = array() ) {

		if ( ! apply_filters( 'pmc_gallery_v4_enable_runway_gallery_options', false ) ) {
			return $cheezcap_groups;
		}

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		// Needed for compatibility with BGR_CheezCap
		// @codeCoverageIgnoreStart
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';
		}
		// @codeCoverageIgnoreEnd

		$cheezcap_options = array(
			new CheezCapDropdownOption(
				esc_html__( 'Enable gallery interstitial', 'pmc-gallery' ),
				esc_html__( 'When enabled, gallery interstitial ads will render see PMC Ad manager for ad placement', 'pmc-gallery' ),
				'pmc_gallery_runway_interstitial',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new CheezCapTextOption(
				esc_html__( 'Number of clicks before ad refresh', 'pmc-gallery' ),
				esc_html__( 'Enter number > 0 to enable ad refresh', 'pmc-gallery' ),
				'pmc_gallery_runway_ad_refresh_clicks',
				2
			),
			new CheezCapTextOption(
				esc_html__( 'Number of clicks before interstitial ad is refresh', 'pmc-gallery' ),
				esc_html__( 'Enter number > 0 to enable interstitial ad refresh', 'pmc-gallery' ),
				'pmc_gallery_runway_interstitial_ad_refresh_clicks',
				25
			),
			new CheezCapTextOption(
				esc_html__( "Don't show interstitials on these galleries", 'pmc-gallery' ),
				esc_html__( 'Comma delimited post IDs, e.g.: 123,456,789', 'pmc-gallery' ),
				'pmc_gallery_runway_interstitial_no_ads',
				null
			),
			new CheezCapDropdownOption(
				'Enable Pinterest Description for images',
				'When enabled, creators can define a Pinterest Description for each image in the gallery.',
				'pmc_gallery_runway_enable_pinterest_description',
				array( 'no', 'yes' ),
				0, // First option => Disabled
				array( 'No', 'Yes' )
			),
			new CheezCapDropdownOption(
				esc_html__( 'Show zoom icon', 'pmc-gallery' ),
				esc_html__( 'When enabled, shows zoom icon on horizontal gallery.', 'pmc-gallery' ),
				'pmc_gallery_runway_enable_zoom',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new CheezCapDropdownOption(
				esc_html__( 'Show Pinit icon', 'pmc-gallery' ),
				esc_html__( 'When enabled, shows pinit icon on horizontal gallery on top left of each slide.', 'pmc-gallery' ),
				'pmc_gallery_runway_enable_pinit',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new CheezCapDropdownOption(
				esc_html__( 'Force all gallery endings to be the same', 'pmc-gallery' ),
				esc_html__( 'When enabled, shows next gallery link and related galleries thumbnail at the end of the slide.', 'pmc-gallery' ),
				'pmc_gallery_runway_force_same_ending',
				array( 'disabled', 'enabled' ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
		);

		$cheezcap_options  = apply_filters( 'pmc_gallery_runway_cheezcap_options', $cheezcap_options );
		$cheezcap_groups[] = new $cheezcap_group_class( esc_html__( 'Runway Gallery Options', 'pmc-gallery' ), 'pmc_gallery_runway_cheezcap', $cheezcap_options );

		return $cheezcap_groups;
	}

	public function get_options() : array {

		$options = [
			'ad_refresh_clicks'            => intval( PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_ad_refresh_clicks' ) ),
			'enable_interstitial'          => 'enabled' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_interstitial' ),
			'vertical_ad_frequency'        => intval( PMC_Cheezcap::get_instance()->get_option( 'pmc_vertical_ad_frequency' ) ),
			'vertical_ad_limit_count'      => intval( PMC_Cheezcap::get_instance()->get_option( 'pmc_vertical_ad_limit_count' ) ),
			'enable_zoom'                  => 'enabled' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_enable_zoom' ),
			'enable_pinit'                 => 'enabled' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_enable_pinit' ),
			'interstitial_refresh_clicks'  => intval( PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_interstitial_ad_refresh_clicks' ) ),
			'force_same_ending'            => 'enabled' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_force_same_ending' ),
			'enable_pinterest_description' => 'yes' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_enable_pinterest_description' ),
			'imageparts'                   => [],
			'multiparts'                   => [],
		];

		return array_merge( $this->get_common_options(), $options );
	}

	/**
	 * Get options for runway gallery.
	 *
	 * @return array
	 */
	public function get_runway_gallery_options() : array {
		$options = [
			'ad_refresh_clicks'            => intval( PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_runway_ad_refresh_clicks' ) ),
			'enable_interstitial'          => 'enabled' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_runway_interstitial' ),
			'enable_zoom'                  => 'enabled' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_runway_enable_zoom' ),
			'enable_pinit'                 => 'enabled' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_runway_enable_pinit' ),
			'interstitial_refresh_clicks'  => intval( PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_runway_interstitial_ad_refresh_clicks' ) ),
			'force_same_ending'            => 'enabled' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_runway_force_same_ending' ),
			'enable_pinterest_description' => 'yes' === PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_runway_enable_pinterest_description' ),
		];

		return array_merge( $this->get_common_options(), $options );
	}

	public function get_common_options() : array {
		return [
			'rail_bottom_ad_refresh_clicks' => intval( PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_rail_bottom_ad_refresh_clicks' ) ),
			'adhesion_ad_refresh_clicks'    => intval( PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_adhesion_ad_refresh_clicks' ) ),
		];
	}

	/**
	 * Check if the current post is on a "no ads" blocklist
	 *
	 * @see PMC_Ads::no_ads_on_this_post()
	 *
	 * @return boolean
	 */
	public function no_ads_on_this_post() {
		$no_ads_string = PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_interstitial_no_ads' );
		$no_ads_array  = explode( ',', $no_ads_string );
		$no_ads_array  = array_map( 'intval', $no_ads_array );

		if ( in_array( get_queried_object_id(), $no_ads_array, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Adding ad locations required for gallery
	 *
	 * @param array $locations Ad locations.
	 *
	 * @return array Ad locations.
	 */
	public function add_gallery_ad_locations( $locations = [] ) {
		$locations['in-gallery-1'] = [
			'title'     => 'Gallery v4: In Gallery Top Ad',
			'providers' => [ 'boomerang', 'google-publisher' ],
		];

		$locations['in-gallery-x'] = [
			'title'     => 'Gallery v4: In Gallery X',
			'providers' => [ 'boomerang', 'google-publisher' ],
		];

		$locations['right-rail-gallery'] = [
			'title'     => 'Right Rail Gallery',
			'providers' => [ 'boomerang', 'google-publisher' ],
		];

		return $locations;
	}

}

// EOF
