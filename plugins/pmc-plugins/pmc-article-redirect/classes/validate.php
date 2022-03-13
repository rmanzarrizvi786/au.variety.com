<?php

namespace PMC\Article_Redirect;

use \PMC\Global_Functions\Traits\Singleton;

class Validate {

	use Singleton;

	protected function __construct() {
		add_action( 'template_redirect', array( $this, 'pmc_redirect_malformed_url_to_correct_article' ) );
	}

	/*
	 * We have article URLs with the post ID in them.
	 * Use this ID to find the post and do a 301 redirect
	 * when the slug, date, or other parts of the URL path are malformed
	 *
	 * @since 2016-02-10
	 * @version 2016-02-10 Archana Mandhare PMCVIP-407
	 *
	 */
	public function pmc_redirect_malformed_url_to_correct_article(){

		global $wp;

		if ( is_404() && ! empty( $wp->request ) ) {

			$post_name = explode( '/', $wp->request );

			if ( empty( $post_name ) ) {
				return;
			}

			// get the last item from the explode array of the url_path
			$path_name = strtolower( end( $post_name ) );

			if ( empty( $path_name ) ) {
				return;
			}

			// Redirect rule for Articles
			if ( preg_match( '/^[a-z0-9-]+-([0-9]+)$/', $path_name, $match ) ) {

				// Check that the match is a valid positive integer with only digits in it
				if ( is_string( $match[1] ) && ctype_digit( $match[1] ) && intval( $match[1] ) > 0 ) {

					$correct_post = get_post( intval( $match[1] ) );

					// Redirect to article page only if it is a valid post and has published status
					if ( ! empty( $correct_post ) && $correct_post instanceof \WP_Post && 'publish' === $correct_post->post_status ) {

						$url = get_permalink( $correct_post->ID );

						if ( ! empty( $url ) ) {

							wp_redirect( $url, 301 );
							exit();

						}
					}
				}
			}
		}
	}
}
