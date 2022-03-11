<?php
/**
 * Social Glimpse
 *
 * Handles the Social Glimpse on the homepage.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

/**
 * Class Social_Glimpse
 *
 * Adds the Social Glimpse Instagram feed functionality to the Variety 500
 * homepage.
 *
 * @since 1.0
 */
class Social_Glimpse {
	/**
	 * The total number of images for the Social Glimpse.
	 */
	const TOTAL_IMAGES = 20;

	/**
	 * Get Instagram Profiles
	 *
	 * Fetches profiles saved in the V500 Settings, then finds the Instagram
	 * usernames and loads them into an array of data to be used on the homepage
	 * in the Social Glimpse area.
	 *
	 * @since 1.0
	 * @return array An array of profiles, else an empty array.
	 */
	public static function get_instagram_profiles() {
		$profiles_string = get_option( 'variety_500_instagram_profiles' );
		$profile_ids     = array();

		if ( ! empty( $profiles_string ) && is_string( $profiles_string ) ) {
			$profile_ids = explode( ',', $profiles_string );
		}

		if ( empty( $profile_ids ) || ! is_array( $profile_ids ) ) {
			return array();
		}

		// Load Instagram usernames into an array.
		$usernames = array();

		foreach ( $profile_ids as $profile_id ) {
			// Ensure we don't have any empty values from the settings page.
			if ( empty( $profile_id = trim( $profile_id ) ) ) {
				continue;
			}

			$meta = get_post_meta( $profile_id, 'social', true );

			if ( ! empty( $meta['instagram_url'] ) ) {
				$instagram_url = $meta['instagram_url'];
			} else {
				$instagram_url = trim( get_post_meta( $profile_id, 'company_instagram_url', true ) );
			}

			if ( ! empty( $instagram_url ) || false === filter_var( $instagram_url, FILTER_VALIDATE_URL ) ) {
				// Remove query params.
				$pos = strpos( $instagram_url, '?' );
				if ( false !== $pos ) {
					$instagram_url = substr( $instagram_url, 0, $pos );
				}
				$instagram_url = untrailingslashit( $instagram_url );
				$url_array     = explode( '/', $instagram_url );
				$usernames[]   = array_pop( $url_array );
			}
		}

		return $usernames;
	}

	/**
	 * Get Home Feed
	 *
	 * Fetches the feed for the home template.
	 *
	 * Returns images in the following order:
	 * 1.  The first image from all accounts, regardless of time.
	 * 2.  Most recent from all accounts, regardless of user.
	 *
	 * @return array
	 */
	public static function get_home_feed() {
		$feed      = array();
		$filler    = array();
		$usernames = self::get_instagram_profiles();
		if ( empty( $usernames ) || ! is_array( $usernames ) ) {
			return $feed;
		}

		// This returns up to 20 images for each user, with the username as the primary key.
		$images = Instagram::get_instance()->get_feeds( $usernames );

		// The first image from all accounts, regardless of time.
		foreach ( $usernames as $user ) {
			if ( empty( $images[ $user ] ) || ! is_array( $images[ $user ] ) ) {
				continue;
			}

			/*
			 * Get the first image from each user, add it to the beginning
			 * section of the feed, then remove it from its user's array.
			 */
			$first_key = key( $images[ $user ] );
			$feed[]    = $images[ $user ][ $first_key ];
			unset( $images[ $user ][ $first_key ] );

			// Put the rest of the user's images into the filler array, and preserve time key.
			$filler = $filler + $images[ $user ];
		}

		// Fill in with most recent from all accounts, regardless of user.
		$needed = ( self::TOTAL_IMAGES - count( $feed ) );
		if ( 0 < $needed && is_array( $feed ) && is_array( $filler ) ) {
			krsort( $filler, SORT_NUMERIC );
			$feed = array_merge( $feed, array_slice( $filler, 0, $needed ) );
		}
		return $feed;
	}
}
