<?php
/**
 * Custom rewrite modifications
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class Rewrites {

	use Singleton;

	/**
	 * Rewrites constructor.
	 *
	 * Initializes rewrite rules for the site.
	 */
	protected function __construct() {

		// Ignoring beacuse trying to defining constant in test will lead to constructor returning null everytime as constant cannot be redefined.
		// @codeCoverageIgnoreStart
		if ( defined( 'PMC_CORE_PERMALINK_DISABLE' ) && true === PMC_CORE_PERMALINK_DISABLE ) {
			return;
		}
		// @codeCoverageIgnoreEnd

		// Generate permalinks
		add_action(
			'init',
			[
				$this,
				'init',
			]
		);

		// Using a very low priority to work around the pmc-listicle slideshow plugin.
		add_filter( 'pre_post_link', [ $this, 'post_type_link' ], 10, 3 );
		add_filter( 'post_type_link', [ $this, 'post_type_link' ], 10, 3 );
		add_filter( 'pmc_gallery_standalone_slug', [ $this, 'gallery_slug' ] );

		// Set post slugs
		add_filter( 'wp_unique_post_slug', [ $this, 'append_id_to_post_slug' ], 10, 4 );
		add_action( 'template_redirect', [ $this, 'redirect_canonical' ] );

	}

	/**
	 * Default Permalink for Core Sites
	 */
	public function init() {

		if ( function_exists( 'wpcom_vip_load_permastruct' ) ) {
			// We need to load our custom permalink. If any change happens here change that in tests/unit/bootstrap.php as well
			wpcom_vip_load_permastruct( '/%category%/%postname%-%post_id%/' );
		}

		if ( function_exists( 'wpcom_vip_load_category_base' ) ) {
			wpcom_vip_load_category_base( 'c' );
		}

		if ( function_exists( 'wpcom_vip_load_tag_base' ) ) {
			wpcom_vip_load_tag_base( 't' );
		}

	}

	/**
	 * Provide permalinks for posts (of all post types).
	 *
	 * @param string   $link Current permalink.
	 * @param \WP_Post $post Post object.
	 *
	 * @return string Permalink for the given post.
	 */
	public function post_type_link( $link, $post, $leavename = false, $canonical = false ) {

		$link = ltrim( $link );

		if ( ! in_array( $post->post_type, apply_filters( 'pmc_core_force_subcategory_in_url_on_post_types', [ 'post', 'pmc-gallery' ] ), true ) ) {
			return $link;
		}

		$terms = get_the_terms( $post, 'category' );

		if ( is_array( $terms ) ) {

			// Force subcategory to appear in URL, even if top-level category is set.
			foreach ( $terms as $term ) {
				if ( ! empty( $term->parent ) ) {
					$parent = get_term( $term->parent, 'category' );
					if ( ! empty( $parent->slug ) && ! empty( $term->slug ) ) {
						return str_replace( '%category%', $parent->slug . '/' . $term->slug, $link );
					}
				}
			}

		}

		$default_rewrite_category = apply_filters( 'pmc_core_permalink_tag_default', 'uncategorized/news' );

		return str_replace( '%category%', $default_rewrite_category, $link );

	}

	/**
	 * Rewrite default gallery slug.
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	public function gallery_slug( $slug ) {

		return 'gallery/%category%';
	}

	/**
	 * Ensure that slugs always have IDs associated with them.
	 *
	 * @param string $slug    Current post_name.
	 * @param int    $post_id Post ID.
	 *
	 * @return string Modified post_name.
	 */
	public function append_id_to_post_slug( $slug, $post_id, $post_status, $post_type ) {

		if ( ! in_array( $post_type, apply_filters( 'pmc_core_append_id_to_post_slug_on_post_types', [ 'pmc-gallery' ] ), true ) ) {
			return $slug;
		}

		if ( ! $post_id ) {
			return $slug;
		}

		if ( ! preg_match( '/-(\d+)$/', $slug, $matches ) ) {
			return "{$slug}-{$post_id}";
		}

		$slug_id = intval( $matches[1] );

		if ( $slug_id === $post_id ) {
			return $slug;
		}

		return preg_replace( '/-\d+$/', '-' . $post_id, $slug );
	}

	public function redirect_canonical() {

		if ( is_category() ) {
			$uri_unslashed   = untrailingslashit( $_SERVER['REQUEST_URI'] ); // phpcs:ignore
			$parsed_uri_path = wp_parse_url( $uri_unslashed, PHP_URL_PATH ); // phpcs:ignore

			$url_path_parts = explode( '/category/', $parsed_uri_path );

			if ( ! empty( $url_path_parts[0] ) && 1 < strlen( $url_path_parts[0] ) ) {

				if ( ! empty( $url_path_parts[1] ) ) {
					$url_path = sanitize_text_field( $url_path_parts[1] );
					$url      = '/category/' . trailingslashit( $url_path );

					wp_safe_redirect( $url, 301 );
					exit();
				}
			}
		}
	}
}
