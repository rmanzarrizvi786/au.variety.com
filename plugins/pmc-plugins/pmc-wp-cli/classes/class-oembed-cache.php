<?php
/**
 * Manage oEmbed cache, ported from SC.
 *
 * @package pmc-wp-cli
 */

namespace PMC\WP_CLI;

use PMC_WP_CLI_Base;

/**
 * Manage oEmbed cache.
 */
class OEmbed_Cache extends PMC_WP_CLI_Base {

	const COMMAND_NAME = 'pmc-oembed-cache';

	/**
	 * Cleanup oEmbed cache
	 *
	 * ## OPTIONS
	 *
	 * [--log-file=<log-file-path>]
	 * : Log file to use for this cli script
	 *
	 * [--dry-run]
	 * : Don't actually do anything
	 *
	 * [--need-confirm=< /false>]
	 * : To require confirmation to overwrite existing file or not.
	 *
	 * [--max-error-reporting-level=< /true>]
	 * : Set timeout to wait an URL to responce.
	 *
	 * [--sleep=<sleep>]
	 * : Time to sleep between bunch of iterations.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pmc-oembed-cache cleanup --url=sc.local
	 *
	 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
	 *
	 * @param array $args
	 * @param array $assoc_args
	 * @synopsis [--log-file=<log-file-path>] [--dry-run] [--stdout-mode] [--max-error-reporting-level] [--sleep] [--need-confirm]
	 * @throws \WP_CLI\ExitException
	 */
	public function cleanup( array $args = [], array $assoc_args = [] ) {
		// Override default max_iteration to sleep
		$this->max_iteration = 5;

		$this->_extract_common_args( $assoc_args );

		$this->_notify_start(
			sprintf(
				'Starting: Cleanup oEmbed cache on %1$s',
				wp_parse_url( home_url(), PHP_URL_HOST )
			)
		);

		global $wpdb;

		$count_query  = "SELECT COUNT(*) FROM `$wpdb->postmeta`";
		$count_query .= ' WHERE `meta_key` LIKE %s';
		$count_query  = $wpdb->prepare(
			$count_query,
			[ '_oembed_%' ]
		);

		if ( empty( $count_query ) ) {
			// Cannot trigger an error for this condition, requires malformed query.
			$this->_error( "Couldn't prepare query to get oEmbed cache rows count" ); // @codeCoverageIgnore
		}

		$count_res = $wpdb->get_var( $count_query );

		if ( null === $count_res ) {
			// Cannot trigger an error for this condition, requires DB failure.
			$this->_error( "Couldn't get oEmbed cache rows count: $wpdb->last_error" ); // @codeCoverageIgnore
		}

		$rows_to_process = (int) $count_res;

		if ( 0 === $rows_to_process ) {
			$this->_error( 'No rows found' );
		}

		$batch_size       = 100;
		$pages_to_process = ceil( $rows_to_process / $batch_size );

		$this->batch_found_posts   = $rows_to_process;
		$this->batch_max_num_pages = $pages_to_process;

		$this->batch_progress_bar = \WP_CLI\Utils\make_progress_bar(
			"Total $pages_to_process pages to process...",
			$pages_to_process
		);

		// Counter to output how many rows are cleaned
		$cleaned_rows = 0;
		for ( $i = 0; $i < $pages_to_process; $i++ ) {
			$offset = 0;

			// Without offset for normal run, because we will delete rows each step
			// and rows will be shifted
			if ( $this->dry_run ) {
				// Does not impact non-dry runs.
				$offset = $batch_size * $i; // @codeCoverageIgnore
			}

			$select_query  = "SELECT * FROM `$wpdb->postmeta`";
			$select_query .= ' WHERE `meta_key` LIKE %s';
			$select_query .= ' ORDER BY `meta_id` ASC';
			$select_query .= ' LIMIT %d, %d';
			$select_query  = $wpdb->prepare(
				$select_query,
				[ '_oembed_%', $offset, $batch_size ]
			);

			if ( empty( $select_query ) ) {
				// Cannot trigger an error for this condition, requires malformed query.
				$this->_error( "Couldn't prepare query to get oEmbed cache rows" ); // @codeCoverageIgnore
			}

			$rows = $wpdb->get_results( $select_query );

			if ( null === $rows ) {
				// Cannot trigger an error for this condition, requires DB failure.
				$this->_error( "Couldn't get oEmbed cache rows: $wpdb->last_error" ); // @codeCoverageIgnore
			}

			$ids = wp_list_pluck( $rows, 'meta_id' );

			$ids_to_delete_placeholder = implode(
				', ',
				array_fill(
					0,
					count( $ids ),
					'%d'
				)
			);
			$this->_write_log(
				sprintf(
					'The following post metas will be deleted %s',
					implode( ', ', $ids )
				)
			);

			if ( ! $this->dry_run ) {
				$delete_query  = "DELETE FROM `$wpdb->postmeta`";
				$delete_query .= " WHERE `meta_id` IN ($ids_to_delete_placeholder)";
				$delete_query  = $wpdb->prepare(
					$delete_query,
					$ids
				);

				if ( empty( $delete_query ) ) {
					// Cannot trigger an error for this condition, requires malformed query.
					$this->_error( "Couldn't prepare query to delete oEmbed cache rows" ); // @codeCoverageIgnore
				}

				$deleted = $wpdb->query( $delete_query );

				if ( false === $deleted ) {
					// Cannot trigger an error for this condition, requires DB failure.
					$this->_error( "Couldn't delete oEmbed cache rows: $wpdb->last_error" ); // @codeCoverageIgnore
				}

				$cleaned_rows += $deleted;
			}

			$this->batch_update_progress_bar();
			$this->_update_iteration();
		}

		$this->_notify_done(
			sprintf(
				'Cleaned [%1$d / %2$d] oEmbed cache rows on %3$s',
				$cleaned_rows,
				$rows_to_process,
				wp_parse_url( home_url(), PHP_URL_HOST )
			)
		);
	}
}
