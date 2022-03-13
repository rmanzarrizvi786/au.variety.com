<?php

namespace PMC\Ad_Placeholders;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

/**
 * Inject advertisement placeholder divs into post content.
 *
 */
class Injection {

	use Singleton;

	/**
	 * Class instantiation.
	 *
	 * Hook into WordPress.
	 */
	protected function __construct() {

		// Only proceed if we're not in the admin.
		if ( is_admin() ) {
			return;
		}

		// Only proceed if injection has been enabled.
		if ( 'enabled' === get_option( 'cap_pmc-ad-placeholders-enable' ) ) {
			\PMC_Inject_Content::get_instance()->register_post_type( 'post' );
		}

		/**
		 * Filters.
		 */
		// Hook into PMC Inject Content to inject our placeholder divs.
		add_filter( 'pmc_inject_content_paragraphs', array( $this, 'filter_pmc_inject_content_paragraphs' ), 10, 3 );

		/**
		 * Action.
		 */
		add_action( 'wp_loaded', array( $this, 'setup_ads' ) );

	}

	/**
	 * @param array  $paragraphs       The paragraphs.
	 * @param int    $total_paragraphs Total number of paragraphs.
	 * @param string $context          The context for the current content.
	 *                                 One of the following: feed, single, or river.
	 *
	 * @return array
	 */
	public function filter_pmc_inject_content_paragraphs( $paragraphs = array(), $total_paragraphs = 0, $context = '' ) {

		// Not required on amp.
		if ( PMC::is_amp() ) {
			return $paragraphs;
		}

		$post_types = [ 'post' ];
		$post_types = apply_filters( 'pmc_ad_placeholders_post_types', $post_types );
		if ( ! empty( $post_types ) && is_array( $post_types ) && ! is_singular( $post_types ) ) {
			return $paragraphs;
		}

		if ( 'enabled' !== \PMC_Cheezcap::get_instance()->get_option( 'pmc-ad-placeholders-enable' ) ) {
			return $paragraphs;
		}

		global $post;

		// Cheezcap instance.
		$cheezcap_instance = \PMC_Cheezcap::get_instance();

		// Get theme option for ad position.
		if ( \PMC::is_mobile() ) {

			// For Mobile.
			$ad_unit_position = array(
				1 => absint( $cheezcap_instance->get_option( 'pmc-ad-placeholders-first-pos-mobile' ) ),
				2 => absint( $cheezcap_instance->get_option( 'pmc-ad-placeholders-second-pos-mobile' ) ),
			);

		} else {

			// For Desktop and others.
			$ad_unit_position = array(
				1 => absint( $cheezcap_instance->get_option( 'pmc-ad-placeholders-first-pos' ) ),
				2 => absint( $cheezcap_instance->get_option( 'pmc-ad-placeholders-second-pos' ) ),
			);

		}

		// This is 3rd ad unit pos from last ad unit added.
		$repeated_ad_pos = absint( $cheezcap_instance->get_option( 'pmc-ad-placeholders-x-pos' ) );

		// Prepare Ad Unit data.
		$ad_unit_data = array(
			1 => array(
				'ad-unit-id' => 'inline-article-ad-1',
				'position'   => max( 500, $ad_unit_position[1] ),
				'inserted'   => false,
			),
			2 => array(
				'ad-unit-id' => 'inline-article-ad-2',
				'position'   => max( 2300, $ad_unit_position[2] ),
				'inserted'   => false,
			),
		);

		// Tracking last inserted ad character limit.
		$last_ad_inserted_at = 0;
		// Tracking total ads inserted.
		$total_ads_inserted = 0;

		// Process content.
		$post_content = wpautop( $post->post_content );

		// Clean content.
		$post_content = strip_shortcodes( $post_content ); // strip short codes.
		$post_content = strip_tags( $post_content, '<p>' ); // strip all tags except P tags.

		// Convert content into array.
		$content_array = explode( '</p>', $post_content );

		$char_count = 0;

		$content_count = count( $content_array );
		$ad_unit_count = count( $ad_unit_data );

		// Loop through all paragraph.
		for ( $index = 0; $index < $content_count; $index++ ) {

			if ( empty( $content_array[ $index ] ) ) {
				continue;
			}

			$char_count += strlen( $content_array[ $index ] );

			// Loop through all ad units.
			for ( $ad_index = 1; $ad_index <= $ad_unit_count; $ad_index++ ) {

				$ad_data = $ad_unit_data[ $ad_index ];

				// If ad is already inserted than ignore.
				if ( true === $ad_data['inserted'] ) {
					continue;
				}

				if ( $char_count >= $ad_data['position'] ) {

					$ad_unit_data[ $ad_index ]['inserted'] = true;

					$paragraphs[ $index + 1 ][] = pmc_adm_render_ads( $ad_data['ad-unit-id'], '', false );
					$last_ad_inserted_at        = $char_count;

					$total_ads_inserted++;

					// Do not add two ads after one paragraph back to back.
					// they should have at least distance of one paragraph.
					continue 2;
				}
			}

			// Handling repeated inline article ad unit 'inline-article-ad-x'
			// Calculating new char limit for ad unit to insert.
			$repeated_ad_char_limit = $last_ad_inserted_at + $repeated_ad_pos;

			// Must be added only after first two ad unit are added.
			if ( ! empty( $repeated_ad_pos ) && $char_count >= $repeated_ad_char_limit && 2 <= $total_ads_inserted ) {
				$ad_html                    = pmc_adm_render_ads( 'inline-article-ad-x', '', false );
				$ad_html                    = str_replace( 'adm-inline-article-ad-x', 'adm-inline-article-ad-x-' . $index, $ad_html );
				$paragraphs[ $index + 1 ][] = $ad_html;
				$last_ad_inserted_at        = $char_count;

				$total_ads_inserted++;
			}
		}

		return $paragraphs;
	}

	/**
	 * Setting up Ad locations on init hook
	 */
	public function setup_ads() {
		$hp_native_river_pos = ( \PMC_Cheezcap::get_instance()->get_option( 'pmc-homepage-native-river-unit' ) );
		$vertical_native_river_pos = ( \PMC_Cheezcap::get_instance()->get_option( 'pmc-vertical-native-river-unit' ) );
		$ad_position               = ( is_home() ) ? $hp_native_river_pos : $vertical_native_river_pos;

		if ( $ad_position > 0 ) {
			add_action( 'pmc_river_after_post_' . $ad_position, array( $this, 'add_native_river_ad_unit' ) );
		}

	}

	/**
	 * Rendering native river ad unit.
	 */
	public function add_native_river_ad_unit() {

		if ( function_exists( 'pmc_adm_render_ads' ) ) {
			pmc_adm_render_ads( 'native-river-ad' );
		}
	}

}
