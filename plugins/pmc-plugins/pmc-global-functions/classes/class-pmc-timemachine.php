<?php
/**
 * Class with some date/time sugar to get/format time and to move in time line
 *
 * @author Amit Gupta
 *
 * @since 2013-12-19
 * @version 2015-09-09 Amit Gupta - added is_it_time()
 * @version 2015-09-11 Amit Gupta - renamed is_it_time() to has_passed()
 */


class PMC_TimeMachine {

	/**
	 * Fallback timezone when none is set in the site's General settings
	 */
	const DEFAULT_TIMEZONE = 'UTC+0';

	/**
	 * @var DateTimeZone The timezone currently in use
	 */
	protected $_timezone;

	/**
	 * @var DateTime
	 */
	protected $_datetime;

	public function __construct( DateTimeZone $timezone ) {
		$this->_timezone = $timezone;
		$this->_datetime = new \DateTime( null, $this->_timezone );
	}

	public static function get_site_timezone() {
		$timezone = get_option( 'timezone_string' );
		if ( empty( $timezone ) ) {
			$timezone = get_option( 'gmt_offset' );
			if ( empty( $timezone ) ) {
				$timezone = self::DEFAULT_TIMEZONE;
			}
		}
		return $timezone;
	}

	protected static function _get_timezone( $timezone = 'America/Los_Angeles' ) {
		try {
			return new \DateTimeZone( $timezone );
		} catch ( \Exception $e ) {
			//invalid timezone passed, lets default to LA timezone
			return new \DateTimeZone( 'America/Los_Angeles' );
		}
	}

	/**
	 * Method which tries to get the timezone string based on GMT offset passed into it.
	 * It accepts offsets in H.M (like 5.5 for 5 hours 30 minutes), HHMM and HH:MM formats.
	 *
	 * @since 2016-09-27 Amit Gupta
	 *
	 * @param float|string $offset GMT Offset which is to be converted into timezone string
	 * @return string Timezone string
	 */
	public static function get_timezone_string( $offset ) {

		if ( empty( $offset ) ) {
			throw new \ErrorException( sprintf( '%s::%s() Offset passed cannot be empty', __CLASS__ , __FUNCTION__ ) );
		}

		$offset_original = $offset;

		if ( is_string( $offset ) && strpos( $offset, '.' ) === false ) {

			if ( strpos( $offset, ':' ) !== false ) {	//offset type HH:MM

				$offset_parts = explode( ':', $offset );

				if ( isset( $offset_parts[1] ) ) {

					$offset_parts[1] = ( floatval( $offset_parts[1] ) / 60 );

					$tmp_min = explode( '.', $offset_parts[1] );

					$offset_parts[1] = array_pop( $tmp_min );

				} else {
					$offset_parts[1] = 0;
				}

				$offset = implode( '.', $offset_parts );

			} elseif( strlen( trim( $offset, '-' ) ) > 2 ) {	//offset type HHMM

				$hours = intval( substr( $offset, 0, -2 ) );
				$minutes = intval( substr( $offset, -2 ) );

				$minutes_to_append = floatval( $minutes / 60 );

				$tmp_min = explode( '.', $minutes_to_append );
				$minutes_to_append = array_pop( $tmp_min );

				$offset = sprintf( '%s.%s', $hours, $minutes_to_append );

			}

		}

		$offset_seconds = floatval( $offset ) * 3600;

		$timezone = timezone_name_from_abbr( '', $offset_seconds, 1 );

		if ( $timezone === false ) {
			$timezone = timezone_name_from_abbr( '', $offset_seconds, 0 );
		}

		if ( $timezone === false ) {
			throw new \ErrorException( sprintf( '%s::%s() - %s is an invalid offset', __CLASS__ , __FUNCTION__, $offset_original ) );
		}

		return $timezone;

	}

	/**
	 * Factory method
	 *
	 * @return PMC_TimeMachine An object of PMC_TimeMachine is returned
	 */
	public static function create( $timezone = null ) {

		if ( ! isset( $timezone ) ) {
			$timezone = \PMC_TimeMachine::get_site_timezone();
		}

		$single_timezones = array(
			'UTC', 'W-SU', 'WET', 'Zulu', 'Turkey', 'UCT', 'Universal', 'PST8PDT', 'ROC', 'ROK', 'Singapore',
			'Poland', 'Portugal', 'PRC', 'GMT', 'MST', 'MST7MDT', 'Navajo', 'NZ',
			'Jamaica', 'Japan', 'Kwajalein', 'Libya',
			'GB', 'GB-Eire', 'GMT', 'GMT+0', 'GMT-0', 'GMT0', 'Greenwich', 'Hongkong', 'HST', 'Iceland', 'Iran', 'Israel',
			'EET', 'Egypt', 'Eire', 'EST', 'CST6CDT', 'Cuba', 'CET',
		);

		if ( ( strpos( $timezone, '/' ) == false && ! in_array( $timezone, $single_timezones ) ) || is_numeric(  $timezone ) ) {
			//GMT offset provided, lets try and find out relevant timezone
			$timezone = static::get_timezone_string( $timezone );
		}

		return new PMC_TimeMachine( static::_get_timezone( $timezone ) );

	}

	public function from_time( $format, $time ) {
		$this->_datetime = \DateTime::createFromFormat( $format, $time, $this->_timezone );

		// Need to specifically set current timezone set in the object because PHP
		// reverts to its default timezone if a timestamp is used here.
		$this->change_timezone_to( $this->_timezone->getName() );

		return $this;
	}

	public function format_as( $format = 'Y-m-d H:i' ) : string {
		if ( ! empty( $this->_datetime ) ) {
			return $this->_datetime->format( $format );
		}
		return '';
	}

	/**
	 * Quick function to get current date and time.
	 * Eg: PMC_TimeMachine::create( 'America/Los_Angeles' )->now()
	 */
	public function now() {
		return $this->format_as( 'Y-m-d H:i' );
	}

	/**
	 * Go back in time
	 * Eg: PMC_TimeMachine::create( 'America/Los_Angeles' )->go_back( '1 month' )->format_as( 'Y-m-d' )
	 */
	public function go_back( $interval ) {
		$this->_datetime->sub( \DateInterval::createFromDateString( $interval ) );

		return $this;
	}

	/**
	 * Go forward in time
	 * Eg: PMC_TimeMachine::create( 'America/Los_Angeles' )->go_forth( '1 month' )->format_as( 'Y-m-d' )
	 */
	public function go_forth( $interval ) {
		$this->_datetime->add( \DateInterval::createFromDateString( $interval ) );

		return $this;
	}

	/**
	 * Go forward in time
	 * Eg: PMC_TimeMachine::create( 'America/Los_Angeles' )->go_forth( '1 month' )->format_as( 'Y-m-d' )
	 */
	public function change_timezone_to( $timezone ) {
		if ( ! empty( $this->_datetime ) ) {
			$this->_datetime->setTimezone( new \DateTimeZone( $timezone ) );
		}

		return $this;
	}

	/**
	 * Utility function to check if current date time has reached a specific time period
	 * in the time stream. Useful for time locks on code.
	 *
	 * Example usage:
	 * If one needs to check for 1st Oct 2015 10am PT then one would do:
	 *
	 *		if ( PMC_TimeMachine::create( 'America/Los_Angeles' )->has_passed( '2015-10-01-10-0-0' ) ) {
	 *			//do stuff, its time
	 *		}
	 *
	 * If one needs to check only for start of a day, like say 1st Oct 2015 PT then it can be done like:
	 *
	 *		if ( PMC_TimeMachine::create( 'America/Los_Angeles' )->has_passed( '2015-10-01' ) ) {
	 *			//do stuff, its time
	 *		}
	 *
	 * Last 3 tokens for Hour, Minutes & Seconds are needed only if you want to check for a specific time
	 * on the said date. All 3 of these are required even if you want to check only for hour like in first
	 * example above.
	 *
	 * @param int|string $date Future date to check against either in Unix timestamp format or as 'Y-m-d-h-i-s'
	 * @return boolean Returns TRUE if specified date & time has been reached else FALSE
	 */
	public function has_passed( $date ) {
		if ( ! is_numeric( $date ) && substr_count( $date, '-' ) < 2 ) {
			throw new \ErrorException( 'Invalid date format passed to ' . __CLASS__ . '::' . __FUNCTION__ . '()' );
		}

		$current_timestamp = $this->format_as( 'U' );

		if ( is_numeric( $date ) ) {
			$future_date = $date;
		} else {
			$date = explode( '-', $date );

			$this->_datetime->setDate( $date[0], $date[1], $date[2] );

			if ( count( $date ) == 6 ) {
				$this->_datetime->setTime( $date[3], $date[4], $date[5] );
			}

			$future_date = $this->format_as( 'U' );
		}

		if ( $current_timestamp > 0 && $future_date > 0 && $current_timestamp >= $future_date ) {
			return true;
		}

		unset( $future_date, $current_timestamp );

		return false;
	}

}	//end of class


//EOF
