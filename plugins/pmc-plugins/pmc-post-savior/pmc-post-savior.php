<?php
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Post_Savior {

	use Singleton;

	private $_url = 'https://pistachio.pmc.com/pmc-post-savior.php';

	function __construct() {
		if( !is_admin()){
			return;
		}
		add_action( 'init', array( $this, 'enable_shutdown_hook' ) );
	}

	function enable_shutdown_hook() {

		if ( !is_admin() ) {
			return false;
		}

		if ( '/wp-admin/post.php' !== $_SERVER['REQUEST_URI'] && '/wp-admin/admin-ajax.php' !== $_SERVER['REQUEST_URI'] ) {
			return false;
		}

		// Must have $_POST['action'] set and it must be a valid value
		if ( !isset( $_POST['action'] ) || ( isset( $_POST['action'] ) && 'autosave' !== $_POST['action'] && 'editpost' !== $_POST['action'] ) ) {
			return false;
		}

		add_action( 'shutdown', array( $this, 'log_post_data' ) );

	}

	public function log_post_data() {

		$data = array(
			'domain'           => parse_url( get_site_url(), PHP_URL_HOST ),
			'data_center'      => defined( 'DATACENTER' ) ? DATACENTER : 'unknown',
			'action'           => $_POST['action'],
			'post_ID'          => $_POST['post_ID'],
			'post_title'       => $_POST['post_title'],
			'content'          => $_POST['content'],
			'post_name'        => $_POST['post_name'],
			'excerpt'          => $_POST['excerpt'],
			'post_author'      => $_POST['post_author'],
			'user_ID'          => $_POST['user_ID'],
			'useragent'        => $_SERVER['HTTP_USER_AGENT'],
			'logged_in_cookie' => "",
			'ip_address'       => $_SERVER['REMOTE_ADDR'],
			'post_saved_date'  => date( 'Y-m-d H:i:s' ),
			'http_referer'     => $_SERVER['HTTP_REFERER'],
			'category'         => json_encode( $_POST['post_category'] ),
			'taxonomy'         => json_encode( $_POST['tax_input'] ),
			'post_tags_new'    => json_encode( $_POST['newtag'] ),
			'comment_status'   => $_POST['comment_status'],
			'post_meta'        => json_encode( get_post_custom( absint( $_POST['post_ID'] ) ) ),
		);

		$cookie_username   = '';
		$cookie_expiration = '';
		if ( isset( $_COOKIE[LOGGED_IN_COOKIE] ) ) {

			$cookie_parts      = wp_parse_auth_cookie( $_COOKIE[LOGGED_IN_COOKIE], 'logged_in' );
			$cookie_expiration = new DateTime( null, new DateTimeZone( 'America/Los_Angeles' ) );
			$cookie_expiration->setTimestamp( $cookie_parts['expiration'] );
			$timezone_string = get_option( 'timezone_string' );
			$gmt_offset      = get_option( 'gmt_offset' );

			if ( !empty( $timezone_string ) ) {
				$cookie_expiration->setTimezone( new DateTimeZone( $timezone_string ) );
			} elseif ( !empty( $gmt_offset ) ) {
				$cookie_expiration->modify( $gmt_offset . ' hours' );
			}

			$cookie_username   = $cookie_parts['username'];
			$cookie_expiration = $cookie_expiration->format( "Y-m-d H:i:s T" );

		}

		if ( 'autosave' === $_POST['action'] ) {

			$data = array_merge( $data,
				array(
					 'cookie_expiration' => $cookie_expiration,
					 'cookie_username'   => $cookie_username,
					 'post_type'         => $_POST['post_type'],
					 'autosave'          => $_POST['autosave'],
				) );
		} elseif ( 'editpost' === $_POST['action'] ) {

			$data = array_merge( $data,
				array(
					 'cookie_expiration'         => $cookie_expiration,
					 'cookie_username'           => $cookie_username,
					 '_wp_http_referer'          => $_POST['_wp_http_referer'],
					 'originalaction'            => $_POST['originalaction'],
					 'original_post_status'      => $_POST['original_post_status'],
					 'referredby'                => $_POST['referredby'],
					 '_wp_original_http_referer' => $_POST['_wp_original_http_referer'],
					 'wp-preview'                => $_POST['wp-preview'],
					 'hidden_post_status'        => $_POST['hidden_post_status'],
					 'post_status'               => $_POST['post_status'],
					 'hidden_post_visibility'    => $_POST['hidden_post_visibility'],
					 'visibility'                => $_POST['visibility'],
					 'date'                      => $_POST['aa'] . '-' . $_POST['mm'] . '-' . $_POST['jj'] . ' ' . $_POST['hh'] . ':' . $_POST['mn'] . ':' . $_POST['ss'],
					 'hidden_date'               => $_POST['hidden_aa'] . '-' . $_POST['hidden_mm'] . '-' . $_POST['hidden_jj'] . ' ' . $_POST['hidden_hh'] . ':' . $_POST['hidden_mn'] . ':00',
					 'cur_date'                  => $_POST['cur_aa'] . '-' . $_POST['cur_mm'] . '-' . $_POST['cur_jj'] . ' ' . $_POST['cur_hh'] . ':' . $_POST['cur_mn'] . ':00',
					 'original_publish'          => $_POST['original_publish'],
					 'save'                      => $_POST['save'],
					 'advanced_view'             => $_POST['advanced_view'],
					 'post_author_override'      => ! empty( $_POST['post_author_override'] ) ? $_POST['post_author_override'] : '',
				)
			);
		}

		if( ! empty( $_POST['post_type'] ) && empty( $data['post_type'] ) ) {
			$data['post_type'] = sanitize_title( $_POST['post_type'] );
		}

		$this->http_post_request( $data );

	}

	function http_post_request( $data ) {

		wp_remote_post( $this->_url,
			array(
				 'method'      => 'POST',
				 'timeout'     => 2,
				 'redirection' => 5,
				 'httpversion' => '1.0',
				 'blocking'    => false,
				 'headers'     => array(),
				 'body'        => $data,
				 'cookies'     => array(),
				 'user-agent'  => $_SERVER['HTTP_USER_AGENT']
			)
		);
	}

}

PMC_Post_savior::get_instance();

//EOF
