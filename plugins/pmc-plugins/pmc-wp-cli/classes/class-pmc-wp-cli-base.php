<?php

/*
 * Command base class for all wp cli command process
 */

class PMC_WP_CLI_Base extends WPCOM_VIP_CLI_Command {
	public $log_file       = '';
	public $sleep          = 2;   // number of second to sleep
	public $max_iteration  = 20;  // number of iteration before calling sleep if requested
	public $batch_size     = 500; // default batch size
	public $batch_num_posts_processed = 0;
	public $batch_found_posts         = 0;
	public $batch_max_num_pages       = 0;
	public $batch_progress_bar        = null;
	public $batch_paged               = 1;
	public $batch_max_paged           = 0;
	public $dry_run                   = true;
	public $need_confirm              = false;
	public $disable_es_index          = false;
	public $email_when_done           = true;
	public $email                     = 'dist.dev@pmc.com';
	public $email_logfile             = true;
	public $file_rotated              = false;

	protected $_iteraction_count = 0;
	protected $_assoc_args_properties_mapping = array();
	protected $_assoc_args = [];

	public function __construct( $args = false , $assoc_args = false ) {
		$this->_extract_common_args( $assoc_args );
	}

	protected function _extract_common_args( $assoc_args ) {

		// default override value if not on VIP
		if ( ! defined( 'WPCOM_IS_VIP_ENV' ) || ! WPCOM_IS_VIP_ENV ) {
			$this->email_when_done = false;
			$this->email           = '';
		}

		$this->_assoc_args = $assoc_args;

		if ( empty( $assoc_args ) ) {
			return false;
		}

		if ( ! empty( $assoc_args['log-file'] ) ) {
			$this->log_file = $assoc_args['log-file'];

			// Rotate existing log filename if needed
			$this->possibly_rotate_file( $this->log_file );
		}

		// Specifically NOT using empty() here because sleep may be set to 0
		if ( isset( $assoc_args['sleep'] ) ) {
			$this->sleep = (int)$assoc_args['sleep'];
		}

		if ( ! empty( $assoc_args['max-iteration'] ) ) {
			$this->max_iteration = (int)$assoc_args['max-iteration'];
		}

		if ( ! empty( $assoc_args['batch-size' ] ) ) {
			$this->batch_size = (int)$assoc_args['batch-size'];

			if ( $this->batch_size > 10000 ) {
				$this->batch_size = 10000;
			} else if ( $this->batch_size < 10 ) {
				$this->batch_size = 10;
			}
		}

		if ( ! empty( $assoc_args['batch-paged'] ) ) {
			$this->batch_paged = (int) $assoc_args['batch-paged'];
		}

		if ( ! empty( $assoc_args['batch-max-paged'] ) ) {
			$this->batch_max_paged = (int) $assoc_args['batch-max-paged'];
		}

		if ( isset( $assoc_args['dry-run'] ) ) {
			if ( false === $assoc_args['dry-run'] ) {
				$this->dry_run = false;
			} else {
				$this->dry_run = in_array( strtolower( $assoc_args['dry-run'] ), [ '', 'yes', 'true', '1' ], true );
			}
		}

		if ( isset( $assoc_args['need-confirm'] ) ) {
			$this->need_confirm = 'false' !== $assoc_args['need-confirm'];
		}

		if ( isset( $assoc_args['disable-es-index'] ) ) {
			$this->disable_es_index = 'false' !== $assoc_args['disable-es-index'];
		}

		if ( isset( $assoc_args['email'] ) ) {
			$this->email = $assoc_args['email'];
		}
		if ( isset( $assoc_args['email-when-done'] ) ) {
			$this->email_when_done = in_array( strtolower( $assoc_args['email-when-done'] ), [ '', 'yes', 'true', '1' ], true );
		}
		if ( isset( $assoc_args['email-logfile'] ) ) {
			$this->email_logfile = in_array( strtolower( $assoc_args['email-logfile'] ), [ '', 'yes', 'true', '1' ], true );
		}

		if ( ! empty( $this->_assoc_args_properties_mapping ) ) {
			foreach ( $this->_assoc_args_properties_mapping as $key => $name ) {
				if ( isset( $assoc_args[ $name ] ) ) {
					if ( in_array( strtolower( $assoc_args[ $name ] ), [ 'true', 'false' ], true ) ) {
						$this->$key = 'true' === $assoc_args[ $name ];
					} else {
						$this->$key = $assoc_args[ $name ];
					}
				}
			}
		}

	}

	protected function _confirm_before_continue( $msg ) {
		if ( $this->need_confirm ) {
			WP_CLI::confirm( $msg );
		}
	}

	/*
	 * @deprecated refer to _update_iteration, function to be phased out and remove in the future
	 */
	protected function _update_interation() {
		$this->_update_iteration();
	}

	protected function _update_iteration() {
		$this->_iteraction_count++;

		if ( $this->sleep > 0 && $this->max_iteration > 0 && $this->_iteraction_count > $this->max_iteration ) {
			$this->_iteraction_count = 0;
			WP_CLI::log( "Sleep for {$this->sleep} seconds..." );
			$this->stop_the_insanity();

			// We don't want to do code coverage that would trigger sleep and pause the testing
			sleep( $this->sleep );  // @codeCoverageIgnore
		}

	}

	/**
	 * IMPORTANT NOTE: DO NOT use this function if callback function make changes to dataset that affect
	 * the query where pagination might be shifting the next page resultset.
	 *
	 * Run large batch tasks with WP_Query
	 *
	 * Identical to normal WP_Query calls with the following additions:
	 * + Runs in batches, handles looping through all paged results.
	 * + Clears objects which consume memoryâ€”stop_the_insanity()
	 * + Passes each found post through a given callback function.
	 * + Displays a progress bar of completeness for processing all the found posts
	 *
	 * @param array    $args          An array of WP_Query arguments
	 * @param function $callable      Call a given function on each found post
	 * @param string   $progress_text Progress bar text message. Defaults to 'Processing Posts'.
	 *
	 * @return null
	 */
	protected function batch_wp_query_task_runner( $args = array(), $callback = null ) {

		if ( empty( $callback ) || ! is_callable( $callback ) ) {
			return;
		}

		$args = wp_parse_args( $args, array(
			'post_type'      => 'any',
			'posts_per_page' => $this->batch_size,
			'paged'          => $this->batch_paged,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		) );

		$query = new WP_Query( $args );

		$this->batch_found_posts   = intval( $query->found_posts );
		$this->batch_max_num_pages = intval( $query->max_num_pages );

		if ( 0 === $this->batch_max_paged ) {
			$this->batch_max_paged = $query->max_num_pages;
		}

		$this->_write_log( "BATCH - Found posts: $this->batch_found_posts", false, true );
		$this->_write_log( "BATCH - Total Query pages: $this->batch_max_num_pages", false, true );

		$this->batch_progress_bar = WP_CLI\Utils\make_progress_bar( "Processing $this->batch_found_posts post(s)..", $this->batch_found_posts );

		do {
			$this->_write_log( "--------------------------", false, true );
			$this->_write_log( "BATCH - Current Query page: {$args['paged']}", false, true );
			$this->_write_log( "--------------------------", false, true );

			if ( 1 < $args['paged'] ) {
				$query = new WP_Query( $args );
			}

			// Run each returned post through the provided callback function
			array_walk(
				$query->posts,
				$callback
			);

			if ( 0 < $this->sleep ) {
				$this->_write_log( "BATCH - Sleep for $this->sleep seconds...", false, true );
				sleep( $this->sleep );
			}

			$args['paged']++;

			$this->stop_the_insanity();

		} while (
			$query->found_posts && $args['paged'] <= $query->max_num_pages && $args['paged'] <= $this->batch_max_paged
		);
	}

	/**
	 * Update the batch WP_Query task runner progress bar
	 *
	 * @param null
	 *
	 * @return null
	 */
	protected function batch_update_progress_bar() {

		$this->batch_progress_bar->tick();

		$this->batch_num_posts_processed++;

		if ( $this->batch_num_posts_processed === $this->batch_found_posts ) {
			$this->batch_progress_bar->finish();
		}
	}

	/**
	 * We we at the end of the current batch?
	 *
	 * @return bool
	 */
	protected function is_end_of_batch() {

		if (
			( 0 === $this->batch_num_posts_processed % $this->batch_size )
			||
			( $this->batch_num_posts_processed === $this->batch_found_posts )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Write data to a CSV file
	 *
	 * @param string $file           The full/absolute path to the csv file to create
	 * @param array  $column_headers An *optional* array of csv column headers
	 * @param array  $data           An array of data to write to the CSV. Each row to a line.
	 * @param null   $callback       An *optional* callback function to adjust the data being written to the csv lines.
	 * @param string $fopen_mode     What mode should the CSV be created with? Defaults to 'w' for write-only mode.
	 *
	 * @return string|bool Written filename on success, false on failure.
	 */
	protected function write_to_csv( $file = '', $column_headers = array(), $data = array(), $callback = null, $fopen_mode = 'w' ) {

		if ( ! is_string( $file ) || empty( $file ) ) {
			return false;
		}

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		// Rename an existing log file if it exists already
		// ..we don't want to overwrite existing logs.
		// Only do this when writing to a CSVâ€”i.e. not when
		// a CSV is being appended to.
		if ( 'w' === $fopen_mode ) {
			$this->possibly_rotate_file( $file );
		}

		$csv = fopen( $file, $fopen_mode );

		// Only create column headers if they've been provided
		if ( ! empty( $column_headers ) && is_array( $column_headers ) ) {
			fputcsv( $csv, $column_headers );
		}

		$put_success = false;

		foreach( $data as $key => $value ) {

			$csv_data = $value;

			// Optionally run the data through a callback function on-the-fly
			// helpful for writing out data in multi-dimensional arrays
			if ( ! empty( $callback ) && is_callable( $callback ) ) {
				$csv_data = call_user_func( $callback, $key, $value );
			}

			$put_success = fputcsv( $csv, (array) $csv_data );
		}

		fclose( $csv );

		if ( false !== $put_success ) {

			// Return the filename on success
			return $file;

		} else {
			return false;
		}
	}

	/**
	 * Incrementally write to the CSV when every batch iteration is complete.
	 *
	 * Saving this data on each batch helps keep our logging arrays small
	 * (as opposed to building a massive array, looping through, and creating CSV's)
	 *
	 * @param array  $column_headers
	 * @param array  $data
	 * @param string $csv_file
	 * @param int    $posts_processed
	 * @param int    $batch_size
	 *
	 * @return bool|string
	 */
	protected function batch_increment_csv( $column_headers = array(), $data = array(), $csv_file = '', $posts_processed = 0, $batch_size = 0, $found_posts = 0 ){

		$success = false;

		// Is this the end of the current batch?
		if ( $this->is_end_of_batch() ) {

			if ( ! empty( $data ) && is_array( $data ) ) {

				$col_headers = array();

				// Write the column headers if this is the first iteration to the csv
				if ( in_array( $posts_processed, array( $batch_size, $found_posts ), true ) || $this->file_rotated ) {
					$col_headers = $column_headers;
					$this->file_rotated = false;
				}

				// Write the csv using the append mode
				$csv_status = $this->write_to_csv(
					$csv_file,
					$col_headers,
					$data,
					null,
					'a'
				);

				if ( false !== $csv_status ) {
					$success = true;
				}
			}
		}

		if ( $success ) {
			return $csv_file;
		} else {
			return false;
		}
	}

	/**
	 * Method to add a log entry and to output message on screen
	 *
	 * @param string $msg             Message to add to log and to outout on screen
	 * @param int    $msg_type        Message type - 0 for normal line, -1 for error, 1 for success, 2 for warning
	 * @param bool   $suppress_stdout If set to TRUE then message would not be shown on screen
	 * @return void
	 */
	protected function _write_log( $msg, $msg_type = 0, $suppress_stdout = false ) {

		// backward compatibility
		if ( $msg_type === true ) {
			// its an error
			$msg_type = -1;
		} elseif ( $msg_type === false ) {
			// normal message
			$msg_type = 0;
		}

		$msg_type = intval( $msg_type );

		$msg_prefix = '';

		// Message prefix for use in log file
		switch ( $msg_type ) {

			case -1:
				$msg_prefix = 'Error: ';
				break;

			case 1:
				$msg_prefix = 'Success: ';
				break;

			case 2:
				$msg_prefix = 'Warning: ';
				break;

		}

		// Log message to log file if a log file
		// path has been specified
		if ( ! empty( $this->log_file ) ) {
			file_put_contents( $this->log_file, $msg_prefix . $msg . "\n", FILE_APPEND );
		}

		// If we don't want output shown on screen then
		// bail out
		if ( $suppress_stdout === true ) {
			return;
		}

		switch ( $msg_type ) {

			case -1:
				WP_CLI::error( $msg );
				break;

			case 1:
				WP_CLI::success( $msg );
				break;

			case 2:
				WP_CLI::warning( $msg );
				break;

			case 0:
			default:
				WP_CLI::log( $msg );
				break;

		}

	}

	/**
	 * Method to log an error message and stop the script from running further
	 *
	 * @param string $msg Message to add to log and to outout on screen
	 * @return void
	 */
	protected function _error( $msg ) {
		$this->_write_log( $msg, -1 );
	}

	/**
	 * Method to log a success message
	 *
	 * @param string $msg Message to add to log and to outout on screen
	 * @return void
	 */
	protected function _success( $msg ) {
		$this->_write_log( $msg, 1 );
	}

	/**
	 * Method to log a warning message
	 *
	 * @param string $msg Message to add to log and to outout on screen
	 * @return void
	 */
	protected function _warning( $msg ) {
		$this->_write_log( $msg, 2 );
	}

	/**
	 * Return the flag value or, if it's not set, the $default value.
	 *
	 * A copy of WP_CLI\Util\get_flag_value() included for VIP classic compat.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array  $assoc_args  Arguments array.
	 * @param string $flag        Flag to get the value.
	 * @param mixed  $default     Default value for the flag. Default: NULL
	 * @return mixed
	 */
	protected static function _get_flag_value( $assoc_args, $flag, $default = null ) {
		return isset( $assoc_args[ $flag ] ) ? $assoc_args[ $flag ] : $default;
	}

	/**
	 * Possibly rotate a file
	 *
	 * Useful for rotating log files
	 *
	 * @param string $file       The file to possibly rotate. Should be an absolute path.
	 * @param int $max_file_size The max filesize each file should be.
	 *                           When set to 0, this function simply rotates
	 *                           existing log files regardless of size.
	 *
	 * @return string            New file name (or old if it didn't rotate out)
	 */
	protected function possibly_rotate_file( $file = '', $max_file_size = 0 ) {

		if ( ! empty( $file ) && file_exists( $file ) ) {

			clearstatcache();

			if ( filesize( $file ) > $max_file_size ) {

				$i = 1;

				do {
					$rotated_file_name = $file . '.' . $i ;
					$i++;
				} while (
					file_exists( $rotated_file_name )
				);

				rename( $file, $rotated_file_name );

				$this->file_rotated = true;

				return $rotated_file_name;
			}
		}

		return $file;
	}

	protected function _disable_es_index() {
		$this->_write_log( 'Disable ES indexing');
		if ( ! class_exists( 'ES_WP_Indexing_Trigger' ) || $this->dry_run ) {
			return;
		}
		ES_WP_Indexing_Trigger::get_instance()->disable();
	}

	protected function _enable_es_index() {
		$this->_write_log( 'Enable ES indexing');
		if ( ! class_exists( 'ES_WP_Indexing_Trigger' ) || $this->dry_run ) {
			return;
		}
		ES_WP_Indexing_Trigger::get_instance()->enable();
	}

	protected function _trigger_es_bulk_index() {
		global $blog_id;

		$this->_write_log( 'Trigger ES bulk indexing' );
		if ( ! class_exists( 'ES_WP_Indexing_Trigger' ) || $this->dry_run ) {
			return;
		}
		ES_WP_Indexing_Trigger::get_instance()->enable();
		ES_WP_Indexing_Trigger::get_instance()->trigger_bulk_index( $blog_id, 'pmc_wp_cli' );
	}

	protected function _notify_start( $msg = '' ) {
		$this->_write_log( sprintf('%s%s', $msg,  $this->dry_run ? ' - Dry run' : '' ) );
		if ( $this->disable_es_index ) {
			$this->_disable_es_index();
		}
		$this->start_bulk_operation();
	}

	protected function _notify_done( $msg = 'WP CLI Script Completed', $attachments = [] ) {
		$this->end_bulk_operation();
		if ( $this->disable_es_index ) {
			$this->_trigger_es_bulk_index();
		}

		$message = sprintf('%s%s', $msg,  $this->dry_run ? ' - Dry run' : '' );

		$this->_write_log( $message );

		if ( $this->email_when_done && ! empty( $this->email ) ) {

			$subject = sprintf( 'WP CLI Script Completed (%s)', wp_parse_url( home_url(), PHP_URL_HOST ) );
			try {
				$arguments = \WP_CLI::get_runner()->arguments;
				if ( ! empty( $arguments ) ) {
					$subject .= ': ' . implode( ' ', (array) $arguments );
				}
				// There is no efficient way to trigger exception to cover the following codes
				// @codeCoverageIgnoreStart
			} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// There is no efficient way to trigger exception to cover the following codes
				// @codeCoverageIgnoreEnd
			}

			$headers = '';
			if ( $this->email_logfile && ! empty( $this->log_file ) ) {
				$attachments[] = $this->log_file;
			}
			$status = wp_mail( $this->email, $subject, $message, $headers, $attachments );

			$this->_write_log( sprintf( '%s to %s', $status ? 'Email sent' : 'Error sending email', $this->email ) );

		}

	}

	/**
	 * Method to check if input file exists on specified path and is readable.
	 * If its not the case then it throws error and exits the script.
	 *
	 * @param string $file_path
	 * @param string $command_name
	 *
	 * @return void
	 */
	protected function validate_input_file( string $file_path = '', string $command_name = '' ) : void {

		if ( ! empty( $file_path ) ) {

			if ( ! is_file( $file_path ) || ! is_readable( $file_path ) || validate_file( $file_path ) !== 0 ) {
				$file_path = '';
			}

		}

		$command_name = ( empty( $command_name ) ) ? 'WP-CLI command' : $command_name;

		if ( empty( $file_path ) ) {
			$this->_error(
				sprintf(
					'%s cannot run without a valid file input',
					$command_name
				)
			);
		}

	}

	/**
	 * Method to get the number of lines in a file
	 *
	 * @param string $file_path
	 * @param bool   $is_csv
	 *
	 * @return int
	 */
	protected function get_total_lines_in_file( string $file_path = '', bool $is_csv = false ) : int {

		$count = 0;

		if ( empty( $file_path ) || ! is_file( $file_path ) || ! is_readable( $file_path ) || 0 !== validate_file( $file_path ) ) {
			return $count;
		}

		$file = new \SplFileObject( $file_path, 'r' );

		// Need to set this flag for CSV files since a single row can be long and occupy more than one line
		// in which case the number of rows returned for the CSV file would be incorrect.
		if ( true === $is_csv ) {
			$file->setFlags( \SplFileObject::READ_CSV );
		}

		$file->seek( PHP_INT_MAX );

		/*
		 * Count is incremented by one to get the actual number of last line
		 * in the file because the internal pointer starts at zero just like
		 * an array index.
		 */
		$count = intval( $file->key() + 1 );

		/*
		 * This is important since there's no public close method in SplFileObject class
		 * and PHP will keep the file open if its not explicitly closed.
		 * Destroying the object is the only way possible, at present, to close the file.
		 */
		unset( $file );

		return $count;

	}

	/**
	 * IMPORTANT: If you add new function to this file, make sure to use "protected" keyword
	 * This will prevent the function being process as a WP CLI sub-command for all WP CLI scripts
	 *
	 * Do not add any functions below this line
	 **/
}

// EOF
