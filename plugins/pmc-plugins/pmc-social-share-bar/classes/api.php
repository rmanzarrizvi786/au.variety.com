<?php

namespace PMC\Social_Share_Bar;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class API {

	use Singleton;

	private static $_cache_group = 'pmc_social_share_bar';
	private static $_social_share_cache_key = 'pmc_social_share_icons_list';

	protected function __construct() {
	}

	/**
	 * Get the share icons from cache or from saved in the database else get from each LOB by using filter
	 * Update the cache with the value from LOB filter or DB
	 *
	 * @since 2016-02-16
	 * @version 2016-02-16 Archana Mandhare - PMCVIP-815
	 *
	 * @param $type string
	 * @return array
	 *
	 */
	public function get_share_icons( $type = 'primary', $post_type = '' ) {
		$admin_instance = Admin::get_instance();
		$icons_count = $admin_instance->get_primary_icons_list_count();
		$keys = $admin_instance->get_keys( $post_type );
		$lob_icons = array_keys( Config::get_instance()->get_social_share_icons_object() );

		$primary_list = array_slice( $lob_icons, 0, $icons_count );
		$secondary_list = array_slice( $lob_icons, $icons_count, count( $lob_icons ) - 1 );

		if( Admin::PRIMARY === $type ) {
			// Get the default icons keys -  have a filter to let lob select what needs to be retained from the entire list
			$lob_icons = apply_filters( "pmc_default_{$keys['cache']['primary']}_share_icons_list", $primary_list );
			$share_icons = wp_cache_get( self::$_social_share_cache_key . $keys['cache']['primary'], self::$_cache_group );
		} else {
			// Get the default icons keys -  have a filter to let lob select what needs to be retained from the entire list
			$lob_icons = apply_filters( "pmc_default_{$keys['cache']['secondary']}_share_icons_list", $secondary_list );
			$share_icons = wp_cache_get( self::$_social_share_cache_key . $keys['cache']['secondary'], self::$_cache_group );
		}

		if ( empty( $share_icons ) ) {
			$cache_key = $type;

			// Get from pmc options saved in DB
			if( Admin::PRIMARY === $type ){
				$cache_key = $keys['cache']['primary'];
				$share_icons = pmc_get_option( $keys['meta']['primary'] );
			} else{
				$cache_key = $keys['cache']['secondary'];
				$share_icons = pmc_get_option( $keys['meta']['secondary'] );
			}

			// if icons not found in DB then get them from the admin default list.
			if ( empty( $share_icons ) && ! empty( $lob_icons ) ) {
				$share_icons = $lob_icons;
			}

			$this->update_cache( $share_icons, $cache_key );
		}

		return $share_icons;

	}

	/**
	 * Update the cache with the share icons list
	 *
	 * @since 2016-02-16
	 * @version 2016-02-16 Archana Mandhare - PMCVIP-815
	 *
	 * @param $share_list array
	 * @param $type string
	 */
	public function update_cache( $share_list, $type = 'primary' ) {
		wp_cache_set( self::$_social_share_cache_key. $type, $share_list, self::$_cache_group );
	}


} // end class

//EOF
