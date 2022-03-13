<?php
namespace PMC\Export;

use PMC\Global_Functions\Traits\Singleton;

class Ajax_Api {
	use Singleton;

	private $_is_active          = false;
	private $_stream_id          = false;
	private $_registered_streams = [];

	protected function __construct() {

		$this->_stream_id = \PMC::filter_input( INPUT_POST, 'stream_id' );
		if ( empty( $this->_stream_id ) ) {
			$this->_stream_id = \PMC::filter_input( INPUT_GET, 'stream_id' );
		}

		add_action( 'init', [ $this, 'action_init' ], 99 );
	}

	/**
	 * Plugin is active only if we have a valid stream registered
	 * @return bool
	 */
	public function is_active() {
		return count( $this->_registered_streams ) > 0;
	}

	/**
	 * Controlling the exporting permission
	 * @return bool
	 */
	public function is_export_allow() {
		return (bool) apply_filters( 'pmc_is_export_allow', current_user_can( 'manage_options' ), $this->_stream_id );
	}

	public function action_init() {

		if ( ! $this->is_active() ) {
			return;
		}

		// Support ajax command pmc_stream
		add_action( 'wp_ajax_pmc_stream', [ $this, 'process_request' ] );

	}

	/**
	 * Processing the Ajax request
	 */
	public function process_request() {

		// Note: We're not using ajax nonce here as we are call the ajax re-cursively from the client without refreshing the page to generate a new nonce for each ajax call
		// The processed result are cached and we do allow replay of ajax request.

		if ( ! $this->is_export_allow() || empty( $this->_stream_id ) ) {
			wp_send_json_error(
				[
					'message' => 'Permissions denied',
				],
				403
			);
		}

		$stream = $this->get_stream();
		if ( empty( $stream ) ) {
			wp_send_json_error(
				[
					'message' => 'Invalid stream id ' . $this->_stream_id,
				]
			);
		}

		if ( ! $this->has_data() ) {
			wp_send_json_success(
				[
					'page'  => 0,
					'pages' => 0,
					'data'  => false,
				]
			);
		}

		$page = (int) \PMC::filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $page ) ) {
			wp_send_json_success(
				[
					'page'  => 0,
					'pages' => $this->get_pages(),
					'data'  => $this->get_data( 0 ),
				]
			);
		}

		return wp_send_json_success(
			[
				'page'  => $page,
				'pages' => $this->get_pages(),
				'data'  => $this->get_data( $page ),
			]
		);

	}

	/**
	 * Return the data stream for the current stream request
	 * @return bool|mixed
	 */
	public function get_stream() {
		if ( empty( $this->_stream_id ) || empty( $this->_registered_streams[ $this->_stream_id ] ) ) {
			return false;
		}
		return $this->_registered_streams[ $this->_stream_id ];
	}

	/**
	 * Return true if we have data
	 * @return bool
	 */
	public function has_data() : bool {
		return $this->get_pages() > 0;
	}

	/**
	 * Return the total number of pages the stream has
	 * @return int
	 */
	public function get_pages() : int {
		$stream = $this->get_stream();
		if ( ! $stream ) {
			return 0;
		}
		return $stream->pages();
	}

	/**
	 * @param int $page
	 * @return bool | mixed The data type is controlled by the stream type
	 */
	public function get_data( int $page ) {
		if ( ! $this->has_data() ) {
			return false;
		}

		$stream = $this->get_stream();

		if ( ! $stream ) {
			// This case will never happen unless has_data doesn't check the stream, we will not be able to reach this code
			// Code added for completeness, no proper way to test the code.
			return false; // @codeCoverageIgnore
		}

		return $stream->data( $page );
	}

	/**
	 * Register the stream to be use by Ajax api
	 * @param $stream The stream object extended from abstract class PMC\Export\Stream
	 */
	public function register_stream( $stream ) {
		$this->_registered_streams[ $stream->id() ] = $stream;
	}

}

