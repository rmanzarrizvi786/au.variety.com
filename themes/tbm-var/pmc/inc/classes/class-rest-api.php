<?php
/**
 * PMC Core REST API setup.
 *
 * @package pmc-core-v2
 *
 * @since   2019-08-29
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class Rest_Api {

	use Singleton;

	/**
	 * Rest namespace.
	 *
	 * @var string
	 */
	public $rest_namespace = 'pmc_core/v1';

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Initializes hooks.
	 */
	protected function _setup_hooks() {

		add_filter( 'query_vars', [ $this, 'register_query_vars' ] );
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Registers soaps birthdays query vars.
	 *
	 * @param array $vars Current registred vars.
	 *
	 * @return array
	 */
	public function register_query_vars( $vars ) {

		$vars[] = 'pmc_core-module';

		return $vars;

	}

	/**
	 * Registers Rest end point for all widgets loaded async.
	 *
	 * @return void
	 */
	public function register_endpoints() {

		register_rest_route(
			$this->rest_namespace,
			'/pmc_core_modules/(?P<module>[a-zA-Z0-9-_]+)',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_module_data' ],
				'permission_callback' => '__return_true',
				'args'                => [],
			]
		);

	}

	/**
	 * Return HTML template for async module/widgets.
	 *
	 * @throws \Exception
	 */
	public function get_module_data( $request ) {

		$params = $request->get_params();

		$module = sanitize_text_field( $params['module'] );
		unset( $params['module'] );

		$results = apply_filters( 'pmc_core_rest_api_data', '', $module, $params );

		if ( empty( $results ) ) {
			$results   = [];
			$results[] = 'No callable function found';
		}

		$response = new \WP_REST_Response( $results );

		return $response;
	}

}

//EOF
