<?php
/**
 * This class is create to help rendering & verify nonce easily;
 *
 * Example:
 *
 * $nonce = \PMC\Global_Functions\Nonce::get_instance( 'my-action' );
 *
 * if ( $nonce->verify() ) {
 *    // Do something...
 * }
 *
 * echo '<form>'
 * $nonce->render();
 * // ...
 * echo '</form>'
 *
 */

namespace PMC\Global_Functions;

/**
 * Final Class Nonce, we don't want to extend this class any further
 * @package PMC\Global_Functions
 */
final class Nonce {
	private static $_instance = [];

	private $_action;
	private $_varname;

	/**
	 * Helper function to get/create once class and initialize with the given nonce action
	 * @param string $action
	 * @return Nonce
	 */
	public static function get_instance( string $action = '_pmc_nonce', string $varname = '_pmc_nonce' ) : self {
		// note: use self here instead of static to indicate we want instance of this class
		// matching return value and this class cannot be extended
		if ( ! isset( self::$_instance[ $action . $varname ] ) ) {
			self::$_instance[ $action . $varname ] = new self( $action, $varname );
		}
		return self::$_instance[ $action . $varname ];
	}

	protected function __construct( string $action, string $varname ) {
		$this->_action  = $action;
		$this->_varname = $varname;
	}

	/**
	 * verify the nonce
	 * @param bool $action If not provide, will use default action value
	 * @return bool
	 */
	public function verify() : bool {
		$nonce = \PMC::filter_input( INPUT_POST, $this->_varname, FILTER_SANITIZE_STRING );
		if ( empty( $nonce ) ) {
			$nonce = \PMC::filter_input( INPUT_GET, $this->_varname, FILTER_SANITIZE_STRING );
		}
		return (bool) wp_verify_nonce( $nonce, $this->_action );
	}

	/**
	 * Render the nonce field
	 * @param bool $refer  Render the referrer for referrer checking if needed, @see wp_nonce_field
	 */
	public function render( bool $refer = false ) : void {
		wp_nonce_field( $this->_action, $this->_varname, $refer, true );
	}

	/**
	 * Return the query string to be use in URL
	 * @return string
	 */
	public function get_query_string() : string {
		return build_query( [ $this->_varname => wp_create_nonce( $this->_action ) ] );
	}

	/**
	 * @see wp_create_nonce
	 * @return false|string
	 */
	public function get() {
		return wp_create_nonce( $this->_action );
	}

	/**
	 * Add nonce querystring to url
	 * @param string $url
	 * @return string
	 */
	public function get_url( string $url = '' ) : string {
		return add_query_arg( [ $this->_varname => $this->get() ], $url );
	}

}
