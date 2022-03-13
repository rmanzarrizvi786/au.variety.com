<?php
/**
 * Class contains PMC Stocks CLI related functions.
 *
 * @since 2016-08-19 - Mike Auteri - PPT-6912
 * @version 2016-08-19 - Mike Auteri - PPT-6912
 */
namespace PMC\Stocks;

use \PMC;

class Cli extends \WPCOM_VIP_CLI_Command {

	/**
	 * Update stock data.
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	function update_stocks( $args, $assoc_args ) {
		$cron = Cron::get_instance();
		$cron->run_bihourly_cron();
	}

	/**
	 * Update historic stock index data.
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	function update_historic_stock_index( $args, $assoc_args ) {
		$cron = Cron::get_instance();
		$cron->run_twice_day_cron();
	}
}