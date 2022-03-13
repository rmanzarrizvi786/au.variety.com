<?php
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Disable_Getty_Images {

	use Singleton;

	protected function __construct() {
		// Allow `attachment` post type to be fetched over REST API using post endpoints.
		// This gives us more flexibility over postmeta related to attachments
		add_action( 'rest_api_allowed_post_types', array( $this, 'rest_api_allowed_post_types' ) );
		// Allowlist postmeta thats useful
		add_action( 'rest_api_allowed_public_metadata', array( $this, 'rest_api_allowed_public_metadata' ) );
		// Filter getty images using postmeta
		add_filter( 'ajax_query_attachments_args', array( $this, 'filter_getty_images' ) );
		//Disable getty image while attaching/inserting into post
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

	}

	public function rest_api_allowed_post_types( $allowed_post_types ) {
		$allowed_post_types[] = 'attachment';

		return $allowed_post_types;
	}

	public function rest_api_allowed_public_metadata( $allowed_meta_keys ) {
		$allowed_meta_keys[] = '_image_credit';
		$allowed_meta_keys[] = '_pmc_hide_in_media_library';

		return $allowed_meta_keys;
	}

	public function filter_getty_images( $query ) {
		$post_id           = intval( $_REQUEST['post_id'] );
		$contract_end_date = "2016-01-31 00:00:00";

		// If post is published before Jan 31th bail
		$post = get_post( $post_id );
		if ( empty( $post ) || $post->post_date < $contract_end_date ) {
			return $query;
		}

		// we don't need pagination. this improves performance
		$query['no_found_rows'] = true;

		// filter attachments that have GettyImages flag
		$query['meta_query'] = array(
			array(
				array(
					'key'     => '_pmc_hide_in_media_library',
					'compare' => 'NOT EXISTS'
				)
			)
		);

		return $query;
	}

	/**
	 * Enqueue js script to restrict selecting getty image in the UI.
	 */
	public function enqueue_admin_assets() {

		wp_enqueue_script( 'pmc-disable-getty-images-js', plugins_url( 'js/disable-getty-images.js', __FILE__ ), [ 'jquery' ], '1.0.0', true );
		wp_enqueue_style( 'pmc-disable-getty-images-css', plugins_url( 'css/admin.css', __FILE__ ), [], '1.0.0' );

	}
}

PMC_Disable_Getty_Images::get_instance();
