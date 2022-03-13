<?php
namespace PMC\WP_CLI;

class Redirect_Manager extends \PMC_WP_CLI_Base {

	const SAFE_REDIRECT_POST_TYPE = 'redirect_rule';

	/**
	 * Migrate into legacy redirector
	 *
	 * @subcommand migrate-into-legacy-redirector
	 *
	 * ## OPTIONS
	 *
	 * [--csv=<file>]
	 * : The file containing the list of rules to migrate
	 *
	 * [--all]
	 * : If csv not pass, migrate all entries
	 *
	 * [--dry-run]
	 * : Do dry run
	 *
	 * --log-file=<log-file>
	 * : Path/Filename to the log file
	 *
	 * ## EXAMPLES
	 *
	 *    wp pmc-redirect-manager migrate-into-legacy --csv=migrate.csv --log-file=/var/log/pmc-safe-redirect-manager-delete-all.log
	 *
	 */
	public function migrate_into_legacy( $args = array(), $assoc_args = array() ) {

		$this->_extract_common_args( $assoc_args );

		$csv = ( ! empty( $assoc_args['csv'] ) ) ? $assoc_args['csv'] : false;
		$all = ( isset( $assoc_args['all'] ) );

		if ( ! empty( $csv ) ) {

			$this->_notify_start( 'Starting: Migrating entries from safe redirect into legacy redirector from csv file' );

			$row    = 0;
			$handle = fopen( $csv, 'r' ); // phpcs:ignore
			if ( false !== $handler ) {

				while ( false !== ( $data = fgetcsv( $handle, 2000, ',' ) ) ) { // phpcs:ignore

					$row++;
					$redirect_from    = $data[0];
					$redirect_to      = false;
					$redirect_post_id = $this->_lookup_redirect_post_id( $redirect_from );
					if ( empty( $data[1] ) ) {
						if ( ! empty( $redirect_post_id ) ) {
							$redirect_to = get_post_meta( $redirect_post_id, '_redirect_rule_to', true );
						}
					} else {
						$redirect_to = $data[1];
					}

					if ( ! empty( $redirect_to ) ) {
						if ( $this->dry_run ) {
							$this->_write_log( "would add to legacy redirect from {$redirect_from} to {$redirect_to}" );
						} else {
							\WPCOM_Legacy_Redirector::insert_legacy_redirect( $redirect_from, $redirect_to );
							$this->_write_log( "Add to legacy redirect from {$redirect_from} to {$redirect_to}" );
						}

						// We're deleting entry, we need to make sure it is safe redirect before deleting
						if ( get_post_type( $redirect_post_id ) === self::SAFE_REDIRECT_POST_TYPE ) {
							if ( $this->dry_run ) {
								$this->_write_log( "Would remove entry id={$redirect_post_id} from safe redirect from {$redirect_from} to {$redirect_to}" );
							} else {
								wp_delete_post( $redirect_post_id );
								$this->_write_log( "Remove entry id={$redirect_post_id} from safe redirect from {$redirect_from} to {$redirect_to}" );
							}
						}

						$this->_update_interation(); // this code take care of calling stop_the_insanity
					} else {
						$this->_warning( "Invalid redirect-to entry at at {$row}" );
					}

				}

				fclose( $handle ); // phpcs:ignore

			}

		}

		$this->_notify_done();

	}

	/**
	 * Lookup up a post id from from safe redirect record
	 * @param  string $redirect_from The redirect from uri
	 * @return mixed                 The post id or false if uri not found
	 */
	private function _lookup_redirect_post_id( $redirect_from ) {
		global $wpdb;

		// use direct query to quickly lookup post meta entry
		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_redirect_rule_from' and meta_value = %s LIMIT 1", $redirect_from  ) );  // phpcs:ignore

		if ( self::SAFE_REDIRECT_POST_TYPE === get_post_type( $post_id ) ) {
			return $post_id;
		}

		return false;
	}

} //end class


//EOF
