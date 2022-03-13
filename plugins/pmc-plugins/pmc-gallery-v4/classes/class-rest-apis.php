<?php
/**
 * To add custom REST APIs endpoint.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @package pmc-gallery-v4
 */

namespace PMC\Gallery;

use PMC\Global_Functions\Traits\Singleton;

class Rest_APIs {

	use Singleton;

	/**
	 * Rest namespace.
	 *
	 * @var string
	 */
	public $rest_namespace = 'pmc-gallery/v4';

	/**
	 * Rest_APIs constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * To register All REST API endpoint.
	 *
	 * @return void
	 */
	public function register_endpoints() {
		register_rest_route(
			$this->rest_namespace,
			'/get-related-gallery-list',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_related_gallery_list' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'_wpnonce' => [
						/**
						 * WordPress will verify the nonce cookie, we just want to ensure nonce was passed as param.
						 *
						 * @see https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
						 */
						'required' => true,
					],
					'post_id'  => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return ( 0 < intval( $param ) && is_numeric( $param ) );
						},
					],
					'paged'    => [
						'required'          => false,
						'default'           => 1,
						'validate_callback' => function ( $param ) {
							return ( 0 < intval( $param ) && is_numeric( $param ) );
						},
					],
				],
			]
		);
	}

	/**
	 * Callback of REST API endpoint.
	 * To Get Related gallery by gallery ID, base on tags, category.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function get_related_gallery_list( \WP_REST_Request $request ) {

		$params = wp_parse_args( $request->get_params(), $request->get_default_params() );

		$paged      = ( ! empty( $params['paged'] ) && 0 < intval( $params['paged'] ) ) ? intval( $params['paged'] ) : 1;
		$gallery_id = ( ! empty( $params['post_id'] ) && 0 < intval( $params['post_id'] ) ) ? intval( $params['post_id'] ) : false;

		// Set Expiry time.
		$expires_time = ( 30 * MINUTE_IN_SECONDS );

		$type = '';

		$taxonomies_to_check = [ 'post_tag', 'sub-category', 'category' ];

		foreach ( $taxonomies_to_check as $taxonomy ) {

			$args = $this->_get_args_for_related_gallery( $gallery_id, $taxonomy );

			$args['paged'] = $paged;

			// If there is no tax_query then, move to next taxonomy.
			if ( empty( $args['tax_query'][0]['taxonomy'] ) ) {
				continue;
			}

			$cache_key = self::_get_cache_key( $args );
			$pmc_cache = new \PMC_Cache( $cache_key );
			$response  = $pmc_cache->expires_in( $expires_time )
				->updates_with( [ $this, 'get_uncached_result' ], [ $args, $gallery_id ] )
				->get();

			// If we have enough post then break the loop.
			if ( ! empty( $response ) && is_array( $response ) ) {
				$type = $taxonomy;
				break;
			}

		}

		if ( empty( $response ) || ! is_array( $response ) ) {
			return new \WP_REST_Response(
				[
					'success' => false,
					'code'    => 'no_gallery_found',
					'message' => esc_html__( 'No Gallery Found.', 'pmc-gallery-v4' ),
				]
			);
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'type'    => $type,
				'data'    => $response,
			]
		);
	}

	/**
	 * Get post from argument.
	 *
	 * @param array $args args of WP_Query
	 * @param int $current_post_id Current post ID.
	 *
	 * @return array list of post ids.
	 */
	public function get_uncached_result( $args, $current_post_id = 0 ) {

		if ( empty( $args ) || ! is_array( $args ) ) {
			return [];
		}

		// Just to make sure, we only get the IDs.
		$args['fields'] = 'ids';

		$response = [];

		$query    = new \WP_Query( $args );
		$post_ids = $query->get_posts();

		if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
			return [];
		}

		foreach ( $post_ids as $post_id ) {
			$permalink = get_permalink( $post_id );

			// Skip current gallery. And posts that is listed in 410 URL list.
			if ( $post_id !== $current_post_id && false === self::_is_410_url( $permalink ) ) {
				$response[] = [
					'ID'    => absint( $post_id ),
					'link'  => $permalink,
					'title' => get_the_title( $post_id ),
				];
			}
		}

		return $response;
	}

	/**
	 * Generate cache key.
	 *
	 * @param string|array $unique base on that cache key will generate.
	 *
	 * @return string Cache key.
	 */
	protected static function _get_cache_key( $unique = '' ) {

		if ( is_array( $unique ) ) {
			ksort( $unique );
			$unique = wp_json_encode( $unique );
		}

		$md5 = md5( $unique );
		$key = sprintf( 'pmc-gallery-fetch-gallery-%s', $md5 );

		return $key;
	}


	/**
	 * To get args for WP_Query to query list of related gallery for provided gallery.
	 *
	 * @param int    $gallery_id Gallery ID
	 * @param string $taxonomy   Taxonomy for which we need to query.
	 *
	 * @return array Arguments.
	 */
	protected function _get_args_for_related_gallery( $gallery_id, $taxonomy = 'post_tag' ) {

		$args = [
			'post_type'      => Defaults::NAME,
			'fields'         => 'ids',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		];

		if ( empty( $gallery_id ) || 0 > intval( $gallery_id ) || empty( $taxonomy ) ) {
			return $args;
		}

		$is_sub_category = ( 0 === strpos( $taxonomy, 'sub-' ) ) ? true : false;

		if ( $is_sub_category ) {
			$taxonomy = str_replace( 'sub-', '', $taxonomy );
		}

		// Find post_tag for current gallery.
		$post_terms = get_the_terms( $gallery_id, $taxonomy );

		if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) && is_array( $post_terms ) ) {

			$post_term_ids = [];
			foreach ( $post_terms as $term ) {
				if ( ! empty( $term->count ) ) {

					// If we only need to apply child term then skip all parent terms.
					if ( $is_sub_category && 0 === $term->parent ) {
						continue;
					}

					$post_term_ids[] = absint( $term->term_id );
				}
			}

			if ( ! empty( $post_term_ids ) ) {

				// Result will be store in cache.
				$args['tax_query'] = [ // phpcs:ignore
					'relation' => 'OR',
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $post_term_ids,
					],
				];

				return $args;
			}

		}

		return $args;

	}

	/**
	 * To check if current URL have 410 status or not.
	 *
	 * @param string $permalink Post's permalink.
	 *
	 * @return bool Return True if permalink is in 410 status list, Otherwise return False
	 */
	protected static function _is_410_url( $permalink ) {

		if ( empty( $permalink ) ) {
			return false;
		}

		static $removed_paths = false;

		// We don't want to apply_filters every time we call this function.
		// once we fetch will store it in static variable.
		if ( false === $removed_paths ) {
			$removed_paths = apply_filters( 'pmc_http_status_410_urls', [] ); // @codeCoverageIgnore
		}

		/**
		 * First check with all URL that passed in 'pmc_http_status_410_urls' filter.
		 * If we found record of that URL in removed path then return true.
		 */
		if ( ! empty( $removed_paths ) && is_array( $removed_paths ) ) {

			$trim_uri = trim( wp_parse_url( $permalink, PHP_URL_PATH ), '/' );

			if ( isset( $removed_paths[ $trim_uri ] ) ) {

				// 410 the requested page.
				return true;

			}

		}

		/**
		 * Check if there is record in Legacy redirector plugin.
		 */
		if ( method_exists( '\WPCOM_Legacy_Redirector', 'get_redirect_uri' ) ) {

			$permalink = apply_filters( 'wpcom_legacy_redirector_request_path', $permalink );

			$redirect_uri = \WPCOM_Legacy_Redirector::get_redirect_uri( $permalink );

			if ( '/pmc-410' === $redirect_uri ) {

				// 410 the requested page.
				return true;

			}

		}

		return false;

	}

}
