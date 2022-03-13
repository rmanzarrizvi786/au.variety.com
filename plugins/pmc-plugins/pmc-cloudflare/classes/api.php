<?php namespace PMC\CloudFlare;
/**
 * The main class for the plugin
 * @since 2017-01-03 Hau Vong
 */

use \PMC_Cheezcap;
use \PMC\Global_Functions\Traits\Singleton;

class Api {

	use Singleton;

	protected function __construct() {
		add_filter( 'pmc_cheezcap_groups', array( $this, 'set_cheezcap_group' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ), 99 );
	}

	public function admin_init() {

		$this->cf_auto_purge_cache = PMC_Cheezcap::get_instance()->get_option('pmc_cf_auto_purge_cache');
		$this->cf_zone_id          = PMC_Cheezcap::get_instance()->get_option('pmc_cf_api_zone');
		$this->cf_email            = PMC_Cheezcap::get_instance()->get_option('pmc_cf_api_email');
		$this->cf_api_key          = PMC_Cheezcap::get_instance()->get_option('pmc_cf_api_key');
		if ( 'enabled' !== $this->cf_auto_purge_cache || empty( $this->cf_zone_id ) || empty( $this->cf_email ) || empty( $this->cf_api_key ) ) {
			return false;
		}

		// post is deleted
		add_action( 'deleted_post', array( $this, 'purge_post_cache' ) );

		// this action take care of all post activities
		add_action( 'save_post', array( $this, 'purge_post_cache' ) );

		// we need this action to check for permalink changes so we can purge the old url from cache
		add_action( 'post_updated', array( $this, 'purge_post_cache' ), 10, 3 );

		$this->validate_cheezcap();
	}

	/**
	 * Helper function to validate cheezcap setting
	 * @return [type] [description]
	 */
	public function validate_cheezcap() {

		if ( ! isset( $GLOBALS['cap'] ) ) {
			return;
		}

		$themeslug = $GLOBALS['cap']->get_setting( 'themeslug' );
		if ( $GLOBALS['plugin_page'] !== $themeslug ) {
			return;
		}

		$success = isset( $_GET['success'] ) ? strtolower( $_GET['success'] ) : '';
		if ( 'update' !== $success ) {
			return;
		}

		// validate the api & related information by retrieving the zone info
		$zone_info = $this->get_zone_info();

		if ( ! empty( $zone_info ) && empty( $zone_info->success ) ) {
			add_action( 'admin_notices', function() {
				echo'<div class="notice notice-error"><p>Cannot authenticate <a href="">CloudFlare API Key</p></div>';
			} );
		}

	}

	public function set_cheezcap_group( $cheezcap_groups = [] ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		// Needed for compatibility with BGR_CheezCap
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';
		}

		$cheezcap_options = [

			new \CheezCapDropdownOption(
				'Enable CloudFlare Auto Purge Cache',
				'When enabled, post updated and/or published will trigger CloudFlare to purge cache',
				'pmc_cf_auto_purge_cache',
				array( 'disabled', 'enabled' ),
				1, // 2nd option => Enabled
				array( 'Disabled', 'Enabled' )
			),

			new \CheezCapTextOption(
				'CloudFlare API Email',
				'The email to identify Cloudflare account',
				'pmc_cf_api_email',
				'dist.dev@pmc.com'
			),

			new \CheezCapTextOption(
				'CloudFlare API Key',
				'The Cloudflare account Global API key',
				'pmc_cf_api_key',
				''
			),

			new \CheezCapTextOption(
				'CloudFlare Zone ID',
				'The Cloudflare account zone ID',
				'pmc_cf_api_zone',
				''
			),

		];

		$cheezcap_groups[] = new $cheezcap_group_class( "CloudFlare", "pmc_cloudflare_group", $cheezcap_options );

		return $cheezcap_groups;

	}

	/**
	 * Function to fire when post is deleted / updated
	 * @param  mixed $post The post ID / Object to be purge
	 */
	public function purge_post_cache( $post, $post_after = false, $post_before = false ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return;
		}

		// add check to prevent multiple cache purge on the same post
		// probably won't happen in admin ui, just a precaution
		if ( ! empty( $this->purge_cache_called[ $post->ID ] ) ) {
			return;
		}

		// ignore auto save or revision
		if ( wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) {
			return;
		}

		// only process post that is published or deleted
		$post_status = get_post_status( $post );
		if ( ! in_array( $post_status, [ 'publish', 'trash' ], true ) ) {
			return;
		}

		// alwasy purge home page
		$purge_urls = [ get_home_url() ];

		// if permalink available, purge the link
		if ( $url = get_permalink( $post ) ) {
			$purge_urls[] = $url;
		}

		if ( is_object( $post_before ) ) {
			if ( $url = get_permalink( $post_before ) ) {
				if ( ! in_array( $url, $purge_urls ) ) {
					$purge_urls[] = $url;
				}
			}
		}

		if ( is_object( $post_after ) ) {
			if ( $url = get_permalink( $post_after ) ) {
				if ( ! in_array( $url, $purge_urls ) ) {
					$purge_urls[] = $url;
				}
			}
		}

		// allow theme to add related urls to the cache purge, eg. vertical, category, tag, etc..
		$purge_urls = apply_filters( 'pmc_cf_purge_cached_urls', $purge_urls, $post );

		if ( empty( $purge_urls ) || ! is_array( $purge_urls ) ) {
			return;
		}

		$this->purge_cache_called[ $post->ID ] = true;
		return $this->purge_cache( $purge_urls );

	}

	/**
	 * Helper function to check api and validate api key and related information
	 * @return [type] [description]
	 */
	public function get_zone_info() {
		if ( empty( $this->cf_zone_id ) || empty( $this->cf_email ) || empty( $this->cf_api_key ) ) {
			return false;
		}

		$url = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s', $this->cf_zone_id );

		$args = [
			'headers' => [
			'X-Auth-Email' => $this->cf_email,
			'X-Auth-Key'   => $this->cf_api_key,
			'Content-Type' => 'application/json',
			],
		];

		$response = wp_safe_remote_get( $url, $args );
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Purge the set of urls from CloudFlare cache
	 * @param  [type] $urls [description]
	 * @return [type]       [description]
	 */
	public function purge_cache( $urls ) {
		if ( empty( $this->cf_zone_id ) || empty( $this->cf_email ) || empty( $this->cf_api_key ) ) {
			return false;
		}

		$data = [ 'files' => is_array( $urls ) ? $urls : [ $urls ] ];

		$url = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/purge_cache', $this->cf_zone_id );
		$args = [
			'method'  => 'DELETE',
			'headers' => [
				'X-Auth-Email' => $this->cf_email,
				'X-Auth-Key'   => $this->cf_api_key,
				'Content-Type' => 'application/json',
			],
			'body' => json_encode( $data ),
		];

		// IMPORTANT: validate to make sure we have the correct end point url before proceeding
		// DO NOT send zone delete request: DELETE /zones/:identifier
		if ( '/purge_cache' !== substr( $url, -12 ) ) {
			return false;
		}
		$response = wp_safe_remote_request( $url, $args );
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

}

// EOF

