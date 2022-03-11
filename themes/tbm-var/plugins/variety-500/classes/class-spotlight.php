<?php
/**
 * Spotlight
 *
 * Responsible for handling the Spotlight functionality
 * on the Home Page and in the Settings.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

/**
 * Class Spotlight
 *
 * Adds the Spotlight functionality for Variety 500.
 *
 * @since 1.0
 */
class Spotlight {
	/**
	 * Get Spotlight Profiles
	 *
	 * Fetches Spotlight Profiles saved in the
	 * V500 Settings, then loads them into an
	 * array of data to be displayed on the Home Page
	 * Spotlight area.
	 *
	 * @since 1.0
	 * @return array  An array of profiles, else an empty array.
	 */
	public static function get_profiles() {
		$profiles_string = get_option( 'variety_500_spotlight_profiles' );
		$profile_ids     = array();

		if ( ! empty( $profiles_string ) && is_string( $profiles_string ) ) {
			$profile_ids = explode( ',', $profiles_string );
		}

		if ( empty( $profile_ids ) || ! is_array( $profile_ids ) ) {
			return array();
		}

		// Load profile data into an array.
		$profiles = array();
		$count = 1;

		foreach ( $profile_ids as $profile_id ) {
			// Ensure we don't have any empty values from the settings page.
			if ( empty( $profile_id = trim( $profile_id ) ) ) {
				continue;
			}

			$profiles[ $count ]['job_title'] = get_post_meta( $profile_id, 'job_title', true );
			$profiles[ $count ]['synopsis']  = get_post_meta( $profile_id, 'brief_synopsis', true );
			$profiles[ $count ]['image']     = get_post_meta( $profile_id, 'honoree_image', true );
			$profiles[ $count ]['link']      = get_permalink( $profile_id );

			// Get the Companies.
			$company_string = '';
			$companies      = get_post_meta( $profile_id, 'companies', true );

			if ( ! empty( $companies ) && is_array( $companies ) ) {
				$companies      = wp_list_pluck( $companies, 'company_name' );
				$company_string = implode( ', ', $companies );
			}

			$profiles[ $count ]['companies'] = $company_string;

			// Get the Name.
			$first_name = get_post_meta( 'firstname', $profile_id, true );
			$last_name  = get_post_meta( 'lastname', $profile_id, true );
			$name       = $first_name . ' ' . $last_name;

			if ( empty( $name ) || 2 > strlen( $name ) ) {
				$name = get_the_title( $profile_id );
			}

			$profiles[ $count ]['name'] = $name;
			$count++;
		}

		return $profiles;
	}
}
