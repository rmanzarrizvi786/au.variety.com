<?php
/**
 * Override settings and add new options to offical web stories plugin.
 *
 * @package pmc-web-stories
 */

namespace PMC\Web_Stories;

use \PMC\Global_Functions\Traits\Singleton;
use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use WP_Query;

/**
 * Class Web_Stories
 */
class User_Controller extends WP_REST_Controller {

	use Singleton;

	/**
	 * Rest namespace.
	 *
	 * @var string
	 */
	public $rest_namespace = 'pmc-web-stories/v1';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup Hooks
	 */
	protected function _setup_hooks() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Registers Rest end point for all widgets loaded async.
	 *
	 * @return void
	 */
	public function register_endpoints() {

		register_rest_route(
			$this->rest_namespace,
			'/users',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => [ $this, 'get_author_items_permissions_check' ],
					'callback'            => [ $this, 'get_items' ],
					'args'                => [
						'context' => $this->get_context_param( [ 'default' => 'view' ] ),
					],
				],
			]
		);

	}

	/**
	 * Checks if current user can get guest author endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_author_items_permissions_check() {
		if ( ! current_user_can( 'edit_web-stories' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to make proxied embed requests.', 'pmc-web-stories' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}

	/**
	 * Retrieves the current user.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		$posts = $this->get_guest_author_posts( $request );

		$user_array = [];

		foreach ( $posts as $post ) {
			$linked_account = get_post_meta( $post->ID, 'cap-linked_account', true );

			if ( empty( $linked_account ) ) {
				continue;
			}

			$user_obj = get_user_by( 'login', $linked_account );

			if ( false === $user_obj ) {
				continue;
			}

			$user_array[] = [
				'id'   => $user_obj->ID,
				'name' => $user_obj->user_nicename,
				'slug' => $user_obj->user_login,
			];
		}
		$response = rest_ensure_response( $user_array );

		return $response;
	}

	/**
	 * Gets the guest author posts array.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	protected function get_guest_author_posts( $request ) {

		$posts_per_page = $request->get_param( 'per_page' ) ?? 50;

		$query = new WP_Query(
			[
				'post_type'      => 'guest-author',
				'posts_per_page' => $posts_per_page,
				'post_status'    => 'any',
			]
		);

		return $query->posts;
	}

}
