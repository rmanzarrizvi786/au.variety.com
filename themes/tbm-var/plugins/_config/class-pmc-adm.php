<?php
/**
 * Class containing all related ads functions
 *
 * @since 2017-08-11 CDWE-471 -- Copied from pmc-variety-2014 theme
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

if ( ! class_exists( '\PMC_Ads' ) ) {
	pmc_load_plugin( 'pmc-adm-v2', 'pmc-plugins' );
}

use PMC\Global_Functions\Traits\Singleton;
use PMC_Ad_Conditions;

class PMC_Adm {

	use Singleton;

	/**
	 * Class Initialization.
	 *
	 */
	protected function __construct() {

		/**
		 * Actions.
		 */
		add_action( 'init', [ $this, 'do_action_init' ] );
		add_action( 'wp_head', [ $this, 'disable_dfp_skin' ] );
		add_action( 'homepage_top_stories_ad_action', [ $this, 'render_hp_top_stories_ad' ] );
		add_action( 'sponsored_homepage_river_ad_action', [ $this, 'render_sponsored_hp_river_ad' ] );
		add_action( 'sponsored_most_popular_ad_action', [ $this, 'render_most_popular_ad' ] );

		/**
		 * Filters.
		 */
		add_filter( 'pmc_adm_dfp_skin_main_content', [ $this, 'get_main_container_selectors' ] );
		add_filter( 'pmc-adm_ads-interruptus_interstitial-site-logo', [ $this, 'get_site_logo' ] );
		add_filter( 'pmc_adm_topic_keywords_post_types', [ $this, 'filter_pmc_adm_topic_keywords_post_types' ] );
		add_filter( 'pmc_adm_blockthrough_placement_ids', [ $this, 'add_blockthrough_placements_config' ] );
		add_filter( 'pmc_adm_display_provider_ads', [ $this, 'filter_pmc_adm_display_provider_ads' ] );
		add_filter( 'pmc_adm_prepare_boomerang_global_settings', [ $this, 'filter_boomerang_global_settings' ] );

	}

	/**
	 * Disable DFP skin for certain pages
	 */
	public function disable_dfp_skin() {
		global $post;

		$page_templates = [
			'page-premier-landing.php',
			'page-premier-marketing.php',
			'page-corporate-subscriptions.php',
			'page-education-discount-selection.php',
		];

		$current_template = get_page_template_slug( $post->ID );

		if ( in_array( $current_template, $page_templates, true ) ) {
			add_filter( 'pmc_adm_dfp_skin_enabled', '__return_false' );
		}

		$this->disable_ads();
	}

	/**
	 *
	 */
	public function disable_ads() {
		global $post;

		$excluded_page_templates = [
			'page-editorial-hub.php',
		];

		$current_template = get_page_template_slug( $post->ID );

		if ( is_page() && ! in_array( $current_template, (array) $excluded_page_templates, true ) ) {
			add_filter( 'pmc_adm_should_render_ad', '__return_false' );
		}
	}

	/**
	 * Initialize hooks.
	 */
	public function do_action_init() {

		if ( ! is_admin() ) {
			add_filter( 'pmc_adm_custom_keywords', array( $this, 'get_custom_keywords' ), 10, 1 );
		}

		$this->init_ad_manager();

	}

	/**
	 * Initialize Ads locations.
	 *
	 * @see http://docs.pmc.com/2014/09/08/pmc-adm/#define-ad-locations
	 */
	public function init_ad_manager() {

		$boomerang_config = [
			'script_url' => 'https://ads.blogherads.com/sk/00/000/00000/27262/header.js',
		];
		\pmc_adm_add_provider( new \Boomerang_Provider( '8352', $boomerang_config ) );

		// IMPORTANT: do not change location slug name.
		\pmc_adm_add_locations(
			[
				'above-footer'                            => __( 'Above Footer', 'pmc-variety' ),
				'article-bottom-river'                    => __( 'Article At Bottom of River', 'pmc-variety' ),
				'belt'                                    => __( 'Belt', 'pmc-variety' ),
				'between-articles-river'                  => __( 'Between Articles in River', 'pmc-variety' ),
				'gallery-interstitial'                    => __( 'Gallery Interstitial', 'pmc-variety' ),
				'homepage-bottom-river'                   => __( 'Homepage At Bottom of River', 'pmc-variety' ),
				'homepage-second-revenue'                 => __( 'Homepage Second Revenue', 'pmc-variety' ),
				'homepage-audience-marketing'             => __( 'Homepage Audience Marketing Ad', 'pmc-variety' ),
				'homepage-audience-marketing-bottom'      => __( 'Homepage Audience Marketing Bottom Ad', 'pmc-variety' ),
				'homepage-top-sticky-ad'                  => __( 'Homepage Top Sticky Ad', 'pmc-variety' ),
				'homepage-top-stories'                    => __( 'Homepage Top Stories', 'pmc-variety' ),
				'in-gallery-x'                            => __( 'In Gallery X', 'pmc-variety' ),
				'inline-article-ad-1'                     => __( 'Inline article Ad 1', 'pmc-variety' ),
				'inline-article-ad-2'                     => __( 'Inline article Ad 2', 'pmc-variety' ),
				'leaderboard'                             => __( 'Leaderboard', 'pmc-variety' ),
				'inline-article-ad-x'                     => __( 'Inline article Ad X (Auto after 2nd)', 'pmc-variety' ),
				'leaderboard-gallery-b'                   => __( 'Leaderboard Gallery B', 'pmc-variety' ),
				'mobile-bottom'                           => __( 'Mobile Bottom', 'pmc-variety' ),
				'mobile-footer'                           => __( 'Mobile Footer', 'pmc-variety' ),
				'mobile-homepage-audience-marketing-ad'   => __( 'Mobile Homepage Audience Marketing Ad', 'pmc-variety' ),
				'right-rail-1'                            => __( 'Right rail 1', 'pmc-variety' ),
				'right-rail-2'                            => __( 'Right rail 2', 'pmc-variety' ),
				'right-rail-gallery-b'                    => __( 'Right Rail Gallery B', 'pmc-variety' ),
				'right-rail-gallery'                      => __( 'Right Rail Gallery', 'pmc-variety' ),
				'sponsored-most-popular'                  => __( 'Right Rail Sponsored Most Popular', 'pmc-variety' ),
				'right-rail-sticky-ad'                    => __( 'Right Rail Sticky Ad', 'pmc-variety' ),
				'section-front-top'                       => __( 'Section Front Top', 'pmc-variety' ),
				'single-audience-marketing'               => __( 'Single Audience Marketing Ad', 'pmc-variety' ),
				'sponsored-article-ad-1'                  => __( 'Sponsored Article Ad 1', 'pmc-variety' ),
				'sponsored-article-page-bottom'           => __( 'Sponsored Article Page Bottom', 'pmc-variety' ),
				'sponsored-homepage-river-desktop-second' => __( 'Sponsored Homepage River Second Page(Desktop)', 'pmc-variety' ),
				'sponsored-homepage-river-desktop'        => __( 'Sponsored Homepage River(Desktop)', 'pmc-variety' ),
				'sponsored-homepage-river-mobile-second'  => __( 'Sponsored Homepage River Second Page(Mobile)', 'pmc-variety' ),
				'sponsored-homepage-river-mobile'         => __( 'Sponsored Homepage River(Mobile)', 'pmc-variety' ),
				'sponsored-section-front-river-desktop'   => __( 'Sponsored Section Front River(Desktop)', 'pmc-variety' ),
				'sponsored-section-front-river-mobile'    => __( 'Sponsored Section Front River(Mobile)', 'pmc-variety' ),
				'vertical-bottom-river'                   => __( 'Vertical At Bottom of River', 'pmc-variety' ),
				'sponsored-homepage-river'                => __( 'Sponsored Homepage River', 'pmc-variety' ),
			]
		);

		// Added for PMCEED-678 experiment.
		// @todo remove this once PMCEED-678 experiment done.
		// @codeCoverageIgnoreStart
		pmc_adm_add_locations(
			[
				'in-gallery-x' => __( 'In Gallery X', 'pmc-variety' ),
			]
		);
		// @codeCoverageIgnoreEnd
		PMC_Ad_Conditions::get_instance()->register(
			'is_editorial',
			[ $this, 'is_editorial' ],
			[ 'term' ]
		);

	}

	/**
	 * If $value in true|false|yes|no|empty return true if taxonomy = editorial
	 * otherwise return true only if taxonomy = editorial && taxonomy term = $value
	 *
	 * @param string $value Taxonomy check string.
	 *
	 * @return bool Return true if taxonomy = editorial
	 *              Otherwise return true only if taxonomy = editorial && taxonomy term = $value
	 */
	public function is_editorial( $value = 'true' ) {

		global $wp_query;

		$value = strtolower( $value );

		// first case, taxonomy is not editorial.
		if ( ! is_tax() || ! isset( $wp_query->query_vars['taxonomy'] ) || 'editorial' !== $wp_query->query_vars['taxonomy'] ) {

			// inverse case condition.
			if ( 'false' === $value || 'no' === $value ) {
				return true;
			}

			return false;

		}

		// second case, check if taxonomy is editorial.
		if ( in_array( $value, array( 'true', 'yes', 'false', 'no', '' ), true ) ) {

			// return true only for value = true|yes|empty.
			return in_array( $value, array( 'true', 'yes', '' ), true );

		}

		// third case, we want to check for matching taxonomy term.
		$term = get_term_by( 'slug', get_query_var( $wp_query->query_vars['taxonomy'] ), $wp_query->query_vars['taxonomy'] );

		if ( $term instanceof \WP_Term ) {
			return ( $term->slug === $value );
		}

		return false;
	}

	/**
	 * Get list of term's slug.
	 *
	 * @param array $keywords List of term's slugs.
	 *
	 * @return array List of term's slugs.
	 */
	public function get_custom_keywords( $keywords ) {

		global $wp_query;
		global $post;

		if ( ! is_array( $keywords ) ) {
			return $keywords;
		}

		if ( is_tax() ) {

			$term = get_queried_object();

			if ( $term instanceof \WP_Term ) {
				$keywords[] = $term->slug;
			}
		}

		if ( is_archive() ) {
			$keywords[] = 'archive';
		}

		if ( ! empty( $wp_query->query['f'] ) ) {
			$keywords[] = sanitize_text_field( $wp_query->query['f'] );
		}

		if ( is_singular( 'pmc-gallery' ) ) {
			$keywords[] = 'spg-a';
		}

		$current_template = get_page_template_slug( $post->ID );
		if ( 'page-editorial-hub.php' === $current_template ) {
			$keywords[] = 'what-to-watch';
		}

		return $keywords;
	}

	/**
	 * Passing the skin ad main content div id.
	 *
	 * @return array Returns div id.
	 */
	public function get_main_container_selectors() {
		return [ 'site-wrap' ];
	}

	/**
	 * To change logo on interruptus interstitial ad.
	 *
	 * @param  string $logo Text/Html will be place in interruptus interstitial ad.
	 *
	 * @return string Text/Html will be place in interruptus interstitial ad.
	 */
	public function get_site_logo( $logo ) {

		$logo_src = sprintf( '%s/assets/build/svg/brand-logo.svg', untrailingslashit( VARIETY_THEME_URL ) );

		return sprintf( '<img src="%1$s" alt="%2$s" title="%2$s" style="width:150px;">', esc_url( $logo_src ), esc_attr( get_bloginfo( 'name' ) ) );
	}

	/**
	 * Adding 'variety_top_video' post type to ADM topic keywords.
	 *
	 * @param array $keywords_post_types Array of post_types.
	 *
	 * @return array List of post types for topics keywords.
	 */
	public function filter_pmc_adm_topic_keywords_post_types( $keywords_post_types ) {

		$keywords_post_types = ( empty( $keywords_post_types ) || ! is_array( $keywords_post_types ) ) ? [] : $keywords_post_types;

		$keywords_post_types[] = 'variety_top_video';

		return array_filter( array_unique( (array) $keywords_post_types ) );

	}

	/**
	 * Setup Blockthrough placement ids per device and adunit size
	 *
	 * @return array
	 */
	public function add_blockthrough_placements_config() {

		$blockthrough_placements = [
			'desktop' => [
				'970x250' => '5d9d0fc80a-238',
				'728x90'  => '5d9d0fc80a-238',
				'300x250' => '5d9d0fd774-238',
				'300x600' => '5d9d0fd774-238',
			],
			'mobile'  => [
				'320x50'  => '5d9d0fe184-238',
				'320x250' => '5d9d0fe184-238',
			],
		];

		return $blockthrough_placements;
	}

	/**
	 * Filter boomerang settings for single pages
	 *
	 * @param array $settings Boomerang settings.
	 *
	 * @return array
	 */
	public function filter_boomerang_global_settings( $settings ) {
		if ( \PMC::is_mobile() ) {
			$settings['country_code_ad_unit_map'] = [
				'AU' => 'Variety_Mobile_AU',
				'NZ' => 'Variety_Mobile_AU',
			];
			$settings['overwrite_dfp_name']       = 'Variety_Mobile';
		} else {
			$settings['country_code_ad_unit_map'] = [
				'AU' => 'Variety_AU',
				'NZ' => 'Variety_AU',
			];
		}
		return $settings;
	}

	/**
	 * Return boomerang ads
	 * @codeCoverageIgnore This is a temp filter which will be removed after the pmc-adm-v2 is launched and site is working fine.
	 * @param string $provider
	 *
	 * @return string
	 */
	public function filter_pmc_adm_display_provider_ads( $provider = '' ) {
		$provider = empty( $provider ) ? 'boomerang' : $provider;
		return $provider;
	}

	/**
	 * Render ad in top stories- top right
	 */
	public function render_hp_top_stories_ad() {
		pmc_adm_render_ads( 'homepage-top-stories', '', true );
	}

	/**
	 * Render ad in homepage news river
	 */
	public function render_sponsored_hp_river_ad() {
		pmc_adm_render_ads( 'sponsored-homepage-river', '', true );
	}

	/**
	 * Render ad in most popular widget location
	 */
	public function render_most_popular_ad() {
		pmc_adm_render_ads( 'sponsored-most-popular', '', true );
	}

}

//EOF
