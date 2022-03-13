<?php

/**
 * Setup the plugin.
 *
 * @package pmc-google-amp
 */

namespace PMC\Google_Amp;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Unit_Test\Mock\WP_Query;

/**
 * Class Plugin
 */

class Plugin {
	use Singleton;

	private $_amp_version;

	protected function __construct() {

		$this->_amp_version = defined( 'AMP__VERSION' ) ? AMP__VERSION : null;

		$this->_setup_hooks();
	}

	protected function _setup_hooks(): void {
		add_action( 'plugins_loaded', [ $this, 'load_text_domain' ] );
		add_action( 'init', [ $this, 'remove_jetpack_amp_sharing_css' ] );
		add_action( 'wp', [ $this, 'disable_newrelic_for_amp_pages' ] );
	}

	/**
	 * Remove Jetpack CSS that conflicts with the AMP plugin's CSS for social share.
	 */
	public function remove_jetpack_amp_sharing_css(): void {
		remove_action( 'amp_post_template_css', [ 'Jetpack_AMP_Support', 'amp_reader_sharing_css' ], 10, 0 );
		remove_action( 'wp_enqueue_scripts', [ 'Jetpack_AMP_Support', 'amp_enqueue_sharing_css' ] );
	}

	public function load_text_domain(): void {
		$levels = 1; // ../

		load_plugin_textdomain( 'pmc-google-amp', false, dirname( __FILE__, $levels ) . '/languages' );
	}

	/**
	 * Check the AMP plugin version against a required version.
	 * This is a feature flag used to deploy updates to this pmc-plugin
	 * that are dependent on a specific AMP version.
	 *
	 * @param string $actual_version   Actual version of AMP WP plugin
	 * @param bool   $required_version Version required
	 *
	 * @return boolean True if actual version is at least required version.
	 */
	public function is_at_least_version( string $required_version ): bool {

		if ( null === $this->_amp_version ) {
			return false;
		}

		return version_compare( $this->_amp_version, $required_version, '>=' );
	}

	/**
	 * @param array $args  @see https://amp.dev/documentation/components/amp-jwplayer/
	 * @param bool $echo
	 * @return string
	 */
	public function render_jwplayer_tag( array $args, bool $echo = true ) : string {
		$safe_html = '';

		if ( ! empty( $args ) ) {

			// Add support for attrib without data- prefix
			foreach ( [ 'player-id', 'media-id', 'playlist-id' ] as $key ) {
				if ( isset( $args[ $key ] ) ) {
					$args[ 'data-' . $key ] = $args[ $key ];
					unset( $args[ $key ] );
				}
			}

			$defaults = [
				'autoplay' => true,
				'layout'   => 'responsive',
				'width'    => 16,
				'height'   => 9,
			];

			$args = wp_parse_args( $args, $defaults );

			$args = apply_filters( 'pmc_google_amp_jwplayer_tag', $args );

			$ads_suppression = apply_filters( 'pmc_ads_suppression', false, [ 'jwplayer', 'all' ] );
			if ( ! empty( $ads_suppression ) ) {
				$args['data-config-json'] = '{"advertising":{}}';
			}

			$safe_html = '<amp-jwplayer';

			foreach ( $args as $key => $value ) {

				if ( false === $value ) {
					continue;
				}

				if ( true === $value ) {
					$safe_html .= sprintf( ' %s', pmc_sanitize_html_attribute_name( $key ) );
				} else {
					$safe_html .= sprintf( ' %s="%s"', pmc_sanitize_html_attribute_name( $key ), esc_attr( $value ) );
				}

			}

			$safe_html .= '></amp-jwplayer>';
		}

		if ( $echo ) {
			echo $safe_html;  // phpcs:ignore
		}

		return $safe_html;
	}

	/**
	 * Disable New Relic Scripts in AMP Pages
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore Cannot test due to extension_loaded condition
	 */
	function disable_newrelic_for_amp_pages() {
		if ( function_exists( 'amp_is_request' ) &&
			amp_is_request() &&
			extension_loaded( 'newrelic' )
		) {
			newrelic_disable_autorum();
		}
	}

}
