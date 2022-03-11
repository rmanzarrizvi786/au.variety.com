<?php
/**
 * Stats
 *
 * Pulls stats from meta data.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Stats
 *
 * Home page stats.
 *
 * @since 1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Stats {

	use Singleton;

	/**
	 * Stats
	 *
	 * Array with stats.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $stats;

	/**
	 * Now
	 *
	 * Unix timestamp of the current time.
	 *
	 * @since 1.0
	 * @var int
	 */
	private $now;

	/**
	 * Year End
	 *
	 * Unix timestamp of the last day/second of the year.
	 *
	 * @since 1.0
	 * @var int
	 */
	private $year_end;

	/**
	 * Year End Seconds
	 *
	 * Seconds value until the end of the year.
	 *
	 * @since 1.0
	 * @var int
	 */
	private $year_end_seconds;

	/**
	 * Cache KEY
	 *
	 * @since 1.0
	 */
	const CACHE_KEY = 'stats';

	/**
	 * Option Group
	 *
	 * @since 1.0
	 */
	const OPTION_GROUP = 'variety_500_stats';

	/**
	 * Class constructor.
	 *
	 * Initialize our templating filters.
	 *
	 * @since 1.0
	 */
	protected function __construct() {
		/*
		 * Use the `wp` action since we only want to set the stats after we have
		 * changed templates. This is because we only set stats when someone
		 * visits the 500 home page.
		 */
		add_action( 'wp', array( $this, 'set_stats' ) );

		// Dates and time that we need for settings cache and option expiry.
		$this->now              = strtotime( date( 'Y-m-d H:i:s' ) );
		$this->year_end         = strtotime( date( 'Y-m-d 23:59:59', strtotime( 'Dec 31' ) ) );
		$this->year_end_seconds = $this->year_end - $this->now;
	}

	/**
	 * Get Stats
	 *
	 * Return the stats array.
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_stats() {
		return $this->stats;
	}

	/**
	 * Set Stats
	 *
	 * Run through our profiles and collect stats (if not cached).
	 *
	 * @since 1.0
	 */
	public function set_stats() {
		// Only get/set stats if we're on the 500 home page or the search template.
		if ( ! Templates::is_home() && ! Templates::is_search() ) {
			return;
		}

		if ( ! class_exists( '\Variety_Hollywood_Executives_API' ) || is_admin() ) {
			return;
		}

		if ( false === ( $this->stats = wp_cache_get( self::CACHE_KEY, Bootstrap::CACHE_GROUP ) ) ) {
			// No cache, so let's first see if we can find it in an option.
			if ( ! $this->stats_option_expired() ) {
				$this->stats = $this->get_stats_from_option();

				// If stats available are from our options, use them.
				if ( ! empty( $this->stats ) ) {
					$this->save_stats();
					return;
				}
			}

			$variety_500_year = get_option( 'variety_500_year', date( 'Y' ) );

			$tax_query = array(
				array(
					'taxonomy'         => 'vy500_year',
					'field'            => 'name',
					'terms'            => $variety_500_year,
					'include_children' => false,
				),
			);

			/*
			 * We have to fetch and run through all 500 profiles to tally up
			 * the stats. We cache this data for a year, plus add a fallback
			 * in to an option (in case the memory cache gets cleared) so
			 * that the query only gets run once a year. We also split it up
			 * in to 5 sub-queries.
			 */
			for ( $i = 0; $i <= 4; $i++ ) {
				$profiles = new \WP_Query( array(
					'post_type'      => \Variety_Hollywood_Executives_API::POST_TYPE,
					'tax_query'      => $tax_query,
					'posts_per_page' => 100,
					'paged'          => $i + 1,
					'order'          => 'ASC',
					'orderby'        => 'ID',
				) );

				if ( ! empty( $profiles->posts ) ) {
					foreach ( $profiles->posts as $profile ) {
						$this->build_stat_counts( get_post_meta( $profile->ID ) );
					}
				} else {
					// Break the loop if we no longer have data.
					break;
				}
			}

			$this->sort_stats();
			$this->save_stats();
			wp_reset_postdata();
		}	// end if()
	}

	/**
	 * Sort States
	 *
	 * Sort the stats from highest count to lowest.
	 *
	 * @since 1.0
	 */
	private function sort_stats() {
		if ( ! empty( $this->stats ) ) {
			foreach ( $this->stats as $key => $stat ) {
				arsort( $this->stats[ $key ] );
			}
		}
	}

	/**
	 * Stats Cache Expired
	 *
	 * Checks to see if our stats option should be regenerated.
	 *
	 * @since 1.0
	 * @return bool
	 */
	private function stats_option_expired() {
		$expiry = pmc_get_option( 'stats_expiry', self::OPTION_GROUP );

		if ( ! empty( $expiry ) && $this->now < $expiry ) {
			return false;
		}

		return true;
	}

	/**
	 * Get Stats From Option
	 *
	 * Returns the stats by fetching them from our option.
	 *
	 * @since 1.0
	 * @return array
	 */
	private function get_stats_from_option() {
		$stats = pmc_get_option( 'stats', self::OPTION_GROUP );

		if ( empty( $stats ) ) {
			return array();
		}

		return $stats;
	}

	/**
	 * Save Stats
	 *
	 * Saves our stats in the cache as well as in an option. Cache is the first
	 * layer we check for stats and if not available, we fall back to the
	 * option.
	 *
	 * @since 1.0
	 */
	private function save_stats() {
		// Cache the data.
		wp_cache_set( self::CACHE_KEY, $this->stats, Bootstrap::CACHE_GROUP, $this->year_end_seconds );

		/*
		 * Save to our options CPT and also add an expiry date. The stats should
		 * expire at the end of the year.
		 */
		pmc_update_option( 'stats_expiry', $this->year_end, self::OPTION_GROUP );
		pmc_update_option( 'stats', $this->stats, self::OPTION_GROUP );
	}

	/**
	 * Invalidate Stats Cache
	 *
	 * Clears the memory and option cache of the stats so that they get
	 * regenerated.
	 *
	 * @since 1.0
	 */
	public function invalidate_stats_cache() {
		wp_cache_delete( self::CACHE_KEY, Bootstrap::CACHE_GROUP );
		pmc_delete_option( 'stats_expiry', self::OPTION_GROUP );
	}

	/**
	 * Build Stat Counts
	 *
	 * Take the meta data and update our count values for each different
	 * statistic that we track.
	 *
	 * @since 1.0
	 * @param array $meta The meta values of the profile.
	 */
	private function build_stat_counts( $meta ) {
		/**
		 * Country stats
		 */
		$types = array( 'country_of_residence', 'country_of_citizenship', 'line_of_work', 'media_category' );

		foreach ( $types as $type ) {
			/*
			 * Runs through each type of stat we need to collect and adds it to
			 * the stats array, using the meta value as the key and a count as
			 * the value.
			 *
			 * Example Output:
			 *
			 * $this->stats = array(
			 *     'country_of_residence' => array(
			 *         'United States'  => 10,
			 *         'United Kingdom' => 5,
			 *     ),
			 *     'country_of_citizenship' => array(
			 *         'United States'  => 12,
			 *         'United Kingdom' => 3,
			 *     ),
			 * );
			 */
			if ( ! empty( $meta[ $type ][0] ) ) {
				// Some values are stored in a serialized array.
				$value = maybe_unserialize( $meta[ $type ][0] );

				// Values could be delimited list, serialized array or string.
				if ( is_array( $value ) ) {
					$values = $value;
				} else {
					$values = explode( ',', $value );
				}

				// We could be dealing with a string or array.
				if ( count( $values ) > 1 ) {
					foreach ( $values as $value ) {
						$value = trim( $value );
						if ( ! empty( $value ) ) {
							if ( empty( $this->stats[ $type ][ $value ] ) ) {
								$this->stats[ $type ][ $value ] = 1;
							} else {
								$this->stats[ $type ][ $value ]++;
							}
						}
					}
				} else {
					$value = trim( $value );
					if ( ! empty( $value ) ) {
						if ( empty( $this->stats[ $type ][ $value ] ) ) {
							$this->stats[ $type ][ $value ] = 1;
						} else {
							$this->stats[ $type ][ $value ]++;
						}
					}
				}
			}
		}	// end foreach()
	}
}

// EOF
