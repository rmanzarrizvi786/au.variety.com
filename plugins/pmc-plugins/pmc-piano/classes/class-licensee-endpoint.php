<?php
/**
 * REST API endpoint for Publisher API access
 *
 * @package pmc-piano
 */

namespace PMC\Piano;

use PMC\Global_Functions\WP_REST_API\Endpoint as Base;
use PMC_Cheezcap;
use UnexpectedValueException;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Licensee_Endpoint
 *
 * Provides a REST endpoint for querying for the licensee's name and ID
 * by contract ID.
 */
class Licensee_Endpoint extends Base {
	const PIANO_SANDBOX_ENVIRONMENT_API = 'https://sandbox.piano.io/api/v3';

	const CONTRACT_API_PATH = '/publisher/licensing/contract/get';

	const LICENSEE_API_PATH = '/publisher/licensing/licensee/get';

	/**
	 * Return endpoint's slug for use within the `pmc` namespace. Slug will be
	 * prefixed with `pmc/` and have version appended automatically.
	 *
	 * @return string
	 */
	protected function _get_namespace_slug(): string {
		return 'piano';
	}

	/**
	 * Return endpoint's route, including any URL parameters (dynamic parts).
	 *
	 * @return string
	 */
	protected function _get_route(): string {
		// Internally, we call licenses 'organizations'
		return 'licensee/(?P<contract_id>[\S]+)';
	}

	/**
	 * Return endpoint's arguments to be passed to `register_rest_route()`
	 *
	 * @return array
	 */
	protected function _get_args(): array {
		return [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_licensee_data' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'contract_id' => [
					'required' => true,
					'type'     => 'string',
				],
			],
		];
	}

	/**
	 * Pass through request to Piano Publisher API for organization name and ID.
	 *
	 * @param WP_REST_Request $request REST request data
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure
	 */
	public function get_licensee_data( WP_REST_Request $request ) {
		// Get API key
		$api_token       = PMC_Cheezcap::get_instance()->get_option( Plugin::PIANO_API_TOKEN );
		$environment_url = PMC_Cheezcap::get_instance()->get_option( Plugin::PIANO_ENVIRONMENT_URL );
		$app_id          = PMC_Cheezcap::get_instance()->get_option( Plugin::PIANO_APP_ID );

		// Set environment url to the sandbox if not set and in dev environment
		if ( empty( $environment_url ) && ! \PMC::is_production() ) {
			$environment_url = self::PIANO_SANDBOX_ENVIRONMENT_API;
		}

		if (
			empty( $api_token )
			|| empty( $app_id )
			|| empty( $environment_url )
		) {
			return new \WP_Error(
				'503',
				'Missing required configuration values.'
			);
		}

		try {
			$licensee_id = $this->_get_licensee_id(
				$request->get_param( 'contract_id' ),
				$api_token,
				$app_id,
				$environment_url
			);

			if ( is_wp_error( $licensee_id ) ) {
				throw new UnexpectedValueException( $licensee_id->get_error_message(), $licensee_id->get_error_code() );
			}

			$name = $this->_get_licensee_name(
				$licensee_id,
				$api_token,
				$app_id,
				$environment_url
			);

			if ( is_wp_error( $name ) ) {
				throw new UnexpectedValueException( $name->get_error_message(), $name->get_error_code() );
			}

		} catch ( \Exception $err ) {
			return \rest_ensure_response(
				new WP_Error(
					is_int( $err->getCode() ) ? $err->getCode() : 500,
					$err->getMessage()
				)
			);
		}

		// Forward the response from Piano
		return \rest_ensure_response(
			[
				'name' => $name,
				'id'   => $licensee_id,
			]
		);
	}

	/**
	 * Make a REST request.
	 *
	 * @param array $args
	 * @param string $url
	 *
	 * @return object|WP_Error The body of the response or a WP_Error
	 */
	public function make_request(
		array $args,
		string $url
	) {
		try {
			$query_string = http_build_query( $args );
			$raw_response = \vip_safe_wp_remote_get( "{$url}?{$query_string}" );

			if ( is_wp_error( $raw_response ) ) {
				throw new UnexpectedValueException( 'Error from Piano API' );
			}

			$response_string = wp_remote_retrieve_body( $raw_response );

			return json_decode( $response_string );
		} catch ( \Exception $err ) {
			return new \WP_Error(
				'500',
				$err->getMessage()
			);
		}
	}

	/**
	 * Make a request to the Piano API for the licensee ID.
	 *
	 * @param string $contract_id - Contract ID of licensee
	 * @param string $api_token - Piano Publisher API token
	 * @param string $app_id - Piano PUblisher App ID
	 * @param string $environment_url - Piano REST url
	 *
	 * @return string|WP_Error The licensee ID if found, or error if not.
	 */
	private function _get_licensee_id(
		string $contract_id,
		string $api_token,
		string $app_id,
		string $environment_url
	) {
		$path     = self::CONTRACT_API_PATH;
		$response = $this->make_request(
			[
				'aid'         => $app_id,
				'api_token'   => $api_token,
				'contract_id' => $contract_id,
			],
			"{$environment_url}{$path}"
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		try {
			$licensee_id = $response->contract->licensee_id;

			if ( empty( $licensee_id ) ) {
				throw new UnexpectedValueException( "No licensee found for ${contract_id}" );
			}

			return $licensee_id;
		} catch ( \Exception $err ) {
			return new \WP_Error(
				'404',
				$err->getMessage()
			);
		}
	}

	/**
	 * Make a request to the Piano API for the licensee name.
	 *
	 * @param string $licensee_id - ID of the license.
	 * @param string $publisher_api_token - Piano Publisher API token
	 * @param string $app_id - Piano PUblisher App ID
	 * @param string $environment_url - Piano REST url
	 *
	 * @return string|WP_Error The License
	 */
	private function _get_licensee_name(
		string $licensee_id,
		string $publisher_api_token,
		string $app_id,
		string $environment_url
	) {
		$path     = self::LICENSEE_API_PATH;
		$response = $this->make_request(
			[
				'aid'         => $app_id,
				'api_token'   => $publisher_api_token,
				'licensee_id' => $licensee_id,
			],
			"{$environment_url}{$path}"
		);

		try {
			$name = $response->licensee->name;

			if ( empty( $name ) ) {
				throw new UnexpectedValueException( "No name found for ${licensee_id}" );
			}

			return $name;
		} catch ( \Exception $err ) {
			return new \WP_Error(
				'404',
				$err->getMessage()
			);
		}
	}
}
