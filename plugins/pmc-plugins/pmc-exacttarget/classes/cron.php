<?php
/**
 * IMPORTANT: ET Object API token has a lifetime of 20 minutes.
 * The api sdk refresh the token if lifetime is less than 5 minutes.
 * This mean we need to re-create the ET Object before 15 minutes elapsed.
 * We also need into consideration cron might be 1 minute behind and server time not in sync, cache grace period, etc..
 * Therefore 20 - 5 - 2 => 13 minutes max for cron interval
 */

namespace PMC\Exacttarget;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Exacttarget\Api;
use PMC\Exacttarget\Cache;

final class Cron {
	use Singleton;

	const INTERVAL = 'pmc_et_client_interval';
	const ACTION   = 'pmc_exacttarget_run_job';

	protected function __construct() {
		add_filter( 'cron_schedules', [ $this, 'filter_cron_schedules' ] );
		add_action( self::ACTION, [ $this, 'run_job' ] );
		\pmc_schedule_event( time() + 60, self::INTERVAL, self::ACTION );
	}

	public function filter_cron_schedules( $schedules ) {
		// Add an interval for 13 minutes
		$schedules[ self::INTERVAL ] = [
			'interval' => 780, // 13 minutes
			'display'  => esc_html__( 'Once every 13 minutes', 'pmc-exacttarget' ),
		];
		return $schedules;
	}

	public function run_job() {
		if ( ! Api::get_instance()->is_active() ) {
			return;
		}

		$client = Api::get_instance()->get_client( [], true );
		if ( ! empty( $client ) ) {
			Cache::get_instance()->refresh();
		}

	}
}
