<?php
/**
 * Report results of JSON audits.
 *
 * @package pmc-apple-news
 */

namespace PMC\Apple_News\WP_CLI;

use PMC_WP_CLI_Base;
use PMC\Apple_News\JSON_Audit;

/**
 * Class JSON_Audit_Report.
 */
class JSON_Audit_Report extends PMC_WP_CLI_Base {
	/**
	 * Timestamp representing start of date range to consider.
	 *
	 * @var int
	 */
	protected $_date_start;

	/**
	 * Timestamp representing end of date range to consider.
	 *
	 * @var int
	 */
	protected $_date_end;

	/**
	 * Report audit results for Buy Now buttons.
	 *
	 * ## OPTIONS
	 *
	 * [--format]
	 * : Output format.
	 *
	 * [--batch-size]
	 * : Size of batch query to retrieve logs from postmeta.
	 *
	 * [--start-date]
	 * : Start of date range to consider.
	 *
	 * [--end-date]
	 * : End of date range to consider.
	 *
	 * @subcommand buy-now-buttons
	 *
	 * @codeCoverageIgnore Modifies no data, but important reporting methods are covered.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function buy_now_buttons( array $args, array $assoc_args ): void {
		$this->_extract_common_args( $assoc_args );
		$this->_extract_date_args( $assoc_args );

		$raw_data = $this->_get_button_data();
		$summary  = $this->_summarize_button_data( $raw_data );
		$summary  = $this->_format_summary_for_output( $summary );

		\WP_CLI\Utils\format_items(
			$assoc_args['format'] ?? 'table',
			$summary,
			[
				'category',
				'metric',
				'value',
			]
		);
	}

	/**
	 * Extract date-range arguments.
	 *
	 * @param array $assoc_args Associative arguments.
	 */
	protected function _extract_date_args( array $assoc_args ): void {
		if ( isset( $assoc_args['start-date'] ) ) {
			$this->_date_start = strtotime(
				$assoc_args['start-date'] . ' 00:00:00'
			);
		}

		if ( isset( $assoc_args['end-date'] ) ) {
			$this->_date_end = strtotime(
				$assoc_args['end-date'] . ' 23:59:59'
			);
		}
	}

	/**
	 * Retrieve all logs from postmeta.
	 *
	 * @return array
	 */
	protected function _get_button_data(): array {
		global $wpdb;

		$i    = 0;
		$data = [];

		do {
			// Disabling due to CLI context.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT %d,%d",
					JSON_Audit::META_KEY_BUY_NOW_BUTTONS,
					$i++,
					$this->batch_size
				)
			);
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$data[] = $result;

			$this->stop_the_insanity();
		} while ( $result && count( $result ) === $this->batch_size );

		$data = array_merge( ...$data );
		$data = wp_list_pluck( $data, 'meta_value', 'post_id' );

		// wp_list_pluck() always returns an array.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		$data = array_map( 'maybe_unserialize', $data );

		return $data;
	}

	/**
	 * Parse logs from postmeta into a human-readable summary.
	 *
	 * @param array $data Postmeta logs.
	 * @return array
	 */
	protected function _summarize_button_data( array $data ): array {
		// How many buttons didn't get published after using max num of retries?
		$hard_fail = [
			'posts'   => 0,
			'missing' => 0,
			'total'   => 0,
		];

		// How many buttons succeeded after failing at least once?
		$soft_fail = [
			'posts'   => 0,
			'buttons' => 0,
			'total'   => 0,
		];

		// Average number of retries needed for all buttons to display?
		$retries = [
			'posts' => 0,
			'count' => 0,
		];

		$effectiveness = [];

		foreach ( $data as $datum ) {
			$log_date = end( $datum );
			$log_date = explode( ' ', $log_date['timestamp'] );
			$log_date = (int) array_pop( $log_date );
			reset( $datum );

			// Filter by date.
			if ( null !== $this->_date_start && $this->_date_start > $log_date ) {
				continue;
			}
			if ( null !== $this->_date_end && $this->_date_end < $log_date ) {
				continue;
			}

			// Retries increased to 10 on the date noted below.
			if ( $log_date > strtotime( '2020-07-31 23:59:48 +0000' ) ) {
				$max_retries = 10;
			} else {
				$max_retries = 3;
			}
			$hard_fail_key = $max_retries - 1;

			/**
			 * Considered a hard fail if, even after the maximum number of
			 * retries, buttons are still missing.
			 */
			if (
				count( $datum ) === $max_retries
				&& $datum[ $hard_fail_key ]['errors']['counts']['shortcodes']
					!== $datum[ $hard_fail_key ]['errors']['counts']['products']
			) {
				$hard_fail['posts']++;

				$hard_fail['missing'] += $datum[ $hard_fail_key ]['errors']['counts']['shortcodes'];
				$hard_fail['missing'] -= $datum[ $hard_fail_key ]['errors']['counts']['products'];

				$hard_fail['total'] += $datum[ $hard_fail_key ]['errors']['counts']['shortcodes'];
			} else {
				// To determine average number of retries needed for success.
				// As a soft fail, a successful retry was not recorded.
				$retries['posts']++;
				$retries['count'] += 1 + count( $datum );

				$soft_fail['posts']++;

				// To determine how many succeeded after failing once.
				// We are only concerned with how many failed at the start.
				$initial_fail = array_shift( $datum );

				$soft_fail['buttons'] += $initial_fail['errors']['counts']['shortcodes'];
				$soft_fail['buttons'] -= $initial_fail['errors']['counts']['products'];

				$soft_fail['total'] += $initial_fail['errors']['counts']['shortcodes'];
			}
		}

		$hard_fail['average']         = $hard_fail['posts']
			? $hard_fail['missing'] / $hard_fail['posts']
			: 0;
		$hard_fail['average_buttons'] = $hard_fail['posts']
			? $hard_fail['total'] / $hard_fail['posts']
			: 0;

		$soft_fail['average']         = $soft_fail['posts']
			? $soft_fail['buttons'] / $soft_fail['posts']
			: 0;
		$soft_fail['average_buttons'] = $soft_fail['posts']
			? $soft_fail['total'] / $soft_fail['posts']
			: 0;

		$retries['average'] = $retries['posts']
			? $retries['count'] / $retries['posts']
			: 0;

		$post_count = $hard_fail['posts'] + $soft_fail['posts'];

		$effectiveness['percent_success'] = $post_count
			? $soft_fail['posts'] / $post_count
			: 0;
		$effectiveness['percent_fail']    = $post_count
			? $hard_fail['posts'] / $post_count
			: 0;

		return compact(
			'hard_fail',
			'soft_fail',
			'retries',
			'effectiveness'
		);
	}

	/**
	 * Format raw summary for WP-CLI's output formatter.
	 *
	 * @param array $summary Summarized log data.
	 * @return array
	 */
	protected function _format_summary_for_output( array $summary ): array {
		$output = [];

		foreach ( $summary as $category => $metrics ) {
			foreach ( $metrics as $metric => $value ) {
				$output[] = compact(
					'category',
					'metric',
					'value'
				);
			}
		}

		return $output;
	}
}
