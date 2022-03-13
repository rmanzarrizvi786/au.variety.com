<?php
class Sailthru_Blast_Repeat { //Sailthru name for recurring newsletter

	static $_log_data = [];

	static function get_repeats() {
		$st_blast_repeats = self::get_custom_post();
		return $st_blast_repeats;
	}

	/**
	* save_api_to_db | pmc-exacttarget/classes/class-sailthru-blast-repeat.php
	*
	* @since 2018-02-09 - Saves the API data to the database
	*
	* @author brandoncamenisch
	* @version 2018-02-09 - feature/PMCVIP-2977:
	* - Adding dockblock
	* - Typecasting array for days api data where array is expected
	*
	* @return array
	*/
	static function save_api_to_db( $api_repeat, $query, $subject, $default_img_url ) {
		$u = explode( 'repeathash=', $api_repeat['data_feed_url'] );
		$feed_ref = '';
		if ( isset( $u[1] ) && ctype_alnum( $u[1] ) ) {
			$feed_ref = $u[1];
		}

		$insert = array(
			'repeat_id'             => sanitize_text_field( $api_repeat['repeat_id'] ),
			'email_id'              => sanitize_text_field( $api_repeat['email_id'] ),
			'days'                  => wp_json_encode( array_filter( (array) $api_repeat['days'], 'sailthru_check_days' ) ),
			'feed_ref'              => $feed_ref,
			'query'                 => wp_json_encode( $query ),
			'name'                  => wp_kses_data( $api_repeat['name'] ),
			'template'              => sanitize_text_field( $api_repeat['template'] ),
			'subject'               => addslashes( wp_kses_data( $subject ) ),
			'dataextension'         => wp_kses_data( $api_repeat['dataextension'] ),
			'default_thumbnail_src' => esc_url( $default_img_url ),
			'send_time'             => wp_kses_data( $api_repeat['send_time'] ),
			'schedules'             => '',
			'state'                 => 'play',
			'img_size'              => sanitize_text_field( $api_repeat['img_size'] ),
			'img_type'              => sanitize_text_field( $api_repeat['img_type'] ),
			'content_builder'       => sanitize_text_field( $api_repeat['content_builder'] ),
		);

		if ( ! empty( $api_repeat['external_feed_url'] ) ) {
			$insert['external_feed_url'] = esc_url_raw( $api_repeat['external_feed_url'] );
		}

		if( isset( $api_repeat['pmc_newsletter_senddefinition'] ) ){
			$insert['pmc_newsletter_senddefinition'] =  $api_repeat['pmc_newsletter_senddefinition'] ;
		}

		$date_string = $query['schedule_start_date'] . " " . $api_repeat['send_time'];
		if ( ! empty( $date_string ) ) {
			//convert_date_to_utc returns array of dates
			$insert['schedules'] = self::convert_date_to_utc( $date_string );
		}

		if( isset( $api_repeat['featured_post_id'] ) ){
			$insert['featured_post_id'] = intval($api_repeat['featured_post_id']);
		}
		$st_blast_repeats = self::get_custom_post();

		$st_blast_repeats_array = array();

		if ( ! empty( $st_blast_repeats ) ) {
			$st_blast_repeats_array = $st_blast_repeats;
			if ( ! empty( $st_blast_repeats_array[ $api_repeat['repeat_id'] ]['feed_ref'] ) ) {
				$insert['feed_ref'] = $st_blast_repeats_array[ $api_repeat['repeat_id'] ]['feed_ref'];
			}
			if ( ! empty( $st_blast_repeats_array[ $api_repeat['repeat_id'] ]['state'] ) ) {
				$insert['state'] = $st_blast_repeats_array[ $api_repeat['repeat_id'] ]['state'];
			}
		}


		$st_blast_repeats_array[$api_repeat['repeat_id']] = $insert;



		self::save_custom_post( $st_blast_repeats_array );

		return array_merge( $insert, $api_repeat );
	}

	static function save_to_db( $repeat ) {
		$st_blast_repeats = self::get_custom_post();

		$st_blast_repeats_array = array();

		if ( sailthru_isset_notempty( $st_blast_repeats ) ) {
			$st_blast_repeats_array = $st_blast_repeats;
		}

		$repeat['days'] = json_encode( $repeat['days'] );
		$repeat['query'] = json_encode( $repeat['query'] );

		$st_blast_repeats_array[$repeat['repeat_id']] = $repeat;

		self::save_custom_post( $st_blast_repeats_array );
	}

	static function load_from_db( $id ) {
		$st_blast_repeats = self::get_custom_post();
		if ( sailthru_isset_notempty( $st_blast_repeats ) ) {
			$repeat = $st_blast_repeats[$id];
			$repeat['days']  = \pmc_et_maybe_decode( $repeat['days'], true );
			$repeat['query'] = \pmc_et_maybe_decode( $repeat['query'], true );
			$repeat['subject'] = stripslashes( $repeat['subject'] );
			return $repeat;
		}
	}

	/**
	 * Helper function for filtering newsletter configurations.
	 * @TODO: This function is not useful for now, keep it just in case it comes handy otherwise remove after some time.
	 *
	 * @codeCoverageIgnore This function isn't used anywhere for now but may come handy.
	 *
	 * @param array  $newsletters             Array of newsletter configurations.
	 * @param string $content_builder_enabled Flag to filter newsletters based on whether content builder is enabled or not.
	 *
	 * @return array
	 */
	static function filter_newsletter_configurations( $newsletters, $content_builder_enabled ) {

		if ( ! empty( $newsletters ) && is_array( $newsletters ) ) {
			$newsletters = array_filter(
				(array) $newsletters,
				function( $newsletter ) use ( $content_builder_enabled ) {

					if ( '1' === $content_builder_enabled ) {
						return ( ! empty( $newsletter['content_builder'] ) && 'yes' === $newsletter['content_builder'] );
					} else {
						return ( empty( $newsletter['content_builder'] ) || 'yes' !== $newsletter['content_builder'] );
					}
				}
			);
		}

		return $newsletters;
	}

	static function save_featured_post( $post_id, $repeat_id ) {
		if ( is_int( $post_id ) ) {

			//check post status. Also some code deliberately trying to set featured post id =0, so check that too.
			if ( $post_id > 0 ) {
				$post_status    = array( 'publish',
										 'future' );
				$current_status = get_post_status( $post_id );

				if ( !in_array( $current_status, $post_status ) ) {
					return;
				}
			}

			$st_blast_repeats                                 = self::get_custom_post();
			$st_blast_repeats[$repeat_id]['featured_post_id'] = $post_id;
			self::save_custom_post( $st_blast_repeats );
		}
	}

	/**
	 * @revision 2015-12-16 Amit Sannad
	 *           Rather then accepting single repeat, now this function accepts multiple repeat ids and resets featured post in single call
	 * @param $post_id
	 * @param $repeat_ids
	 */
	static function remove_featured_post( $post_id, $repeat_ids ) {

		$st_blast_repeats = self::get_custom_post();

		if ( empty( $repeat_ids ) ) {
			return;
		}

		$flag_featured_updated = false;

		foreach ( $repeat_ids as $repeat_id ) {

			if ( ! empty( $st_blast_repeats[ $repeat_id ]['featured_post_id'] ) && $post_id == $st_blast_repeats[ $repeat_id ]['featured_post_id'] ) {
				$st_blast_repeats[ $repeat_id ]['featured_post_id'] = null;
				$flag_featured_updated                              = true;
			}
		}

		if ( $flag_featured_updated ) {
			self::save_custom_post( $st_blast_repeats );
		}

	}


	static function remove_from_db( $id ) {
		$st_blast_repeats = self::get_custom_post();
		if ( sailthru_isset_notempty( $st_blast_repeats ) ) {
			unset( $st_blast_repeats[$id] );

			self::save_custom_post( $st_blast_repeats );

			return !isset( $st_blast_repeats[$id] );
		}
	}

	static function load_by_feed_ref( $qid ) {
		$st_blast_repeats = self::get_custom_post();
		if ( sailthru_isset_notempty( $st_blast_repeats ) ) {
			foreach ( $st_blast_repeats as $repeat ) {
				if ( isset( $repeat['feed_ref'] ) && $repeat['feed_ref'] == $qid ) {
					$repeat['days']    = \pmc_et_maybe_decode( $repeat['days'], true );
					$repeat['query']   = \pmc_et_maybe_decode( $repeat['query'], true );
					$repeat['subject'] = stripslashes( $repeat['subject'] );
					return $repeat;
				}
			}
		}
	}

	static function save_custom_post( $post_data ) {

		$post_present = get_posts( array( 'post_type' => 'sailthru_recurring',
										'numberposts' => 1 )
		);
		if ( isset( $post_data ) && !empty( $post_data ) )
			$post_data = addslashes( serialize( $post_data ) );
		else
			$post_data = '';
		$data = array(
			'post_name' => '',
			'post_type' => 'sailthru_recurring',
			'post_title' => "Sailthru Recurring News Letter",
			'post_content' => $post_data,
			'post_status' => 'publish',
			'post_date' => current_time( 'mysql' )
		);
		remove_action( 'save_post', 'sailthru_publish_to_sailthru' );
		if ( isset( $post_present[0] ) && !empty( $post_present[0] ) && isset( $post_present[0]->ID ) && $post_present[0]->ID > 0 ) {
			$data['ID'] = $post_present[0]->ID;
			wp_update_post( $data );
		} else {
			wp_insert_post( $data );
		}
		add_action( 'save_post', 'sailthru_publish_to_sailthru' );
	}

	/**
	 * Helper function to retrieve the custom post where newsletter configurations are stored.
	 * We're storing the configurations in a single post's post content as a json object.
	 *
	 * @return array
	 */
	static function get_custom_post() {
		// There's only 1 "sailthru_recurring" post, used as a datastore
		$post_latest = get_posts( array( 'post_type' => 'sailthru_recurring',
									   'numberposts' => 1 ) );
		if ( ! $post_latest || ! isset($post_latest[0]->post_content) ) {
			return null;
		}
		return \pmc_et_maybe_decode( $post_latest[0]->post_content );
	}

	public static function convert_date_to_utc( $date_string = '' ) {

		if ( empty( $date_string ) ) {
			return;
		}

		$return_array = array();

		$date_PST = new DateTime( $date_string, new DateTimeZone( 'America/Los_Angeles' ) );

		$date_UTC = new DateTime( $date_string, new DateTimeZone( 'America/Los_Angeles' ) );
		$date_UTC->setTimezone( new DateTimeZone( 'UTC' ) );

		$return_array['local_time'] = $date_PST;
		$return_array['utc_time']   = $date_UTC;

		return $return_array;

	}

	public static function schedule_newsletter() {

		$sailthru_repeats = Sailthru_Blast_Repeat::get_custom_post();

		if ( empty( $sailthru_repeats ) ) {
			return;
		}

		foreach ( $sailthru_repeats as $id => $sailthru_repeat ) {

			self::schedule_newsletter_event( $id, $sailthru_repeat );
		}

	}

	public static function schedule_newsletter_event( $repeat_id, $repeat_data ) {

		$days = \pmc_et_maybe_decode( $repeat_data['days'] );

		//If no days specified return
		if ( empty( $days ) ) {
			return;
		}

		$frequency = 'daily';

		//We need to run once a week so change frequency to weekly
		if ( 1 == count( $days ) ) {
			$frequency = 'sailthru_weekly';
		}

		if ( empty( $repeat_data['schedules'] ) ) {
			return;
		}

		$schedules = $repeat_data['schedules'];

		if ( empty( $schedules['utc_time'] ) ) {
			return;
		}

		$utc_time_string = date_format( $schedules['utc_time'], 'Y-m-d H:i:s' );

		$schedule_time = 'today ' . date_format( $schedules['utc_time'], 'H:i' );

		$next_schedule_time = new DateTime( $schedule_time, new DateTimeZone( 'UTC' ) );

		$action_name = 'sailthru_schedule_repeats';

		add_action( $action_name, array( get_called_class(), 'process_scheduled_newsletter' ), 10, 2 );

		if ( ! wp_next_scheduled( $action_name, array( $repeat_id, 'v2', $utc_time_string, $days ) ) ) {
			wp_schedule_event( $next_schedule_time->getTimestamp(), $frequency, $action_name,
				array( $repeat_id, 'v2', $utc_time_string, $days ) );
		}

	}

	public static function process_scheduled_newsletter( $repeat_id, $version ) {

		//Return if v2 is not the version. We will get rid of other old crons.
		if ( 'v3' != $version ) {
			self::log_data( "asannad@pmc.com", "version", sanitize_text_field( $version ) );

			return;
		}

		$repeat_data = Sailthru_Blast_Repeat::load_from_db( $repeat_id );

		$email = 'asannad@pmc.com';
		$site  = get_bloginfo('name');
		$title = "ET Scheduled Newsletter send Failed for {$site}. - " . $repeat_data['name'];

		if ( isset( $repeat_data['state'] ) && 'pause' === $repeat_data['state'] ) {
			self::log_data( '', $title, 'This newsletter has been paused' );
			return;
		}

		if ( ! empty( $repeat_data['last_send_date'] ) && $repeat_data['last_send_date'] === date( 'Ymd' ) ) {
			self::log_data( $email, wp_specialchars_decode( $title ), 'This newsletter has already been sent today' );

			//This newsletter has already been sent today, so bail
			return;
		}

		// Get some of the time data for the newsletters
		$schedules = $repeat_data['schedules'];
		$days      = $repeat_data['days'];

		// Get current date
		$datetime = new DateTime( 'now', new DateTimeZone( 'America/Los_Angeles' ) );

		//Get sent time which is in format hour:minutes am/pm
		$send_time = $repeat_data['send_time'];

		// What we are going to do now is loop through all the days and then get the timediff and if
		// its within permisible limit send else bail

		$to_send = false;
		$log_data ='';
		if( is_array( $days ) ){

			foreach ( $days as $day ) {

				$datetime_send = new DateTime( "{$day} {$send_time}", new DateTimeZone( 'America/Los_Angeles' ) );

				$diff_in_mins = ( $datetime->getTimestamp() - $datetime_send->getTimestamp() ) / 60;

				//Dont send scheduled news letter if its over 7 mins or under 2 mins
				if ( $diff_in_mins >= 7 || $diff_in_mins < - 2 ) {
					$log_data = json_encode( $datetime ) . '<br/>' . json_encode( $datetime_send ) . '<br/>' . $diff_in_mins . '<br/>';
				} else {
					$to_send = true;
					break;
				}
			}
		}

		if ( ! $to_send ) {
			self::log_data( $email, wp_specialchars_decode( $title ), 'Different time.' . $log_data );

			return;
		}
		$name               = sanitize_title_with_dashes( $repeat_data['name'] );
		$already_sent_today = wp_cache_get( 'et_blast_repeat_sch_' . $name, 'pmc_exacttarget' );

		//Check from cache if this has been sent already
		if ( ! empty( $already_sent_today ) ) {
			self::log_data( $email, wp_specialchars_decode( $title ), 'Already sent cache says. first check.' );

			return;
		}

		self::send_repeat_newsletter( $repeat_data );

	}

	public static function send_repeat_newsletter( $repeat_data ) {

		if ( empty( $repeat_data ) ) {
			return;
		}

		$email = 'asannad@pmc.com';
		$site  = get_bloginfo('name');
		$title = "ET Scheduled Newsletter send Failed for {$site}. - " . $repeat_data['name'];

		if ( ! empty( $repeat_data['content_builder'] ) && 'yes' === $repeat_data['content_builder'] ) {

			$et_email = Exact_Target::get_email_from_content_builder( $repeat_data['email_id'] );

			// If the corresponding email doesn't exist in ET then bail out.
			if ( true !== $et_email->status ) {
				self::log_data( $email, wp_specialchars_decode( $title ), 'Exact_Target object email retrieval failed. Error: ' . $et_email->message );
				return;
			}

		} else {

			$et_email = Exact_Target::get_email( $repeat_data['email_id'], 'ID', 'object' );

			if ( empty( $et_email ) ) {
				self::log_data( $email, wp_specialchars_decode( $title ), 'Exact_Target object email retrieval fail.' );
			}
		}

		if ( isset( $repeat_data['state'] ) && $repeat_data['state'] == 'pause' ) {
			self::log_data( $email, wp_specialchars_decode( $title ), 'This newsletter has been paused' );

			return;
		}

		$unique_id = uniqid( mt_rand(), true );

		//This needs to be atomic operation so it does not get sent twice. But can not do lock here. So doing a double lock checking.
		$already_sent_today = wp_cache_get( 'et_blast_repeat_sch_' . sanitize_title_with_dashes( $repeat_data['name'] ), 'pmc_exacttarget' );

		//Check from cache if this has been sent already
		if ( ! empty( $already_sent_today ) ) {
			self::log_data( $email, wp_specialchars_decode( $title ), 'Already sent cache says. Second Check.' );

			return;
		}

		//Cache set for 30 mins
		wp_cache_set( 'et_blast_repeat_sch_' . sanitize_title_with_dashes( $repeat_data['name'] ), $unique_id, 'pmc_exacttarget', 1800 );

		$already_sent_today = wp_cache_get( 'et_blast_repeat_sch_' . sanitize_title_with_dashes( $repeat_data['name'] ), 'pmc_exacttarget' );

		//Check if some other process updated the cache value or not again to be safe.
		if ( $unique_id != $already_sent_today ) {
			// No way to recreate this scenario for testing.
			// @codeCoverageIgnoreStart
			self::log_data( $email, wp_specialchars_decode( $title ), 'Already sent cache says. Third check.' );
			return;
			// @codeCoverageIgnoreEnd
		}

		if ( ! empty( $repeat_data['content_builder'] ) && 'yes' === $repeat_data['content_builder'] ) {

			$result = PMC_Newsletter::send_recurring_newsletter_content_builder( $repeat_data );
		} else {

			$result = PMC_Newsletter::send_recurring_newsletter( $repeat_data );
		}

		if ( ! empty( $result['error'] ) ) {
			$error_msg = wp_json_encode( $result );
			self::log_data( $email, wp_specialchars_decode( $title ), $error_msg );
		}

		$repeat_data['last_send_date'] = date( 'Ymd' );
		if ( ! empty( $repeat_data['query']['filter_posts_by_zone'] ) ) {
			$repeat_data['state'] = 'pause';
		}

		Sailthru_Blast_Repeat::save_to_db( $repeat_data );
	}

	/**
	 * Initial Load function.
	 *
	 * @version 2015-08-03 Amit Sannad Added functionality to register end point, so we can
	 *          run cron our selves. VIP cron either runs multiple times or once and our newsletter gets
	 *          sent multiple times. ref: https://wordpressvip.zendesk.com/requests/43372
	 */
	public static function load() {
		add_filter( 'cron_schedules', array( get_called_class(), 'cron_add_weekly' ) );
		/**
		 * Passthrough system to run all cron events through the WP.com jobs system.
		 */
		add_filter( 'wpcom_vip_passthrough_cron_to_jobs', array( get_called_class(), 'cron_to_jobs' ) );

		add_action( 'init', [ get_called_class(), 'add_et_rewrite_endpoint' ] );
		add_action( 'template_redirect', array( get_called_class(), 'template_redirect' ) );
	}

	public static function cron_add_weekly( $schedules ) {

		$schedules['sailthru_weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Once Weekly' )
		);

		return $schedules;
	}

	/**
	 * Add our event so it runs through job system of wp.com.
	 * This is to fix multiple sends as jobs system rarely gets called multiple time.
	 *
	 * @version 2015-07-30 Amit Sannad https://wordpressvip.zendesk.com/requests/43372
	 *
	 * @param $whitelist
	 *
	 * @return array
	 */
	public static function cron_to_jobs( $whitelist ) {
		$whitelist[] = 'sailthru_schedule_repeats';

		return $whitelist;
	}

	/**
	 * Run scheduled newsletter via seperate endpoint registered in load function of this class.
	 *
	 * @version 2015-08-03 Amit Sannad fix for ref: https://wordpressvip.zendesk.com/requests/43372
	 */
	public static function template_redirect() {
		global $wp_query;

		if ( empty( $wp_query->query_vars['pmc-exacttarget'] ) ) {
			return;
		}

		$tokens = explode( '/', $wp_query->query_vars['pmc-exacttarget'] );

		if ( empty( $tokens[0] ) ) {
			wp_die( "Empty Token" );
		}

		$token = pmc_get_option( 'pmc_newsletter_api_token', 'exacttarget' );

		if ( $token != $tokens[0] ) {
			wp_die( "Wrong Token " . wp_kses_post( $tokens[0] ) );
		}
		/**
		 * Every thing went well. Lets run the scheduled jobs now.
		 */

		$sailthru_repeats = self::get_custom_post();

		if ( empty( $sailthru_repeats ) ) {
			wp_die( "There are no newsletters to run." );
		}

		foreach ( $sailthru_repeats as $id => $sailthru_repeat ) {

			self::process_scheduled_newsletter( $id, 'v3' );
		}
		Exact_Target::log();
		wp_die( "success=>" . json_encode( self::$_log_data, JSON_PRETTY_PRINT ), 200 );
	}

	/**
	 * Save Log Data for either sending email or logging it somewhere in future.
	 * Right not this will just show in response.
	 *
	 * @version 2015-08-03 Amit Sannad https://wordpressvip.zendesk.com/requests/43372
	 *
	 * @param $email
	 * @param $title
	 * @param $description
	 */
	public static function log_data( $email, $title, $description ) {
		self::$_log_data[] = wp_specialchars_decode( $title ) . '===>' . $description . " ";
	}

	/**
	 * Init Action.
	 *
	 * @return void
	 */
	public static function add_et_rewrite_endpoint() {
		add_rewrite_endpoint( 'pmc-exacttarget', EP_ROOT );
	}
}

Sailthru_Blast_Repeat::load();

//EOF
