<?php

/**
 * Class Variety_Hollywood_Executives_REST_API
 *
 * Process REST API requests to manage exec profiles
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Hollywood_Executives_REST_API {

	use Singleton;

	const VY_EXEC_PROFILE_POST_TYPE                    = 'hollywood_exec';
	const VY_EXEC_PROFILE_TAXONOMY                     = 'vy500_year';
	const UPDATED_TIMESTAMP_META_KEY                   = '_updated_timestamp';
	const VY_EXEC_PROFILES_REST_API_ERROR_CODE         = 'vy500-exec-profiles-rest-api-error';
	const VY500_EXEC_PROFILE_INGESTION_CHEEZCAP_OPTION = 'vy_500_enable_exec_profile_ingestion';
	const PMC_API_USER_EMAIL                           = 'dist.dev+pmcapi@pmc.com'; // VI Hollywood Exec Profile Author Role.
	const PMC_VI_API_USER                              = 'viapiuser';
	const PMC_VI_API_USER_EMAIL                        = 'dist.dev+viapiuser@pmc.com'; // VI Hollywood Exec Profile Author Role.
	const VY500_AJAX_NONCE_ACTION                      = 'vy500_featured_image_ingestion';
	const VY500_EXEC_PROFILE_UPDATE_CHEEZCAP_OPTION    = 'vy500_enable_exec_profile_update';

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		add_action( 'rest_api_init', array( $this, 'action_rest_api_init' ) );
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'filter_pmc_global_cheezcap_options' ) );
		add_action( 'wp_ajax_nopriv_vy500_featured_image', array( $this, 'action_wp_ajax_nopriv_vy500_featured_image' ) );
		add_filter( 'nonce_user_logged_out', array( $this, 'filter_nonce_user_logged_out' ), 10, 2 );
		add_filter( 'two_factor_user_api_login_enable', [ $this, 'filter_two_factor_user_api_login_enable' ], 10, 2 );
	}

	/**
	 * Filters whether the user who generated the nonce is logged out.
	 *
	 * @param int $uid ID of the nonce-owning user.
	 * @param string $action The nonce action.
	 */
	public function filter_nonce_user_logged_out( $uid = 0, $action = '' ) {

		// The nonce we generate for wp_remote_post to admin-ajax
		// is created by the user who OAUTHs with WPCOM to reach our custom endpoint.
		//
		// However, because the ajax call must be nopriv, that same user
		// is not present when we check the nonce in our ajax callback,
		// so we must instruct check_ajax_referrer to verify the nonce
		// created by the same user user.

		if ( self::VY500_AJAX_NONCE_ACTION === $action ) {

			$user = get_user_by( 'email', self::PMC_VI_API_USER_EMAIL );

			if ( $user ) {
				return $user->ID;
			} else {

				unset( $user );
				$user = get_user_by( 'email', self::PMC_API_USER_EMAIL );

				if ( $user ) {
					return $user->ID;
				}

			}

		}

		return $uid;
	}

	/*
     * Ingest VY 500 profile featured image via AJAX
     */
	public function action_wp_ajax_nopriv_vy500_featured_image() {

		if ( ! defined( 'DOING_AJAX' ) && ! DOING_AJAX ) {
			return;
		}

		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$img_url = filter_input( INPUT_POST, 'img_url', FILTER_SANITIZE_URL );

		$nonce_check = check_ajax_referer( self::VY500_AJAX_NONCE_ACTION, 'nonce', false );
		if ( ! $nonce_check ) {
			wp_send_json_error( array( 'Unable to ingest vy500 featured image. Error: Nonce check failed' ) );
		}

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'No post ID provided.' ) );
		}

		if ( empty( $img_url ) || ( 'www.varietyinsight.com' !== wp_parse_url( $img_url, PHP_URL_HOST ) ) ) {
			wp_send_json_error( array( 'No image url provided.' ) );
		}

		$attachment_id = wpcom_vip_download_image( $img_url, $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( array( 'Unable to ingest vy500 featured image. Error: ' . $attachment_id->get_error_message() ) );
		}

		$thumbnail = set_post_thumbnail( $post_id, $attachment_id );

		if ( empty( $thumbnail ) ) {
			wp_send_json_error( array( 'Unable to set featured image.' ) );
		}

		wp_send_json_success( array( $attachment_id ) );
	}

	/**
	 * Add cheezcap option to enable/disable exec profiles rest api
	 *
	 * @param  int $post_id
	 * @param  string $honoree_image_url
	 * @param  string $variety_500
	 *
	 * @return boolean|WP_Error
	 */
	public function vy500_ingest_featured_image( $post_id, $honoree_image_url, $variety_500 ) {

		// Don't insert featured image for non-vy500 profiles
		if ( 'yes' !== $variety_500 ) {
			return false;
		}

		// Bail if there is an existing thumbnail that matches the url
		$existing_honoree_image = get_post_meta( $post_id, 'honoree_image', true );
		if ( has_post_thumbnail( $post_id ) && $existing_honoree_image === $honoree_image_url ) {
			return false;
		}

		// Rest routes are run in the non-admin context and hence
		// have no access to admin functionality, e.g. is_admin() is false here.
		// Which means wpcom_vip_download_image and media_sideload_image
		// both fail here since they're only run when is_admin is true.
		// to bypass this we'll use an admin-ajax action to handle the image ingestion.
		// See https://wordpressvip.zendesk.com/hc/en-us/requests/55657
		$ajax_response = wp_remote_post( admin_url( 'admin-ajax.php' ), array(
			'body'    => array(
				'nonce'   => wp_create_nonce( self::VY500_AJAX_NONCE_ACTION ),
				'action'  => 'vy500_featured_image',
				'post_id' => $post_id,
				'img_url' => $honoree_image_url,
			),
			'timeout' => 45,
		) );

		if ( is_wp_error( $ajax_response ) ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Error: ' . $ajax_response->get_error_message(), array( 'status' => 404 ) );
		}

		$ajax_body = json_decode( $ajax_response['body'] );

		// Bail if the AJAX call failed
		if ( empty( $ajax_body->success ) ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Error: ' . $ajax_body->data[0], array( 'status' => 404 ) );
		}

		return true;
	}

	/**
	 * Add cheezcap option to enable/disable exec profiles rest api
	 *
	 * @param  array $cheezcap_options
	 *
	 * @return array $cheezcap_options
	 */
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {

		if ( ! is_array( $cheezcap_options ) ) {
			return $cheezcap_options;
		}

		$cheezcap_options[] = new CheezCapDropdownOption(
			'Variety 500 exec profile ingestion',
			'When enabled Variety 500 exec profiles are managed from REST endpoints.',
			self::VY500_EXEC_PROFILE_INGESTION_CHEEZCAP_OPTION,
			array(
				'disabled',
				'enabled',
			),
			0,
			array( 'Disabled', 'Enabled' )
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			'Variety 500 exec profile update',
			'When enabled, Variety 500 exec profiles get updated as per the request from Variety Insight. When disabled, profile with variety_500="Yes" param will be prevented from update.',
			self::VY500_EXEC_PROFILE_UPDATE_CHEEZCAP_OPTION,
			array(
				'enabled',
				'disabled',
			),
			0,
			array( 'Enabled', 'Disabled' )
		);

		return $cheezcap_options;
	}

	/**
	 * Check whether rest api ingestion is enabled
	 */
	public static function is_enabled() {
		if ( 'disabled' === get_option( self::VY500_EXEC_PROFILE_INGESTION_CHEEZCAP_OPTION ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Register exec profiles rest api endpoints
	 */
	public function action_rest_api_init() {
		if ( ! function_exists( 'register_rest_route' ) ) {
			return $this->error( 404, 'VY does not allow exec profiles to be ingested via REST API.' );
		}

		if ( ! static::is_enabled() ) {
			return $this->error( 404, 'VY does not allow exec profiles to be ingested.' );
		}

		$version   = '1';
		$namespace = 'exec-profiles/' . $version;
		$base      = 'import';

		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_profile' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_exec_profiles' );
				},
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_profile' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_exec_profiles' );
				},
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_profile' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_exec_profiles' );
				},
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_profile' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_exec_profiles' );
				},
			),
		) );
	}

	/**
	 * Validate and sanitize json params
	 *
	 * @param  array $raw_json_params
	 *
	 * @return array|WP_Error $params
	 */
	public function validate_and_sanitize_params( $raw_json_params ) {

		if ( empty( $raw_json_params ) || ! is_array( $raw_json_params ) ) {

			return new WP_Error(
				self::VY_EXEC_PROFILES_REST_API_ERROR_CODE,
				'Empty Request',
				array(
					'status' => 400,
				)
			);

		}

		$params = array();

		if ( empty( $raw_json_params['profiles'] ) || ! is_array( $raw_json_params['profiles'] ) ) {

			return new WP_Error(
				self::VY_EXEC_PROFILES_REST_API_ERROR_CODE,
				'No profile found',
				array(
					'status'  => 400,
					'message' => 'No Profile found in request. Request must contain "profile" argument as array.',
				)
			);

		}

		$profiles = $raw_json_params['profiles'];

		if ( count( $profiles ) !== 1 ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Only one profile is allowed to be pushed at a time.', array( 'status' => 400 ) );
		}

		$profile = array_shift( $profiles );
		if ( empty( $profile ) || ! is_array( $profile ) ) {
			return new WP_Error( 'vy500-exec-profile-api-error', 'Invalid profile format in request.', array( 'status' => 400 ) );
		}

		if ( empty( $profile['variety_id'] ) || ( ! is_numeric( $profile['variety_id'] ) ) ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid variety ID', array( 'status' => 400 ) );
		}

		$params['variety_id']  = $profile['variety_id'];
		$params['variety_500'] = ( ! empty( $profile['variety_500'] ) && 'yes' === strtolower( $profile['variety_500'] ) ) ? 'yes' : 'no';

		$params['vy500_year'] = array();

		if ( ! empty( $profile['v500_year'] ) ) {
			$vy500_years = explode( ',', $profile['v500_year'] );
			$vy500_years = array_unique( array_filter( array_map( 'trim', $vy500_years ) ) );

			if ( ! empty( $vy500_years ) ) {
				$args = array(
					'taxonomy'   => 'vy500_year',
					'fields'     => 'names',
					'hide_empty' => false,
				);

				$vy500_terms = get_terms( $args );
				if ( ( ! empty( $vy500_years ) ) && ( ! is_wp_error( $vy500_terms ) ) ) {
					$vy500_terms = (array) $vy500_terms;
					foreach ( $vy500_years as $vy500_year ) {
						if ( ( ! empty( $vy500_year ) ) && ( ! in_array( $vy500_year, $vy500_terms, true ) ) ) {
							return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid variety 500 year term', array( 'status' => 400 ) );
						}
						$params['vy500_year'][] = $vy500_year;
					}
				}
			}
		}

		if ( empty( $profile['first_name'] ) ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid first name', array( 'status' => 400 ) );
		}

		$params['first_name'] = sanitize_text_field( $profile['first_name'] );

		$params['last_name']   = ( ! empty( $profile['last_name'] ) ) ? sanitize_text_field( $profile['last_name'] ) : '';
		$params['middle_name'] = ( ! empty( $profile['middle_name'] ) ) ? sanitize_text_field( $profile['middle_name'] ) : '';
		$params['nicknames']   = ( ! empty( $profile['nicknames'] ) ) ? sanitize_text_field( $profile['nicknames'] ) : '';
		$params['aka']         = ( ! empty( $profile['aka'] ) ) ? sanitize_text_field( $profile['aka'] ) : '';

		$params['title'] = $profile['first_name'] . ' ' . $profile['last_name'];

		$params['gender']            = ( ! empty( $profile['gender'] ) ) ? sanitize_text_field( $profile['gender'] ) : '';
		$params['ethnicity']         = ( ! empty( $profile['ethnicity'] ) ) ? sanitize_text_field( $profile['ethnicity'] ) : '';
		$params['country_of_origin'] = ( ! empty( $profile['country_of_origin'] ) ) ? sanitize_text_field( $profile['country_of_origin'] ) : '';

		$params['country_of_residence'] = ( ! empty( $profile['country_of_residence'] ) ) ? sanitize_text_field( $profile['country_of_residence'] ) : '';

		if ( empty( $params['country_of_residence'] ) && 'Yes' === $params['variety_500'] ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid country of residency', array( 'status' => 400 ) );
		}

		$list_of_countries                = $this->get_list_of_countries();
		$params['country_of_citizenship'] = array();

		if ( ! empty( $profile['country_of_citizenship'] ) ) {
			if ( is_array( $profile['country_of_citizenship'] ) ) {
				foreach ( $profile['country_of_citizenship'] as $country ) {
					$country_of_citizenship = sanitize_text_field( $country );
					if ( ! in_array( $country_of_citizenship, $list_of_countries, true ) ) {
						return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid country of citizenship', array( 'status' => 400 ) );
					}
					$params['country_of_citizenship'][] = sanitize_text_field( $country );
				}
			} else {
				$params['country_of_citizenship'] = array( sanitize_text_field( $profile['country_of_citizenship'] ) );
			}
		}

		if ( empty( $params['country_of_citizenship'] ) && 'Yes' === $params['variety_500'] ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid country of citizenship', array( 'status' => 400 ) );
		}

		$params['education1'] = ( ! empty( $profile['education1'] ) ) ? sanitize_text_field( $profile['education1'] ) : '';
		$params['education2'] = ( ! empty( $profile['education2'] ) ) ? sanitize_text_field( $profile['education2'] ) : '';
		$params['education3'] = ( ! empty( $profile['education3'] ) ) ? sanitize_text_field( $profile['education3'] ) : '';

		$params['job_title']    = ( ! empty( $profile['job_title'] ) ) ? sanitize_text_field( $profile['job_title'] ) : '';
		$params['job_function'] = ( ! empty( $profile['job_function'] ) ) ? sanitize_text_field( $profile['job_function'] ) : '';

		$params['brief_synopsis'] = ( ! empty( $profile['brief_synopsis'] ) ) ? sanitize_text_field( $profile['brief_synopsis'] ) : '';
		if ( empty( $params['brief_synopsis'] ) && 'Yes' === $params['variety_500'] ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid brief synopsis', array( 'status' => 400 ) );
		}

		$params['biography'] = ( ! empty( $profile['biography'] ) ) ? wp_kses_post( $profile['biography'] ) : '';
		if ( empty( $params['biography'] ) && 'Yes' === $params['variety_500'] ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid biography', array( 'status' => 400 ) );
		}

		$params['international'] = ( ( ! empty( $profile['international'] ) ) && 'yes' === strtolower( $profile['international'] ) ) ? 'yes' : 'no';

		$params['honors']             = ( ! empty( $profile['honors'] ) ) ? sanitize_text_field( $profile['honors'] ) : '';
		$params['survey_advice']      = ( ! empty( $profile['survey_advice'] ) ) ? sanitize_text_field( $profile['survey_advice'] ) : '';
		$params['survey_inspiration'] = ( ! empty( $profile['survey_inspiration'] ) ) ? sanitize_text_field( $profile['survey_inspiration'] ) : '';
		$params['photo_url']          = ( ! empty( $profile['photo_url'] ) ) ? esc_url_raw( $profile['photo_url'] ) : '';
		$params['photo_metadata']     = ( ! empty( $profile['photo_metadata'] ) ) ? sanitize_text_field( $profile['photo_metadata'] ) : '';

		$params['honoree_image'] = ( ! empty( $profile['honoree_image'] ) ) ? esc_url_raw( $profile['honoree_image'] ) : '';
		if ( 'Yes' === $params['variety_500'] && ( empty( $params['honoree_image'] ) || ( ! filter_var( $params['honoree_image'], FILTER_VALIDATE_URL ) ) ) ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid honoree image', array( 'status' => 400 ) );
		}

		$params['media_category'] = array();
		$media_categories         = ( ! empty( $profile['media_category'] ) ) ? explode( ',', $profile['media_category'] ) : array();
		if ( empty( $media_categories ) && 'Yes' === $params['variety_500'] ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid media category', array( 'status' => 400 ) );
		}

		$valid_media_categories = array(
			'Film',
			'Gaming',
			'Music',
			'Technology',
			'Live Entertainment',
			'TV',
		);

		if ( ! empty( $media_categories ) ) {
			foreach ( $media_categories as $media_category ) {
				if ( ! in_array( $media_category, $valid_media_categories, true ) ) {
					return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid media category', array( 'status' => 400 ) );
				}

				$params['media_category'][] = $media_category;
			}
		}

		$params['line_of_work'] = array();
		$line_of_works          = ( ! empty( $profile['line_of_work'] ) ) ? explode( ',', $profile['line_of_work'] ) : array();
		if ( empty( $line_of_works ) && 'Yes' === $params['variety_500'] ) {
			return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid line of work', array( 'status' => 400 ) );
		}

		$valid_line_of_work = array(
			'Artists',
			'Backers',
			'Dealmakers',
			'Execs',
			'Moguls',
			'Producers',
		);

		if ( ! empty( $line_of_works ) ) {
			foreach ( $line_of_works as $line_of_work ) {
				if ( ! in_array( $line_of_work, $valid_line_of_work, true ) ) {
					return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid line of work', array( 'status' => 404 ) );
				}

				$params['line_of_work'][] = $line_of_work;
			}
		}

		$companies = $this->validate_and_sanitize_companies_data( $profile['companies'] );
		if ( is_wp_error( $companies ) ) {
			return $companies;
		}

		$params['companies'] = $companies;

		$params['company_instagram_url'] = esc_url_raw( $profile['company_instagram_url'] );
		$params['social']                = $this->sanitize_array_data( $profile['social'] );
		$params['philanthropy']          = $this->sanitize_array_data( $profile['philanthropy'], true );
		$params['career_highlights']     = $this->sanitize_array_data( $profile['career_highlights'] );
		$params['exec_credits']          = $this->sanitize_array_data( $profile['exec_credits'] );
		$params['talent_credits']        = $this->sanitize_array_data( $profile['talent_credits'] );
		$params['talent']                = $this->sanitize_array_data( $profile['talent'] );

		$params['related_profiles'] = array();
		$related_profiles           = $raw_json_params['related_profiles'];
		if ( ( ! empty( $related_profiles ) ) && is_array( $related_profiles ) ) {
			foreach ( $related_profiles as $variety_id => $related_profile ) {
				$params['related_profiles'][ $variety_id ] = $this->sanitize_array_data( $related_profile );
			}
		}

		$params['variety_articles'] = $this->validate_and_sanitize_related_articles( $profile['variety_articles'] );

		return $params;

	}

	/**
	 * Validate and sanitize related profile data
	 *
	 * @param  array $raw_companies_data
	 *
	 * @return array|WP_Error $sanitized_related_profile_data
	 */
	public function validate_and_sanitize_companies_data( $raw_companies_data ) {
		$sanitized_companies_data = array();

		if ( empty( $raw_companies_data ) || ! is_array( $raw_companies_data ) ) {
			return $sanitized_companies_data;
		}

		foreach ( $raw_companies_data as $company_id => $raw_company_data ) {
			if ( empty( $raw_company_data['company_name'] ) ) {
				return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid company name for company id ' . $company_id, array( 'status' => 400 ) );
			}

			$company_name = sanitize_text_field( $raw_company_data['company_name'] );

			if ( empty( $raw_company_data['company_type'] ) ) {
				return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid company type for company id ' . $company_id, array( 'status' => 400 ) );
			}

			$company_type = sanitize_text_field( $raw_company_data['company_type'] );

			if ( empty( $raw_company_data['jobs'] ) ) {
				return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, 'Invalid jobs for company id ' . $company_id, array( 'status' => 400 ) );
			}

			$jobs = sanitize_text_field( $raw_company_data['jobs'] );

			$sanitized_companies_data[ $company_id ] = array(
				'company_name' => $company_name,
				'company_type' => $company_type,
				'jobs'         => $jobs,
			);
		}

		return $sanitized_companies_data;
	}

	/**
	 * Validate and sanitize related articles data
	 *
	 * @param  array $related_articles_data
	 *
	 * @return array $sanitized_related_articles_data
	 */
	public function validate_and_sanitize_related_articles( $related_articles_data ) {
		$sanitized_related_articles_data = array();

		if ( empty( $related_articles_data ) || ! is_array( $related_articles_data ) ) {
			return $sanitized_related_articles_data;
		}

		foreach ( $related_articles_data as $related_article ) {
			$sanitized_related_articles_data[] = array(
				'title'        => sanitize_text_field( $related_article['title'] ),
				'image'        => esc_url_raw( $related_article['image'] ),
				'url'          => esc_url_raw( $related_article['url'] ),
				'published_at' => sanitize_text_field( $related_article['published_at'] ),
				'contents'     => wp_kses_post( $related_article['contents'] ),
			);
		}

		return $sanitized_related_articles_data;
	}

	/**
	 * Sanitize keys and values of an array
	 *
	 * @version 2017-09-11 - Dhaval Parekh - CDWE-632
	 *
	 * @param  array $raw_array array for sanitize.
	 * @param  bool  $maintain_array_key True if you want to maintain key value.
	 *
	 * @return array $sanitized_array
	 */
	public function sanitize_array_data( $raw_array, $maintain_array_key = false ) {
		if ( empty( $raw_array ) || ( ! is_array( $raw_array ) ) ) {
			return array();
		}

		$sanitized_array = array();
		foreach ( $raw_array as $key => $value ) {

			if ( true === $maintain_array_key ) {
				$sanitized_key = sanitize_text_field( $key );
			} else {
				$sanitized_key = sanitize_key( $key );
			}

			if ( is_array( $value ) ) {
				$sanitized_array[ $sanitized_key ] = $this->sanitize_array_data( $value );
				continue;
			}

			if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
				$sanitized_array[ $sanitized_key ] = esc_url_raw( $value );
				continue;
			}

			$sanitized_array[ $sanitized_key ] = sanitize_text_field( $value );
		}

		return $sanitized_array;
	}

	/**
	 * Get a list of valid countries
	 *
	 * @return array $list_of_countries
	 */
	public function get_list_of_countries() {
		$list_of_countries = array(
			'Afghanistan',
			'Albania',
			'Algeria',
			'American Samoa',
			'Andorra',
			'Angola',
			'Anguilla',
			'Antarctica',
			'Antigua And Barbuda',
			'Argentina',
			'Armenia',
			'Aruba',
			'Australia',
			'Austria',
			'Azerbaijan',
			'Bahamas',
			'Bahrain',
			'Bangladesh',
			'Barbados',
			'Belarus',
			'Belgium',
			'Belize',
			'Benin',
			'Bermuda',
			'Bhutan',
			'Bolivia',
			'Bosnia And Herzegovina',
			'Botswana',
			'Bouvet Island',
			'Brazil',
			'British Indian Ocean Territory',
			'Brunei Darussalam',
			'Bulgaria',
			'Burkina Faso',
			'Burundi',
			'Cambodia',
			'Cameroon',
			'Canada',
			'Cape Verde',
			'Cayman Islands',
			'Central African Republic',
			'Chad',
			'Chile',
			'China',
			'Christmas Island',
			'Cocos (Keeling) Islands',
			'Colombia',
			'Comoros',
			'Congo, Republic of the',
			'Congo, Democratic Republic of the',
			'Cook Islands',
			'Costa Rica',
			'Ivory Coast',
			'Croatia',
			'Cuba',
			'Cyprus',
			'Czech Republic',
			'Denmark',
			'Djibouti',
			'Dominica',
			'Dominican Republic',
			'East Timor',
			'Ecuador',
			'Egypt',
			'El Salvador',
			'Equatorial Guinea',
			'Eritrea',
			'Estonia',
			'Ethiopia',
			'Falkland Islands (Malvinas)',
			'Faroe Islands',
			'Fiji',
			'Finland',
			'France',
			'French Guiana',
			'French Polynesia',
			'French Southern Territories',
			'Gabon',
			'Gambia',
			'Georgia (Eurasia)',
			'Germany',
			'Ghana',
			'Gibraltar',
			'Greece',
			'Greenland',
			'Grenada',
			'Guadeloupe',
			'Guam',
			'Guatemala',
			'Guinea',
			'Guinea-Bissau',
			'Guyana',
			'Haiti',
			'Heard Island And Mcdonald Islands',
			'Vatican City',
			'Honduras',
			'Hong Kong',
			'Hungary',
			'Iceland',
			'India',
			'Indonesia',
			'Iran',
			'Iraq',
			'Ireland',
			'Isle of Man',
			'Israel',
			'Italy',
			'Jamaica',
			'Japan',
			'Jordan',
			'Kazakstan',
			'Kenya',
			'Kiribati',
			'Korea (North)',
			'Korea (South)',
			'Kosovo',
			'Kuwait',
			'Kyrgyzstan',
			'Laos',
			'Latvia',
			'Lebanon',
			'Lesotho',
			'Liberia',
			'Libyan Arab Jamahiriya',
			'Liechtenstein',
			'Lithuania',
			'Luxembourg',
			'Macau',
			'Macedonia',
			'Madagascar',
			'Malawi',
			'Malaysia',
			'Maldives',
			'Mali',
			'Malta',
			'Marshall Islands',
			'Martinique',
			'Mauritania',
			'Mauritius',
			'Mayotte',
			'Mexico',
			'Micronesia, Federated States Of',
			'Moldova, Republic Of',
			'Monaco',
			'Mongolia',
			'Montenegro',
			'Montserrat',
			'Morocco',
			'Mozambique',
			'Myanmar',
			'Namibia',
			'Nauru',
			'Nepal',
			'Netherlands',
			'Netherlands Antilles',
			'New Caledonia',
			'New Zealand',
			'Nicaragua',
			'Niger',
			'Nigeria',
			'Niue',
			'Norfolk Island',
			'Northern Mariana Islands',
			'Norway',
			'Oman',
			'Pakistan',
			'Palau',
			'Palestinian Territory, Occupied',
			'Panama',
			'Papua New Guinea',
			'Paraguay',
			'Peru',
			'Philippines',
			'Pitcairn',
			'Poland',
			'Portugal',
			'Puerto Rico',
			'Qatar',
			'Reunion',
			'Romania',
			'Russia',
			'Rwanda',
			'Saint Helena',
			'Saint Kitts And Nevis',
			'Saint Lucia',
			'Saint Pierre And Miquelon',
			'Saint Vincent And The Grenadines',
			'Samoa',
			'San Marino',
			'Sao Tome And Principe',
			'Saudi Arabia',
			'Senegal',
			'Serbia',
			'Seychelles',
			'Sierra Leone',
			'Singapore',
			'Slovakia',
			'Slovenia',
			'Solomon Islands',
			'Somalia',
			'South Africa',
			'South Georgia & South Sandwich Is.',
			'Spain',
			'Sri Lanka',
			'Sudan',
			'Suriname',
			'Svalbard And Jan Mayen',
			'Swaziland',
			'Sweden',
			'Switzerland',
			'Syrian Arab Republic',
			'Taiwan, Province Of China',
			'Tajikistan',
			'Tanzania, United Republic Of',
			'Thailand',
			'Togo',
			'Tokelau',
			'Tonga',
			'Trinidad And Tobago',
			'Tunisia',
			'Turkey',
			'Turkmenistan',
			'Turks And Caicos Islands',
			'Tuvalu',
			'Uganda',
			'Ukraine',
			'United Arab Emirates',
			'United Kingdom',
			'United States',
			'United States Minor Outlying Islands',
			'Uruguay',
			'Uzbekistan',
			'Vanuatu',
			'Venezuela',
			'Vietnam',
			'Virgin Islands, British',
			'Virgin Islands, U.S.',
			'Wallis And Futuna',
			'Western Sahara',
			'Yemen',
			'Yugoslavia',
			'Zambia',
			'Zimbabwe',
		);

		return $list_of_countries;
	}

	/**
	 * Create profile
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error $response
	 */
	public function create_profile( $request ) {

		$params = $this->validate_and_sanitize_params( $request->get_json_params() );
		if ( is_wp_error( $params ) ) {
			$error_data       = $params->get_error_data();
			$response_code    = $error_data['status'];
			$response_message = $params->get_error_message();

			return $this->error( $response_code, $response_message, $params, $request );
		}

		$post_id = Variety_Hollywood_Executives_Profiles_API::query_post_id_from_variety_id( $params['variety_id'] );
		if ( $post_id ) {
			return $this->error( 404, 'Exec profiles already exists.', $params, $request );
		}

		$args = array(
			'post_type'    => self::VY_EXEC_PROFILE_POST_TYPE,
			'post_title'   => $params['title'],
			'post_status'  => 'publish',
			'post_date'    => current_time( 'mysql' ),
			'post_content' => $params['biography']
		);

		$post_id = wp_insert_post( $args );
		if ( is_wp_error( $post_id ) ) {
			return $this->error( 404, $post_id->get_error_message(), $params, $request );
		}

		wp_set_object_terms( $post_id, $params['vy500_year'], Variety_Hollywood_Executives_Profile::VY_500_YEAR_TAXANOMY );
		wp_set_object_terms( $post_id, Variety_Hollywood_Executives_Profiles_API::get_variety_id_taxonomy( $params['variety_id'] ), Variety_Hollywood_Executives_Profile::VY_500_VARIETY_ID_TAXANOMY );

		$this->update_profile_meta( $post_id, $params );

		$result = $this->vy500_ingest_featured_image( $post_id, $params['honoree_image'], $params['variety_500'] );
		if ( is_wp_error( $result ) ) {
			return $this->error( 404, $result->get_error_message(), $params, $request );
		}

		$response = $this->response( 200, $post_id, $params, $request );

		return $response;
	}

	/**
	 * Update profile
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error $response
	 */
	public function update_profile( $request ) {

		$params = $this->validate_and_sanitize_params( $request->get_json_params() );

		if ( is_wp_error( $params ) ) {
			$error_data       = $params->get_error_data();
			$response_code    = $error_data['status'];
			$response_message = $params->get_error_message();

			return $this->error( $response_code, $response_message, $params, $request );
		}

		if ( ! $this->is_vy500_profile_update_enabled( $params ) ) {

			// If Variety 500 Profile update is not enabled than bail out.
			return $this->error( 403, 'Variety 500 profile update request has been blocked', $params, $request );
		}

		$variety_id = (int) $request['id'];

		$post_id = Variety_Hollywood_Executives_Profiles_API::query_post_id_from_variety_id( $variety_id );
		if ( empty( $post_id ) ) {
			return $this->error( 404, 'There is no post associated with variety_id', $params, $request );
		}

		$args = array(
			'ID'           => $post_id,
			'post_type'    => self::VY_EXEC_PROFILE_POST_TYPE,
			'post_title'   => $params['title'],
			'post_date'    => current_time( 'mysql' ),
			'post_content' => $params['biography'],
		);

		$post_id = wp_update_post( $args );
		if ( is_wp_error( $post_id ) ) {
			return $this->error( 404, 'Something went wrong while updating exec profile', $params, $request );
		}

		wp_set_object_terms( $post_id, $params['vy500_year'], 'vy500_year' );

		$this->update_profile_meta( $post_id, $params );

		$result = $this->vy500_ingest_featured_image( $post_id, $params['honoree_image'], $params['variety_500'] );
		if ( is_wp_error( $result ) ) {
			return $this->error( 404, $result->get_error_message(), $params, $request );
		}

		// Delete old cache
		$cache_key = Variety_Hollywood_Executives_Profiles_API::get_exec_profile_cache_key( $variety_id );
		$cache     = new PMC_Cache( $cache_key );
		$cache->invalidate();

		$response = $this->response( 200, $post_id, $params, $request );

		return $response;
	}

	/**
	 * Delete profile
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return array $sanitized_array
	 */
	public function delete_profile( $request ) {

		$variety_id = (int) $request['id'];

		$post_id = Variety_Hollywood_Executives_Profiles_API::query_post_id_from_variety_id( $variety_id );
		if ( empty( $post_id ) ) {
			return $this->error( 404, 'There is no post associated with variety_id' );
		}

		$post = wp_delete_post( $post_id, array( 'force_delete' => true ) );
		if ( empty( $post ) ) {
			return $this->error( 404, 'Something went wrong while deleting exec profile' );
		}

		// Delete term
		$term = get_term_by( 'slug', Variety_Hollywood_Executives_Profiles_API::get_variety_id_taxonomy( $variety_id ), Variety_Hollywood_Executives_Profile::VY_500_YEAR_TAXANOMY );
		wp_delete_term( $term->term_id, Variety_Hollywood_Executives_Profile::VY_500_YEAR_TAXANOMY );

		// Delete cache
		$cache_key = Variety_Hollywood_Executives_Profiles_API::get_exec_profile_cache_key( $variety_id );
		$cache     = new PMC_Cache( $cache_key );
		$cache->invalidate();

		$response = $this->response( 200, $post_id, array(), $request );

		return $response;
	}

	/**
	 * Get profile data
	 *
	 * @param  int $variety_id
	 *
	 * @return array| $profile
	 */
	public function get_profile_data( $variety_id ) {
		$variety_exec_profiles_api = new Variety_Hollywood_Executives_Profiles_API( $variety_id, true );
		$profile                   = $variety_exec_profiles_api->get();

		return $profile;
	}

	/**
	 * Get profile
	 *
	 * @param  WP_REST_Request $request
	 *
	 * @return WP_REST_Response $response
	 */
	public function get_profile( $request ) {
		$variety_id = (int) $request['id'];

		// Keep cache data for 30 minutes
		$cache_key = Variety_Hollywood_Executives_Profiles_API::get_exec_profile_cache_key( $variety_id );
		$cache     = new PMC_Cache( $cache_key );
		$profile   = $cache->expires_in( 30 * MINUTE_IN_SECONDS )->updates_with( array( $this, 'get_profile_data' ), array( $variety_id ) )->get();

		if ( empty( $profile ) ) {
			return $this->error( 404, 'Coud not retrieve specified profile' );
		}

		$response = $this->response( 200, $profile, array(), $request );

		return $response;
	}

	/**
	 * Update profile meta
	 *
	 * @param  int $post_id
	 * @param  array $params
	 *
	 */
	public function update_profile_meta( $post_id, $params ) {

		// Variety Insights meta data
		update_post_meta( $post_id, 'variety_id', $params['variety_id'] );
		update_post_meta( $post_id, 'variety_500', $params['variety_500'] );
		update_post_meta( $post_id, 'vy500_year', $params['vy500_year'] );

		// log modified timestamps
		update_post_meta( $post_id, self::UPDATED_TIMESTAMP_META_KEY, current_time( 'timestamp' ) );
		update_post_meta( $post_id, self::UPDATED_TIMESTAMP_META_KEY . '_vi', strftime( $params['modified'] ) );

		update_post_meta( $post_id, 'firstname', $params['first_name'] );
		update_post_meta( $post_id, 'middlename', $params['middle_name'] );
		update_post_meta( $post_id, 'lastname', $params['last_name'] );
		update_post_meta( $post_id, 'nicknames', $params['nicknames'] );
		update_post_meta( $post_id, 'aka', $params['aka'] );

		update_post_meta( $post_id, 'gender', $params['gender'] );
		update_post_meta( $post_id, 'ethnicity', $params['ethnicity'] );
		update_post_meta( $post_id, 'country_of_residence', $params['country_of_residence'] );
		update_post_meta( $post_id, 'country_of_origin', $params['country_of_origin'] );
		update_post_meta( $post_id, 'country_of_citizenship', $params['country_of_citizenship'] );

		update_post_meta( $post_id, 'education1', $params['education1'] );
		update_post_meta( $post_id, 'education2', $params['education2'] );
		update_post_meta( $post_id, 'education3', $params['education3'] );

		update_post_meta( $post_id, 'job_function', $params['job_function'] );
		update_post_meta( $post_id, 'media_category', $params['media_category'] );
		update_post_meta( $post_id, 'honors', $params['honors'] );
		update_post_meta( $post_id, 'vy500_year', $params['vy500_year'] );
		update_post_meta( $post_id, 'international', $params['international'] );
		update_post_meta( $post_id, 'brief_synopsis', $params['brief_synopsis'] );
		update_post_meta( $post_id, 'biography', $params['biography'] );
		update_post_meta( $post_id, 'company_instagram_url', $params['company_instagram_url'] );
		update_post_meta( $post_id, 'philanthropy', $params['philanthropy'] );
		update_post_meta( $post_id, 'survey_advice', $params['survey_advice'] );
		update_post_meta( $post_id, 'survey_inspiration', $params['survey_inspiration'] );
		update_post_meta( $post_id, 'line_of_work', $params['line_of_work'] );
		update_post_meta( $post_id, 'honoree_image', $params['honoree_image'] );
		update_post_meta( $post_id, 'job_title', $params['job_title'] );

		update_post_meta( $post_id, 'companies', $params['companies'] );
		update_post_meta( $post_id, 'career_highlights', $params['career_highlights'] );
		update_post_meta( $post_id, 'exec_credits', $params['exec_credits'] );
		update_post_meta( $post_id, 'talent_credits', $params['talent_credits'] );
		update_post_meta( $post_id, 'social', $params['social'] );
		update_post_meta( $post_id, 'talent', $params['talent'] );
		update_post_meta( $post_id, 'related_profiles', $params['related_profiles'] );
		update_post_meta( $post_id, 'variety_articles', $params['variety_articles'] );

		update_post_meta( $post_id, 'photo_url', $params['photo_url'] );
		update_post_meta( $post_id, 'photo_metadata', $params['photo_metadata'] );

		update_post_meta( $post_id, '_variety_insight_api_log', $params );
	}

	/**
	 * Send an error response on failure. Log relevant data.
	 *
	 * @param  int $response_code
	 * @param  string $response_message
	 * @param  array $params
	 * @param  array $request
	 *
	 * @return WP_Error
	 */
	public function error( $response_code = 404, $response_message = '', $params = array(), $request = array() ) {
		Variety_Hollywood_Executives_REST_API_Logger::get_instance()->log_push_data( $response_code, $response_message, $params, $request );

		return new WP_Error( self::VY_EXEC_PROFILES_REST_API_ERROR_CODE, $response_message, array( 'status' => $response_code ) );
	}

	/**
	 * Send a WP_REST_Response response on success. Log relevant data
	 *
	 * @param  int $response_code
	 * @param  string $response_message
	 * @param  array $params
	 * @param  array $request
	 *
	 * @return WP_REST_Response
	 */
	public function response( $response_code = 200, $response_message = '', $params = array(), $request = array() ) {
		Variety_Hollywood_Executives_REST_API_Logger::get_instance()->log_push_data( $response_code, $response_message, $params, $request );

		return new WP_REST_Response( $response_message, 200 );
	}

	/**
	 * Check if request is of variety_500 and the cheezcap option is enabled to update profile.
	 *
	 * @param array $params rest request filtered params.
	 *
	 * @return boolean
	 */
	public function is_vy500_profile_update_enabled( array $params ) : bool {

		if ( 'yes' !== $params['variety_500'] ) {
			return true;
		}

		$update = PMC_Cheezcap::get_instance()->get_option( self::VY500_EXEC_PROFILE_UPDATE_CHEEZCAP_OPTION );

		if ( 'enabled' === $update ) {
			return true;
		}

		return false;
	}

	/**
	 * Allow the API user to bypass 2FA when using an application password.
	 *
	 * See https://github.com/Automattic/vip-go-mu-plugins/blob/master/two-factor.php#L15
	 * See https://wordpressvip.zendesk.com/hc/en-us/requests/117086
	 * See https://confluence.pmcdev.io/x/hoVJAg
	 * See https://confluence.pmcdev.io/x/4IVJAg
	 *
	 * @param bool $enabled API access enabled.
	 * @param int $user_id  WP user ID.
	 *
	 * @return bool
	 */
	public function filter_two_factor_user_api_login_enable( bool $enabled, int $user_id ) {
		$user = get_userdata( $user_id );

		if ( $user && static::PMC_VI_API_USER === $user->user_login ) {
			return true;
		}

		return $enabled;
	}
}

Variety_Hollywood_Executives_REST_API::get_instance();

//EOF
