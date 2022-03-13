<?php

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Exporter {

	use Singleton;

	const NONCE_KEY = "_nonce_pmc_ads_exporter";
	private $_post_ids = array();
	private $_flag = false;

	protected function __construct() {
		add_action( 'admin_init', array( $this, 'export_ads' ) );
	}

	public function export_ads() {

		if ( ! is_admin() ) {
			return;
		}

		if ( empty( $_GET['page'] ) || $_GET['page'] != 'ad-manager' ) {
			return;
		}

		if ( ! current_user_can( 'pmc_manage_ads_cap' ) ) {
			return;
		}

		if ( empty( $_GET['action'] ) || 'pmc-ads-export' !== $_GET['action'] ) {
			return;
		}

		$nonce = $_GET['_wpnonce'];

		if ( ! wp_verify_nonce( $nonce, self::NONCE_KEY ) ) {
			return;
		}

		if ( empty( $_GET['post_ids'] ) ) {
			return;
		}

		$post_ids = explode( ",", $_GET['post_ids'] );

		$this->export_ads_wxr( $post_ids );
	}

	public function export_ads_wxr( array $post_ids = array() ) {

		if ( empty( $post_ids ) ) {
			return;
		}

		$this->_post_ids = array_map( 'intval', $post_ids );
		$this->_post_ids = array_filter(
			$this->_post_ids, function ( $a ) {
				return $a > 0;
			}
		);

		$this->_post_ids = array_unique( $this->_post_ids );

		/*
		export_wp does not have option to just export by post_id/'s nor it has filter.
		Therefore hooking on to query filter to pass our ids.
		Also going to use _flag var to make sure the filter runs only once, since inside export_qp there are quite a few queries that's run.
		*/
		add_filter( 'query', array( $this, 'add_post_ids_to_query' ), 100 );

		if ( ! function_exists( 'export_wp' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/export.php' );
		}

		$this->_flag = true;

		export_wp( array( 'content' => PMC_Ads::POST_TYPE ) );

		remove_filter( 'query', array( $this, 'add_post_ids_to_query' ), 100 );

		die();

	}

	/**
	 * @param $query
	 *
	 * @return mixed
	 */
	public function add_post_ids_to_query( $query ) {

		if ( ! $this->_flag ) {
			return $query;
		}

		$this->_flag = false;

		if ( ! is_admin() ) {
			return $query;
		}


		if ( empty( $this->_post_ids ) ) {
			return;
		}

		$post_ids = implode( ",", $this->_post_ids );

		$query = str_replace( "WHERE", "WHERE ID IN ({$post_ids}) AND ", $query );

		return $query;
	}

}

PMC_Ads_Exporter::get_instance();

//EOF
