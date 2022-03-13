<?php
namespace PMC\Google_Amp;

use PMC;
use PMC\Global_Functions\Traits\Singleton;

class Optimera {
	use Singleton;

	protected $_client_id   = 21;
	protected $_client_key  = 'optimera';
	protected $_amp_ad_type = 'doubleclick';

	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		// Note: is_amp won't be valid until wp is initialized
		add_action( 'wp', [ $this, 'action_wp' ], 20 );
	}

	/**
	 * Process wp action event to detect amp ad type via `pmc_google_amp_ad_template` filter
	 */
	public function action_wp() {
		if ( ! PMC::is_amp() ) {
			return;
		}
		add_action( 'amp_post_template_head', [ $this, 'action_amp_post_template_head' ] );
		add_filter( 'pmc_google_amp_ad_rtc_config', [ $this, 'filter_pmc_google_amp_ad_rtc_config' ], 10, 4 );

		// Using this filter to detect if theme has overridden amp ad template
		$template = apply_filters( 'pmc_google_amp_ad_template', '' );

		if ( preg_match( '/ad-slot-boomerang.php/', $template ) ) {
			$this->_amp_ad_type = 'shemedia';
		} else {
			$this->_amp_ad_type = 'doubleclick';
		}

	}

	/**
	 * Filter to add optimera's rtc config for amp ad
	 *
	 * @param array $rtc_config
	 * @param string $ad_slot
	 * @param string $ad_div_id
	 * @return array
	 */
	public function filter_pmc_google_amp_ad_rtc_config( $rtc_config, $ad_slot = null, $ad_div_id = '', $renderer_options = [] ) {
		if ( empty( $rtc_config ) || ! is_array( $rtc_config ) ) {
			$rtc_config = [];
		}

		if ( ! empty( $ad_div_id ) ) {
			$json_name            = empty( $renderer_options['is_sticky_ad'] ) ? $ad_div_id : 'sticky';
			$link                 = preg_replace( '@https?://@', '', untrailingslashit( get_permalink() ) );
			$rtc_config['urls'][] = sprintf( 'https://d19nl3yv2kmj7e.cloudfront.net/%s/%s/%s.json', $this->get_client_id(), $link, $json_name );
		}
		return $rtc_config;
	}

	/**
	 * Generate the amp script hash value
	 */
	public function action_amp_post_template_head() {
		if ( $this->is_active() ) {
			// REV-294: hash value for ops-amp-1.0.4.js
			echo '<meta name="amp-script-src" content="sha384-BvmzPgCdhcTmTQJPMz2uCe3RrSrHwBGGIJz0nt2rQ3ujWT6kkrtxhC5XIOK-kFs5">';
		}
	}

	/**
	 * Return true if client id & key are provided
	 * @return bool
	 */
	public function is_active() {
		return ! empty( $this->get_client_id() )
			&& ! empty( $this->get_client_key() )
			&& PMC::is_amp()
			&& 'doubleclick' === $this->_amp_ad_type; // Only activate for doubleclick amp ad type
	}

	/**
	 * Return the client id
	 * @return string
	 */
	public function get_client_id() {
		return apply_filters( 'pmc_google_amp_optimera_client_id', $this->_client_id );
	}

	/**
	 * Return the client key
	 * @return string
	 */
	public function get_client_key() {
		return apply_filters( 'pmc_google_amp_optimera_client_key', $this->_client_key );
	}

	/**
	 * If active, render the amp-script & amp-state tag
	 */
	public function render_start() {
		if ( ! $this->is_active() ) {
			return;
		}

		$data = [
			'page' => [
				'canonical'        => get_permalink(),
				'optimeraClientId' => $this->get_client_id(),
				'optimeraKey'      => $this->get_client_key(),
				'refresh'          => 30,
			],
		];

		$script_url = sprintf( 'https://d1if9rot2w9rj0.cloudfront.net/ops-amp-1.0.4.js?cid=%s', $this->get_client_id() );

		// render the amp-script opening tag only, will need to close it when render is done
		printf( '<amp-script layout="container" src="%s">' . PHP_EOL, esc_url( $script_url ) );

		// render the full amp-state tag
		printf( '<amp-state id="optimera-params"><script type="application/json">%s</script></amp-state>' . PHP_EOL, wp_json_encode( $data, JSON_UNESCAPED_SLASHES ) );

	}

	/**
	 * Render the closing amp-script tag if active
	 */
	public function render_end() {
		if ( ! $this->is_active() ) {
			return;
		}
		echo '</amp-script>' . PHP_EOL;
	}

}
