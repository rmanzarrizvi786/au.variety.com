<?php
/**
 * JW Player REST API endpoints.
 *
 * @package pmc-video-player
 */

namespace PMC\Video_Player\REST_API;

use JWPlayer_api;
use PMC\Global_Functions\Traits\Singleton;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class JW_Player.
 */
class JW_Player {
	use Singleton;

	/**
	 * REST API namespace.
	 */
	public const NAMESPACE = 'pmc/video-player/jw-player/v1';

	/**
	 * Message returned to user when the JW API is not configured.
	 *
	 * @var string
	 */
	protected $error_text_api_unavailable;

	/**
	 * HTTP status code returned when JW API is not configured.
	 *
	 * @var int
	 */
	protected $error_code_api_unavailable = 503;

	/**
	 * HTTP status code returned when JW API returns an error.
	 *
	 * @var int
	 */
	protected $error_code_invalid_request = 400;

	/**
	 * JWPlayer constructor.
	 */
	protected function __construct() {
		$this->error_text_api_unavailable = __(
			'JW Player API is unavailable',
			'pmc-video-player'
		);

		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Register REST endpoints.
	 */
	public function register_endpoints(): void {
		if ( ! $this->_should_load() ) {
			// Cannot unload pmc-gutenberg plugin.
			return; // @codeCoverageIgnore
		}

		register_rest_route(
			static::NAMESPACE,
			'import',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'do_import' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'args'                => [
					'title' => [
						'required' => false,
						'type'     => 'string',
					],
					'url'   => [
						'required' => true,
						'type'     => 'string',
					],
				],
			]
		);

		register_rest_route(
			static::NAMESPACE,
			'players',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_players' ],
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);

		register_rest_route(
			static::NAMESPACE,
			'search',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'do_search' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'args'                => [
					'query' => [
						'required' => true,
						'type'     => 'string',
					],
					'type'  => [
						'required' => true,
						'type'     => 'string',
					],
				],
			]
		);

		register_rest_route(
			static::NAMESPACE,
			'upload',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'get_upload_data' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'args'                => [
					'filename' => [
						'required' => true,
						'type'     => 'string',
					],
					'title'    => [
						'required' => false,
						'type'     => 'string',
					],
				],
			]
		);
	}

	/**
	 * Check if user has necessary permissions to interact with JW Player.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Import a video into JW Player from its URL.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function do_import( WP_REST_Request $request ) {
		$jw_api = $this->_get_jw_api_instance();

		if ( null === $jw_api ) {
			return $this->_create_error(
				$this->error_text_api_unavailable,
				$this->error_code_api_unavailable
			);
		}

		$url = $request->get_param( 'url' );

		$title = $request->get_param( 'title' );
		if ( empty( $title ) ) {
			$title = basename( $url );
		}

		$api_params = [
			'sourcetype'   => 'url',
			'sourceurl'    => $url,
			'sourceformat' => 'mp4',
			'title'        => $title,
		];

		// Try to set the `sourceformat` using JWP's map.
		if ( defined( 'JWPLAYER_SOURCE_FORMAT_EXTENSIONS' ) ) {
			$extension         = pathinfo( $url, PATHINFO_EXTENSION );
			$jw_extensions_map = json_decode(
				JWPLAYER_SOURCE_FORMAT_EXTENSIONS,
				true
			);

			foreach ( $jw_extensions_map as $format => $extensions ) {
				if ( in_array( $extension, (array) $extensions, true ) ) {
					$api_params['sourceformat'] = $format;
					break;
				}
			}
		}

		$response = $jw_api->call( '/videos/create', $api_params );

		if (
			! is_array( $response )
			|| ! isset( $response['status'] )
			|| 'ok' !== $response['status']
		) {
			return $this->_create_error(
				sprintf(
					/* translators: 1. Error text from JW Player API. */
					__(
						'The video could not be imported due to an error: %1$s.',
						'pmc-video-player'
					),
					$response['message']
				),
				$this->error_code_invalid_request
			);
		}

		return rest_ensure_response( $response['video'] );
	}

	/**
	 * List players.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_players() {
		$jw_api = $this->_get_jw_api_instance();

		if ( null === $jw_api ) {
			return $this->_create_error(
				$this->error_text_api_unavailable,
				$this->error_code_api_unavailable
			);
		}

		$response = $jw_api->call( '/players/list' );

		if (
			! is_array( $response )
			|| ! isset( $response['status'] )
			|| 'ok' !== $response['status']
		) {
			return $this->_create_error(
				sprintf(
					/* translators: 1. Error text from JW Player API. */
					__(
						'Players could not be retrieved due to an error: %1$s.',
						'pmc-video-player'
					),
					$response['message']
				),
				$this->error_code_invalid_request
			);
		}

		$trimmed_response = [
			[
				'label' => _x(
					'Default',
					'Default player for JW Player videos.',
					'pmc-video-player'
				),
				'value' => '',
			],
		];

		foreach ( $response['players'] as $player ) {
			$trimmed_response[] = [
				'label' => $player['name'],
				'value' => $player['key'],
			];
		}

		return rest_ensure_response( $trimmed_response );
	}

	/**
	 * Perform search request. If no term is provided, return recent entries.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function do_search( WP_REST_Request $request ) {
		$jw_api = $this->_get_jw_api_instance();

		if ( null === $jw_api ) {
			return $this->_create_error(
				$this->error_text_api_unavailable,
				$this->error_code_api_unavailable
			);
		}

		$query = $request->get_param( 'query' );
		$type  = $request->get_param( 'type' );

		$api_method = 'video' === $type ? '/videos/list' : '/channels/list';

		$api_params = [
			'result_limit' => 5,
		];

		// Channel queries do not support ordering by date.
		if ( 'video' === $type ) {
			$api_params['order_by'] = 'date:desc';
		}

		if ( ! empty( $query ) ) {
			$api_params['text'] = $query;
		}

		$response = $jw_api->call( $api_method, $api_params );

		if (
			! is_array( $response )
			|| ! isset( $response['status'] )
			|| 'ok' !== $response['status']
		) {
			return $this->_create_error(
				sprintf(
					/* translators: 1. Error text from JW Player API. */
					__(
						'The search request failed due to an error: %1$s.',
						'pmc-video-player'
					),
					$response['message']
				),
				$this->error_code_invalid_request
			);
		}

		$results         = 'video' === $type ? $response['videos'] : $response['channels'];
		$trimmed_results = [];

		foreach ( $results as $key => $result ) {
			$trimmed_results[ $key ] = [
				'key'   => $result['key'],
				'title' => $result['title'],
			];
		}

		return rest_ensure_response( $trimmed_results );
	}

	/**
	 * Get data needed to upload a video to JW Player.
	 *
	 * Upload itself happens client-side using modern browser APIs.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_upload_data( WP_REST_Request $request ) {
		$jw_api = $this->_get_jw_api_instance();

		if ( null === $jw_api ) {
			return $this->_create_error(
				$this->error_text_api_unavailable,
				$this->error_code_api_unavailable
			);
		}

		$title = $request->get_param( 'title' );
		if ( empty( $title ) ) {
			$title = basename( $request->get_param( 'filename' ) );
		}

		$api_params = [
			'resumable' => true,
			'title'     => $title,
		];

		$response = $jw_api->call( '/videos/create', $api_params );

		if (
			! is_array( $response )
			|| ! isset( $response['status'] )
			|| 'ok' !== $response['status']
		) {
			return $this->_create_error(
				sprintf(
					/* translators: 1. Error text from JW Player API. */
					__(
						'The video could not be uploaded due to an error: %1$s.',
						'pmc-video-player'
					),
					$response['message']
				),
				$this->error_code_invalid_request
			);
		}

		$response['url'] = sprintf(
			'https://%1$s%2$s',
			$response['link']['address'],
			$response['link']['path']
		);
		$response['url'] = add_query_arg( $response['link']['query'], $response['url'] );
		$response['url'] = add_query_arg( 'api_format', 'json', $response['url'] );

		unset( $response['link'], $response['rate_limit'], $response['status'] );

		return rest_ensure_response( $response );
	}

	/**
	 * Determine if plugin's requirements are met.
	 *
	 * @codeCoverageIgnore Cannot unload pmc-gutenberg plugin.
	 *
	 * @return bool
	 */
	protected function _should_load(): bool {
		if ( ! function_exists( 'wpcom_vip_plugin_is_loaded' ) ) {
			return false;
		}

		if (
			! wpcom_vip_plugin_is_loaded(
				'pmc-plugins/pmc-gutenberg/pmc-gutenberg.php'
			)
		) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve instance of JW Player API class.
	 *
	 * @codeCoverageIgnore Cannot unload JW Player plugin.
	 *
	 * @return JWPlayer_api|null
	 */
	protected function _get_jw_api_instance(): ?JWPlayer_api {
		$instance = null;

		if ( function_exists( 'jwplayer_api_get_instance' ) ) {
			$instance = jwplayer_api_get_instance();
		} elseif ( function_exists( 'jwplayer_get_api_instance' ) ) {
			$instance = jwplayer_get_api_instance();
		}

		return $instance;
	}

	/**
	 * Return a standardized error.
	 *
	 * @param string $text Error text.
	 * @param int    $code Status code.
	 * @return WP_Error
	 */
	protected function _create_error( string $text, int $code ): WP_Error {
		return new WP_Error( $code, $text );
	}
}
