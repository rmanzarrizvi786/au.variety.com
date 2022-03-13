<?php

/**
 * Register new XMLRPC methods
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Print_Post_XMLRPC {

	use Singleton;

	/**
	 * Setup class
	 *
	 * @uses add_filter
	 * @return void
	 */
	protected function __construct() {
		add_filter( 'xmlrpc_methods', array( $this, 'filter_xmlrpc_methods' ) );
	}

	/**
	 * Register new xmlrpc method
	 *
	 * @param array $methods
	 * @return array
	 */
	public function filter_xmlrpc_methods( $methods ) {
		$methods['pmc.updateExportedDate'] = array( $this, 'update_exported_date' );
		return $methods;
	}

	/**
	 * Update exported date for a give post
	 *
	 * @param array $args
	 * @uses update_post_meta
	 * @return void
	 */
	public function update_exported_date( $args ) {
		global $wp_xmlrpc_server;
    	$wp_xmlrpc_server->escape( $args );

		$username = $args[1];
		$password = $args[2];

		// Verify credentials
		if ( ! $wp_xmlrpc_server->login( $username, $password ) ) {
			return $wp_xmlrpc_server->error;
		}

    	if ( isset( $args[3] ) && empty( $args[3]['post_id'] ) )
    		return;

    	$post_id = (double) $args[3]['post_id'];

    	update_post_meta( $post_id, '_pmc_exported_on', time() );
	}
}

PMC_Print_Post_XMLRPC::get_instance();
