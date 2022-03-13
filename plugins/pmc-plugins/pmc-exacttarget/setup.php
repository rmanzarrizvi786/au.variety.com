<?php
/*
Description: Prepare data for setting up various api parameters for exacttarget e.g api key's.
*/
if ( ! current_user_can( 'publish_posts' ) ) {
	die( 'Access Denied' );
}

use PMC\Exacttarget\Cache;
use PMC\Exacttarget\Config;
use PMC\Global_Functions\Nonce;

$action = PMC::filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
if ( 'render-backup' === $action ) {
	printf("<div>Version: %s</div>", esc_html( PMC_EXACTTARGET_VERSION ) );
	echo '<pre>';
	PMC\Exacttarget\Backup::get_instance()->render_json( PMC_EXACTTARGET_VERSION );
	echo '</pre>';
	return;
}

$list_of_post_types = get_post_types( array( 'public' => true ), 'names' );

$nonce = Nonce::get_instance( basename( __FILE__ ) );

// processing post action
if ( $nonce->verify() ) {

	$action = PMC::filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
	if ( empty( $action ) ) {
		$action = PMC::filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
	}

	switch ( $action ) {
		case 'flush_cache':
			Cache::get_instance()->refresh();
			$notices[] = 'API Cache Flushed.';
			break;
		case 'post_types':
			if ( is_array( $_POST['post_types'] ) ) { // phpcs:ignore
				$post_types = array_map( 'sanitize_text_field', array_keys( $_POST['post_types'] ) ); // phpcs:ignore
			} else {
				$post_types = PMC::filter_input( INPUT_POST, 'post_types', FILTER_SANITIZE_STRING );
			}
			if ( ! empty( $post_types ) ) {
				Config::get_instance()->set( 'supported_post_types', $post_types );
			}
			break;
		case 'api':

			$config = [
				'legacy_app'    => (int) PMC::filter_input( INPUT_POST, 'api_legacy_app', FILTER_SANITIZE_NUMBER_INT ),
				'disabled'      => (int) PMC::filter_input( INPUT_POST, 'api_disabled', FILTER_SANITIZE_NUMBER_INT ),
				'key'           => PMC::filter_input( INPUT_POST, 'api_key', FILTER_SANITIZE_STRING ),
				'secret'        => PMC::filter_input( INPUT_POST, 'api_secret', FILTER_SANITIZE_STRING ),
				'account_id'    => PMC::filter_input( INPUT_POST, 'api_account_id', FILTER_SANITIZE_STRING ),
				'base_url'      => PMC::filter_input( INPUT_POST, 'api_base_url', FILTER_SANITIZE_STRING ),
				'base_auth_url' => PMC::filter_input( INPUT_POST, 'api_base_auth_url', FILTER_SANITIZE_STRING ),
				'base_soap_url' => PMC::filter_input( INPUT_POST, 'api_base_soap_url', FILTER_SANITIZE_STRING ),
			];
			Config::get_instance()->update( $config );

			break;
	}

}

$api_active = Exact_Target::is_active();

$reset_api = PMC::filter_input( INPUT_GET, 'reset_api' );
if ( ! $api_active || ( '1' === $reset_api && empty( $_POST ) ) ) { //phpcs:ignore
	$config = Config::get_instance()->api();

	\PMC::render_template(
		sprintf( '%s/views/api-setup-tpl.php', untrailingslashit( PMC_EXACTTARGET_PATH ) ),
		[
			'items' => $config,
			'nonce' => $nonce,
		],
		true
	);

} else {
	\PMC::render_template(
		sprintf( '%s/views/setup-tpl.php', untrailingslashit( PMC_EXACTTARGET_PATH ) ),
		[
			'post_types' => $list_of_post_types,
			'nonce'      => $nonce,
			'notices'    => $notices,
		],
		true
	);
}
