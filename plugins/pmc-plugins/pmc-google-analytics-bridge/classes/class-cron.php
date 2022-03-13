<?php
/**
 * Refresh cache via cron.
 *
 * @package pmc-google-analytics-bridge
 */

namespace PMC\Google_Analytics_Bridge;

use PMC\Global_Functions\Traits\Singleton;

class Cron {
	use Singleton;

	/**
	 * Name of cron event and its hook.
	 */
	protected const CRON_HOOK = 'pmc_ga_bridge_cron_refresh';

	/**
	 * Slug for custom schedule used for refreshes.
	 */
	protected const CRON_SCHEDULE = 'pmc-gab-refresh-schedule';

	/**
	 * Keyed array of cron events to schedule on shutdown.
	 *
	 * @var array
	 */
	protected $_crons = [];

	/**
	 * Cron constructor.
	 */
	public function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		// VIP Go uses Cron Control, shorter intervals are fine.
		// phpcs:ignore WordPress.WP.CronInterval
		add_filter( 'cron_schedules', [ $this, 'add_schedule' ] );
		add_action( 'shutdown', [ $this, 'do_scheduling' ] );

		add_action( static::CRON_HOOK, [ $this, 'do_event' ], 10, 4 );

	}

	/**
	 * Add custom schedule for refreshes.
	 *
	 * @param array $schedules Supported cron schedules.
	 * @return array
	 */
	public function add_schedule( array $schedules ): array {
		$schedules[ static::CRON_SCHEDULE ] = [
			'interval' => ( 5 * MINUTE_IN_SECONDS ) + 15,
			'display'  => 'PMC Google Analytics Bridge refresh schedule',
		];

		return $schedules;
	}

	/**
	 * Process scheduling queue.
	 */
	public function do_scheduling(): void {
		foreach ( $this->_crons as $args ) {
			pmc_schedule_event(
				time() + ( MINUTE_IN_SECONDS / 2 ) + wp_rand( 1, 9 ),
				static::CRON_SCHEDULE,
				static::CRON_HOOK,
				[
					$args,
				]
			);
		}
	}

	/**
	 * Schedule cache rebuild.
	 *
	 * @param string $hook               Hook to invoke to build cached data.
	 * @param array  $ga_api_args        Google Analytics API arguments.
	 * @param int    $cache_life_minutes Cache life in minutes.
	 */
	public static function schedule(
		string $hook,
		array $ga_api_args,
		int $cache_life_minutes
	): void {
		static::get_instance()->queue_scheduling(
			compact(
				'hook',
				'ga_api_args',
				'cache_life_minutes'
			)
		);
	}

	/**
	 * Queue a cron event for scheduling.
	 *
	 * @param array $args Cron event arguments.
	 */
	public function queue_scheduling( array $args ): void {
		$key = md5( wp_json_encode( $args ) );

		if ( isset( $this->_crons[ $key ] ) ) {
			return;
		}

		$this->_crons[ $key ] = $args;
	}

	/**
	 * Refresh a particular cache.
	 *
	 * @param array $args Arguments required to refresh cache.
	 */
	public function do_event( array $args ): void {
		$cache = new Cache(
			$args['hook'],
			$args['ga_api_args'],
			$args['cache_life_minutes']
		);

		$cache->rebuild_cache();
	}
}
