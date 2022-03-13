<?php
/**
 * class which adds some extras to wpcom-legacy-redirector plugin
 *
 * @since 2016-09-16 Amit Gupta
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_WPCOM_Legacy_Redirector_Extras {

	use Singleton;

	/**
	 * Regex wildcard tokens for search/match
	 */
	protected $_wildcard_tokens = array(
		'*' => '(.*)',		//match anything, zero or more
		'+' => '(.+)',		//match anything, one or more
	);

	/**
	 * @var array Wildcard Redirect Rules
	 * Don't add any redirect rules here directly, create a plugin config for
	 * wpcom-legacy-redirector plugin in '<lob-theme>/plugins/config/' dir and
	 * register the wildcard redirect rules via that config.
	 *
	 * The redirect rules registered here must already have been imported into
	 * wpcom-legacy-redirector plugin as this will only allow wildcard matching
	 * for the said rules, redirection is still done by the plugin which will
	 * not happen if rules are not setup with it.
	 */
	protected $_wildcard_rules = array();

	/**
	 * Init method for the singleton
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to setup listeners on WP hooks (actions/filters)
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		add_filter( 'wpcom_legacy_redirector_request_path', array( $this, 'match_wildcard_rules' ) );
		add_filter( 'wpcom_legacy_redirector_request_path', [ $this, 'trailingslashit_in_url' ] );

	}

	/**
	 * Method to register the wildcard redirect rules for a site.
	 *
	 * The redirect rules registered here must already have been imported into
	 * wpcom-legacy-redirector plugin as this will only allow wildcard matching
	 * for the said rules, redirection is still done by the plugin which will
	 * not happen if rules are not setup with it.
	 *
	 * @param array $wildcard_rules An array of wildcard redirect rules to match
	 * @return boolean TRUE on success, FALSE on failure
	 */
	public function register_wildcard_rules( array $wildcard_rules ) {

		if ( empty( $wildcard_rules ) || count( $wildcard_rules ) < 1 ) {
			//throw exception only if current env is not production
			return PMC::maybe_throw_exception( sprintf( '%s::%s() expects a list of wildcard rules passed as a non-empty array', get_called_class(), __FUNCTION__ ) );
		}

		$this->_wildcard_rules = array_unique( $wildcard_rules );

		return true;

	}

	/**
	 * Called by 'wpcom_legacy_redirector_request_path' filter, this method
	 * checks the passed URL for match against the wildcard redirect rules
	 * and if a match is found then it returns that particular rule else
	 * the original URL passed to it.
	 *
	 * @param string $url URI path of the current requested URL
	 * @return string Wildcard redirect rule if a match is found with passed URL else originally passed URL
	 */
	public function match_wildcard_rules( $url ) {

		if ( empty( $this->_wildcard_rules ) || ! is_array( $this->_wildcard_rules ) ) {
			return $url;
		}

		$rules_count = count( $this->_wildcard_rules );

		for ( $i = 0; $i < $rules_count; $i++ ) {

			$pattern = '@' . $this->_wildcard_rules[ $i ] . '@i';

			foreach ( $this->_wildcard_tokens as $wildcard => $token ) {

				$pattern = str_replace( $wildcard, $token, $pattern );

			}

			if ( preg_match( $pattern, $url ) ) {

				/*
				 * match found
				 * return the wildcard rule instead so that
				 * destination can be looked up
				 */
				return $this->_wildcard_rules[ $i ];

			}

			unset( $pattern );

		}

		return $url;

	}

	/**
	 * To added trailing slash in url.
	 *
	 * @param string $url Path of URL excluding domain.
	 *
	 * @return string Path of URL excluding domain.
	 */
	public function trailingslashit_in_url( $url ) {

		$path  = trailingslashit( wp_parse_url( $url, PHP_URL_PATH ) );
		$query = wp_parse_url( $url, PHP_URL_QUERY );

		$url = $path;

		if ( ! empty( $query ) ) {
			$url .= '?' . $query;
		}

		return $url;
	}

}	//end class


/*
 * Init class
 */
PMC_WPCOM_Legacy_Redirector_Extras::get_instance();


//EOF
