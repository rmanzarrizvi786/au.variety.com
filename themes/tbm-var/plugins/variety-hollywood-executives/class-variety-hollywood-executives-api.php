<?php

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Hollywood_Executives_API {

	use Singleton;

	const POST_TYPE                  = 'hollywood_exec';
	const TAXONOMY_COMPANY           = 'hollywood_exec_company';
	const UPDATED_TIMESTAMP_META_KEY = '_updated_timestamp';
	const PHOTO_URL_HOST             = 'https://www.varietyinsight.com';
	const CACHE_GROUP                = 'exec_profiles_api';
	const CACHE_EXPIRE               = 300;

	protected $_fields = array();
	protected $_meta_log = array();

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		// These are not doing meta query lookup & are used for fetching/deleting meta for posts.
		$this->_fields = array(
			'first_name'   => array( 'meta_key' => 'firstname' ),
			'last_name'    => array( 'meta_key' => 'lastname' ),
			'nicknames'    => array(),
			'company_name' => array( 'meta_key' => 'companyname' ),
			'companies' => array(
					'meta_key'  => 'companies',
					'sanitizer' => array( $this, 'sanitize_companies' ),
				),
			'quotes' => array(
					'sanitizer' => array( $this, 'sanitize_quotes' ),
				),
			'credits' => array(
					'sanitizer' => array( $this, 'sanitize_credits' ),
					'fields'    => array( 'title', 'production_status', 'job_title', 'networks', 'domestic_box_office', 'season_year', 'release_date' ),
				),
			'photo_url' => array(),
			'social'   => array(
				'sanitizer' => array( $this, 'sanitize_social' ),
			),
			'talent' => array(
					'sanitizer' => array( $this, 'sanitize_talent' ),
				),
		);

	}

	public function sanitize_social( $social ) {
		if ( is_array( $social ) ) {
			array_walk( $social, 'sanitize_text_field');
		} else {
			$social = array();
		}
		return $social;
	}

	public function sanitize_talent( $talent  ) {
		if ( is_array( $talent ) ) {
			if ( !empty( $talent['credits'] ) ) {
				array_walk( $talent['credits'], array( $this, 'sanitize_credits' ) );
			}
			if ( !empty( $talent['social'] ) ) {
				array_walk( $talent['social'], array( $this, 'sanitize_social' ) );
			}
			if ( !empty( $talent['photo_url'] ) ) {
				$talent['photo_url'] = sanitize_text_field ( $talent['photo_url'] );
			}
		} else {
			$talent = array();
		}

		return $talent;

	}

	public function sanitize_credit_array( array $arrs ) {

		if ( empty( $arrs ) ) {
			return array();
		}
		$result = array();
		foreach ( $arrs as $_idx => $value ) {
			$data = array();
			$fields = $this->_fields['credits']['fields'];

			foreach ( $fields as $key ) {
				if ( !empty( $value[$key] ) ) {
					$data[$key] = sanitize_text_field( $value[$key] );
				}
			}

			if ( !empty( $data['release_date'] ) ) {
				if ( is_int( $data['release_date'] ) ) {
					$data['release_date'] = date('Y-m-d', $data['release_date'] );
				} else if ( '0000-00-00' === $data['release_date'] ) {
					$data['release_date'] = '';
				}
			}

			$data['companies'] = !empty( $value['companies'] ) ? $this->sanitize_companies( $value['companies' ] ) : array();
			$data['distributors'] = !empty( $value['distributors'] ) ? $this->sanitize_distributors( $value['distributors' ] ) : array();

			$result[$_idx] = $data;
		}

		return $result;
	}


	public function sanitize_credits( $credits ) {

		if ( is_array( $credits ) ) {
			array_walk( $credits, array( $this, 'sanitize_credit_array' ) );
		} else {
			$credits = array();
		}
		return $credits;
	}

	public function sanitize_distributors ( $distributors ) {
		if ( empty( $distributors ) ) {
			return array();
		}
		if ( is_array ( $distributors ) ) {
			array_walk( $distributors, function ( &$distributor ){
				if ( is_array( $distributor ) ) {
					array_walk( $distributor, function ( &$value ) {
						$value = sanitize_text_field( $value );
					});
				} else {
					$distributor = array();
				}
			});
		}
		return $distributors;
	}

	public function sanitize_companies ( $companies ) {
		if ( empty( $companies ) ) {
			return array();
		}
		if ( is_string ( $companies ) ) {
			$list = preg_split( '/\n/', $companies, 0, PREG_SPLIT_NO_EMPTY );
			$companies = array();
			foreach ( $list as $item ) {
				$tokens = explode( ':', $item );
				$companies[] = array(
						'company_name' => sanitize_text_field( trim( $tokens[0] ) ),
						'jobs' => isset( $tokens[1] ) ? sanitize_text_field( trim( $tokens[1] ) ) : '',
					);
			}
		}
		else {
			array_walk( $companies, function ( &$company, $id ){
				if ( is_string ( $company ) ) {
					$tokens = explode( ':', $company );
					$company = array(
						'company_name' => sanitize_text_field( trim( $tokens[0] ) ),
						'jobs' => isset( $tokens[1] ) ? sanitize_text_field( trim( $tokens[1] ) ) : '',
					);
				} else {
					$company['company_id'] = $id;
					array_walk( $company, function ( &$value ) {
						$value = sanitize_text_field( $value );
					});
				}
			});
		}
		return $companies;
	}

	protected function sanitize_quotes( $list ) {

		if ( !empty( $list ) ) {

			if ( is_string( $list ) ) {
				$list = preg_split( '/\n/', $list, 0, PREG_SPLIT_NO_EMPTY );
			}

			if ( is_array( $list ) ) {
				array_walk( $list, function ( &$value ){
					$value = sanitize_text_field($value);
				});
				return $list;
			}
		}
		return array();
	}

	public static function full_name ( array $profile ) {
		$first = isset( $profile['first_name'] ) ? trim ( $profile['first_name'] ) : '';
		$last = isset( $profile['last_name'] ) ? trim ( $profile['last_name'] ) : '';
		$full_name = trim( $first );
		if ( !empty( $last ) ) {
			$full_name .= !empty( $full_name ) ? " " : "";
			$full_name .= trim( $last );
		}
		return $full_name;
	}

	public function get_related_profiles( $post_id, $offset, $limit ) {
		$post = get_post( $post_id );

		if ( empty( $post ) || self::POST_TYPE !== $post->post_type ) {
			return false;
		}

		$companies = get_post_meta( $post_id, 'companies', true );

		if ( empty( $companies ) ) {
			return false;
		}

		$company = reset( $companies );

		if ( empty( $company['company_id'] ) ) {
			return false;
		}
		$company_id = $company['company_id'];

		// TODO: need primary company association for query

		// for now, temporarily return arbitually list.
		// Usage of meta_query required to get posts with same companies.
		$wpquery = new WP_Query( array (
				'fields'         => 'ids',
				'post_type'      => self::POST_TYPE,
				'cache_results'  => true,
				'offset'         => $offset,
				'posts_per_page' => $limit,
				'meta_query'     => array( array( 'key' => 'company_id', 'value' => $company_id, 'compare' => '=', 'type' => 'NUMERIC' ) ),
				'post__not_in' => array( $post_id ),
				'orderby'      => 'modified',
			) );

		if ( 0 === $wpquery->post_count ) {
			return false;
		}

		$profiles = array();
		$keys = array( 'first_name', 'last_name', 'companies' );

		foreach ( $wpquery->posts as $post_id ) {
			$profiles[$post_id] = $this->get_profile( $post_id, $keys );
		}

		return $profiles;
	}

	public function get_profile( $post_id, array $keys ) {

		$talent = array();
		$profile = array();

		foreach ( $keys as $key ) {
			$meta_key = isset( $this->_fields[$key]['meta_key'] ) ? $this->_fields[$key]['meta_key'] : $key;
			$profile[$key] = get_post_meta( $post_id, $meta_key, true );
		}

		$profile['full_name'] = $this->full_name( $profile );
		$profile['link'] = get_permalink( $post_id );

		$thumb_id = get_post_thumbnail_id( $post_id );

		if ( !empty( $thumb_id ) ) {
			$profile['thumbnail'] = PMC::get_attachment_attributes( $thumb_id, array(60,86), $post_id );
		} else {

			$photo_url = get_post_meta( $post_id, 'photo_url', true );

			if ( empty( $photo_url ) ) {
				if ( empty( $talent ) ) {
					$talent = get_post_meta( $post_id, 'talent', true );
				}

				if ( !empty( $talent['photo_url'] ) ) {
					$photo_url = $talent['photo_url'];
				}

			}

			if ( !empty( $photo_url ) ) {
				$profile['thumbnail'] = array(
					'src'    => self::PHOTO_URL_HOST . $photo_url,
					'alt'    => $profile['full_name'],
				);
			} else {
				$profile['thumbnail'] = array(
					'width'  => 60,
					'height' => 86,
					'src'    => sprintf( '%s/assets/build/images/global/thumb-profile-60x86.png', untrailingslashit( get_stylesheet_directory_uri() ) ),
					'class'  => "attachment-60x86",
					'alt'    => $profile['full_name'],
				);
			}
		}
		return $profile;
	}

	public function get_credits( $post_id, $show_talent = false ) {
		$talent_credits = get_post_meta( $post_id, 'talent_credits', true );
		$talent         = $show_talent ? get_post_meta( $post_id, 'talent', true ) : false;
		$exec_credits   = get_post_meta( $post_id, 'exec_credits', true );

		if ( ! empty( $talent['talent_credits'] ) && is_array( $talent['talent_credits'] ) ) {

			$talent_credits = ( is_array( $talent_credits ) ) ? $talent_credits : [];
			$talent_credits = $this->_merge_data_recursively( (array) $talent_credits, (array) $talent['talent_credits'] );

		}

		$credits      = [];
		$exec_credits = ( is_array( $exec_credits ) ) ? $exec_credits : [];

		if ( ! empty( $talent_credits ) ) {
			if ( ! empty( $talent_credits['film'] ) ) {
				$credits['film'] = $talent_credits['film'];
			}

			if ( ! empty( $talent_credits['tv'] ) ) {
				$credits['tv'] = $talent_credits['tv'];
			}
		}

		if ( false !== $talent ) {
			if ( ! empty( $talent['exec_credits'] ) ) {
				$exec_credits = $this->_merge_data_recursively( (array) $exec_credits, (array) $talent['exec_credits'] );
			}

			if ( ! empty( $exec_credits['film'] ) ) {
				if ( ! empty( $credits['film'] ) ) {
					$credits['film'] = array_merge( $credits['film'], $exec_credits['film'] );
				} else {
					$credits['film'] = $exec_credits['film'];
				}
			}

			if ( ! empty( $exec_credits['tv'] ) ) {
				if ( ! empty( $credits['tv'] ) ) {
					$credits['tv'] = array_merge( $credits['tv'], $exec_credits['tv'] );
				} else {
					$credits['tv'] = $exec_credits['tv'];
				}
			}
		}

		$credits = $this->_separate_digital_credits( $credits );

		return array(
			'credits'        => $credits,
			'exec_credits'   => $exec_credits,
			'talent_credits' => $talent_credits,
		);
	}

	/**
	 * Method to merge two dimensional associative array keeping all values and keys without overwriting any dimension.
	 * This is not same as array_merge_recursive().
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	protected function _merge_data_recursively( array $array1, array $array2 ) : array {

		foreach ( $array2 as $key => $value ) {

			$array1[ $key ] = ( isset( $array1[ $key ] ) ) ? ( (array) $array1[ $key ] + (array) $value ) : $value;

		}

		return $array1;

	}

	/**
	 * Separate out digital credits into their own node for frontend display
	 *
	 * @param array $credits
	 *
	 * @return array
	 */
	protected function _separate_digital_credits( array $credits ) : array {

		$digital = [];

		if ( ! empty( $credits['tv'] ) ) {

			foreach ( $credits['tv'] as $key => $credit ) {

				if ( ! empty( $credit['media_type'] ) && 'digital' === strtolower( $credit['media_type'] ) ) {
					$digital[ $key ] = $credit;
					unset( $credits['tv'][ $key ] );
				}

			}

		}

		$credits['digital'] = $digital;

		return $credits;

	}

	public function get_photo_url ( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}

		$cache_key = md5( 'photo_url' . $post_id );
		$photo_url = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( false !== $photo_url ) {
			return $photo_url;
		}

		$photo_url = get_post_meta( $post_id, 'photo_url', true );
		if ( empty( $photo_url ) ) {
			$talent = get_post_meta( $post_id, 'talent', true );
			if ( !empty( $talent['photo_url'] ) ) {
				$photo_url = $talent['photo_url'];
			}
		}

		if ( !empty( $photo_url ) ) {
			$photo_url = self::PHOTO_URL_HOST . $photo_url;
		} else {
			$photo_url = '';
		}

		wp_cache_set( $cache_key, $photo_url, self::CACHE_GROUP, self::CACHE_EXPIRE );

		return $photo_url;
	}

	public function get_social( $post_id ) {

		if ( empty( $post_id ) ) {
			return false;
		}

		$cache_key = md5( 'social' . $post_id );
		$social = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( false !== $social ) {
			return $social;
		}

		$social = get_post_meta( $post_id, 'social', true );
		$talent = get_post_meta( $post_id, 'talent', true );

		if ( !empty( $talent['social'] ) ) {
			foreach ($talent['social'] as $key => $value ) {
				if ( empty( $social[$key] ) ) {
					$social[$key] = $value;
				}
			}
		}

		wp_cache_set( $cache_key, $social, self::CACHE_GROUP, self::CACHE_EXPIRE );

		return $social;
	}

}

Variety_Hollywood_Executives_API::get_instance();

// EOF
