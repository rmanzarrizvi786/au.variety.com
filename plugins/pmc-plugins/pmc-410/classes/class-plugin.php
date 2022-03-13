<?php

namespace PMC\PMC_410;

use PMC\Global_Functions\Traits\Singleton;
/**
 * Class to set 410 status for any url
 *
 */
class Plugin {

	use Singleton;

	/**
	 * Construct method for current class.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'wpcom_legacy_redirector_redirect_status', [ $this, 'filter_wpcom_legacy_redirector_redirect_status' ], 10, 2 );

		/**
		 * Actions
		 */
		//Supporting existing functionality if url is supplied in theme via filter
		add_action( 'wp', array( $this, 'maybe_set_http_status_410' ) );

	}

	/**
	 * Set 410 status if url ends with pmc-410
	 *
	 * @param $status
	 * @param $url
	 *
	 * @return mixed
	 */
	public function filter_wpcom_legacy_redirector_redirect_status( $status, $url ) {

		if ( ! empty( $url ) && method_exists( '\WPCOM_Legacy_Redirector', 'get_redirect_uri' ) ) {

			$url = apply_filters( 'wpcom_legacy_redirector_request_path', $url );

			$redirect_uri = \WPCOM_Legacy_Redirector::get_redirect_uri( $url );

			if ( '/pmc-410' === $redirect_uri ) {

				//Now 410 the requested page
				$this->_set_410_response();

			}
		}

		return $status;
	}

	/**
	 * To set 410 status to specific url.
	 *
	 * @hook parse_request
	 *
	 * @return void
	 */
	public function maybe_set_http_status_410() {

		/**
		 * To get list of urls for which need to set 410 status.
		 * Url should be in array key without site domain and trimmed with '/'
		 * And value should be anything
		 * i.g. array( 'url/components' => 1 )
		 */
		$removed_paths = apply_filters( 'pmc_http_status_410_urls', array() );

		if ( empty( $removed_paths ) ) {
			return;
		}

		$request_uri = $this->filter_input( INPUT_SERVER, 'REQUEST_URI' );
		$trim_uri    = trim( wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );

		if ( isset( $removed_paths[ $trim_uri ] ) ) {

			$this->_set_410_response();

		}

	}

	/**
	 * @param $type
	 * @param $name
	 *
	 * @return mixed
	 */
	public function filter_input( $type, $name ) {
		return filter_input( $type, $name );
	}

	/**
	 * Set page to 410
	 *
	 */
	private function _set_410_response() {

		do_action( 'pmc_410_response' ); // you can use this to customise the response message.
		wp_die(
			'The requested page has been removed.',
			'',
			array(
				'response' => 410,
			)
		);
	}

}
