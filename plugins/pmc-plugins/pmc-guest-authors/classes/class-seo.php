<?php
namespace PMC\Guest_Authors;

use \PMC\Global_Functions\Traits\Singleton;

class SEO {

	use Singleton;

	protected $_disallow_index_by_default = null; // no default, theme should decide true or false.

	/**
	 * SEO constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		add_action( 'init', [ $this, 'maybe_enable_seo_option' ] );

	}

	/**
	 * Maybe enable the SEO option is filter returns true or false.
	 */
	public function maybe_enable_seo_option() {

		$this->_disallow_index_by_default = apply_filters( 'pmc_guest_authors_disallow_seo_indexing', $this->_disallow_index_by_default );

		/**
		 * `pmc_guest_authors_disallow_seo_indexing` must return
		 * a boolean of true or false to enable this feature.
		 * Plugin should not assume.
		 */
		if ( is_bool( $this->_disallow_index_by_default ) ) {
			$this->_setup_hooks();
		}

	}

	/**
	 * Setup Hooks.
	 */
	protected function _setup_hooks() : void {

		add_filter( 'pmc_seo_tweaks_robots_override', [ $this, 'seo_tweaks_robots_override' ] );
		add_filter( 'pmc_seo_tweaks_robot_names', [ $this, 'seo_tweaks_robot_names' ] );
		add_filter( 'pmc_canonical_url', [ $this, 'canonical_url' ] );
		add_filter( 'post_type_link', [ $this, 'post_type_link' ], 10, 2 );
		add_filter( 'pmc_sitemaps_post_type_whitelist', [ $this, 'sitemaps_post_type_whitelist' ] );
		add_filter( 'pmc_sitemaps_guest-author_include_date_range', '__return_false' );
		add_filter( 'pmc_sitemaps_guest-author_basename', [ $this, 'sitemaps_basename' ] );
		add_filter( 'pmc_sitemap_exclude_post', [ $this, 'sitemap_maybe_exclude_post_by_default' ], 10, 2 );
		add_filter( 'pmc_sitemaps_guest-author_exclude_from_seo_initial', [ $this, 'exclude_from_seo_initial' ] );

	}

	/**
	 * Properly format guest-author URL to that of author URL.
	 * @todo contribute back to plugin.
	 *
	 * @param $path
	 * @param $post
	 *
	 * @return mixed
	 */
	public function post_type_link( $path, $post ) {

		global $coauthors_plus, $wp_rewrite;

		if (
			is_a( $post, \WP_Post::class )
			&& 'guest-author' === $post->post_type
			&& 'publish' === $post->post_status
		) {
			$guest_author = $coauthors_plus->get_coauthor_by( 'id', $post->ID );
			if ( is_object( $guest_author ) && ! empty( $guest_author->user_login ) ) {
				$path = home_url( sprintf( '%sauthor/%s/', $wp_rewrite->front, $guest_author->user_login ) );
			}
		}

		return $path;

	}

	/**
	 * Add Guest Author post type to whitelist.
	 *
	 * @param $post_types
	 *
	 * @return array
	 */
	public function sitemaps_post_type_whitelist( $post_types ) {

		$post_types[] = 'guest-author';

		return $post_types;

	}

	/**
	 * Change basename for guest-author post type to not include year and month (202002)
	 * to allow all guest authors to appear under one sitemap.
	 *
	 * @param string $basename
	 *
	 * @return string
	 */
	public function sitemaps_basename( $basename ) : string {

		return 'guest-author-sitemap';

	}

	/**
	 * Should guest authors be excluded from sitemap by default
	 * if the exclude post meta is empty?
	 *
	 * @param $exclude
	 * @param $post
	 *
	 * @return bool
	 */
	public function sitemap_maybe_exclude_post_by_default( $exclude, $post ) : bool {

		if ( is_object( $post ) ) {
			if ( empty( get_post_meta( $post->ID, '_mt_pmc_exclude_from_seo', true ) ) ) {
				return (bool) $this->_disallow_index_by_default;
			}
		}

		return (bool) $exclude;

	}

	/**
	 * Set initial checkbox value to either exclude from SEO or not.
	 *
	 * @param string $initial
	 *
	 * @return string
	 */
	public function exclude_from_seo_initial( string $initial ) : string {

		if ( $this->_disallow_index_by_default ) {
			return 'on';
		}

		return $initial;

	}

	/**
	 * Robots meta tag override.
	 *
	 * @param $meta_value
	 *
	 * @return string|bool
	 */
	public function seo_tweaks_robots_override( $meta_value ) {

		if ( is_author() && $this->_do_not_allow_index() ) {
			$meta_value = 'noindex, nofollow';
		}

		return $meta_value;

	}

	/**
	 * If supposed to be indexed, remove robots meta tag.
	 *
	 * @param $names
	 *
	 * @return array
	 */
	public function seo_tweaks_robot_names( $names ) {

		if ( is_author() && ! $this->_do_not_allow_index() ) {
			$key = array_search( 'robots', (array) $names, true );

			unset( $names[ $key ] );

			$names = array_values( $names );
		}

		return $names;

	}

	/**
	 * Removes canonical URL tag if 'noindex, follow' added for page.
	 * The canonical tag is unnecessary and will contradict the 'noindex' rule if
	 * both are allowed on the same page.
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function canonical_url( $url ) {

		if ( is_author() && $this->_do_not_allow_index() ) {
			return '';
		}

		return $url;

	}

	/**
	 * Check if should disallow SEO indexing for a particular Guest Author page.
	 * Note: Check that is_author() is `true` before calling this method
	 * since get_queried_object_id() can be the ID in another context.
	 *
	 * @return bool
	 */
	private function _do_not_allow_index() : bool {

		$no_index = get_post_meta( get_queried_object_id(), '_mt_pmc_exclude_from_seo', true );

		switch ( $no_index ) {
			case 'on':
				return true;
			case 'off':
				return false;
		}

		return (bool) $this->_disallow_index_by_default;

	}

}

//EOF
