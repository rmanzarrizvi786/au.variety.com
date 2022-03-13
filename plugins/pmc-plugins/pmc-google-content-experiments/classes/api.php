<?php

namespace PMC\Google_Content_Experiments;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Google Analytics Content Experiments PHP Client
 *
 * @package PMC\Google_Content_Experiments
 * @original_link   https://github.com/thomasbachem/php-gacx
 * @original_author Thomas Bachem <mail@thomasbachem.com>
 * @link https://developers.google.com/analytics/devguides/collection/gajs/experiments
 */
class API {

	use Singleton;

	const GACX_URL = 'https://www.google-analytics.com/cx/api.js';

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * Initilize the singleton object of our class.
	 */
	protected function __construct() {

		/**
		 * Create batcache buckets for $_COOKIE usage
		 *
		 * We use a cookie 'pmc_google_content_experiments' so we can reliably
		 * serve the same experiment variations to return visitors. The following
		 * instructs batcache to create a cache bucket per experiment/variation combo
		 * so that we can rely on $_COOKIE working properly in api.php.
		 */
		if ( ! is_admin() ) {
			if ( function_exists( 'vary_cache_on_function' ) ) {
				vary_cache_on_function( $this->get_batcache_cookie_variant_string() );
			}
		}
	}

	/**
	 * Set the experiment ID
	 *
	 * @param string $id The experiment ID string
	 *
	 * @return bool True on success, false on failure.
	 */
	public function set_id( $id = '' ) {
		if ( ! empty( $id ) ) {
			$this->id = $id;
			return true;
		}
		return false;
	}

	/**
	 * Get the experiment ID
	 *
	 * @return string|bool The experiment ID string on success, false on failure.
	 */
	public function get_id() {
		if ( ! empty( $this->id ) ) {
			return $this->id;
		}

		return false;
	}

	/**
	 * Get the string used to create batcache variants per experiment/variation combo.
	 *
	 * @return string The batcache variant string to be run through create_function()
	 */
	public function get_batcache_cookie_variant_string() {
		return '
			if ( ! empty( $_COOKIE["pmc_google_content_experiments"] ) ) {
	
				// Example cookie value for user participating in multiple experiments:
				// Aa2rX5sfTIq0jVH9Va3V0A==3|CQgGxYBQTsmCxoTo7LlUCA==1|wYdqtPuLT-yAaUqoYXJUnA==4
				// Explained: experiment_id==experiment_variation|experiment_id... and so on
				$experiments = explode( "|", $_COOKIE["pmc_google_content_experiments"] );
	
				if ( ! empty( $experiments ) && is_array( $experiments ) ) {
					
					// Bail if the number of experiments is too high
					// Just in case—this is simply an arbitrary limit
					// to prevent any bad scenarios if the cookie became 
					// corrupt or similar.
					if ( 20 <= count( $experiments ) ) {
						return false;
					}
					
					// Tell batcache to create a cache bucket for each experiment/variation combo
					// that may be stored in a user cookie. There could be numerous permutations.
					// Examples: 
					// Aa2rX5sfTIq0jVH9Va3V0A==3
					// Aa2rX5sfTIq0jVH9Va3V0A==2|CQgGxYBQTsmCxoTo7LlUCA==1
					// wYdqtPuLT-yAaUqoYXJUnA==4|Aa2rX5sfTIq0jVH9Va3V0A==0|CQgGxYBQTsmCxoTo7LlUCA==5
					// ... etc etc
					return $_COOKIE["pmc_google_content_experiments"];
				}
			}
			
			return false;
		';
	}

	/**
	 * Gets an existing variation in the user's cookie, or determines
	 * a new variation for the user.
	 *
	 * @param int $total_variants Number of variants in an experiment
	 *
	 * @return int|bool The selected variation on success. False on failure.
	 */
	public function get_variation( $total_variants = 2 ) {

		if ( ! empty( $this->id ) ) {

			// Helper for debugging variations to force a specific variation
			if ( isset( $_GET['expVar'] ) ) {
				return intval( $_GET['expVar'] );
			}

			$variation = $this->get_existing_variation();

			// Specifically using isset here because $variation may === 0
			if ( ! isset( $variation ) || false === $variation ) {
				$variation = $this->choose_new_variation_v2( $total_variants );
			}

			// Do another check to ensure we only return valid data
			// Again, using isset() because $variation may be 0
			// (not to be confused with false)
			if ( isset( $variation ) && false !== $variation ) {
				return $variation;
			}
		}

		return false;
	}

	/**
	 * See if the current users has already received a variation
	 * and it's still stored in their cookies.
	 *
	 * NOTE!!! Any edits made to this function MUST be mirrored in get_batcache_cookie_variant_string()
	 *
	 * @return int|bool A previously-set variation # stored in a cookie. False on failure.
	 */
	public function get_existing_variation() {

		// @see $this->_init() which creates cache buckets
		// so we can reliably use $_COOKIE here.
		if ( ! empty( $_COOKIE['pmc_google_content_experiments'] ) ) {

			// Example cookie value for user participating in multiple experiments:
			// Aa2rX5sfTIq0jVH9Va3V0A==3|CQgGxYBQTsmCxoTo7LlUCA==1|wYdqtPuLT-yAaUqoYXJUnA==4
			$experiments = explode( '|', $_COOKIE['pmc_google_content_experiments'] );

			if ( ! empty( $experiments ) && is_array( $experiments ) ) {

				// Bail if the number of experiments is too high
				// Just in case—this is simply an arbitrary limit
				// to prevent any bad scenarios if the cookie became
				// corrupt or similar.
				if ( 20 <= count( $experiments ) ) {
					return false;
				}

				foreach ( $experiments as $experiment ) {

					// For example: Aa2rX5sfTIq0jVH9Va3V0A==3
					// Experiment Aa2rX5sfTIq0jVH9Va3V0A uses variation 3
					$experiment_data = explode( '==', $experiment );

					if ( 2 === count( $experiment_data ) ) {

						$experiment_id        = $experiment_data[0];
						$experiment_variation = $experiment_data[1];

						// $this->id will be an experiment id, e.g. Aa2rX5sfTIq0jVH9Va3V0A
						if ( $this->id === $experiment_id ) {
							return sanitize_text_field( $experiment_variation );
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Choose a new variation for the current experiment.
	 *
	 * Reads the experiment data from GA, including the weighting
	 * for each variation, and computes a variation to use.
	 *
	 * @param array $data An array of GA JS API data for the current experiment. See get|load_data().
	 *
	 * @return int The variation number.
	 */
	public function choose_new_variation( $data = array() ) {
		if ( ! empty( $data ) && is_array( $data ) ) {
			if ( ! empty( $data['items'] ) && is_array( $data['items'] ) ) {
				$rand = mt_rand( 0, 1E9 ) / 1E9;
				foreach ( $data['items'] as $item ) {
					if ( ! empty( $item['weight'] ) && empty( $item['disabled'] ) ) {
						if ( $rand < $item['weight'] ) {
							// Specifically using isset() here because $item['id'] may be "0"
							if ( isset( $item['id'] ) ) {
								return intval( $item['id'] );
							}
						}
						$rand -= $item['weight'];
					}
				}
			}
		}

		return false;
	}

	/**
	 * Choose a new variant ot serve new user - Optimize suggests to use rand()
	 *
	 * @param int $total_variants
	 *
	 * @return int
	 */
	public function choose_new_variation_v2( $total_variants = 0 ) {

		if ( 2 > $total_variants ) {
			return 0;// Set to original `0` if there are less than 2 variants
		}

		$total_variants_limit = $total_variants - 1;

		return wp_rand( 0, $total_variants_limit );
	}

	/**
	 * Get data from GA about the current experiment.
	 *
	 * @return array An array of GA JS API data for the current experiment. See load_data().
	 */
	protected function get_data() {
		if ( empty( $this->data ) ) {
			$this->data = $this->load_data();
		}
		return $this->data;
	}

	/**
	 * Retrieves the experiment data from Google Analytics servers.
	 *
	 * This approach fetches the GA CX JS API via PHP and
	 * parses the experiment data output of that response.
	 * This is ghetto, and could fail at any time if GA changes
	 * their API JS. We're only using this hacky approach
	 * (originally from https://github.com/thomasbachem/php-gacx)
	 * to save on initial development time/build an MVP.
	 *
	 * @todo - use the Google Analytics Management API instead of this hacky approach.
	 *
	 * This allows us to take the different variation weights into account to make use of
	 * the multi-armed bandit algorithm (https://support.google.com/analytics/answer/2844870).
	 *
	 * @param bool $refresh_cache Defaults to false. Pass true to force a cache refresh.
	 *
	 * @return array|string An array of GA JS API data about the experiment on success.
	 *                      An error message on failure.
	 */
	protected function load_data( $refresh_cache = false ) {

		$error = false;
		$cache_key = "gacx-experiment-data-{$this->id}";
		$data = wp_cache_get( $cache_key );

		if ( false === $data || $refresh_cache ) {
			try {

				/**
				 * The URL used to fetch the GA JS API
				 *
				 * @param string $url The full URL to api.js including the experiment id
				 */
				$ga_js_api_url = apply_filters( 'pmc_google_content_experiments_ga_js_api_url', self::GACX_URL . '?experiment=' . rawurlencode( $this->id ) );

				if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
					$response = vip_safe_wp_remote_get( $ga_js_api_url );
				} else {
					$response = wp_remote_get( $ga_js_api_url );
				}

				if ( is_wp_error( $response ) ) {
					$error_code = wp_remote_retrieve_response_code( $response );
					$error_message = wp_remote_retrieve_response_message( $response );
					throw new \Exception( "Google CX Error: $error_code - $error_message" );
				} else {

					$response_body = wp_remote_retrieve_body( $response );

					// Use a recursive regex to match nested JSON objects
					// (relies on the fact that strings do not contain "{" or "}"!)
					if ( ! preg_match( '#\.experiments_\s*=\s*(\{(?:[^{}]+|(?1))+\})#', $response_body, $matches ) ) {
						throw new \Exception( 'Unable to find experiments in Google Analytics Content Experiments API response.' );
					} else {
						$experiments = json_decode( $matches[1], true );

						if ( ! empty( $experiments[ $this->id ]['data'] ) ) {
							$data = $experiments[ $this->id ]['data'];
							wp_cache_set( $cache_key, $data );
						} else {
							throw new \Exception( 'Unable to parse JSON from Google Analytics Content Experiments API response.' );
						}
					}
				}
			} catch ( \Exception $e ) {
				$data = $e->getMessage();
			}
		}

		return $data;
	}
}
