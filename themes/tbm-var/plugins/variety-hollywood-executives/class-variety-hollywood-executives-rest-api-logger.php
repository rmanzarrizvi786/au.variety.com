<?php

/**
 * Class Variety_Hollywood_Executives_REST_API_Logger
 *
 * Process log requests and log in Variety.com and external pistachio server
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Hollywood_Executives_REST_API_Logger {

	use Singleton;

	const VY_EXEC_PROFILE_LOG_POST_TYPE     = 'hollywood_exec_log';
	const PISTACHIO_URL                     = 'https://pistachio.pmc.com/variety-exec-logger.php';
	const VY_EXEC_PROFILE_LOGGER_ERROR_CODE = 'vy500-exec-profile-api-logger-error';
	const DO_NOT_LOG_IN_PISTACHIO           = 'do_not_log_in_pistachio';

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	/**
	 * Register hollywood_exec_log post type
	 */
	public function action_init() {
		$args = array(
			'description'         => __( 'Post type to log incoming push data from Variety Insights.', 'pmc-variety' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=hollywood_exec',
			'show_in_admin_bar'   => true,
			'can_export'          => true,
			'delete_with_user'    => false,
			'hierarchical'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'supports'            => array( 'title', 'editor' ),
			'labels'              => array(
				'name'               => __( 'HL Exec logs', 'pmc-variety' ),
				'singular_name'      => __( 'HL Exec log', 'pmc-variety' ),
				'menu_name'          => __( 'HL Exec logs', 'pmc-variety' ),
				'name_admin_bar'     => __( 'HL Exec logs', 'pmc-variety' ),
				'add_new'            => __( 'Add New', 'pmc-variety' ),
				'add_new_item'       => __( 'Add New HL Exec log', 'pmc-variety' ),
				'edit_item'          => __( 'Edit HL Exec log', 'pmc-variety' ),
				'new_item'           => __( 'New HL Exec log', 'pmc-variety' ),
				'view_item'          => __( 'View HL Exec log', 'pmc-variety' ),
				'search_items'       => __( 'Search HL Exec logs', 'pmc-variety' ),
				'not_found'          => __( 'No HL Exec logs found', 'pmc-variety' ),
				'not_found_in_trash' => __( 'No HL Exec logs found in trash', 'pmc-variety' ),
				'all_items'          => __( 'HL Exec logs', 'pmc-variety' ),
			),
		);

		register_post_type( self::VY_EXEC_PROFILE_LOG_POST_TYPE, $args );
	}

	/**
	 * Log relevant push data from Variety Insights
	 *
	 * @param  int $response_code
	 * @param  string $response_message
	 * @param  array $params
	 * @param  array $request
	 *
	 */
	public function log_push_data( $response_code = 404, $response_message = '', $params = array(), $request = array() ) {
		$log_data = $this->prepare_log_data( $response_code, $response_message, $params, $request );
		$this->log_data_on_hl_exec_log_post_type( $log_data );
		$this->log_data_on_pistachio( $log_data );
	}

	/**
	 * Prepare data for logging
	 *
	 * @param  int $response_code
	 * @param  string $response_message
	 * @param  array $params
	 * @param  array $request
	 *
	 * @return array $log_data
	 */
	public function prepare_log_data( $response_code = 404, $response_message = '', $params = array(), $request = array() ) {

		$log_data = array(
			'response_code'  => $response_code,
			'response_body'  => $response_message,
			'sanitized_data' => $params,
			'request_header' => '',
			'raw_data'       => '',

		);

		if ( ! empty( $request ) ) {
			$log_data['request_header'] = $request->get_headers();
			$log_data['raw_data']       = $request->get_json_params();
		}

		switch ( (int) $response_code ) {
			case 200:
				$log_data['post_title'] = 'SUCCESS - ' . $params['title'];
				break;
			case 400:
			case 404:
				$log_data['post_title'] = 'FAILURE - ' . $response_message;
				break;
			case 403:
				$log_data['post_title'] = 'FORBIDDEN - ' . $response_message;
				break;
		}

		return $log_data;
	}

	/**
	 * Log relevant push data in hl_exec_log post type
	 *
	 * @param  array $log_data
	 *
	 * @return int|WP_Error $post_id
	 */
	public function log_data_on_hl_exec_log_post_type( $log_data ) {
		$args = array(
			'post_type'    => self::VY_EXEC_PROFILE_LOG_POST_TYPE,
			'post_title'   => $log_data['post_title'],
			'post_status'  => 'publish',
			'post_content' => wp_json_encode( $log_data ),
		);

		$post_id = wp_insert_post( $args );
		if ( is_wp_error( $post_id ) ) {
			return new WP_Error( self::VY_EXEC_PROFILE_LOGGER_ERROR_CODE, 'Something went wrong while logging data' );
		}

		return $post_id;
	}

	/**
	 * Log relevant push data to pistachio server
	 *
	 * @param  array $log_data
	 *
	 * @return array|WP_Error $result
	 */
	public function log_data_on_pistachio( $log_data = array() ) {
		$do_no_log_in_pistachio = get_transient( self::DO_NOT_LOG_IN_PISTACHIO );
		if ( $do_no_log_in_pistachio ) {
			return;
		}
		$http_referer = filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL );
		$user_agent   = filter_input( INPUT_SERVER, 'HTTP_USER_AGENT' );

		$data = array(
			'http_referer'   => $http_referer,
			'push_time'      => current_time( 'mysql', 1 ),
			'post_id'        => $log_data['post_id'],
			'request_header' => wp_json_encode( $log_data['request_header'] ),
			'response_code'  => $log_data['response_code'],
			'response_body'  => $log_data['response_body'],
			'raw_data'       => $log_data['raw_data'],
			'sanitized_data' => $log_data['sanitized_data'],
			'misc'           => wp_json_encode( $log_data ),
		);

		$args = array(
			'method'     => 'POST',
			'timeout'    => 2,
			'blocking'   => false,
			'body'       => $data,
			'user-agent' => $user_agent,
		);

		$result = wp_remote_post(
			self::PISTACHIO_URL,
			$args
		);

		if ( is_wp_error( $result ) ) {
			// Pause logging for 5 minutes if there is an error
			set_transient( self::DO_NOT_LOG_IN_PISTACHIO, true, MINUTES_IN_SECONDS * 5 );
			return new WP_Error( self::VY_EXEC_PROFILE_LOGGER_ERROR_CODE, 'Something went wrong while logging data in pistachio' );
		}

		return $result;
	}

}

Variety_Hollywood_Executives_REST_API_Logger::get_instance();

//EOF
