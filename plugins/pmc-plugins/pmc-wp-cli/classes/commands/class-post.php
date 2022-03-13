<?php

namespace PMC\WP_CLI\Commands;

/**
 * Class \PMC\WP_CLI\Commands\Post
 *
 * Post-related commands for WP-CLI
 *
 * @package PMC\WP_CLI
 */
class Post extends \PMC_WP_CLI_Base {

	const COMMAND_NAME = 'pmc-post';

	private $_assign_author = false;

	/**
	 * Fixes posts that are missing authors due likely to bugs in migration process.
	 *
	 * ## OPTIONS
	 *
	 * [--post_types=<types>]
	 * : Comma separated post types for script to review.
	 *
	 * [--assign_author=<user_id>]
	 * : User ID of the author you want to assign unassigned posts to.
	 *
	 * [--batch-size=<integer>]
	 * : Limit size of query, default is 500.
	 *
	 * [--sleep=<integer>]
	 * : Number of seconds to sleep between batches, default is 2 seconds.
	 *
	 * [--max-iteration=<integer>]
	 * : Number of iteration before calling sleep if requested, default is 20.
	 *
	 * [--dry-run]
	 * : If set, no data will be updated, just logged.
	 *
	 * ex: wp pmc-post fix_missing_author --post_types=post,pmc-gallery --assign_author=3 --dry-run
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function fix_missing_author( array $args, array $assoc_args ) : void {
		$command_name = sprintf( '%s:%s', self::COMMAND_NAME, 'fix_missing_author' );
		$post_types   = [ 'post' ];

		$this->_extract_common_args( $assoc_args );

		if ( ! empty( $assoc_args['post_types'] ) ) {
			$post_types_array = explode( ',', $assoc_args['post_types'] );
			$post_types       = array_values(
				array_filter(
					(array) $post_types_array,
					[ $this, '_is_valid_post_type' ]
				)
			);
			$post_types       = array_map( 'sanitize_key', (array) $post_types );
			$post_types_diff  = array_diff( (array) $post_types_array, (array) $post_types );

			if ( ! empty( $post_types_diff ) ) {
				$this->_error(
					sprintf(
						'%s are not valid post type(s)',
						implode( ', ', $post_types_diff )
					)
				);
			}
		}

		if ( ! empty( $assoc_args['assign_author'] ) ) {
			$check_author = get_userdata( $assoc_args['assign_author'] );

			if ( is_a( $check_author, \WP_User::class ) ) {
				$this->_assign_author = intval( $assoc_args['assign_author'] );
			} else {
				$this->_error(
					sprintf(
						'Author ID %d does not exist',
						$assoc_args['assign_author']
					)
				);
			}
		}

		if ( empty( $post_types ) ) {
			$this->_error( 'Post type not set' );
		}

		$this->_notify_start(
			sprintf( 'WP-CLI command %s :-: Started', $command_name )
		);

		$this->batch_wp_query_task_runner(
			[
				'post_type'   => $post_types,
				'post_status' => 'any',
				'fields'      => 'all',
			],
			[ $this, '_posts_task_runner' ]
		);

		$this->_notify_done(
			sprintf( 'WP-CLI command %s :-: Completed', $command_name )
		);

	}

	/**
	 * Check if post type is valid.
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	protected function _is_valid_post_type( string $type ) : bool {

		if ( ! empty( $type ) && post_type_exists( $type ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Task handler for each post.
	 *
	 * @param \WP_Post $post
	 */
	protected function _posts_task_runner( \WP_Post $post ) : void {

		$authors = \PMC::get_post_authors_list( $post->ID, 'all', 'display_name' );

		if ( empty( $authors ) ) {
			$this->_warning(
				sprintf(
					'Post ID %1$d is missing an author. Post type: %2$s. Post title: %3$s',
					$post->ID,
					$post->post_type,
					$post->post_title
				)
			);

			if ( 0 < intval( $this->_assign_author ) ) {
				if ( ! $this->dry_run ) {
					$updated = wp_update_post(
						[
							'ID'          => intval( $post->ID ),
							'post_author' => intval( $this->_assign_author ),
						],
						true
					);

					if ( ! is_wp_error( $updated ) ) {
						$this->_success(
							sprintf(
								'Post ID %1$d updated to Author ID %2$d',
								$post->ID,
								$this->_assign_author
							)
						);
					} else {
						$this->_warning(
							sprintf(
								'Post ID %1$d not updated to Author ID %2$d',
								$post->ID,
								$this->_assign_author
							)
						);
					}
				} else {
					$this->_success(
						sprintf(
							'DRY RUN: Post ID %1$d updated to Author ID %2$d',
							$post->ID,
							$this->_assign_author
						)
					);
				}
			}
		}

	}

} //end class

//EOF
