<?php
/**
 * WP CLI to handle PMC Sitemaps functionality.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since BR-309
 *
 * @package pmc-sitemaps
 */

namespace PMC\Sitemaps;

use PMC\Unit_Test\Mock\WP_Query;

class PMC_Sitemaps_CLI extends \PMC_WP_CLI_Base {
	/**
	 * Sitemap Rebuild command to update the existing sitemap data, without doing http request.
	 *
	 * ## OPTIONS
	 *
	 * [--log-file=<file>]
	 * : Path to log file
	 *
	 * [--email]
	 * : Email to send notification after script complete.
	 *
	 * [--email-when-done]
	 * : Whether to send notification or not.
	 *
	 * [--email-logfile]
	 * : Whether to send log file or not.
	 *
	 * [--sitemap-type=<string>]
	 * : List or single type of sitemap.
	 *
	 * [--year=<string>]
	 * : Specific year for which sitemap needs to be rebuild.
	 *
	 * [--month=<string>]
	 * : Specific month for which sitemap needs to be rebuild.
	 *
	 * [--dry-run]
	 * : Defaults to enabled. Run live with --no-dry-run.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-sitemaps rebuild --url=example.com --sitemap-type=post,index,pmc-gallery,category --year=2018 --month=01
	 *     wp pmc-sitemaps rebuild --url=example.com --sitemap-type=post --year=2018 --month=01
	 *     wp pmc-sitemaps rebuild --url=example.com --sitemap-type=post
	 *     wp pmc-sitemaps rebuild --url=example.com --sitemap-type=index
	 *
	 * @subcommand rebuild
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function rebuild( $args, $assoc_args ) {

		// Extract Arguments.
		$this->_extract_common_args( $assoc_args );

		$this->_notify_start( 'PMC Sitemaps rebuild: Started' );

		$sitemaps_instance = \PMC_Sitemaps::get_instance();
		$sitemap_types     = explode( ',', $assoc_args['sitemap-type'] );

		$year  = ( ! empty( $assoc_args['year'] ) ) ? $assoc_args['year'] : false;
		$month = ( ! empty( $assoc_args['month'] ) ) ? $assoc_args['month'] : false;

		if ( ( $year && ! $month ) || ( ! $year && $month ) ) {
			$this->_error( 'Year and Month both needs to be specified' );
			return;
		}

		if ( ! empty( $sitemap_types ) ) {

			foreach ( $sitemap_types as $sitemap_type ) {
				$this->_rebuild_sitemap( $sitemap_type, $year . $month );
			}
		}

		$this->_notify_done( 'PMC Sitemaps rebuild: Completed' );
	}

	/**
	 * To rebuild all sitemap or specific sitemap from CLI.
	 *
	 */
	private function _rebuild_sitemap( $sitemap_type, $sitemap_n ) : void {
		$instance = \PMC_Sitemaps::get_instance();

		$query = new \WP_Query();
		$query->set( 'pmc_sitemap', $sitemap_type );
		$query->set( 'pmc_sitemap_n', $sitemap_n );

		$instance->sitemap_request( $query );

		if ( ! empty( $sitemap_n ) || 'index' === $sitemap_type ) {

			$posts = get_posts( // phpcs:ignore
				[
					'post_type'        => 'pmc_sitemap',
					'name'             => $instance->get_sitemap_name( 'sanitize' ),
					'posts_per_page'   => 1,
					'suppress_filters' => false,
				]
			);

			if ( ! empty( $posts ) ) {
				$post = $posts[0];
				if ( $instance->rebuild_content( $post ) ) {
					$this->_write_log( sprintf( ' -> OK: %d - %s ', $post->ID, $post->post_title ) );
				} else {
					$this->_write_log( sprintf( ' -> Failed: %d - %s ', $post->ID, $post->post_title ) );  // @codeCoverageIgnore
				}
			}

		} else {

			$paged         = 1;
			$page_size     = 5;
			$sitemap_title = $instance->get_sitemap_name();

			do {

				$posts = get_posts( // phpcs:ignore
					[
						'post_type'      => 'pmc_sitemap',
						's'              => $sitemap_title,
						'posts_per_page' => $page_size,
						'paged'          => $paged,
					]
				);

				$paged++;  // increase the page number right after the get_posts so we don't forget

				if ( ! empty( $posts ) ) {
					foreach ( $posts as $post ) {
						if ( false !== strpos( $post->post_title, $sitemap_title ) ) {
							if ( $instance->rebuild_content( $post ) ) {
								$this->_write_log( sprintf( ' -> OK: %d - %s ', $post->ID, $post->post_title ) );
							} else {
								$this->_write_log( sprintf( ' -> Failed: %d - %s ', $post->ID, $post->post_title ) );
							}
						}
					}
				} else {
					break;
				}

				$this->_update_iteration();

			} while ( count( $posts ) === $page_size );

		}
	}

}
