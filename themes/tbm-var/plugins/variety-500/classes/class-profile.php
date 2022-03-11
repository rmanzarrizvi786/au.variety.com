<?php
/**
 * Profile
 *
 * Profile-related functionality.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Profile
 *
 * Helper static functions for handling and retrieving profile data.
 *
 * @since 1.0
 */
class Profile {

	use Singleton;

	/**
	 * Cache Key.
	 */
	const CACHE_KEY = 'profile_urls';

	/**
	 * The plugin.
	 *
	 * @var object Plugin The Variety_500 class.
	 */
	public $plugin;

	/**
	 * The post meta fields.
	 *
	 * @var array Array of post meta.
	 */
	public $meta = array();

	/**
	 * Class constructor.
	 *
	 * Initializes the plugin and gets things started on the `init` action.
	 *
	 * @since 1.0
	 */
	protected function __construct() {
		$this->plugin = Bootstrap::get_instance();
		add_action( 'wp', array( $this, 'set_meta' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_action( 'wp_head', array( $this, 'add_profile_meta_tags' ) );
	}

	/**
	 * Adds meta tags for v500 profile page.
	 *
	 * @global object $post
	 *
	 * @since 2017-09-13 Milind More CDWE-623
	 *
	 * @return void
	 */
	public function add_profile_meta_tags() {

		global $post;

		// Check if current page is v500 profile page
		if ( empty( $post ) || ! Templates::is_profile() ) {
			return;
		}

		if ( ! empty( $post->post_title ) ) {

			printf( '<meta name="title" content="%s - V500 | Variety.com" />', esc_attr( $post->post_title ) );
			echo "\n";

		}

		$description = '';

		if ( ! empty( $post->post_content ) ) {

			// extract first sentance from article body.
			$description = substr( $post->post_content, 0, strpos( wp_strip_all_tags( $post->post_content ), '.' ) + 1 );

		}

		if ( ! empty( $description ) ) {
			printf( '<meta name="description" content="%s" />', esc_attr( $description ) );
			echo "\n";
		}

	}

	/**
	 * Call
	 *
	 * Magic method to query post meta data. The methods
	 * should start with `get_` and then be the field name,
	 * e.g. `get_first_name()`.
	 *
	 * @param  string $method Name of method invoked.
	 * @param  array  $arguments Arguments passed to the method (ignored).
	 * @return mixed Post meta data on success, or null on error.
	 */
	public function __call( $method, $arguments ) {
		if ( 0 === strpos( $method, 'get_' ) ) {
			$key = substr( $method, 4 );
			if ( isset( $this->meta[ $key ] ) ) {
				return $this->meta[ $key ];
			}
		}

		return null;
	}

	/**
	 * Get Meta
	 *
	 * Returns all the meta data for a Variety 500 executive.
	 *
	 * @since 1.0
	 * @action wp
	 * @param string|int $post_id The post ID of the executive profile.
	 */
	public function set_meta( $post_id = 0 ) {
		if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
			$post_id = get_the_ID();
		}
		$this->meta = array(
			'first_name'             => get_post_meta( $post_id, 'firstname', true ),
			'middle_name'            => get_post_meta( $post_id, 'middlename', true ),
			'last_name'              => get_post_meta( $post_id, 'lastname', true ),
			'nicknames'              => get_post_meta( $post_id, 'nicknames', true ),
			'aka'                    => get_post_meta( $post_id, 'aka', true ),
			'modified'               => get_post_meta( $post_id, 'modified', true ),
			'gender'                 => get_post_meta( $post_id, 'gender', true ),
			'ethnicity'              => get_post_meta( $post_id, 'country_of_origin', true ),
			'country_of_origin'      => get_post_meta( $post_id, 'country_of_residence', true ),
			'country_of_residence'   => get_post_meta( $post_id, 'country_of_residence', true ),
			'country_of_citizenship' => get_post_meta( $post_id, 'country_of_citizenship', true ),
			'education1'             => get_post_meta( $post_id, 'education1', true ),
			'education2'             => get_post_meta( $post_id, 'education2', true ),
			'education3'             => get_post_meta( $post_id, 'education3', true ),
			'job_function'           => get_post_meta( $post_id, 'job_function', true ),
			'job_title'              => get_post_meta( $post_id, 'job_title', true ),
			'companies'              => get_post_meta( $post_id, 'companies', true ),
			'career_highlights'      => get_post_meta( $post_id, 'career_highlights', true ),
			'media_category'         => get_post_meta( $post_id, 'media_category', true ),
			'honors'                 => get_post_meta( $post_id, 'honors', true ),
			'international'          => get_post_meta( $post_id, 'international', true ),
			'variety_500'            => get_post_meta( $post_id, 'variety_500', true ),
			'variety500_photo'       => get_post_meta( $post_id, 'variety500_photo', true ),
			'photo_url'              => get_post_meta( $post_id, 'photo_url', true ),
			'shutterstock_url'       => get_post_meta( $post_id, 'shutterstock_url', true ),
			'brief_synopsis'         => get_post_meta( $post_id, 'brief_synopsis', true ),
			'twitter_handle'         => get_post_meta( $post_id, 'twitter_handle', true ),
			'twitter_url'            => get_post_meta( $post_id, 'twitter_url', true ),
			'facebook_url'           => get_post_meta( $post_id, 'facebook_url', true ),
			'googleplus_url'         => get_post_meta( $post_id, 'googleplus_url', true ),
			'linkedin_url'           => get_post_meta( $post_id, 'linkedin_url', true ),
			'company_instagram_url'  => get_post_meta( $post_id, 'company_instagram_url', true ),
			'philanthropy'           => get_post_meta( $post_id, 'philanthropy', true ),
			'survey_advice'          => get_post_meta( $post_id, 'survey_advice', true ),
			'survey_inspiration'     => get_post_meta( $post_id, 'survey_inspiration', true ),
			'line_of_work'           => get_post_meta( $post_id, 'line_of_work', true ),
			'honoree_image'          => get_post_meta( $post_id, 'honoree_image', true ),
			'social'                 => get_post_meta( $post_id, 'social', true ),
			'talent'                 => get_post_meta( $post_id, 'talent', true ),
			'talent_credits'         => get_post_meta( $post_id, 'talent_credits', true ),
			'related_profiles'       => get_post_meta( $post_id, 'related_profiles', true ),
			'related_news'           => get_post_meta( $post_id, 'variety_articles', true ),
		);
	}

	/**
	 * Has Career Highlights
	 *
	 * Check if Career Highlights exist.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_career_highlights() {
		return ( ! empty( $this->meta['career_highlights']['company'] ) || ! empty( $this->meta['career_highlights']['projects'] ) || ! empty( $this->meta['career_highlights']['albums'] ) );
	}

	/**
	 * Has Education
	 *
	 * Check if an Education value exists.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_education() {
		return ( ! empty( $this->meta['education1'] ) || ! empty( $this->meta['education2'] ) || ! empty( $this->meta['education3'] ) );
	}

	/**
	 * Has Philanthropy
	 *
	 * Check if a Philanthropy value exists.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_philanthropy() {
		return ( ! empty( $this->meta['philanthropy'] ) && is_array( $this->meta['philanthropy'] ) && 1 <= $this->meta['philanthropy'] );
	}

	/**
	 * Has Survey
	 *
	 * Check if survey responses exist.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_survey() {
		return ( ! empty( $this->meta['survey_advice'] ) || ! empty( $this->meta['survey_inspriation'] ) );
	}

	/**
	 * Has Line of Work
	 *
	 * Check if "line_of_work" exists.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_line_of_work() {
		return ( ! empty( $this->meta['line_of_work'] ) && is_array( $this->meta['line_of_work'] ) );
	}

	/**
	 * Has Media Category
	 *
	 * Check if "media_category" exists.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_media_category() {
		return ( ! empty( $this->meta['media_category'] ) && is_array( $this->meta['media_category'] ) );
	}

	/**
	 * Has Jobs
	 *
	 * Check if "line_of_work" or "media_category" are valid.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_jobs() {
		return ( ! empty( $this->has_line_of_work() ) || ! empty( $this->has_media_category() ) );
	}

	/**
	 * Has Social
	 *
	 * Check if any social urls exist.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_social() {
		return ( ! empty( $this->meta['social']['twitter_url'] ) || ! empty( $this->meta['social']['instagram_url'] ) || ! empty( $this->meta['company_instagram_url'] ) );
	}

	/**
	 * Has News
	 *
	 * Check if any News Items exist.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_news() {
		return ( ! empty( $this->meta['related_news'] ) && is_array( $this->meta['related_news'] ) && 1 <= count( $this->meta['related_news'] ) );
	}

	/**
	 * Has Related Profiles
	 *
	 * Check if any Related Profiles exist.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_related_profiles() {
		return ( ! empty( $this->meta['related_profiles'] ) && is_array( $this->meta['related_profiles'] ) && 1 <= count( $this->meta['related_profiles'] ) );
	}

	/**
	 * Get Companies
	 *
	 * Create a comma-separated string of companies.
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_companies() {
		if ( empty( $this->meta['companies'] ) || ! is_array( $this->meta['companies'] ) ) {
			return '';
		}
		$companies = wp_list_pluck( $this->meta['companies'] , 'company_name' );
		return implode( ', ', $companies );
	}

	/**
	 * Get Twitter URL
	 *
	 * Get the Twitter URL from the social array.
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_twitter_url() {
		return ( ! empty( $this->meta['social']['twitter_url'] ) ) ? $this->meta['social']['twitter_url'] : '';
	}

	/**
	 * Get Twitter Handle
	 *
	 * Get the Twitter handle, starting with an appropriate "@".
	 *
	 * @since 1.0
	 * @return string The twitter handle with an @ on the front, else empty.
	 */
	public function get_twitter_handle() {
		if ( empty( $this->meta['social']['twitter_handle'] ) ) {
			return '';
		}
		$handle = $this->meta['social']['twitter_handle'];
		if ( 0 !== strpos( $handle, '@' ) ) {
			$handle = '@' . $handle;
		}
		return $handle;
	}

	/**
	 * Get Instagram URL
	 *
	 * Get the Instagram URL.
	 *
	 * @since 1.0
	 * @return mixed A URL string, else false.
	 */
	public function get_instagram_url() {
		$url = false;
		if ( ! empty( $this->meta['social']['instagram_url'] ) ) {
			$url = $this->meta['social']['instagram_url'];
		} elseif ( ! empty( $this->meta['company_instagram_url'] ) ) {
			$url = $this->meta['company_instagram_url'];
		}

		$pos = strpos( $url, '?' );
		if ( false !== $pos ) {
			$url = substr( $url, 0, $pos );
		}
		return $url;
	}

	/**
	 * Get Instagram Username
	 *
	 * Get Instagram Username from a URL string.
	 *
	 * Converts a URL into an array, then fetches
	 * the last string from the end of the array.
	 *
	 * Finally, adds an "@" symbol to the username.
	 *
	 * @since 1.0
	 * @return mixed|string
	 */
	public function get_instagram_username() {
		$url = $this->get_instagram_url();
		// Ensure we're dealing with a URL.
		if ( empty( $url ) || false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return '';
		}

		// Remove query params.
		$pos = strpos( $url, '?' );
		if ( false !== $pos ) {
			$url = substr( $url, 0, $pos );
		}

		$url = untrailingslashit( $url );
		$url_array = explode( '/', $url );
		// Get last item from array.
		$username = array_pop( $url_array );
		return $username;
	}

	/**
	 * Get Instagram Feed
	 *
	 * Get the Instagram feed.
	 *
	 * @since 1.0
	 * @return array|bool Instagram feed, else false.
	 */
	public function get_instagram_feed() {
		$username = $this->get_instagram_username();
		if ( ! empty( $username ) ) {
			return Instagram::get_instance()->get_feed( $username, 10 );
		}
		return false;
	}

	/**
	 * Get Job Icon Class
	 *
	 * Check if a job has a corresponding icon.
	 *
	 * Formats job string to match possible job
	 * names. The format is lowercase with a hyphen ("-").
	 *
	 * The file name is set up to match the class names in
	 * /assets/scss/components/_c-profile-jobs.scss.
	 *
	 * Also, these class names match the job image file names
	 * in /assets/images/jobs/.
	 *
	 * If the file exists, then return the class name.
	 *
	 * @since 1.0
	 * @param string $string A job title.
	 * @return string The icon class, else an empty string.
	 */
	public function get_job_icon_class( $string ) {
		// Ensure we have usable input.
		if ( empty( $string ) || ! is_string( $string ) ) {
			return '';
		}

		$job_class = sanitize_title( $string );
		$file = untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/images/jobs/' . $job_class . '.png';

		// Check to make sure we have an array of icon classes.
		if ( file_exists( $file ) ) {
			return '';
		}

		return $job_class;
	}

	/**
	 * Find Career Highlight Image URL
	 *
	 * Get a career highlight company/track/album image.
	 *
	 * @since 1.0
	 * @param array $company A company/track/album array of data.
	 * @return string An image URL.
	 */
	public static function find_career_highlight_image_url( $company ) {
		if ( empty( $company ) || ! is_array( $company ) ) {
			return '';
		}

		if ( ! empty( $company['company_logo'] ) ) {
			return $company['company_logo'];
		} elseif ( ! empty( $company['track_photo'] ) ) {
			return $company['track_photo'];
		} elseif ( ! empty( $company['album_image'] ) ) {
			return $company['album_image'];
		}
		return '';
	}

	/**
	 * Find Career Highlight Title
	 *
	 * Get a career highlight company/track/album title.
	 *
	 * @since 1.0
	 * @param array $company A company/track/album array of data.
	 * @return string A company/track/album title.
	 */
	public static function find_career_highlight_title( $company ) {
		if ( empty( $company ) || ! is_array( $company ) ) {
			return '';
		}

		if ( ! empty( $company['company_name'] ) ) {
			return $company['company_name'];
		} elseif ( ! empty( $company['title'] )  ) {
			return $company['title'];
		} elseif ( ! empty( $company['album_name'] ) ) {
			return $company['album_name'];
		}
		return '';
	}

	/**
	 * Find Related Profile URL
	 *
	 * @since 1.0
	 * @param string|int $variety_id A Variety ID.
	 * @return bool|string A Permalink string, else false.
	 */
	public static function find_related_profile_url( $variety_id ) {
		if ( ! is_numeric( $variety_id ) ) {
			return false;
		}

		// This transient holds an array of variety_id => permalink pairs.
		$url_array = wp_cache_get( self::CACHE_KEY, Bootstrap::CACHE_GROUP );
		if ( empty( $url_array ) ) {
			$url_array = array();
		}

		if ( empty( $url_array[ $variety_id ] ) || false === filter_var( $url_array[ $variety_id ], FILTER_VALIDATE_URL ) ) {

			// @codeCoverageIgnoreStart
			$post_id = self::get_exec_profile_id( $variety_id );

			if ( empty( $post_id ) ) {
				return false;
			}

			$url_array[ $variety_id ] = get_permalink( $post_id );
			// @codeCoverageIgnoreEnd
			wp_cache_set( self::CACHE_KEY, $url_array, Bootstrap::CACHE_GROUP );
		}

		return $url_array[ $variety_id ];
	}

	/**
	 * Get Exec Profile Post ID from variety_id meta key.
	 *
	 * @param int|string $variety_id Meta value for `variety_id` meta key.
	 *
	 * @return mixed
	 */
	public static function get_exec_profile_id( $variety_id ) {

		$args = array(
			'fields'                 => 'ids',
			'post_type'              => 'hollywood_exec',
			'meta_key'               => 'variety_id', // WPCS: Slow query okay.
			'meta_value'             => $variety_id, // WPCS: Slow query okay.
			'posts_per_page'         => 1,
			'meta_compare'           => '==',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$query = new \WP_Query( $args );

		if ( empty( $query->posts[0] ) ) {
			return false;
		}

		return $query->posts[0];
	}

	/**
	 * Save Post
	 *
	 * Perform Profile-related procedures
	 * on the save_post action.
	 *
	 * @since 1.0
	 * @param int|string $post_id A Post ID.
	 * @param object     $post  \WP_Post object.
	 */
	public function save_post( $post_id, $post ) {
		if ( 'hollywood_exec' !== $post->post_type ) {
			return;
		}
		wp_cache_delete( self::CACHE_KEY, Bootstrap::CACHE_GROUP );
	}

	/**
	* Helper to return years in VY500
	*
	* Checks if exec has any vy500 terms, if so return array with values, else empty array
	*
	* @since 2.0
	* @return array an array of years nominated, if none then empty array
	* @param $post_id  Post ID
	*
	*/
	public function honored_years( $post_id = 0 ) {
		// @codeCoverageIgnoreStart
		if ( empty( $post_id ) ) {
			$post_id = get_the_id();
		}
		// @codeCoverageIgnoreEnd

		$vy500_years_terms = get_the_terms( $post_id, 'vy500_year' );  // get the terms
		$years_arr         = array();

		if ( ! empty( $vy500_years_terms ) && ! is_wp_error( $vy500_years_terms ) ) {

			foreach ( (array) $vy500_years_terms as $vy500_year ) {
				array_push( $years_arr, $vy500_year->slug );
			}
		}

		return $years_arr;
	}

	/**
	* Display Honor Badge if Current Year
	*
	* Checks if the current year (set in Settings) is in the vy500_year array.
	* If exec nominated in current year, return true
	*
	* @since 2.0
	* @return string if current year, then returns year string, else empty string
	*
	*/
	public function honoree_current_year() {
		$variety_500_year = get_option( 'variety_500_year', date( 'Y' ) ); // get current year based on settings
		$post_id          = get_the_ID();
		$vy500_years_arr  = $this->honored_years( $post_id );

		// if meta value not array or empty, set to empty array, else use meta array
		$vy500_years_arr = ( is_array( $vy500_years_arr ) && ( ! empty( $vy500_years_arr ) ) ) ? $vy500_years_arr : array();

		// when setting $meta_years_arr above, checking if array, if not, casting as empty array
		if ( in_array( $variety_500_year, (array) $vy500_years_arr, true ) ) {
			return $variety_500_year;
		}

		return '';
	}

	/**
	 * New Profile
	 *
	 * Check if this is the first time an exec has been honored
	 *
	 * @since 2.0
	 * @param int|string $post_id A Post ID.
	 * @return boolean
	 */
	public function new_profile( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		// get the years honored. Cast as array in honored_years function.
		$vy500_years_arr = $this->honored_years( $post_id );
		$current_year    = get_option( 'variety_500_year', date( 'Y' ) );

		// if only one year in array, if that year equals current year and the current year is greater than or equal to 2018.
		if ( 1 === count( $vy500_years_arr ) && ( $current_year === $vy500_years_arr[0] ) ) {
			return true;
		} else {
			return false;
		}
	}
}

