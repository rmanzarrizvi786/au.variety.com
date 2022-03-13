<?php

namespace PMC\JW_YT_Video_Migration;

/**
* Cheez_Options | cheez-options.php
*
* @author brandoncamenisch
* @version 2017-04-10 brandoncamenisch - feature/PMCBA-363:
*
*/


/**
* Cheez_Options
*
* @since 2017-04-10
*
* @version 2017-04-10 - brandoncamenisch - feature/PMCBA-363:
*
*/

use \PMC\Global_Functions\Traits\Singleton;

class Cheez_Options {

	use Singleton;

	// Define cheez group
	const PMC_VIDEO_MIGRATION_CHEEZ_GROUP = 'pmc_jw_yt_video_migration_cheez_group';

	// Define cheez enabled option name
	const PMC_VIDEO_MIGRATION_CHEEZ_ENABLED = 'pmc_jw_yt_video_migration_cheez_enabled';

	// Defined cheez dev option
	const PMC_VIDEO_MIGRATION_CHEEZ_DEV = 'pmc_jw_yt_video_migration_cheez_dev';

	// Define our mapping option name for final video mapping
	const PMC_VIDEO_MIGRATION_MAPPING_OPTION_NAME = 'pmc_jw_yt_video_migration_shortcode_mapping';

	// Define a filter that we can use to toggle on/off the plugin
	const PMC_VIDEO_MIGRATION_CHEEZ_FILTER = 'pmc_jw_yt_video_migration_cheez_enabled_filter';


	protected function __construct() {
		// Including the cap_ variation because old options might exist for cheez and we need to filter in case of cheez
		add_filter( 'pre_update_option_cap_' . self::PMC_VIDEO_MIGRATION_MAPPING_OPTION_NAME, array( $this, 'set_filter_option' ), 10, 2 );
		add_filter( 'pre_update_option_' . self::PMC_VIDEO_MIGRATION_MAPPING_OPTION_NAME, array( $this, 'set_filter_option' ), 10, 2 );
		add_filter( 'pre_option_cap_' . self::PMC_VIDEO_MIGRATION_MAPPING_OPTION_NAME, array( $this, 'get_filter_option' ), 10, 1 );
		add_filter( 'pre_option_' . self::PMC_VIDEO_MIGRATION_MAPPING_OPTION_NAME, array( $this, 'get_filter_option' ), 10, 1 );
		add_filter( 'pmc_cheezcap_groups', array( $this, 'filter_pmc_cheezcap_groups' ) );
		add_filter( self::PMC_VIDEO_MIGRATION_CHEEZ_FILTER, array( $this, 'dev_toggle' ) );
	}


	/**
	* set_filter_option | cheez-options.php
	*
	* @since 2017-04-12
	*
	* @author brandoncamenisch
	* @version 2017-04-12 - PMCBA-363:
	* - Updates option to pmc-options as opposed to cheez
	*
	* @param new_value
	* @param old_value
	* @return new_value
	**/
	public function set_filter_option( $new_value, $old_value ) {
		if ( function_exists( 'pmc_update_option' ) ) {
			pmc_update_option( self::PMC_VIDEO_MIGRATION_MAPPING_OPTION_NAME, $new_value );
			return true; // Saves true or 1 as cheez option
		} else {
			return $new_value;
		}
	}


	/**
	* get_filter_option | cheez-options.php
	*
	* @since 2017-04-12
	*
	* @author brandoncamenisch
	* @version 2017-04-12 - PMCBA-363:
	* - Gets the option from pmc-options plugin instead of cheez
	*
	* @param val value of option to get
	* @return val value of option
	**/
	public function get_filter_option( $val ) {
		if ( function_exists( 'pmc_get_option' ) ) {
			return pmc_get_option( self::PMC_VIDEO_MIGRATION_MAPPING_OPTION_NAME );
		} else {
			return $val;
		}
	}


	/**
	* filter_pmc_cheezcap_groups | cheez-options.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Filters in additional theme cheez settings for video migration
	* @version 2017-04-16 - feature/PMCBA-363:
	* - Adding a dev group toggle for testing
	*
	* @param cheezcap_groups array of options for cheez
	* @return cheezcap_groups array of options for cheez
	**/
	public function filter_pmc_cheezcap_groups( $cheezcap_groups = array() ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		$cheezcap_options = array(
			new \CheezCapDropdownOption(
				'Video Shortcode Override',
				'When active the [jwplatform] and [jwplayer] shortcodes render their counterpart YouTube videos',
				self::PMC_VIDEO_MIGRATION_CHEEZ_ENABLED,
				array( 'no', 'yes' ),
				0, // first option => No.
				array( 'No', 'Yes' )
			),
			new \CheezCapDropdownOption(
				'Restrict to PMC dev group',
				'When active the feature is only live to PMC dev group',
				self::PMC_VIDEO_MIGRATION_CHEEZ_DEV,
				array( 'yes', 'no' ),
				0, // first option => Yes
				array( 'Yes', 'No' )
			),
			new \CheezCapTextOption(
				'Video Mapping',
				'Only valid json should be added to this field the options mapping can be updated via WP-CLI',
				self::PMC_VIDEO_MIGRATION_MAPPING_OPTION_NAME,
				'{"jwkey":"ytid","kVor0tQF":"LbNkwBs8OjU","EVd6FH4q":"71j9zQk02FY"}',
				true
			),
		);

		$cheezcap_groups[] = new \CheezCapGroup( 'Video Migration', self::PMC_VIDEO_MIGRATION_CHEEZ_GROUP, $cheezcap_options );

		return $cheezcap_groups;
	}


	/**
	* dev_toggle| cheez-options.php
	*
	* @since 2017-04-16
	*
	* @author brandoncamenisch
	* @version 2017-04-16 - feature/PMCBA-363:
	* - Filters the cheez option to only expose itself to pmc-dev group
	*
	* @return bool
	**/
	public function dev_toggle( $val ) {
		$dev_enabled = strtolower( get_option( 'cap_' . self::PMC_VIDEO_MIGRATION_CHEEZ_DEV ) );
		if ( 'yes' === $dev_enabled && function_exists( 'pmc_current_user_is_member_of' ) ) {
			if ( pmc_current_user_is_member_of( 'pmc-dev' ) ) {
				$val = 'yes';
			} else {
				$val = 'no';
			}
		}
		return $val;
	}


	/**
	* is_migration_enabled| cheez-options.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Checks if the cheez option for the plugin should be active
	* @version 2017-04-16 - feature/PMCBA-363:
	* - Adding a filter for the cheez option
	*
	* @return bool
	**/
	public static function is_migration_enabled() {
		$mig_enabled = apply_filters( self::PMC_VIDEO_MIGRATION_CHEEZ_FILTER, strtolower( get_option( 'cap_' . self::PMC_VIDEO_MIGRATION_CHEEZ_ENABLED ) ) );
		if ( 'yes' === $mig_enabled ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	* get_option| cheez-options.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Gets the option from cheez
	*
	* @return json object used for mapping videos
	**/
	public static function get_option() {
		return json_decode( pmc_get_option( self::PMC_VIDEO_MIGRATION_MAPPING_OPTION_NAME ), true );
	}

}

// EOF
