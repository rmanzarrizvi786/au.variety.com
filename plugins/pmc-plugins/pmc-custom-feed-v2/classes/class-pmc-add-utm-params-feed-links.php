<?php
/**
 * This class adds functionality to add utm params on pmc brand urls.
 */

namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Feed_UTM_Params {

	use Singleton;

	/**
	 * Class Constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Add action and filters hooks.
	 */
	protected function _setup_hooks() {

		add_filter( 'pmc_custom_feed_content', [ $this, 'add_utm_params_to_content_links' ], 11, 4 ); // priority 11 to ensure it fires after other content processing
		add_filter( 'the_permalink_rss', [ $this, 'add_utm_params_to_permalink' ] );

	}

	/**
	 * Renders utm params on pmc brand urls in post content.
	 *
	 * @param $content
	 * @param $feed
	 * @param $post
	 * @param $feed_options
	 * @return string
	 */
	public function add_utm_params_to_content_links( $content, $feed, $post, $feed_options ) : string {

		$utm_params = $this->get_utm_params( $feed_options );

		if ( empty( $utm_params ) ) {
			return $content;
		}

		$pmc_domains = [
			'artnews.com',
			'bgr.com',
			'billboard.com',
			'blogher.com',
			'deadline.com',
			'dirt.com',
			'footwearnews.com',
			'goldderby.com',
			'hollywoodreporter.com',
			'indiewire.com',
			'robbreport.com',
			'rollingstone.com',
			'sheknows.com',
			'soaps.sheknows.com',
			'sportico.com',
			'spy.com',
			'stylecaster.com',
			'tvline.com',
			'variety.com',
			'vibe.com',
			'wwd.com',
		];

		// get all href links in content
		preg_match_all( '<a(.*?)href="([^"]+)"(.*?)>', $content, $matches );

		foreach ( $matches[2] as $match ) {

			$parsed_url = wp_parse_url( html_entity_decode( $match ) );

			// get domain.com to check against pmc brands array
			$domain = $parsed_url['host'];

			preg_match( '/[^\.\/]+\.[^\.\/]+$/', $domain, $results );

			// only add utm params for pmc domains
			if ( in_array( $results[0], (array) $pmc_domains, true ) ) {

				$url_full_path = $parsed_url['host'] . $parsed_url['path'];
				$parsed_query  = $parsed_url['query'];

				// break up query string params to key/value array
				// if content urls have existing non-utm query params, add to utm_params array
				// override existing utm params with input params from admin
				$args = [];

				wp_parse_str( $parsed_query, $args );

				foreach ( $args as $k => $v ) {
					if ( strpos( $k, 'utm_' ) === false ) {
						$utm_params[ $k ] = $v;
					}
				}

				$utm_params_str = http_build_query( $utm_params );
				$new_url        = $url_full_path . '#' . $utm_params_str;
				$content        = str_replace( $match, $new_url, $content );
			}
		}

		return $content;
	}

	/**
	 * Helper function to build UTM string
	 *
	 * @param $feed_options
	 * @return array
	 */
	public function get_utm_params( $feed_options ) {

		$utm_array = [];

		if ( ! empty( $feed_options['utm_campaign'] ) ) {
			$utm_array['utm_campaign'] = $feed_options['utm_campaign'];
		}

		if ( ! empty( $feed_options['utm_source'] ) ) {
			$utm_array['utm_source'] = $feed_options['utm_source'];
		}

		if ( ! empty( $feed_options['utm_medium'] ) ) {
			$utm_array['utm_medium'] = $feed_options['utm_medium'];
		}

		return $utm_array;
	}

	/**
	 * Adds utm params to permalinks on feed templates.
	 *
	 * @param string $permalink
	 * @return string
	 */
	public function add_utm_params_to_permalink( $permalink ) {

		$feed_options = \PMC_Custom_Feed::get_instance()->get_feed_config();

		$utm_params = $this->get_utm_params( $feed_options );

		if ( empty( $utm_params ) ) {
			return $permalink;
		}

		$utm_params_str = http_build_query( $utm_params );

		return $permalink . '#' . $utm_params_str;

	}

}

PMC_Feed_UTM_Params::get_instance();
