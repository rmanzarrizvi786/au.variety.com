<?php
/**
 * Digital Daily utility functions.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

/**
 * Determine if current context is part of the Digital Daily.
 *
 * @return bool
 */
function is_dd(): bool {
	return is_singular( POST_TYPE ) || is_post_type_archive( POST_TYPE );
}

/**
 * Retrieve ID of latest Digital Daily issue.
 *
 * @return int|null
 */
function get_latest(): ?int {
	// `suppress_filters` is set.
	// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
	$latest = get_posts(
		[
			'post_type'              => POST_TYPE,
			'posts_per_page'         => 1,
			'post_status'            => 'publish',
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'suppress_filters'       => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	return empty( $latest ) ? null : array_shift( $latest );
}

/**
 * Retrieve IDs of issues published before a particular issue.
 *
 * @param int $post_id Post ID for relative date.
 * @param int $qty     Number of IDs to return.
 * @return array|null
 */
function get_previous( int $post_id, int $qty ): ?array {
	// `suppress_filters` is set.
	// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
	$ids = get_posts(
		[
			'post_type'              => POST_TYPE,
			'posts_per_page'         => $qty,
			'post_status'            => 'publish',
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'date_query'             => [
				[
					'before' => get_the_date( 'Y-m-d', $post_id ),
				],
			],
			'fields'                 => 'ids',
			'suppress_filters'       => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	return empty( $ids ) ? null : $ids;
}
