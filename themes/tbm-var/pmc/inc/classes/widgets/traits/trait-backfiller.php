<?php
/*
 * trait Backfiller, used by Widget classes.
 *
 * @codeCoverageIgnore
 */
namespace PMC\Core\Inc\Widgets\Traits;

/**
 * Auto load partials for widgets.
 */
trait Backfiller {

	/**
	 * Query args for backfilling.
	 *
	 * @return array WP_Query args.
	 */
	abstract protected function _backfill_criteria();

	/**
	 * The number of posts to output.
	 *
	 * @return int
	 */
	protected function _min_count() : int {
		return 2;
	}

	/**
	 * Backfill posts.
	 *
	 * @param  array $data Post IDs.
	 * @return array WP_Post objects.
	 */
	protected function _backfill_posts( array $data ) : array {
		return ( new \PMC\Core\Inc\Curated_Posts( $this->_min_count(), [ $data, $this->_backfill_criteria() ] ) )->get_posts();
	}
}
