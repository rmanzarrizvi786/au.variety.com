<?php

namespace PMC\Google_OAuth2;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class OAuth2 {

	use Singleton;

	protected $_connect_callback_url       = 'oauth2callback_google';
	protected $_disconnect_callback_option = 'pmc-disconnect-google-oauth2';
	protected $_google_auth_url            = 'https://accounts.google.com/o/oauth2/auth';
	protected $_google_token_url           = 'https://accounts.google.com/o/oauth2/token';
	protected $_capability                 = 'manage_options';
	protected $_admin_settings_page        = 'pmc_google_oauth2';
	protected $_google_scope_requested_url = 'https://www.googleapis.com/auth/';

	protected function __construct() {
		add_action( 'init', array( $this, 'handle_google_auth_callback' ) );
		add_action( 'init', array( $this, 'handle_google_disconnect_callback' ) );
		add_action( 'admin_menu', array( $this, 'action_admin_menu_late' ), 11 ); // Add to the end of the stack
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'filter_pmc_global_cheezcap_options' ) );
	}

	/**
	 * Global CheezCap for Google OAuth2 credentials.
	 *
	 * @param array $cheezcap_options
	 *
	 * @return array
	 */
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {
		$cheezcap_options[] = new \CheezCapTextOption(
			__( 'Google OAuth2 Client ID', 'pmc-plugins' ),
			__( 'Client ID for Google OAuth2 connections.', 'pmc-plugins' ),
			'pmc_google_oauth2_client_id',
			''
		);

		$cheezcap_options[] = new \CheezCapTextOption(
			__( 'Google OAuth2 Client Secret', 'pmc-plugins' ),
			__( 'Client Secret for Google OAuth2 connections.', 'pmc-plugins' ),
			'pmc_google_oauth2_client_secret',
			''
		);

		return $cheezcap_options;
	}

	/**
	 * Gets Client ID.
	 * @return mixed|void
	 */
	public function get_client_id() {
		return get_option( 'cap_pmc_google_oauth2_client_id', '' );
	}

	/**
	 * Get Client Secret.
	 * @return mixed|void
	 */
	public function get_client_secret() {
		return get_option( 'cap_pmc_google_oauth2_client_secret', '' );
	}

	/**
	 * Builds the redirect_uri to send to Google with the auth callback request.
	 *
	 * Because Google disallows .dev and .local domains as callback urls, we
	 * replace those with localhost here. In test environments, the developer is
	 * responsible for building a local pass-through redirect from localhost to
	 * the domain of your dev site. Note: it also works to just let Google
	 * redirect to 'localhost', then changing the domain in your address bar and
	 * passing the request on yourself.
	 */
	protected function _get_oauth_callback_redirect_uri() {
		$query_args = array(
			'page' => rawurlencode( $this->_connect_callback_url ),
		);

		$redirect_uri = add_query_arg( $query_args, admin_url( 'options-general.php' ) );

		$pattern = '/(http(s)?:\/\/)(.*)\.vip\.(local|dev)/';
		$replacement = '$1localhost';
		return preg_replace( $pattern, $replacement, $redirect_uri );
	}

	/**
	 * Is the current access token expired?
	 *
	 * @param $service
	 * @return bool
	 */
	private function _is_google_access_token_expired( $service ) {
		$access_details = $this->get_google_auth_details( $service );

		if ( empty( $access_details['expire_time'] ) || $access_details['expire_time'] < time() + 60 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Refresh the Google access token
	 *
	 * @param $service
	 * @return bool|\WP_Error
	 */
	private function _refresh_google_access_token( $service ) {
		$access_details = $this->get_google_auth_details( $service );

		if ( empty ( $access_details['refresh_token'] ) ) {
			return false;
		}

		// Fetch the actual token from the Google.
		$response = wp_remote_post( esc_url_raw( $this->_google_token_url ), array( 'body' => array(
			'client_id'        => $this->get_client_id(),
			'client_secret'    => $this->get_client_secret(),
			'grant_type'       => 'refresh_token',
			'refresh_token'    => $access_details['refresh_token'],
		) ) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new \WP_Error( 'oauth-refresh', esc_html__( 'Error fetching oauth2 token from Google', 'pmc-plugins' ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $data->access_token ) ) {
			return new \WP_Error( 'oauth-refresh',
				esc_html__( 'Error fetching oauth2 token from Google', 'pmc-plugins' )
			);
		}

		pmc_update_option( $service, array(
			'access_token'        => sanitize_text_field( $data->access_token ),
			'expire_time'         => time() + (int) $data->expires_in,
			'refresh_token'       => $access_details['refresh_token'],
			'original_response'   => wp_remote_retrieve_body( $response ),
		) );

		return true;
	}

	/**
	 * Add to Settings menu.
	 */
	public function action_admin_menu_late() {
		add_submenu_page( 'options-general.php', esc_html__( 'Google OAuth2', 'pmc-plugins' ), esc_html__( 'Google OAuth2', 'pmc-plugins' ), $this->_capability, $this->_admin_settings_page, array( $this, 'handle_settings_page' ) );
	}

	/**
	 * Build the disconnect URL to link to, which begins the de-auth process.
	 *
	 * @param $key
	 * @return string
	 */
	public function get_disconnect_callback_url( $key ) {
		$query_args = array(
			'action' => $this->_disconnect_callback_option,
			'service' => $key,
			'nonce' => wp_create_nonce( $this->_disconnect_callback_option ),
		);

		return add_query_arg( $query_args, admin_url( 'index.php' ) );
	}

	/**
	 * Handle a request to disconnect Google auth
	 */
	public function handle_google_disconnect_callback() {
		if ( empty( $_GET['action'] ) || $this->_disconnect_callback_option !== $_GET['action'] ) {
			return;
		}

		if ( empty( $_GET['service'] ) ) {
			return;
		}

		$service = sanitize_text_field( $_GET['service'] );

		if ( ! current_user_can( $this->_capability ) || ! wp_verify_nonce( $_GET['nonce'], $this->_disconnect_callback_option ) ) {
			wp_die( esc_html__( "You shouldn't be doing this, sorry.", 'pmc-plugins' ) );
		}

		pmc_update_option( $service, '' );

		$query_args = array(
			'page'    => $this->_admin_settings_page,
			'success' => 'google-disconnect',
		);

		$redirect_url = add_query_arg( $query_args, admin_url( 'admin.php' ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render Google OAuth2 settings page.
	 */
	public function handle_settings_page() {
		echo PMC::render_template( PMC_GOOGLE_OAUTH2_ROOT . '/assets/templates/settings.php', array(
			'controller' => $this,
			'services'   =>	apply_filters( 'pmc-google-oauth2', array() ),
		) );
	}

	/**
	 * Handle authentication callback from Google
	 */
	public function handle_google_auth_callback() {
		if ( empty( $_SERVER['REQUEST_URI'] )
			|| false === stripos( $_SERVER['REQUEST_URI'], $this->_connect_callback_url ) ) {
			return;
		}

		if ( empty( $_GET['code'] ) ) {
			wp_die( esc_html__( 'Invalid authorization code.', 'pmc-plugins' ) );
		}

		$services = apply_filters( 'pmc-google-oauth2', array() );
		$service = sanitize_text_field( $_GET['state'] );

		if ( empty( $services[ $service ] ) ) {
			wp_die( esc_html__( 'This service does not exist.', 'pmc-plugins' ) );
		}

		if ( ! current_user_can( $this->_capability ) ) {
			wp_die( esc_html__( 'You don\'t have access to perform this action. Please contact an administrator.', 'pmc-plugins' ) );
		}

		$response = wp_safe_remote_post( $this->_google_token_url, array(
			'body' => array(
				'code'          => sanitize_text_field( $_GET['code'] ),
				'client_id'     => $this->get_client_id(),
				'client_secret' => $this->get_client_secret(),
				'redirect_uri'  => $this->_get_oauth_callback_redirect_uri(),
				'grant_type'    => 'authorization_code',
			)
		) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			wp_die( esc_html__( 'Error fetching oauth2 token from Google', 'pmc-plugins' ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );
		if ( empty( $data->access_token ) ) {
			wp_die( esc_html__( 'Error fetching oauth2 token from Google', 'pmc-plugins' ) );
		}

		$refresh_token = ! empty( $data->refresh_token ) ? sanitize_text_field( $data->refresh_token ) : '';

		pmc_update_option( $service, array(
			'access_token'        => sanitize_text_field( $data->access_token ),
			'expire_time'         => time() + (int) $data->expires_in,
			'refresh_token'       => $refresh_token,
			'original_response'   => wp_remote_retrieve_body( $response ),
		) );

		$query_args = array(
			'success'       => 'google-connect',
			'page'          => $this->_admin_settings_page,
		);
		$redirect_url = add_query_arg( $query_args, admin_url( 'admin.php' ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Get the token.
	 *
	 * @param $service
	 *
	 * @return bool|mixed
	 */
	public function get_current_google_token( $service ) {
		if ( ! $this->get_google_auth_details( $service ) ) {
			return false;
		}

		if ( $this->_is_google_access_token_expired( $service ) ) {
			$access_refreshed = $this->_refresh_google_access_token( $service );

			if ( ! $access_refreshed || is_wp_error( $access_refreshed ) ) {
				return false;
			}
		}

		return $this->get_google_auth_details( $service );
	}

	/**
	 * Build the auth URL to link to, which begins the auth process.
	 *
	 * @param $service
	 * @param $scope
	 *
	 * @return bool|string
	 */
	public function get_auth_callback_url( $service, $scope ) {
		$scope = sanitize_text_field( $scope );

		if ( empty( $scope ) || ! is_string( $scope ) ) {
			return false;
		}

		$query_args = array(
			'client_id' => $this->get_client_id(),
			'redirect_uri' => $this->_get_oauth_callback_redirect_uri(),
			'response_type' => 'code',
			'access_type' => 'offline',
			'approval_prompt' => 'force',
			'scope' => urlencode( $this->_google_scope_requested_url . $scope ),
			'state' => sanitize_text_field( $service ),
		);

		return add_query_arg( $query_args, $this->_google_auth_url );
	}

	/**
	 * Get our requisite details for authenticating with Google
	 *
	 * @param $service
	 * @return bool|mixed
	 */
	public function get_google_auth_details( $service ) {
		$service = sanitize_text_field( $service );

		if ( empty ( $service ) || ! is_string( $service ) ) {
			return false;
		}

		return pmc_get_option( $service );
	}

}

// EOF
