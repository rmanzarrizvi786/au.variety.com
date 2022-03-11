<?php

/*

Impelement ajax action: variety_authentication
	cmd: verify-credential | verify-session | remove-session | get-credential | get-protected-data

*/

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Authentication
{

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{
		add_action('admin_init', array($this, 'do_admin_init'));
		add_action('init', array($this, 'do_init'));
	}

	public function do_admin_init()
	{
		add_action('wp_ajax_variety_authentication', array($this, 'do_ajax_action'));
		add_action('wp_ajax_nopriv_variety_authentication', array($this, 'do_ajax_action'));
	}

	public function do_init()
	{
		add_action('wp_enqueue_scripts', array($this, 'do_enqueue_scripts'), 11);
	}

	public function do_enqueue_scripts()
	{

		if (false === apply_filters('variety_authentication', true)) {
			return;
		}

		$url_prefix = get_stylesheet_directory_uri();

		wp_enqueue_script(
			'pmc-core-jq-cookie',
			$url_prefix . '/plugins/variety-authentication/js/jquery-cookie.js',
			['jquery']
		);
		wp_enqueue_script(
			'pmc-auth',
			$url_prefix . '/plugins/variety-authentication/js/auth-redirect.js',
			['jquery', 'pmc-core-jq-cookie']
		);
		wp_register_script(
			'variety_authentication_scripts',
			$url_prefix . '/plugins/variety-authentication/js/variety-authentication.js',
			['jquery', 'pmc-core-jq-cookie', 'pmc-uls']
		);
		/* wp_localize_script(
			'variety_authentication_scripts',
			'variety_authentication_object',
			[
				'ajax_url'              => admin_url( 'admin-ajax.php', 'https' ),
				'bypass_authentication' => intval( cheezcap_get_option( 'enable_bypass_authentication', false ) ),
				'ajax_nonce'            => wp_create_nonce( 'variety_authentication_nonce' ),
			]
		); */
		wp_enqueue_script('variety_authentication_scripts');
	}

	public function do_ajax_action()
	{

		$data = false;

		check_ajax_referer('variety_authentication_nonce', 'security');

		$args = wp_parse_args(
			$_POST,
			array(
				'cmd'        => '',
				'username'   => '',
				'password'   => '',
				'persist'    => 0,
				'session_id' => '',
				'data_type'  => false,
				'data_args'  => false,
			)
		); // input var ok

		switch ($args['cmd']) {
			case 'get-protected-data':
				if (
					0 === intval(cheezcap_get_option('enable_ajax_authentication', false))
					|| 1 === intval(cheezcap_get_option('enable_bypass_authentication', false))
					|| \PMC\Uls\Session::get_instance()->can_access('vy-digital')
				) {

					$results = apply_filters('variety_get_protected_data_' . $args['data_type'], $results, $args['data_args']);
					$results = apply_filters('variety_get_protected_data', $results, $args['data_type'], $args['data_args']);
					$data    = array(
						'status'  => 1,
						'results' => $results,
					);
				} else {
					$data = array(
						'status' => 0,
						'error'  => 'Access denied',
					);
				}
			default:
				break;
		}

		ob_clean();
		header('Content-Type: application/json');
		echo wp_json_encode($data);
		unset($data);
		wp_die();
	}
}

variety_authentication::get_instance();
