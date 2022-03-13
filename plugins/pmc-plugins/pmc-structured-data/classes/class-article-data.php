<?php

namespace PMC\Structured_Data;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Cache;

/**
* Article_Data | themes/vip/pmc-plugins/pmc-structured-data/classes/class-article-data.php
*
* @author brandoncamenisch
* @version 2017-12-04 brandoncamenisch - PMCER-307:
* - Class for handling structured article data for search engines and other meta
* information.
*
*/


/**
* Article_Data | themes/vip/pmc-plugins/pmc-structured-data/classes/class-article-data.php
*
* @since 2017-12-04
*
* @version 2017-12-04 - brandoncamenisch - PMCER-307:
* - Class for handling structured article data for search engines and other meta
* information.
* @version 2017-12-20 brandoncamenisch - PMCER-307:
* - Adding cache key constants
*
*/
class Article_Data {

	use Singleton;

	const FILTER_AUTHOR              = 'pmc_structured_data_author';
	const FILTER_DATA                = 'pmc_structured_data';
	const FILTER_POST_TYPE_WHITELIST = 'pmc_structured_data_post_type_whitelist';
	const FILTER_CONTENT_SELECTORS   = 'pmc_structured_data_content_selectors';

	const CACHE_KEY   = 'pmc_structured_data_';
	const CACHE_GROUP = 'pmc_structured_data';
	const CACHE_LIFE  = 3600; // 1 hr

	// @TODO: need to figure out if we have to render at wp_header?
	// if not, we would prefer to render the structure data on footer.
	protected $_rendered_on_action     = 'wp_head';
	protected $_whitelisted_post_types = [ 'post' ];
	protected $_all_post_types         = false;

	/**
	* __construct | themes/vip/pmc-plugins/pmc-structured-data/classes/class-build-data.php
	*
	* @since 2017-12-04 - Setup the class
	*
	* @author brandoncamenisch
	* @version 2017-12-04 - PMCER-307:
	* - Writing class
	*
	*/
	protected function __construct() {
		add_action( 'init', [ $this, 'action_init' ] );
		add_action( 'admin_init', [ $this, 'action_admin_init' ] );
	}

	public function action_admin_init() {
		add_action( 'post_save', [ $this, 'action_post_save' ] );
	}

	/**
	 * wp init action function
	 */
	public function action_init() {
		add_action( $this->_rendered_on_action, [ $this, 'output_article_data' ] );
	}

	public function action_post_save( $post_ID ) {
		if ( empty( $post_ID ) ) {
			return;
		}

		$cache_key = self::CACHE_KEY . $post_ID;
		$pmc_cache = new PMC_Cache( $cache_key, self::CACHE_GROUP );
		$pmc_cache->invalidate();

	}

	/**
	 * Setup the plugin whether to activate for all post types or not
	 * @param bool $value True to activate plugin for all post types, ignored whitelist filter
	 * @return $this
	 */
	public function activate_on_all_post_types( $value = true ) : self {
		$this->_all_post_types = (bool) $value;
		return $this;
	}

	/**
	 * Setup the plugin where to render the data structured json
	 * @param string $action wp_head | wp_footer, define which action to render the data structured json
	 * @return $this
	 */
	public function render_on_action( string $action ) : self {
		if ( in_array( $action, [ 'wp_head', 'wp_footer' ], true ) ) {
			$this->_rendered_on_action = $action;
		}
		return $this;
	}

	/**
	* build_article_data | themes/vip/pmc-plugins/pmc-structured-data/classes/class-build-data.php
	*
	* @since 2017-12-04 - Builds the structured data model.
	* @uses uses
	* @see https://github.com/Automattic/amp-wp/blob/develop/includes/class-amp-post-template.php#L153
	* @see https://bitbucket.org/penskemediacorp/pmc-wwd-2016/src/2dee2a992edf3df9cf9417d88fb9a8989430ac55/plugins/paywall/classes/class-post-json-ld.php?at=master&fileviewer=file-view-default
	*
	* @author brandoncamenisch
	* @version 2017-12-04 - PMCER-307:
	* - Extracting methods from VIP google AMP plugin to use as a starting base.
	* - Found a similar implementation on WWD 2016 see link above
	*
	* @version 2017-12-15 brandoncamenisch - feature/PMCER-307:
	* - Adding logo to publisher type definition
	*
	* @version 2017-12-20 brandoncamenisch - feature/PMCER-307:
	* - Changing function visibility from protected to public because PMC_Cache
	* needs to have it public.
	*/
	public function build_article_data( $post_ID = 0 ) {

		if ( intval( $post_ID ) < 1 ) {
			return false;
		}

		$post = get_post( $post_ID );

		if ( ! $post ) {
			return false;
		}

		$has_parts         = [];
		$content_selectors = apply_filters( self::FILTER_CONTENT_SELECTORS, [], $post_ID );

		if ( ! empty( $content_selectors ) && is_array( $content_selectors ) ) {

			foreach ( $content_selectors as $selector => $is_free ) {
				if ( in_array( strtolower( (string) $is_free ), [ 'free' ], true ) ) {
					$is_free = 'True';
				} else {
					$is_free = 'False';
				}

				$has_parts[] = [
					'@type'               => 'WebPageElement',
					'isAccessibleForFree' => $is_free,
					'cssSelector'         => $selector,
				];
			}

		}

		$canonical_url = wp_get_canonical_url( $post );
		if ( empty( $canonical_url ) ) {
			// if post status != publish, canonical url will be empty, this is a fallback
			$canonical_url = get_permalink( $post );
		}

		$blog_info = wp_strip_all_tags( get_bloginfo( 'name', 'display' ), true );
		$metadata  = [
			'@context'            => 'http://schema.org',
			'@type'               => 'Article',
			'name'                => $blog_info,
			'mainEntityOfPage'    => [
				'@type' => 'WebPage',
				'@id'   => $canonical_url,
			],
			'headline'            => wp_strip_all_tags( $this->_get_title( $post->ID ), true ),
			'datePublished'       => date( 'c', get_post_time( 'U', $post->ID ) ),
			'dateModified'        => date( 'c', get_post_modified_time( 'U', false, $post ) ),
			'description'         => wp_strip_all_tags( get_the_excerpt( $post_ID ), true ),
			'author'              => [
				'@type' => 'Person',
				'name'  => wp_strip_all_tags( $this->_get_author( $post_ID ), true ),
			],
			'publisher'           => [
				'@type' => 'Organization',
				'name'  => $blog_info,
				'url'   => home_url(),
			],
			'isAccessibleForFree' => ( 0 < count( $has_parts ) ? 'False' : 'True' ),
		];

		$logo = $this->_get_logo();

		if ( ! empty( $logo ) ) {
			$metadata['publisher']['logo'] = [
				'@type' => 'ImageObject',
				'url'   => $logo,
			];
		}

		if ( has_post_thumbnail( $post ) ) {
			$metadata['image'] = [
				'@type' => 'ImageObject',
				'url'   => wp_get_attachment_url( get_post_thumbnail_id( $post_ID ) ),
			];
		}

		if ( ! empty( $has_parts ) ) {
			if ( 1 === count( $has_parts ) ) {
				$metadata['hasPart'] = $has_parts[0];
			} else {
				$metadata['hasPart'] = $has_parts;
			}
		}

		return $metadata;

	}

	/**
	 * Helper function to return the SEO and fallback to post title if empty
	 * @param $post_ID
	 * @return string
	 */
	protected function _get_title( $post_ID ) : string {
		$title = get_post_meta( $post_ID, 'mt_seo_title', true );
		if ( empty( $title ) ) {
			$title = get_the_title( $post_ID );
		}
		return $title;
	}

	/**
	* _get_logo | classes/class-article-data.php
	*
	* @since 2017-12-27 - Gets the site icon on local/qa or blavatar on VIP
	*
	* @author brandoncamenisch
	* @version 2017-12-27 - feature/PMCER-307:
	* - Adding method
	*
	* @return string
	*/
	protected function _get_logo() {
		if ( \PMC::is_production()
			&& function_exists( 'blavatar_exists' )
			&& blavatar_exists( blavatar_current_domain() )
		) {
			// We can't cover this code because of is_production & function may not exist
			$icon = blavatar_url( blavatar_current_domain() );  // @codeCoverageIgnore
		}
		if ( empty( $icon ) ) {
			$icon = get_site_icon_url();
		}
		return $icon;
	}

	/**
	* _get_coauthors | www/wp-content/themes/vip/pmc-plugins/pmc-structured-data/classes/class-article-data.php
	*
	* @since 2017-12-13 - Gets the author information for the article
	*
	* @author brandoncamenisch
	* @version 2017-12-13 - PMCER-307:
	* - Adding method
	*
	* @param int $post_ID
	* @return arr $author
	*/
	protected function _get_author( $post_ID ) {
		if ( intval( $post_ID ) < 1 ) {
			return false;
		}

		$author = apply_filters( self::FILTER_AUTHOR, false, $post_ID );
		if ( ! empty( $author ) ) {
			return $author;
		}

		$author = get_bloginfo( 'name', 'display' ) . ' Staff';

		if ( function_exists( 'get_coauthors' ) ) {

			$authors = get_coauthors( $post_ID );

			if ( is_array( $authors ) ) {
				// Get the author display name
				$authors = wp_list_pluck( array_values( $authors ), 'display_name' );

				// We only care about the first author
				if ( ! empty( $authors[0] ) ) {
					$author = $authors[0];
				}
			}

		}

		return $author;
	}

	/**
	* _get_article_data | themes/vip/pmc-plugins/pmc-structured-data/classes/class-article-data.php
	*
	* @since 2017-12-04 - Gets the structured article data from post meta.
	* @uses PMC_Cache
	* @author brandoncamenisch
	* @version 2017-12-04 - PMCER-307:
	* - Retrieves the article meta information object from post meta for use.
	* @version 2017-12-20 brandoncamenisch - PMCER-307:
	* - Adding cache mechanism from PMC_Cache
	*
	*/
	protected function _get_article_data( $post_ID ) {
		if ( intval( $post_ID ) < 1 || 'publish' !== get_post_status( $post_ID ) ) {
			return false;
		}

		$cache_key = self::CACHE_KEY . $post_ID;
		$pmc_cache = new \PMC_Cache( $cache_key, self::CACHE_GROUP );

		$article_data = $pmc_cache->expires_in( self::CACHE_LIFE )
			->updates_with( [ $this, 'build_article_data' ], [ $post_ID ] )
			->get();

		return $article_data;
	}

	/**
	* output_data | themes/vip/pmc-plugins/pmc-structured-data/classes/class-build-data.php
	*
	* @since 2017-12-04 - Outputs the data built for structured GA data.
	*
	* @author brandoncamenisch
	* @version 2017-12-04 - PMCER-207
	* - Outputting prepared data using PMC::render_template method
	*
	* @param params
	* @return returns
	*/
	public function output_article_data() : void {
		global $post;

		if ( ! is_single() // we want to check this first
			|| is_feed() // we don't want to continue if current page is feed
			|| is_admin() // definitely don't want to render anything on wp-admin
			// post in whitelist?
			|| (
				! in_array( get_post_type(), (array) apply_filters( self::FILTER_POST_TYPE_WHITELIST, $this->_whitelisted_post_types ), true )
				&& ! $this->_all_post_types
				)
			) {

			return;

		}

		$data = apply_filters( self::FILTER_DATA, $this->_get_article_data( $post->ID ) );

		if ( ! empty( $data ) && is_array( $data ) ) {
			\PMC::render_template( PMC_STRUCTURED_DATA_PATH . 'templates/article-data.php', [ 'data' => $data ], true );
		}

	}
}
