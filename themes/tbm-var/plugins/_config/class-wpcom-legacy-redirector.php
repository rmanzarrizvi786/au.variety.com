<?php
/**
 * Config file for wpcom-legacy-redirector plugin
 *
 * @since 2017-04-2017 CDWE-583
 *
 * @see pmc-variety-2014/plugins/config/wpcom-legacy-redirector.php
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC_WPCOM_Legacy_Redirector_Extras;

class WPCOM_Legacy_Redirector {

	use Singleton;

	/**
	 * Wildcard rules for redirection
	 *
	 * @var array
	 */
	protected $_wildcard_rules = [
		'/2015/film/asia/india-nfdc-to-launch-film-facilitation-office-1201644054/%09%09%09%09%09*',
		'/editorial/*',
		'/e/*',
		'/festivals/cannes-film-festival/*',
		'/t/eye-on-the-oscars-*/',
		'/t/oscar-hai*/',
		'/t/oscar-handica*/',
		'/t/oscar-part*/',
		'/t/blue-is-the-warmest-color-*/',
		'/t/mtv-ne*/',
		'/t/video-music-awar*/',
		'/t/natalie-portman-*/',
		'/t/sag-award-*/',
		'/t/sag-awards-*/',
		'/gift-guide/*',
	];

	/**
	 * Class Initialization
	 */
	protected function __construct() {

		$this->_wildcard_rules = array_unique( (array) $this->_wildcard_rules );

		$this->_setup_wildcard_rules();

		add_filter( 'allowed_redirect_hosts', [ $this, 'filter_allowed_redirect_hosts' ], 10, 2 );

		// Workaround issue with legacy redirect to on VIP environment not working properly for query parameters
		add_filter( 'wpcom_legacy_redirector_redirect_status', [ $this, 'filter_wpcom_legacy_redirector_redirect_status' ] );
	}

	/**
	 * Setup wildcard rules
	 */
	protected function _setup_wildcard_rules() {

		PMC_WPCOM_Legacy_Redirector_Extras::get_instance()->register_wildcard_rules( $this->_wildcard_rules );

	}

	/**
	 * Use filter to trigger add new wp_redirect filter
	 * @param $status
	 * @return mixed
	 */
	public function filter_wpcom_legacy_redirector_redirect_status( $status ) {
		add_filter( 'wp_redirect', [ $this, 'fix_redirect_uri' ] );
		return $status;
	}

	/**
	 * We're using this filter to fix &amp; encoded by legacy redirect on VIP environment
	 * Will need to remove these code once we have a solution fix at VIP end
	 * @see https://wordpressvip.zendesk.com/hc/en-us/requests/102864
	 * @param $uri
	 * @return string
	 */
	public function fix_redirect_uri( $uri ) {
		if ( strpos( $uri, '&amp;' ) ) {
			$uri = str_replace( '&amp;', '&', $uri );
		}
		remove_filter( 'wp_redirect', [ $this, 'fix_redirect_uri' ] );
		return $uri;
	}

	/**
	 * Filter to return a list of allow hosts for wp safe redirect
	 * @param $allowed_hosts The array of allowed hosts
	 * @param $host          The current location host name
	 * @return array
	 */
	public function filter_allowed_redirect_hosts( $allowed_hosts, $host ) : array {

		if ( ! empty( $host ) ) {
			$whitelist = [
				'docs.google.com',
				'tmt.knect365.com',
				'pmcvariety.files.wordpress.com',
				'events.variety.com',
				'uls.varietyultimate.com',
				'www.pubservice.com',
				'itunes.apple.com',
				'feature.variety.com',
				'web.cvent.com',
				'cafilm-encore.squarespace.com',
				'goo.gl',
				'cvent.me',
			];
			if ( in_array( $host, (array) $whitelist, true ) ) {
				$allowed_hosts[] = $host;
			}
		}

		return (array) $allowed_hosts;

	}

}
