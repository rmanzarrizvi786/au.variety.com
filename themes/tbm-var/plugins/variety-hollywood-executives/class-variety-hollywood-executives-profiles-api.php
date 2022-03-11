<?php

/**
 * Class Variety_Hollywood_Executives_Profiles_API
 *
 * API to easily fetch Exec Profile data
 *
 */
class Variety_Hollywood_Executives_Profiles_API {

	const VY_EXEC_PROFILES_API_ERROR_CODE = 'vy500-exec-profiles-api-error';
	const VY_EXEC_PROFILE_POST_TYPE = 'hollywood_exec';
	const VY_TAXONOMY_ID_PREFIX = 'vi_';

	// cache prefixes
	const VY_EXEC_PROFILE_CACHE_PREFIX = 'vy_exec_profile_';
	const VY_EXEC_PROFILE_ID_CACHE_PREFIX = 'vy_exec_profile_post_id_';


	private $_id;
	private $_is_exec_id;
	private $_is_valid;

	/**
	 * Constructor
	 *
	 * @param  int $id
	 * @param  boolean $is_exec_id
	 *
	 */
	public function __construct( $id, $is_exec_id = false ) {
		$this->_id         = $id;
		$this->_is_exec_id = $is_exec_id;
	}

	/**
	 * Get post id
	 */
	public function get_post_id() {
		$post_id = $this->_id;
		if ( $this->_is_exec_id ) {

			$post_id = static::get_post_id_from_variety_id( $this->_id );

			if ( empty( $post_id ) ) {
				$this->_is_valid = false;

				return new WP_Error( self::VY_EXEC_PROFILES_API_ERROR_CODE, 'There is no post associated with variety_id' );
			}
		}

		return $post_id;
	}

	/**
	 * Get cached profile data
	 */
	public function get() {

		if ( false === $this->_is_valid ) {
			return new WP_Error( self::VY_EXEC_PROFILES_API_ERROR_CODE, 'There is no post associated with variety_id' );
		}

		$post_id = $this->get_post_id();
		if ( is_wp_error( $post_id ) ) {
			return new WP_Error( self::VY_EXEC_PROFILES_API_ERROR_CODE, $post_id->get_error_message() );
		}

		// Keep the cache data for 30 minutes
		$cache_key = static::get_exec_profile_cache_key( $post_id );
		$cache     = new PMC_Cache( $cache_key );
		$profile   = $cache->expires_in( 30 * MINUTE_IN_SECONDS )
							->updates_with( array( $this, 'get_uncached' ) )
							->get();

		if ( empty( $profile ) ) {
			return new WP_Error( self::VY_EXEC_PROFILES_API_ERROR_CODE, 'Could not retrieve specified profile' );
		}

		return $profile;
	}

	/**
	 * Get profile data
	 */
	public function get_uncached() {

		if ( false === $this->_is_valid ) {
			return new WP_Error( self::VY_EXEC_PROFILES_API_ERROR_CODE, 'There is no post associated with variety_id' );
		}

		$post_id = $this->get_post_id();

		if ( is_wp_error( $post_id ) ) {
			$this->_is_valid = false;

			return new WP_Error( self::VY_EXEC_PROFILES_API_ERROR_CODE, $post_id->get_error_message() );
		}

		$profile = get_post( $post_id );
		if ( empty( $profile ) ) {
			$this->_is_valid = false;

			return new WP_Error( self::VY_EXEC_PROFILES_API_ERROR_CODE, 'There is no profile associated with the id' );
		}

		$this->_is_valid = true;

		$profile = array(
			'biography' => $profile->post_content,
		);

		$profile_meta = $this->get_meta_data( $post_id );
		$profile      = array_merge( $profile, $profile_meta );

		return $profile;
	}

	/**
	 * Helper function to get profile meta
	 *
	 * @param  array $post_id
	 *
	 * @return array $meta_data
	 */
	public function get_meta_data( $post_id ) {
		$profile_meta = get_post_meta( $post_id );
		$meta_data    = array();

		$meta_data['variety_id']             = ( ! empty( $profile_meta['variety_id'] ) ) ? array_shift( $profile_meta['variety_id'] ) : '';
		$meta_data['first_name']             = ( ! empty( $profile_meta['firstname'] ) ) ? array_shift( $profile_meta['firstname'] ) : '';
		$meta_data['middle_name']            = ( ! empty( $profile_meta['middlename'] ) ) ? array_shift( $profile_meta['middlename'] ) : '';
		$meta_data['last_name']              = ( ! empty( $profile_meta['lastname'] ) ) ? array_shift( $profile_meta['lastname'] ) : '';
		$meta_data['modified']               = ( ! empty( $profile_meta['modified'] ) ) ? array_shift( $profile_meta['modified'] ) : '';
		$meta_data['nicknames']              = ( ! empty( $profile_meta['nicknames'] ) ) ? array_shift( $profile_meta['nicknames'] ) : '';
		$meta_data['aka']                    = ( ! empty( $profile_meta['aka'] ) ) ? array_shift( $profile_meta['aka'] ) : '';
		$meta_data['variety_500']            = ( ! empty( $profile_meta['variety_500'] ) ) ? array_shift( $profile_meta['variety_500'] ) : '';
		$meta_data['gender']                 = ( ! empty( $profile_meta['gender'] ) ) ? array_shift( $profile_meta['gender'] ) : '';
		$meta_data['ethnicity']              = ( ! empty( $profile_meta['ethnicity'] ) ) ? array_shift( $profile_meta['ethnicity'] ) : '';
		$meta_data['country_of_origin']      = ( ! empty( $profile_meta['country_of_origin'] ) ) ? array_shift( $profile_meta['country_of_origin'] ) : '';
		$meta_data['country_of_residence']   = ( ! empty( $profile_meta['country_of_residence'] ) ) ? (array) $this->_maybe_convert_to_array( array_shift( $profile_meta['country_of_residence'] ) ) : array();
		$meta_data['country_of_citizenship'] = ( ! empty( $profile_meta['country_of_citizenship'] ) ) ? (array) $this->_maybe_convert_to_array( array_shift( maybe_unserialize( array_shift( $profile_meta['country_of_citizenship'] ) ) ) ) : array();
		$meta_data['education1']             = ( ! empty( $profile_meta['education1'] ) ) ? array_shift( $profile_meta['education1'] ) : '';
		$meta_data['education2']             = ( ! empty( $profile_meta['education2'] ) ) ? array_shift( $profile_meta['education2'] ) : '';
		$meta_data['education3']             = ( ! empty( $profile_meta['education3'] ) ) ? array_shift( $profile_meta['education3'] ) : '';
		$meta_data['job_function']           = ( ! empty( $profile_meta['job_function'] ) ) ? array_shift( $profile_meta['job_function'] ) : '';
		// We receive serialized data and need to unserialize it.
		$meta_data['media_category']         = ( ! empty( $profile_meta['media_category'] ) ) ? unserialize( array_shift( $profile_meta['media_category'] ) ) : '';
		$meta_data['honors']                 = ( ! empty( $profile_meta['honors'] ) ) ? array_shift( $profile_meta['honors'] ) : '';
		$meta_data['vy500_year']             = ( ! empty( $profile_meta['vy500_year'] ) ) ? array_shift( $profile_meta['vy500_year'] ) : '';
		$meta_data['international']          = ( ! empty( $profile_meta['international'] ) ) ? array_shift( $profile_meta['international'] ) : '';
		$meta_data['brief_synopsis']         = ( ! empty( $profile_meta['brief_synopsis'] ) ) ? array_shift( $profile_meta['brief_synopsis'] ) : '';
		$meta_data['company_instagram_url']  = ( ! empty( $profile_meta['company_instagram_url'] ) ) ? array_shift( $profile_meta['company_instagram_url'] ) : '';
		$meta_data['philanthropy']           = ( ! empty( $profile_meta['philanthropy'] ) ) ? array_shift( $profile_meta['philanthropy'] ) : '';
		$meta_data['survey_advice']          = ( ! empty( $profile_meta['survey_advice'] ) ) ? array_shift( $profile_meta['survey_advice'] ) : '';
		$meta_data['survey_inspiration']     = ( ! empty( $profile_meta['survey_inspiration'] ) ) ? array_shift( $profile_meta['survey_inspiration'] ) : '';
		// We receive serialized data and need to unserialize it.
		$meta_data['line_of_work']           = ( ! empty( $profile_meta['line_of_work'] ) ) ? unserialize( array_shift( $profile_meta['line_of_work'] ) ) : '';
		$meta_data['honoree_image']          = ( ! empty( $profile_meta['honoree_image'] ) ) ? array_shift( $profile_meta['honoree_image'] ) : '';
		$meta_data['job_title']              = ( ! empty( $profile_meta['job_title'] ) ) ? array_shift( $profile_meta['job_title'] ) : '';
		// We receive serialized data and need to unserialize it.
		$meta_data['companies']              = ( ! empty( $profile_meta['companies'] ) ) ? unserialize( array_shift( $profile_meta['companies'] ) ) : '';
		$meta_data['social']                 = ( ! empty( $profile_meta['social'] ) ) ? unserialize( array_shift( $profile_meta['social'] ) ) : '';
		$meta_data['talent']                 = ( ! empty( $profile_meta['talent'] ) ) ? unserialize( array_shift( $profile_meta['talent'] ) ) : '';
		$meta_data['talent_credits']         = ( ! empty( $profile_meta['talent_credits'] ) ) ? unserialize( array_shift( $profile_meta['talent_credits'] ) ) : '';
		$meta_data['career_highlights']      = ( ! empty( $profile_meta['career_highlights'] ) ) ? unserialize( array_shift( $profile_meta['career_highlights'] ) ) : '';
		$meta_data['related_profiles']       = ( ! empty( $profile_meta['related_profiles'] ) ) ? unserialize( array_shift( $profile_meta['related_profiles'] ) ) : '';
		$meta_data['variety_articles']       = ( ! empty( $profile_meta['variety_articles'] ) ) ? unserialize( array_shift( $profile_meta['variety_articles'] ) ) : '';

		return $meta_data;
	}

	/**
	 * Method to convert array to string if specified seperator exists in the string
	 *
	 * @param string $data
	 * @param string $seperator
	 *
	 * @return array|string
	 */
	protected function _maybe_convert_to_array( $data, $seperator = ',' ) {

		if ( empty( $data ) || ! is_string( $data ) || empty( $seperator ) || ! is_string( $seperator ) ) {
			return $data;
		}

		if ( strpos( $data, $seperator ) === false ) {
			return $data;
		}

		$data_array = explode( $seperator, $data );

		return array_map( 'trim', $data_array );

	}

	/**
	 * Get post ID from variety ID from cache
	 *
	 * @param  int /string $variety_id
	 *
	 * @return int $post_id
	 */
	public static function get_post_id_from_variety_id( $variety_id ) {
		// Keep the cache data for one day
		$cache_key = self::get_exec_profile_id_cache_key( $variety_id );
		$cache     = new PMC_Cache( $cache_key );
		$post_id   = $cache->expires_in( DAY_IN_SECONDS )
							->updates_with( array( get_called_class(), 'query_post_id_from_variety_id' ), array( $variety_id ) )
							->get();

		return $post_id;
	}

	/**
	 * Get post ID from variety ID from db
	 *
	 * @param  int $variety_id
	 *
	 * @return int $post_id
	 */
	public static function query_post_id_from_variety_id( $variety_id ) {

		// Usage of tax_query is required to get posts with variety_id.
		$args = array(
			'post_type'        => self::VY_EXEC_PROFILE_POST_TYPE,
			'tax_query'        => array(
				array(
					'taxonomy'         => Variety_Hollywood_Executives_Profile::VY_500_VARIETY_ID_TAXANOMY,
					'field'            => 'slug',
					'terms'            => self::get_variety_id_taxonomy( $variety_id ),
					'include_children' => false,
				),
			),
			'suppress_filters' => false,
		);

		$posts = get_posts( $args );
		if ( empty( $posts ) ) {
			return false;
		}

		$post    = array_shift( $posts );
		$post_id = $post->ID;

		return $post_id;
	}

	/**
	 * Get exec profile cache key
	 *
	 * @param  string /int $varierty_id
	 *
	 * @return string cache_key
	 */
	public static function get_exec_profile_cache_key( $variety_id ) {
		if ( strpos( $variety_id, self::VY_EXEC_PROFILE_CACHE_PREFIX ) !== false ) {
			return $variety_id;
		}

		return self::VY_EXEC_PROFILE_CACHE_PREFIX . $variety_id;
	}

	/**
	 * Get exec profile id cache key
	 *
	 * @param  string /int $varierty_id
	 *
	 * @return string cache_key
	 */
	public static function get_exec_profile_id_cache_key( $variety_id ) {
		if ( strpos( $variety_id, self::VY_EXEC_PROFILE_ID_CACHE_PREFIX ) !== false ) {
			return $variety_id;
		}

		return self::VY_EXEC_PROFILE_ID_CACHE_PREFIX . $variety_id;
	}

	/**
	 * Check whether the profile is in vy500 for the specified year
	 *
	 * @param  string /int $varierty_id
	 *
	 * @return string taxonomy_id
	 */
	public static function get_variety_id_taxonomy( $variety_id ) {
		if ( strpos( $variety_id, self::VY_TAXONOMY_ID_PREFIX ) !== false ) {
			return $variety_id;
		}

		return self::VY_TAXONOMY_ID_PREFIX . $variety_id;
	}

	/**
	 * Check the post ID is valid
	 *
	 * @return boolean true/false
	 */
	public function is_valid() {

		if ( null !== $this->_is_valid ) {
			return $this->_is_valid;
		}

		$post_id = $this->get_post_id();

		if ( is_wp_error( $post_id ) ) {
			$this->_is_valid = false;

			return false;
		}

		if ( get_post_type( $post_id ) !== self::VY_EXEC_PROFILE_POST_TYPE ) {
			$this->_is_valid = false;

			return false;
		}

		$this->_is_valid = true;

		return true;

	}

	/**
	 * Check whether the profile is in vy500 for any year.
	 *
	 * @param  string $year
	 *
	 * @return boolean true/false
	 *
	 * @codeCoverageIgnore // created PMCEED-834 to create unit test for this
	 */
	public function is_in_500( $year = '' ) {
		$post_id          = $this->get_post_id();
		$vy500_years      = get_the_terms( $post_id, 'vy500_year' );
		$variety_500_year = absint( get_option( 'variety_500_year', date( 'Y' ) ) );

		if ( empty( $vy500_years ) || is_wp_error( $vy500_years ) ) {
			return false;
		}

		// loop through term slugs
		foreach ( (array) $vy500_years as $term ) {

			// is it actually a WP_Term
			if ( ! is_a( $term, 'WP_Term' ) ) {
				continue;
			}

			// the current year must be greater than or equal to term->slug for this to be a Profile
			if ( $variety_500_year >= absint( $term->slug ) ) {
				return true;
				break;
			}
		}

		return false;
	}

}


//EOF
