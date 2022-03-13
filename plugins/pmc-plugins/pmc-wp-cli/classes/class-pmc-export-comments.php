<?php
namespace PMC\WP_CLI;

class PMC_Export_Comments extends \PMC_WP_CLI_Base {

	const COMMAND_NAME = 'pmc-export-comments';

	/**
	 * Export all comments from 1 year ago
	 *
	 *
	 * ## OPTIONS
	 *
	 * [--csv=<file>]
	 * : The file to write the comment exports to
	 *
	 * [--log-file=<log-file>]
	 * : Path/Filename to the log file
	 *
	 * [--dry-run]
	 * : No operations are carried out while in this mode
	 *
	 * [--batch-paged]
	 * : The WP_Query page to start on. Helpful for picking up where you left off.
	 *
	 * [--batch-max-paged]
	 * : The maximum number of wp_query pages to iterate through. Helpful for running a small test batch.
	 *
	 * [--batch-size=<batch-size>]
	 * : The WP_Query posts_per_page argument you wish to set (number of batches). Default 500
	 *
	 * ## EXAMPLES
	 *
	 *    wp pmc-export-comments export-comments --csv=/srv/www/wpcom/public_html/wp-content/variety_comments.csv --url=pmc-variety-2020.wpcom.test --log-file=/srv/www/wpcom/public_html/wp-content/exports.log
	 *
	 * @subcommand export-comments
	 *
	 * @param array $args       Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 */
	public function export_comments( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );

		$csv_file = ( ! empty( $assoc_args['csv'] ) ) ? $assoc_args['csv'] : false;

		$this->_write_log( 'Comment export process starting...' );

		$header_row = [
			'Comment Post ID',
			'User ID',
			'Comment ID',
			'Comment Content',
			'Comment Author',
			'Comment Author Email',
			'Comment Approved',
			'Comment Date',
			'Comment Parent',
		];

		// date query $args
		$args = array(
			'date_query'    => array(
				'after'     => array(
					'year'  => 2019,
					'month' => 9,
					'day'   => 1,
				),
				'inclusive' => true,
			),
			'number'        => abs( $this->batch_size ),
			'paged'         => 1,
			'no_found_rows' => false,
		);

		if ( false === $this->dry_run && false !== $csv_file ) {
			$this->write_to_csv( $csv_file, [], [ $header_row ], null, 'w' );
		}

		$comments_query = new \WP_Comment_Query();

		do {
			$this->_write_log( '--------------------------' );
			$this->_write_log( 'BATCH - Current Query page: ' . $args['paged'] );
			$this->_write_log( '--------------------------' );

			// get all comments from 1 yr ago
			$comments = $comments_query->query( $args );

			if ( 0 === $this->batch_max_paged ) {
				$this->batch_max_paged = $comments_query->max_num_pages;
			}

			// get comment fields to export
			foreach ( $comments as $comment ) {

				$row = [
					// required fields
					$comment->comment_post_ID,
					$comment->user_id,
					$comment->comment_ID,
					$comment->comment_content,
					// nice to haves
					$comment->comment_author,
					$comment->comment_author_email,
					$comment->comment_approved,
					$comment->comment_date,
					$comment->comment_parent,
				];

				if ( $this->dry_run ) {

					$this->_write_log( 'Dry run: Exporting Comment: ' . $comment->comment_ID );

				} else {

					$this->_write_log( 'Exporting Comment: ' . $comment->comment_ID );

					if ( false !== $csv_file ) {

						$this->write_to_csv( $csv_file, [], [ $row ], null, 'a' );

					}

				}

			}

			$args['paged']++;

			$this->stop_the_insanity();
			sleep( $this->sleep );

		} while (

			$comments_query->found_comments && $args['paged'] <= $comments_query->max_num_pages && $args['paged'] <= $this->batch_max_paged

		); // close while

		$this->_write_log( 'Export process completed.' );

	}

} //end class

//EOF
