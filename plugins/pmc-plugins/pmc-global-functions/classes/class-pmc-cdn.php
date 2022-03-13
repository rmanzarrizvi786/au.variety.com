<?php
/**
 * This class is added to allow cdn host configuration per lob.  We need to reference all asses to use
 * cdn.[lob].com cname to files.wordpress.com to bypass china firewall.
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_CDN {

	use Singleton;

	private $_cheezcap_options_active = false;
	private $_cdn_options = false;

	/**
	 * Class constructor
	 */
	protected function __construct() {

		$this->_setup_hooks();

		// china
		( ! function_exists( 'pmc_geo_add_location' ) ) ?: pmc_geo_add_location( 'cn' );  // phpcs:ignore

	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @retrun void
	 */
	protected function _setup_hooks() : void {

		// we need a lower priority run after cheezcap have a chance to run
		add_action( 'init', [ $this, 'action_init' ], 11 );

		// this filter need to add before init fire so cheezcap can see the filter during init.
		add_filter( 'pmc_global_cheezcap_options', [ $this, 'filter_pmc_global_cheezcap_options' ] );

	}

	public function action_init() {
		$this->load_cdn_options();

		// activate script tag override only if CDN is active
		if ( $this->is_active() ) {
			add_filter( 'script_loader_tag', array( $this, 'filter_script_loader_tag' ), 10, 3 );
		}

	}

	/**
	 * Filter to override wp script output to support Cloudflare Rocket Loader script optimization
	 */
	public function filter_script_loader_tag( $tag, $handle, $src ) {
		// only override if script is tag for cf async mode
		if ( true === wp_scripts()->get_data( $handle, 'cfasync' ) ) {
			$tag = str_replace('<script', '<script data-cfasync=\'true\'', $tag );
		}
		return $tag;
	}

	/**
	 * Return the function as string for vary cache on request header for cloudflare request
	 * @return string
	 */
	public function get_vary_cache_on_function_string() {
		return '
			if ( ! empty( $_SERVER["HTTP_CF_RAY"] ) ) { return "cf"; }
			if ( ! empty( $_SERVER["HTTP_VIA"] ) && false !== stripos( $_SERVER["HTTP_VIA"], "akamai" ) ) { return "ak"; }
			return "";
		';
	}

	// get cdn options from cheezcap load via PMC::load_custom_cdn
	public function load_cdn_options() {

		// reset variable to allow unit testing properly
		$this->_cdn_options = false;
		if ( is_admin() || ! $this->_cheezcap_options_active || is_preview() ) {
			return;
		}

		// cdn host override won't work on SSL
		if ( PMC::is_https() ) {
			// allow force override?
			if ( 'on' !== cheezcap_get_option( 'pmc_cdn_https' , false ) ) {
				return;
			}
			add_filter( 'pmc_custom_cdn_ssl_opt_in', '__return_true' );
		}

		$activate_condition = cheezcap_get_option( 'pmc_cdn_activate_condition' , false );

		if ( empty( $activate_condition ) || 'disabled' === $activate_condition ) {
			return;
		}

		if ( 'all' !== $activate_condition && pmc_geo_get_user_location() !== $activate_condition ) {

			if ( ! in_array( $activate_condition, [ 'cloudflare', 'akamai', 'cdn' ], true ) ) {
				return;
			}

			if ( function_exists( 'vary_cache_on_function' ) ) {
				vary_cache_on_function( $this->get_vary_cache_on_function_string() );
			}

			if ( ! empty( $_SERVER['HTTP_CF_RAY'] ) ) {
				$via_cdn = 'cloudflare';
			} elseif ( ! empty( $_SERVER['HTTP_VIA'] ) && false !== stripos( $_SERVER['HTTP_VIA'], 'akamai' ) ) {
				$via_cdn = 'akamai';
			} else {
				return;
			}

		}

		$cdn_options = array();
		$list = array( 'cdn_host_static', 'cdn_host_media', 'cdn_host_photon', 'cdn_host_media_origin' );

		foreach ( $list as $key ) {

			$value = cheezcap_get_option( 'pmc_' . $key , false );

			if ( empty( $value ) ) {
				continue;
			}

			if ( ! empty( $via_cdn ) && 'cdn' === $activate_condition && preg_match( '/' . preg_quote( $via_cdn ) . '\=([\w+\.]+)/', $value, $matches ) ) {
				$value = $matches[1];
			}

			$cdn_options[ $key ] = $value;
		}

		if ( empty( $cdn_options ) ) {
			return;
		}

		$this->_cdn_options = $cdn_options;
		PMC::load_custom_cdn( $cdn_options );

		// if custom cdn photon host is define, add filter to override
		if ( ! empty( $this->_cdn_options['cdn_host_photon'] ) ) {
			add_filter( 'jetpack_photon_domain', array( $this, 'filter_jetpack_photon_domain' ), 10, 2 );
		}

		// if custom cdn media host & origin is define
		if ( ! empty( $this->_cdn_options['cdn_host_media_origin'] ) && ! empty( $this->_cdn_options['cdn_host_media'] ) ) {
			// add filter to fix the photon url
			add_filter( 'jetpack_photon_pre_image_url', array( $this, 'filter_jetpack_photon_pre_image_url' ), 10, 3 );

			// add filter to fix widget & other custom plugin that output direct html contents
			// Utilizing PMC::html_ssl_friendly function
			add_filter( 'pmc_pre_ssl_friendly_url', array( $this, 'filter_pmc_pre_ssl_friendly_url' ) );
			add_filter( 'pmc_html_ssl_friendly', array( $this, 'filter_pmc_html_ssl_friendly') );

		}

	}

	// return true if cdn is active
	public function is_active() {
		if ( empty( $this->_cdn_options ) || empty( $this->_cdn_options['cdn_host_media'] ) ) {
			return false;
		}
		return true;
	}

	// filter to fix image reference url, translate origin media host into cdn host
	public function filter_pmc_pre_ssl_friendly_url( $url ) {
		if ( ! empty( $this->_cdn_options['cdn_host_media_origin'] ) && ! empty( $this->_cdn_options['cdn_host_media'] ) ) {
			return str_ireplace( '/' . $this->_cdn_options['cdn_host_media_origin'] . '/', '/' . $this->_cdn_options['cdn_host_media'] . '/', $url );
		}
		return $url;
	}

	public function filter_pmc_html_ssl_friendly( $html ) {
		if ( ! empty( $this->_cdn_options['cdn_host_media_origin'] ) && ! empty( $this->_cdn_options['cdn_host_media'] ) ) {
			return str_ireplace( '//' . $this->_cdn_options['cdn_host_media_origin'] . '/', '//' . $this->_cdn_options['cdn_host_media'] . '/', $html );
		}
		return $html;
	}

	// filter to fix photon url, photon url should not query cdn host
	public function filter_jetpack_photon_pre_image_url( $image_url, $args, $scheme ) {
		if ( ! empty( $this->_cdn_options['cdn_host_media_origin'] ) && ! empty( $this->_cdn_options['cdn_host_media'] ) ) {
			return str_ireplace( '/' . $this->_cdn_options['cdn_host_media'] . '/', '/' . $this->_cdn_options['cdn_host_media_origin'] . '/', $image_url );
		}
		return $image_url;
	}

	// filter to override photon domain
	public function filter_jetpack_photon_domain( $domain, $image_url ) {
		if ( ! empty( $this->_cdn_options['cdn_host_photon'] ) ) {
			return $this->_cdn_options['cdn_host_photon'];
		}
		return $domain;
	}

	// add cheezcap options to enter cdn hosts
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {

		$this->_cheezcap_options_active = true;

		if ( empty( $cheezcap_options ) || ! is_array( $cheezcap_options ) ) {
			$cheezcap_options = array();
		}

		$cheezcap_options[] = new CheezCapTextOption(
					'CDN Host Static',
					'Hostname of the CDN for static assets',
					'pmc_cdn_host_static',
					''
				);
		$cheezcap_options[] = new CheezCapTextOption(
					'CDN Host Media',
					'Hostname of the CDN for media library assets',
					'pmc_cdn_host_media',
					''
				);
		$cheezcap_options[] = new CheezCapTextOption(
					'CDN Host Photon',
					'Hostname of the CDN for jetpack photon assets',
					'pmc_cdn_host_photon',
					''
				);
		$cheezcap_options[] = new CheezCapTextOption(
					'CDN Host Origin Media',
					'The wordpress host origin for the media files',
					'pmc_cdn_host_media_origin',
					''
				);
		$cheezcap_options[] = new CheezCapDropdownOption(
					'Activate CDN Host override',
					'Activate CDN Host override condition',
					'pmc_cdn_activate_condition',
					array( 'disabled', 'cn', 'cloudflare', 'akamai', 'cdn', 'all' ),
					0, // 1sts option => Disabled
					array( 'Disabled', 'Only traffics from China', 'Cloudflare', 'Akamai', 'Cloudflare/Akamai', 'All traffics' )
				);
		$cheezcap_options[] = new CheezCapDropdownOption(
					'Activate CDN Host HTTPS',
					'Activate CDN Host over HTTPS traffic',
					'pmc_cdn_https',
					array( 'off', 'on' ),
					0, // 1sts option => Off
					array( 'Off', 'On' )
				);

		return $cheezcap_options;
	}

}

PMC_CDN::get_instance();
// EOF
