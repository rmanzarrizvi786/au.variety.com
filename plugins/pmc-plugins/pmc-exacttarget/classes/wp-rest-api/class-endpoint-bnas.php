<?php
/**
 * Breaking News Alerts REST API endpoints for Gutenberg.
 *
 * @package pmc-exacttarget
 */
namespace PMC\Exacttarget\WP_REST_API;

use PMC_TimeMachine;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Endpoint_BNAs.
 */
class Endpoint_BNAs extends Endpoint {
	/**
	 * Slug for registered route, for convenience.
	 */
	public const ROUTE = 'bna';

	/**
	 * Register endpoints.
	 */
	public function add_endpoints(): void {
		register_rest_route(
			static::NAMESPACE,
			static::ROUTE . static::ENDPOINT_ID_REGEX,
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_bnas' ],
					'permission_callback' => [ $this, 'check_endpoint_read_permissions' ],
					'args'                => [
						'id' => [
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => [ $this, 'validate_numeric' ],
						],
					],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [ $this, 'process_bnas_for_post' ],
					'permission_callback' => [ $this, 'check_endpoint_update_permissions' ],
					'args'                => [
						'id'              => [
							'required'          => true,
							'type'              => 'integer',
							'validate_callback' => [ $this, 'validate_numeric' ],
						],
						'selectedAlerts'  => [
							'required'          => true,
							'type'              => 'array',
							'sanitize_callback' => [ $this, 'sanitize_selected_alerts' ],
						],
						'sendOverride'    => [
							'required' => false,
							'type'     => 'boolean',
							'default'  => false,
						],
						'subjectOverride' => [
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => [ $this, 'sanitize_subject_override' ],
						],
					],
				],
			]
		);
	}

	/**
	 * Restrict endpoint write access.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function check_endpoint_update_permissions(
		WP_REST_Request $request
	): bool {
		$id = $request->get_param( 'id' );

		$capability = 'page' === get_post_type( $id )
			? 'edit_page'
			: 'edit_post';

		return current_user_can( $capability, $id );
	}

	/**
	 * Sanitize alert destinations.
	 *
	 * @param array $alerts Lists to send to.
	 * @return array
	 */
	public function sanitize_selected_alerts( array $alerts ): array {
		// Argument is strictly typed.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		return array_map( 'sanitize_text_field', $alerts );
	}

	/**
	 * Sanitize subject override.
	 *
	 * @param string $subject Custom subject.
	 * @return string
	 */
	public function sanitize_subject_override( string $subject ): string {
		return sanitize_text_field( $subject );
	}

	/**
	 * Prepare data needed to configure BNAs.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_bnas( WP_REST_Request $request ): WP_REST_Response {
		$id = $request->get_param( 'id' );

		$response = [
			'id'              => $id,
			'allowSubject'    => sailthru_is_breaking_news_subject_enabled(),
			'selectedAlerts'  => [],
			'sendOverride'    => false,
			'subjectOverride' => '',
		];

		$bnas = sailthru_get_fast_newsletter();

		// Sniff fails to detect `is_array` check.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		$response['alerts'] = is_array( $bnas ) ? array_keys( $bnas ) : [];

		$this->_decorate_response( $response, $id );

		return rest_ensure_response( $response );
	}

	/**
	 * Process a post's BNA settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function process_bnas_for_post(
		WP_REST_Request $request
	): WP_REST_Response {
		$id = $request->get_param( 'id' );

		$response = [
			'id' => $id,
		];

		$lists            = $request->get_param( 'selectedAlerts' );
		$subject_override = sailthru_is_breaking_news_subject_enabled()
			? $request->get_param( 'subjectOverride' )
			: null;
		$lock_override    = $request->get_param( 'sendOverride' );

		sailthru_process_bnas(
			$id,
			$lists,
			$subject_override,
			$lock_override
		);

		$this->_decorate_response( $response, $id );

		return rest_ensure_response( $response );
	}

	/**
	 * Add common data to both read and write requests.
	 *
	 * @param array $response Response data.
	 * @param int   $id       Post ID.
	 */
	protected function _decorate_response( array &$response, int $id ): void {
		$response['log'] = $this->_get_log_data( $id );
		$status          = get_post_status( $id );

		if ( sailthru_is_breaking_news_subject_enabled() ) {
			$subject_override_meta = get_post_meta(
				$id,
				'_sailthru_alert_subject',
				true
			);

			if ( ! empty( $subject_override_meta ) ) {
				$response['subjectOverride'] = $subject_override_meta;
			}
		}

		if ( 'draft' === $status ) {
			$selected_alerts = get_post_meta(
				$id,
				'_sailthru_selected_alerts',
				true
			);
		} elseif ( 'future' === $status ) {
			$meta = get_post_meta(
				$id,
				'_sailthru_breaking_news_meta_data',
				true
			);

			if ( is_array( $meta ) ) {
				$selected_alerts = wp_list_pluck( $meta, 'name' );
			}
		}

		if ( ! empty( $selected_alerts ) ) {
			$response['selectedAlerts'] = $selected_alerts;
		}
	}

	/**
	 * Format post's ET log for rendering in Gutenberg.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	protected function _get_log_data( int $post_id ): array {
		$formatted = [];
		$raw       = get_post_meta(
			$post_id,
			'_sailthru_alert_blast_log',
			true
		);

		if ( empty( $raw ) || ! is_array( $raw ) ) {
			return $formatted;
		}

		$timezone = sailthru_get_site_timezone();

		foreach ( $raw as $timestamp => $entry ) {
			$user = get_userdata( $entry['user'] );

			natsort( $lists );
			$lists = array_values( $entry['lists'] );

			// PHPUnit is confused.
			$formatted[] = [ // @codeCoverageIgnore
				'timestamp' => PMC_TimeMachine::create( $timezone )
												->from_time( 'U', $timestamp )
												->format_as( 'Y-m-d H:i' ),
				'username'  => $user->user_nicename,
				'lists'     => $lists,
			];
		}

		return $formatted;
	}
}
