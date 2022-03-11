<?php
/*
This class is responsible to retrieve data feed from remote server twice a daily.
The feed data is parsed and stored in pmc options as an array of 40 items.

Version 2014-09-03 Hau - Change schedule to run hourly to check for latest issue before running process_feed
Version 2015-04-16 Adaeze Esiobu -- allow pagination of the entries and not just the first 40 entries.

*/

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Digital_Feed {

	use Singleton;

	const FEED_FILTER        = 'variety_digital__pagesuite_url';
	const FEED_URL           = 'https://live.portal.pagesuite.com/api/endpoint/get_editions.aspx?order=descending&pbid=fe4e46ea-69d0-4885-b927-e2c6891b677e';
	const LIMIT              = 40;
	const MAX_LOG_ENTRIES    = 10;
	const OPTION_GROUP       = 'variety-digital-v3';
	const OPTION_JSON_FEED   = 'variety_digital_json_feed';
	const OPTION_LOG_ENTRIES = 'log_entries';

	public $latest_issue_id = 0;

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	public function action_init() {
		add_action( 'variety_digital_feed_refresh', array( $this, 'refresh' ) );

		if ( ! wp_next_scheduled( 'variety_digital_feed_refresh' ) ) {
			wp_schedule_event( time(), 'hourly', 'variety_digital_feed_refresh' );
		}

		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );

	}

	public function action_admin_menu() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_submenu_page( 'tools.php', 'Variety Digital', 'Variety Digital', 'manage_options', 'variety-digital', array( $this, 'render_admin' ) );
	}

	public function render_admin() {

		// code shouldn't reach here if permission isn't allow, but we check it anyway
		// since we do not want any to access and refresh the cached data.
		if ( current_user_can( 'manage_options' ) ) {

			// Just grab the querystring directly from wp admin link
			$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

			if ( ! empty( $action ) && 'refresh' === $action ) {

				$this->process_feed();

				$this->write_log( 'Latest issue: ' . $this->get_latest_issue_id() . ' ' . ( $this->is_issue_out_of_sync() ? ' out of sync' : ' in synced' ) );

			}

		}

		$latest_issue_id    = $this->get_latest_issue_id();
		$is_issue_synched   = $this->is_issue_out_of_sync() ? 'out of sync' : 'in synced';
		$total_issue_synced = count( $this->get_issues() );

		/**
		 * @since 2017-09-01 Milind More CDWE-499
		 */
		echo \PMC::render_template( CHILD_THEME_PATH . '/plugins/variety-digital-subscriber/templates/feed-admin.php', // xss ok
			array(
				'latest_issue_id'    => $latest_issue_id,
				'is_issue_synched'   => $is_issue_synched,
				'total_issue_synced' => $total_issue_synced,
			)
		);

	}

	/**
	 * Check to determine data out of sync before calling process_feed
	 *
	 * @return bool
	 */
	public function refresh() {

		// @todo Investigate whether we need this condition or not because this execute always.
		if ( true || $this->is_issue_out_of_sync() ) {
			$status = $this->process_feed();
			$this->write_log( 'Latest issue: ' . $this->get_latest_issue_id() . ' ' . ( $this->is_issue_out_of_sync() ? ' out of sync' : ' in synced' ) );
			return $status;
		}

		return false;

	}

	// get feed from remote server and stored in pmc options
	// Note: this feed contains large amount of data and should avoid running too often.
	public function process_feed() {

		// Increase timeout from 5 to 15 seconds due to process_feed might timeout causing data not synchronize
		$args = array(
			'timeout' => 15, // seconds
		);

		$url      = apply_filters( self::FEED_FILTER, self::FEED_URL );
		$response = wp_safe_remote_get( $url, $args );

		if ( intval( wp_remote_retrieve_response_code( $response ) ) !== 200 ) {
			$this->write_log( 'Error retrieving data from ' . $url );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$feed = json_decode( $body );

		if ( ! is_object( $feed ) ) {
			return false;
		}

		$item_collection = array();
		//split our response what we got into groups of 40
		while ( count( $feed->Data ) > 0 ) { // phpcs:ignore
			$item_collection[] = array_splice( $feed->Data, 0, self::LIMIT ); // phpcs:ignore
		}

		unset( $feed );
		unset( $body );
		unset( $response );

		$issues = array();
		$issue_count = 0;
		foreach ( $item_collection as $items ) {
			foreach ( $items as $item ) {
				// Digital Edition Naming Convention
				// For Weekly:         mmddyyyy - Variety - EC3 (Variety)
				// For Extra Editions: mmddyyyy - Variety EE - EC1 (Supplement)
				// For Show Dailies:   mmddyyy - Variety SD - EC1 (Festival)

				if ( ! is_object( $item ) ) {
					continue;
				}

				$issue_name = (string) strtolower( $item->editionname );

				// Get custom cover page if one is specified.
				$cover_page = 1;

				// Regex to find Edition Cover, eg, - EC3 means the cover is on page 3.
				//
				// ref: https://regex101.com/r/HYxLAQ/1
				//
				// $issue_name = '08212018 - Variety - EC3';
				// Successful $matches.
				// $matches = [
				//    '- ec3',
				//    '3',
				// ];
				preg_match( '/- ec(\d+)$/i', $issue_name, $matches );

				if ( is_array( $matches ) && 2 === count( $matches ) ) {
					$cover_page = intval( $matches[1] );
				}

				// Get issue type within name.
				$issue_type = 'Variety';

				// Default issue type is `Variety`. Other issue types for
				// `Supplement` (- Variety EE) and `Festival` (- Variety SD)
				// are found in the issue name as well based on the standardized
				// naming convention listed above.
				if ( false !== strpos( $issue_name, '- variety ee' ) ) {
					$issue_type = 'Supplement';
				} elseif ( false !== strpos( $issue_name, '- variety sd' ) ) {
					$issue_type = 'Festival';
				}

				// Break date string appart on `/` to get month, day, and year
				// for `date` property below.
				$date_parts = explode( '/', $item->editiondate );

				// By default, image width in URL is set to `&w=400`. By changing
				// the width size on query parameter, we can get image width sizes
				// for 1200, 960, and 320 which we set below.
				//
				// ref: https://regex101.com/r/gP440E/1
				//
				// In the pattern below we are capturing `&w=` and replacing the
				// numeric value.
				$img_pattern = '/(&w=)\d+$/i';
				$cover_page  = '&pnum=' . intval( $cover_page );

				$issues[ $issue_count ][] = array(
					'id'     => (string) $item->editionname,
					'img'    => (string) preg_replace( $img_pattern, '${1}1200', $item->imageurl ) . $cover_page,
					'img960' => (string) preg_replace( $img_pattern, '${1}960', $item->imageurl ) . $cover_page,
					'img320' => (string) preg_replace( $img_pattern, '${1}320', $item->imageurl ) . $cover_page,
					'date'   => mktime( 0, 0, 0, intval( $date_parts[0] ), intval( $date_parts[1] ), intval( $date_parts[2] ) ),
					'url'    => (string) $item->launchurl,
					'type'   => $issue_type,
				);
			} // end of inner foreach
			$issue_count++;
		} // end of outer foreach

		pmc_update_option( self::OPTION_JSON_FEED, $issues, self::OPTION_GROUP );
		$this->write_log( 'Retrieved ' . count( $issues ) . ' issues.' );
		return true;
	}

	/**
	 * Get the latest issue of Variety of the Variety type.
	 *
	 * @return array
	 */
	public function get_latest_variety_issue() {
		$i = 1;

		while ( 2 >= $i ) {
			// Limit to checking 2 levels of pagination at most.
			$issues = $this->get_issues( $i );

			if ( ! empty( $issues ) && is_array( $issues ) && count( $issues ) ) {
				foreach ( $issues as $issue ) {
					if ( 'variety' === strtolower( $issue['type'] ) ) {
						return $issue;
					}
				}
			}

			$i++;
		}

		return [];
	}

	/**
	 * Get latest issue by retrieving the cover page html and parse for the issue id
	 * more efficient than pulling data from the large JSON feed
	 *
	 * @return boolean|integer
	 */
	public function get_latest_issue_id() {
		if ( ! empty( $this->latest_issue_id ) ) {
			return $this->latest_issue_id;
		}

		// Keep response small if possible by only getting latest year.
		$url      = apply_filters( self::FEED_FILTER, self::FEED_URL . '&year=latest' );
		$response = wp_safe_remote_get( $url );

		if ( intval( wp_remote_retrieve_response_code( $response ) ) !== 200 ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$feed = json_decode( $body );

		if ( ! is_object( $feed ) ) {
			return false;
		}

		// No count for latest year, so let's try previous year.
		if ( ! intval( $feed->count ) ) {
			$feed = $this->get_feed_from_prev_year();
		}

		if ( is_object( $feed ) && is_array( $feed->Data ) && count( $feed->Data ) && is_object( $feed->Data[0] ) ) { // phpcs:ignore
			$this->latest_issue_id = $feed->Data[0]->editionname; // phpcs:ignore

			return $this->latest_issue_id;
		}

		return false;
	}

	/**
	 * Get feed from previous year.
	 *
	 * @return boolean|object
	 */
	public function get_feed_from_prev_year() {
		$url      = apply_filters( self::FEED_FILTER, self::FEED_URL . '&year=' . intval( date( 'Y' ) - 1 ) );
		$response = wp_safe_remote_get( $url );

		if ( intval( wp_remote_retrieve_response_code( $response ) ) !== 200 ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body );
	}

	/**
	 * Check if issues are out of sync.
	 *
	 * @return boolean
	 */
	public function is_issue_out_of_sync() {
		$issues = $this->get_issues();

		if ( empty( $issues ) ) {
			return true;
		}

		$id = intval( $this->get_latest_issue_id() );

		if ( empty( $id ) ) {
			return false;
		}

		$ids = wp_list_pluck( $issues, 'id' );
		$ids = array_map( 'intval', $ids );

		// if $id is not in the array, then it's out of sync
		return ( ! in_array( $id, $ids, true ) );
	} // function

	/*
	 * Helper function to write log information
	 */
	public function write_log( $data ) {
		$histories = pmc_get_option( self::OPTION_LOG_ENTRIES, self::OPTION_GROUP );

		if ( empty( $histories ) ) {
			$histories = array();
		}

		if ( count( $histories ) > self::MAX_LOG_ENTRIES ) {
			array_shift( $histories );
		}

		$histories[] = array(
			'timestamp' => date( 'Y-m-d H:i:s' ),
			'data'      => $data,
		);
		pmc_update_option( self::OPTION_LOG_ENTRIES, $histories, self::OPTION_GROUP );
	}

	public function get_log_entries() {
		return pmc_get_option( self::OPTION_LOG_ENTRIES, self::OPTION_GROUP );
	}

	/**
	 * Calculates the number of pages of issues we got back from the feed.
	 *
	 * @return integer
	 */
	public function get_number_of_issue_pages() {
		$issues = pmc_get_option( self::OPTION_JSON_FEED, self::OPTION_GROUP );

		$number_of_issue_pages = 1;

		if ( isset( $issues ) && is_array( $issues ) ) {
			$number_of_issue_pages = count( $issues );
		}

		return $number_of_issue_pages;
	}

	/**
	 * Returns the issues for the current page.
	 *
	 * @param integer $page_num
	 *
	 * @return boolean
	 */
	public function get_issues( $page_num = 1 ) {

		$issues = pmc_get_option( self::OPTION_JSON_FEED, self::OPTION_GROUP );
		// this will happen only when we have done a new deploy and changed the option group
		if ( empty( $issues ) ) {
			$this->process_feed();
			$issues = pmc_get_option( self::OPTION_JSON_FEED, self::OPTION_GROUP );
		}

		$issue_number = $page_num - 1;

		if ( ! empty( $issues ) && is_array( $issues ) && $issue_number < count( $issues ) ) {
			return $issues[ $issue_number ];
		}

		return false;
	}

}

//EOF
