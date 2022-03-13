<?php
/**
 * Manage REST API endpoints for Gutenberg.
 *
 * @package pmc-exacttarget
 */
namespace PMC\Exacttarget\WP_REST_API;

use Exact_Target;
use PMC\Exacttarget\Config;
use PMC\Global_Functions\Traits\Singleton;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Manager.
 */
class Manager {
	use Singleton;

	/**
	 * Link added to REST responses, indicating user can manage ET.
	 */
	public const SUPPORT_LINK = 'pmc:exact-target-supported';

	/**
	 * WP_REST_API constructor.
	 */
	protected function __construct() {
		$this->_load_endpoints();
		$this->_setup_hooks();
	}

	/**
	 * Instantiate endpoints.
	 */
	protected function _load_endpoints(): void {
		Endpoint_BNAs::get_instance();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'rest_api_init', [ $this, 'modify_responses' ] );
	}

	/**
	 * Register callback to add links to responses for supported objects.
	 */
	public function modify_responses(): void {
		if ( ! Exact_Target::is_active() ) {
			return;
		}

		if (
			! Endpoint_BNAs::get_instance()->check_endpoint_read_permissions()
		) {
			return;
		}

		$post_types = Config::get_instance()->get(
			'supported_post_types'
		);

		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $post_type ) {
			add_filter(
				'rest_prepare_' . $post_type,
				[ $this, 'add_action_links' ],
				10,
				3
			);
		}
	}

	/**
	 * Add indicator that current user can manage newsletter settings.
	 *
	 * @param WP_REST_Response $response Response object.
	 * @param WP_Post          $post     Post object.
	 * @param WP_REST_Request  $request  Request object.
	 * @return WP_REST_Response
	 */
	public function add_action_links(
		WP_REST_Response $response,
		WP_Post $post,
		WP_REST_Request $request
	): WP_REST_Response {
		if (
			! isset( $request['context'] )
			|| 'edit' !== $request['context']
		) {
			return $response;
		}

		if ( ! use_block_editor_for_post( $post ) ) {
			return $response;
		}

		$self = $response->get_links()['self'] ?? null;

		if ( ! is_array( $self ) ) {
			return $response;
		}

		$self = array_shift( $self );

		$response->add_link( static::SUPPORT_LINK, $self['href'] );

		return $response;
	}
}
