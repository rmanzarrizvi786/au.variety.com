<?php
/**
 * Class contains PMC Stocks Cron related functions.
 *
 * @since 2016-07-18 - Mike Auteri - PPT-6906
 * @version 2016-07-18 - Mike Auteri - PPT-6906
 */
namespace PMC\Stocks;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Cron {

	use Singleton;

	public $database = '';

	protected function __construct() {
		$this->_setup_hooks();
		$this->create_cron_events();
	}

	protected function _setup_hooks() {
		add_filter( 'cron_schedules', array( $this, 'add_bihourly' ) );
		add_action( 'pmc_save_twice_day_stocks_data', array( $this, 'run_twice_day_cron' ) );
		add_action( 'pmc_save_bihourly_stocks_data', array( $this, 'run_bihourly_cron' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'init', [ $this, 'register_rewrite_rules' ] );
	}

	/**
	 * Register rewrite rules
	 */
	public function register_rewrite_rules() {
		add_rewrite_endpoint( 'pmc-stocks-cron', EP_ROOT );
		add_rewrite_endpoint( 'pmc-historic-stocks-cron', EP_ROOT );
	}

	public function add_bihourly( $schedules ) {
		$schedules['pmc_stocks_bihourly'] = array(
			'interval' => HOUR_IN_SECONDS * 2,
			'display'  => __( 'Once Every 2 Hours' )
		);

		$schedules['pmc_stocks_twice_day'] = array(
			'interval' => HOUR_IN_SECONDS * 12,
			'display'  => __( 'Once Every 12 Hours' )
		);

		return $schedules;
	}

	/**
	 * Creates the cron events.
	 */
	public function create_cron_events() {
		wp_clear_scheduled_hook( 'pmc_save_daily_stocks_data' ); // clear old cron.

		if ( ! wp_next_scheduled( 'pmc_save_bihourly_stocks_data' ) ) {
			wp_schedule_event( time(), 'pmc_stocks_bihourly', 'pmc_save_bihourly_stocks_data' );
		}

		if ( ! wp_next_scheduled( 'pmc_save_twice_day_stocks_data' ) ) {
			wp_schedule_event( strtotime( '+5 minutes' ), 'pmc_stocks_twice_day', 'pmc_save_twice_day_stocks_data' );
		}
	}

	/**
	 * Twice daily cron that gets run. Keeps historical stock index data updated twice daily.
	 *
	 * @return bool
	 */
	public function run_twice_day_cron() {
		$api = Api::get_instance();
		$response = array();

		$configs = $api->get_stocks_configs();

		if ( ! empty ( $configs ) && is_array( $configs ) ) {
			if ( ! empty( $configs['database'] ) ) {
				$this->database = sanitize_key( $configs['database'] );
				$response[] = $this->record_success( 'BigQuery database is set: ' . $this->database );
			} else {
				$response[] = $this->record_error( 'BigQuery database not set' );
			}
		}

		if ( empty ( $this->database ) ) {
			return $response;
		}

		$lock = 'pmc_stocks_lock_twice_daily';
		$lock_value = mt_rand(); // Prevent race conditions

		if ( get_transient ( $lock ) && $lock_value !== get_transient ( $lock ) ) {
			$response[] = $this->record_line( 'Script is currently locked' );
			return $response;
		}

		set_transient( $lock, $lock_value, HOUR_IN_SECONDS );

		// Secondary to prevent race conditions
		// Please Note: this is ONLY run in a CLI, Cron, and admin API end point and is to prevent running stuff simultaneously.
		sleep( mt_rand( 1, 5 ) );

		if ( get_transient ( $lock ) && $lock_value !== get_transient ( $lock ) ) {
			$response[] = $this->record_line( 'Script is currently locked' );
			return $response;
		}

		if ( $lock_value === get_transient( $lock ) ) {
			$response[] = $this->record_success( 'Lock in place' );
		} else {
			$response[] = $this->record_error( 'Error placing lock' );
		}

		$response[] = $this->record_line( 'Updating historic stock index...' );

		$this->update_historic_stock_index( $api );

		$response[] = $this->record_line( 'Done.' );

		// deletes all stocks cache.
		$cache_deleted = $api->delete_stocks_cache();

		if ( $cache_deleted ) {
			$response[] = $this->record_success( 'Cache deleted' );
		} else {
			$response[] = $this->record_error( 'Error deleting cache or some cache was not yet set' );
		}

		delete_transient( $lock );

		if ( ! get_transient( $lock ) ) {
			$response[] = $this->record_success( 'Lock was removed.' );
		} else {
			$response[] = $this->record_error( 'Error removing lock.' );
		}

		return $response;
	}

	/**
	 * Bihourly cron that gets run. Keeps data updated almost real-time.
	 *
	 * @return bool
	 */
	public function run_bihourly_cron() {
		$api = Api::get_instance();
		$response = array();

		$configs = $api->get_stocks_configs();

		if ( ! empty ( $configs ) && is_array( $configs ) ) {
			if ( ! empty( $configs['database'] ) ) {
				$this->database = sanitize_key( $configs['database'] );
				$response[] = $this->record_success( 'BigQuery database is set: ' . $this->database );
			} else {
				$response[] = $this->record_error( 'BigQuery database not set' );
			}
		}

		if ( empty ( $this->database ) ) {
			return $response;
		}

		$lock = 'pmc_stocks_lock_bihourly';
		$lock_value = mt_rand(); // Prevent race conditions

		if ( get_transient ( $lock ) && $lock_value !== get_transient ( $lock ) ) {
			$response[] = $this->record_line( 'Script is currently locked' );
			return $response;
		}

		set_transient( $lock, $lock_value, HOUR_IN_SECONDS );

		// Secondary to prevent race conditions
		// Please Note: this is ONLY run in a CLI, Cron, and admin API end point and is to prevent running stuff simultaneously.
		sleep( mt_rand( 1, 5 ) );

		if ( get_transient ( $lock ) && $lock_value !== get_transient ( $lock ) ) {
			$response[] = $this->record_line( 'Script is currently locked' );
			return $response;
		}

		if ( $lock_value === get_transient( $lock ) ) {
			$response[] = $this->record_success( 'Lock in place' );
		} else {
			$response[] = $this->record_error( 'Error placing lock' );
		}

		$response[] = $this->record_line( 'Updating stocks...' );

		$this->update_stock( $api );

		$response[] = $this->record_line( 'Done.' );
		$response[] = $this->record_line( 'Updating stock index...' );

		$this->update_stock_index( $api );

		$response[] = $this->record_line( 'Done.' );
		$response[] = $this->record_line( 'Updating stock summary...' );

		$this->update_stock_summary( $api );

		$response[] = $this->record_line( 'Done.' );

		// deletes all stocks cache.
		$cache_deleted = $api->delete_stocks_cache();

		if ( $cache_deleted ) {
			$response[] = $this->record_success( 'Cache deleted' );
		} else {
			$response[] = $this->record_error( 'Error deleting cache or some cache was not yet set' );
		}

		delete_transient( $lock );

		if ( ! get_transient( $lock ) ) {
			$response[] = $this->record_success( 'Lock was removed.' );
		} else {
			$response[] = $this->record_error( 'Error removing lock.' );
		}

		return $response;
	}

	/**
	 * Helper success function.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function record_success( $value ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::success( $value );
		}
		return 'Success: ' . sanitize_text_field( $value );
	}

	/**
	 * Helper error function.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function record_error( $value ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::error( $value, false );
		}
		return 'Error: ' . sanitize_text_field( $value );
	}

	/**
	 * Helper line function.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function record_line( $value ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::line( $value );
		}
		return sanitize_text_field( $value );
	}

	/**
	 * Endpoint for forcing updates on stock data.
	 */
	public function template_redirect() {
		global $wp_query;
		if ( empty( $wp_query->query_vars['pmc-stocks-cron'] ) && empty( $wp_query->query_vars['pmc-historic-stocks-cron'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You cannot do this.' );
		}

		$response = array();

		if ( ! empty( $wp_query->query_vars['pmc-stocks-cron'] ) ) {
			$response = $this->run_bihourly_cron();
		} else if ( ! empty ( $wp_query->query_vars['pmc-historic-stocks-cron'] ) ) {
			$response = $this->run_twice_day_cron();
		}

		wp_send_json_success( $response );
	}

	/**
	 * Cron method that adds and updates pmc-stock posts.
	 *
	 * @param $api
	 *
	 * @return void|bool
	 */
	public function update_stock( $api ) {
		$post_type = 'pmc-stock';

		if ( empty( $this->database ) ) {
			return false;
		}

		$sql = "select * from " . sanitize_key( $this->database ) . ".stock_data_symbols_summary";

		// Note: $stocks keys/values are sanitized in bigquery_request method.
		$stocks = $api->bigquery_request( $sql );

		foreach ( (array) $stocks as $stock ) {
			if ( empty( $stock['name'] ) ) {
				continue;
			}

			// Need to escape name to match.
			$name = esc_html( $stock['name'] );

			$post_id = $api->create_stock_type( $name, $post_type );

			if ( ! intval( $post_id ) ) {
				continue;
			}

			// Check and update post meta.
			$prev_post_meta = get_post_meta( $post_id, $api->meta_key, true );

			if ( empty( $prev_post_meta ) ) {
				$prev_post_meta = '';
			}

			update_post_meta( $post_id, $api->meta_key, $stock, $prev_post_meta );

			// Add or Replace taxonomy term for region and category.
			if ( ! empty( $stock['region'] ) ) {
				wp_set_object_terms( $post_id, sanitize_text_field( strtolower( $stock['region'] ) ), 'pmc-stock-region', false );
			}

			if ( ! empty( $stock['category'] ) ) {
				wp_set_object_terms( $post_id, sanitize_text_field( strtolower( $stock['category'] ) ), 'pmc-stock-category', false );
			}

			$message = 'Updated: ' . $post_id . ' | ' . $name . ' | ' . $post_type;
			$this->record_line( $message );
		}

	}

	/**
	 * Cron method that adds and updates all pmc-stock-index posts.
	 *
	 * @param $api
	 *
	 * @return bool
	 */
	public function update_historic_stock_index( $api ) {
		$post_type = 'pmc-stock-index';

		if ( empty( $this->database ) ) {
			return false;
		}

		$sql = "select * from " . sanitize_key( $this->database ) . ".index_stock_data";

		// Note: $stocks keys/values are sanitized in bigquery_request method.
		$stocks = $api->bigquery_request( $sql );

		foreach ( (array) $stocks as $stock ) {
			if ( empty( $stock['date'] ) && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $stock['date'] ) ) {
				continue;
			}

			$date = sanitize_text_field( $stock['date'] );

			$args = array(
				'post_date'     => date( $date ),
				'post_date_gmt' => date( $date ),
			);

			$post_id = $api->create_stock_type( $date, $post_type, $args );

			if ( ! intval( $post_id ) ) {
				return false;
			}

			// Check and update post meta.
			$prev_post_meta = get_post_meta( $post_id, $api->meta_key, true );

			if ( empty( $prev_post_meta ) ) {
				$prev_post_meta = '';
			}

			update_post_meta( $post_id, $api->meta_key, $stock, $prev_post_meta );

			$message = 'Updated: ' . $post_id . ' | ' . $date . ' | ' . $post_type;
			$this->record_line( $message );
		}
	}

	/**
	 * Cron method that adds and updates recent pmc-stock-index posts.
	 *
	 * @param $api
	 *
	 * @return void|bool
	 */
	public function update_stock_index( $api ) {
		global $wpdb;

		$post_type = 'pmc-stock-index';

		if ( empty( $this->database ) ) {
			return false;
		}

		$sql = "select * from " . sanitize_key( $this->database ) . ".index_stock_data";

		$date = $api->get_the_start_date( $post_type );

		if ( $date ) {
			// Note: Using prepare for escaping SQL for BigQuery.
			$sql  =  $wpdb->prepare( $sql . " where date >= %s", $date );
		} else {
			// If no date, let's not go crazy and update everything. That's why we have update_historic_stock_index
			return false;
		}

		// Note: $stocks keys/values are sanitized in bigquery_request method.
		$stocks = $api->bigquery_request( $sql );

		foreach ( (array) $stocks as $stock ) {
			if ( empty( $stock['date'] ) && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $stock['date'] ) ) {
				continue;
			}

			$date = sanitize_text_field( $stock['date'] );

			$args = array(
				'post_date'     => date( $date ),
				'post_date_gmt' => date( $date ),
			);

			$post_id = $api->create_stock_type( $date, $post_type, $args );

			update_post_meta( $post_id, $api->meta_key, $stock );

			$message = 'Updated: ' . $post_id . ' | ' . $date . ' | ' . $post_type;
			$this->record_line( $message );
		}
	}

	/**
	 * Cron method that adds and updates pmc-stock-summary posts.
	 *
	 * @param $api
	 *
	 * @return void|bool
	 */
	public function update_stock_summary( $api ) {
		$post_type = 'pmc-stock-summary';

		if ( empty( $this->database ) ) {
			return false;
		}

		$sql = "select * from " . sanitize_key( $this->database ) . ".index_stock_data_symbols_summary";
		$stock = $api->bigquery_request( $sql );

		if ( empty( $stock ) || ! is_array( $stock ) ) {
			return false;
		}

		$stock = current( $stock );
		$name = 'PMC Stock Summary';
		$post_id = $api->create_stock_type( $name, $post_type );

		if ( ! intval( $post_id ) ) {
			return false;
		}

		// Check and update post meta.
		$prev_post_meta = get_post_meta( $post_id, $api->meta_key, true );

		if ( empty( $prev_post_meta ) ) {
			$prev_post_meta = '';
		}

		update_post_meta( $post_id, $api->meta_key, $stock, $prev_post_meta );

		$message = 'Updated: ' . $post_id . ' | ' . $name . ' | ' . $post_type;
		$this->record_line( $message );
	}

}

// EOF
