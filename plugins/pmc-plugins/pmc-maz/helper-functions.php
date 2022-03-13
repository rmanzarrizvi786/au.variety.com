<?php
/**
 * Helper functions for plugin
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2018-04-11
 *
 * @package pmc-maz
 */

/**
 * To identify if current request is maz request or not.
 *
 * @return bool True if it is Maz endpoint otherwise False
 */
function is_pmc_maz_endpoint() {
	return \PMC\Maz\Plugin::get_instance()->is_maz_endpoint();
}

/**
 * To get Maz URL for Post.
 *
 * @param  int $post_id Post ID.
 *
 * @return boolean|string Maz permalink.
 */
function pmc_maz_get_permalink( $post_id ) {

	if ( empty( $post_id ) || 0 >= intval( $post_id ) ) {
		return false;
	}

	return \PMC\Maz\Plugin::get_instance()->get_permalink( $post_id );
}

/**
 * To convert url to maz url.
 *
 * @param  string $url Post perma link.
 *
 * @return string Post maz link.
 */
function pmc_make_maz_url( $url ) {

	if ( empty( $url ) ) {
		return '';
	}

	return \PMC\Maz\Plugin::get_instance()->make_maz_url( $url );
}
