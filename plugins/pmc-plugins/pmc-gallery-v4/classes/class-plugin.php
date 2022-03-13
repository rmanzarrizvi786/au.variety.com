<?php

namespace PMC\Gallery;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Cache;

class Plugin {
	use Singleton;

	/**
	 * Return the cache group to use for gallery caching
	 * Allowing future improvement for caching dependencies
	 *
	 * @param string $name Name of the group to use
	 * @return string
	 */
	public function cache_group( string $name ) : string {
		// @TODO: We can detect gallery cheezcap setting changes and rotate the cache
		return sprintf( 'pmc-gallery-%s-%s', $name, PMC_GALLERY_CACHE_VERSION );
	}

	/**
	 * Helper function to create the pmc cache object
	 *
	 * @param string $key   The cache key to use
	 * @param string $group The group to use
	 * @return PMC_Cache
	 * @throws \ErrorException
	 */
	public function create_cache_instance( string $key, string $group = '' ) : PMC_Cache {
		return new PMC_Cache( $key, $this->cache_group( $group ) );
	}

	/**
	 * Helper function to get the advert module data
	 *
	 * @param string $ad_location
	 * @return array
	 */
	public function get_ads( string $ad_location ) : array {

		if ( empty( $ad_location ) ) {
			return [];
		}

		$data     = [];
		$function = function( $ad ) use ( &$data ) {
			// Translated the ad data for advert module
			$data[] = [
				'divId'       => $ad['div-id'],
				'displayType' => $ad['ad-display-type'],
				'targeting'   => $ad['targeting_data'],
				'lazyLoad'    => ! empty( $ad['is_lazy_load'] ) ? $ad['is_lazy_load'] : 'no',
				'zone'        => trim( $ad['zone'] ?? '', '/' ),
				'sizes'       => $ad['ad-width'],
			];
			return $ad;
		};

		add_filter( 'pmc_adm_prepare_boomerang_ad_data', $function );

		$html = pmc_adm_render_ads( $ad_location, '', false, 'boomerang' );

		remove_filter( 'pmc_adm_prepare_boomerang_ad_data', $function );

		if ( ! empty( $html ) ) {
			return [
				'html' => $html,
				'data' => $data,
			];
		}

		return [];
	}
}
