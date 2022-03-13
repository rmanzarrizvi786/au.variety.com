<?php

namespace PMC\Dateless_Link;
use PMC\Global_Functions\Traits\Singleton;
use PMC_Cache;
use WP_Post;


/**
 * Permalink class,
 * Responsible for altering permalink structure based on configuration.
 */
class Permalink {

	use Singleton;

	const CACHE_GROUP = 'pmc_dateless_link';
	const CACHE_LIFE  = HOUR_IN_SECONDS * 12;

	/**
	 * Array containing all configuration for altering the permalink.
	 * [
	 *    'option-slug' => [                                  // This key is used as slug for registering Post Option.
	 *        'label'            => 'Post Option Title'       // Used as label for registering post option.
	 *        'description'      => 'Post Option Description' // Used as description for registering post option.
	 *        'permalink_prefix' => 'permalink-prefix'        // This will be used to replace dates in the URL.
	 *        'news_sitemap'     => true | false,             // Determines whether to include the post in news sitemap.
	 *        'priority'         => 1, // Considered as a priority when more than one post options from this configuration are selected on the post. Higher number = higher priority.
	 *     ],
	 * ];
	 *
	 * @var array
	 */
	private $_settings = [
		'shop-content'      => [
			'label'            => 'Shop Content',
			'description'      => 'Posts with this term will be marked as Shop Content and permalink will not include date',
			'permalink_prefix' => 'shop',
			'news_sitemap'     => false,
			'priority'         => 0,
		],
		'evergreen-content' => [
			'label'            => 'Evergreen Content',
			'description'      => 'Posts with this term will be set as Evergreen Content.',
			'permalink_prefix' => 'feature',
			'news_sitemap'     => false,
			'priority'         => 10,
		],
	];

	/**
	 * Class Constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Setup filters and actions.
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		/**
		 * Actions.
		 */
		add_action( 'init', [ $this, 'add_default_terms' ] );
		add_action( 'init', [ $this, 'rewrite_post_link' ] );
		add_action( 'template_redirect', [ $this, 'redirect_old_urls' ] );
		add_action( 'save_post_post', [ $this, 'action_post_save_clear_cache' ], 10, 3 );

		/**
		 * Filters.
		 */
		add_filter( 'pre_post_link', [ $this, 'filter_post_link' ], 11, 3 ); // Hooking at a higher priority so it can run after all default priority hooks are finished running.
		add_filter( 'post_type_link', [ $this, 'filter_post_link' ], 11, 3 );
		add_filter( 'pre_pmc_vertical_permalink_tag', [ $this, 'filter_pre_pmc_vertical_permalink_tag' ], 10, 5 );
		add_filter( 'jetpack_sitemap_news_skip_post', [ $this, 'maybe_skip_post_in_news_sitemap' ], 10, 2 );
		add_filter( 'wpcom_sitemap_news_skip_post', [ $this, 'maybe_skip_post_in_news_sitemap' ], 10, 2 );
	}

	/**
	 * Redirect old urls with date to new urls without date.
	 */
	public function redirect_old_urls() : void {

		global $wp;
		$post = get_post();

		// Bail out if its not single post, or is a preview, or is site root, or post type isn't supported.
		if (
			empty( $post )
			|| ! is_singular( 'post' )
			|| is_preview()
			|| '/' === wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) // @phpcs:ignore
			|| 'post' !== $post->post_type
		) {
			return;
		}

		$post_option_config = $this->get_registered_option_from_post( $post );

		// No need to redirect. Post has registered post option and URL is correct.
		if ( ! empty( $post_option_config ) && preg_match( sprintf( '#^%s/#', $post_option_config['permalink_prefix'] ), $wp->request ) ) {
			return;
		}

		$requested_permalink = trailingslashit( home_url( $wp->request ) );
		$new_permalink       = trailingslashit( get_permalink( $post ) );

		if ( \PMC::is_amp() ) {
			if ( function_exists( 'amp_get_permalink' ) ) {
				$new_permalink = trailingslashit( amp_get_permalink( $post->ID ) );
			}
		} else {
			$page = get_query_var( 'page' );
			if ( ! empty( $page ) ) {
				$new_permalink .= $page . '/';
			}
		}

		if ( $requested_permalink !== $new_permalink ) {
			wp_safe_redirect( $new_permalink, '302' );
			// This case is highly unlikely, no need for testing; only adding for code completion.
			exit(); // @codeCoverageIgnore
		}
	}

	/**
	 * Registers each post options configured in settings.
	 *
	 * @return void
	 */
	public function add_default_terms() {

		$settings = $this->get_registered_settings();

		// Checking Taxonomy class because it is loaded by post options plugin when plugin is loaded.
		// Autoloading is false to prevent PHP from loading class, make sure plugin does that job.
		if ( class_exists( '\PMC\Post_Options\Taxonomy', false ) && ! empty( $settings ) ) {

			// Register each post options configured in settings.
			foreach ( $settings as $post_option_slug => $config ) {

				if ( ! is_array( $config ) || empty( $config['label'] ) || empty( $config['description'] ) ) {
					continue;
				}

				\PMC\Post_Options\API::get_instance()->register_global_options(
					[
						$post_option_slug => [
							'label'       => $config['label'],
							'description' => $config['description'],
						],
					]
				);
			}
		}
	}

	/**
	 * Fires on save_post_post to purge permalink cache.
	 *
	 * @param int      $post_id Post ID of post being saved.
	 * @param \WP_Post $post    Post Object.
	 * @param bool     $update  Whether this is an existing post being updated or not.
	 */
	public function action_post_save_clear_cache( $post_id, $post, $update ) {
		$this->get_registered_option_from_post( $post, true );
	}

	/**
	 * Alter the permalink for the marked posts. Removes dates from URL.
	 * Uses configured permalink prefix as per selected post options.
	 *
	 * @param string   $permalink The post's permalink structure.
	 * @param \WP_Post $post      The post in question.
	 *
	 * @return string Permalink structures for post.
	 */
	public function filter_post_link( $permalink, $post, $leavename ) : string {

		// Get all selected registered post options.
		$post_option_config = $this->get_registered_option_from_post( $post );

		if ( empty( $post_option_config ) || empty( $post_option_config['permalink_prefix'] ) ) {
			return $permalink;
		}

		if ( is_a( $post, '\WP_Post' ) && 'post' === $post->post_type ) {

			if ( 0 === substr_compare( $post->post_name, $post->ID, -strlen( $post->ID ) ) ) {

				$permalink = sprintf(
					'/%1$s/%2$s/',
					$post_option_config['permalink_prefix'],
					( ( $leavename ) ? '%postname%' : $post->post_name )
				);

			} else {

				$permalink = sprintf(
					'/%1$s/%2$s-%3$s/',
					$post_option_config['permalink_prefix'],
					( ( $leavename ) ? '%postname%' : $post->post_name ),
					$post->ID
				);
			}
		}

		return $permalink;
	}

	/**
	 * Helper for getting all post options of a given post. Uncached.
	 *
	 * @param \WP_Post $post The Post object.
	 *
	 * @return array
	 */
	public function get_post_options( $post ) : array {

		$post = get_post( $post );

		if ( ! is_a( $post, '\WP_Post' ) ) {
			return [];
		}

		$post_options = \PMC\Post_Options\API::get_instance()->post( $post )->get_post_options();

		if ( ! empty( $post_options ) && is_array( $post_options ) ) {
			return wp_list_pluck( $post_options, 'slug' );
		}

		return [];
	}

	/**
	 * Add rewrite rules for all the registered configuration.
	 */
	public function rewrite_post_link() {

		$settings = $this->get_registered_settings();

		if ( empty( $settings ) ) {
			return;
		}

		$prefixes        = wp_list_pluck( $settings, 'permalink_prefix' );
		$prefixes        = array_filter( $prefixes );
		$prefix_patterns = '(?:' . implode( '|', $prefixes ) . ')';

		add_rewrite_rule( $prefix_patterns . '/(?:[^/]+)-(\d+)/feed(/(.*))?/?$', 'index.php?post_type=post&p=$matches[1]&feed=feed', 'top' );
		add_rewrite_rule( $prefix_patterns . '/(?:[^/]+)-(\d+)/amp(/(.*))?/?$', 'index.php?post_type=post&p=$matches[1]&amp=$matches[2]', 'top' );
		add_rewrite_rule( $prefix_patterns . '/(?:[^/]+)-(\d+)(?:/(\d+)/?)?', 'index.php?post_type=post&p=$matches[1]&page=$matches[2]', 'top' );
	}

	/**
	 * Prevent pmc-vertical plugin from overriding the permalink structure.
	 *
	 * @param bool     $override   Current value of whether to disable permalink overriding or not.
	 * @param string   $permalink  Permalink being overridden.
	 * @param \WP_Post $post       Post object for which the permalink is being overridden.
	 * @param bool     $leavename  Whether to keep the post name.
	 * @param bool     $canonical Whether to modify permalink for canonical urls.
	 *
	 * @return mixed
	 */
	public function filter_pre_pmc_vertical_permalink_tag( string $override, string $permalink, $post, $leavename, $canonical ) : string {

		/**
		 * If the $post has registered post option then return $permalink as it is,
		 * because $this->filter_post_link() function would have already taken care of the permalink structure for all registered post options on pre_post_link filter.
		 */
		if ( ! empty( $this->get_registered_option_from_post( $post ) ) ) {
			return $permalink;
		}

		return $override;
	}

	/**
	 * Helper for getting selected registered post option. Cached.
	 *
	 * @param \WP_Post $post          The post in question.
	 * @param bool     $refresh_cache Deprecated. Whether to refresh the cache or not.
	 *
	 * @return array|bool
	 */
	public function get_registered_option_from_post( $post, $refresh_cache = false ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return false;
		}

		$key   = $post->ID . '_post_options';
		$cache = new PMC_Cache( $key, self::CACHE_GROUP );

		$cache
			->expires_in( self::CACHE_LIFE )
			->updates_with(
				[ $this, 'get_registered_option_from_post_uncached' ],
				[ $post ]
			);

		if ( $refresh_cache ) {
			$cache->invalidate();
		}

		return $cache->get();
	}

	/**
	 * Helper for getting selected registered post option. Uncached.
	 *
	 * @return array
	 */
	public function get_registered_option_from_post_uncached( $post ) : array {

		$settings                = $this->get_registered_settings();
		$selected_post_options   = $this->get_post_options( $post );
		$registered_post_options = array_filter( array_keys( (array) $settings ) );

		if (
			empty( $selected_post_options )
			|| empty( $registered_post_options )
			|| ! is_array( $selected_post_options )
		) {
			return [];
		}

		$return_value = [];
		$priority     = -1;

		foreach ( $selected_post_options as $post_option ) {

			if ( ! empty( $post_option ) && in_array( $post_option, (array) $registered_post_options, true ) ) {

				if ( $settings[ $post_option ]['priority'] > $priority ) {

					$priority             = $settings[ $post_option ]['priority'];
					$return_value         = $settings[ $post_option ];
					$return_value['slug'] = $post_option;
				}
			}
		}

		/**
		 * Filter to allow altering config for the current post.
		 *
		 * @param array    $return_value   Config for selected post option for $post
		 * @param \WP_Post $post           Post Object
		 */
		return (array) apply_filters( 'pmc_dateless_link_post_option_config', $return_value, $post );
	}

	/**
	 * @see https://developer.jetpack.com/hooks/jetpack_sitemap_news_skip_post/
	 *
	 * Method to exclude marked posts from News Sitemap
	 * This is hooked on to 'jetpack_sitemap_news_skip_post' filter.
	 *
	 * @param bool   $skip
	 * @param object $post
	 *
	 * @return bool
	 */

	public function maybe_skip_post_in_news_sitemap( $skip, $post ) {

		$config = $this->get_registered_option_from_post( $post );

		if ( ! empty( $config ) && false === $config['news_sitemap'] ) {
			return true;
		}

		return $skip;
	}

	/**
	 * Helper function to get the registered settings for the class.
	 */
	public function get_registered_settings() {
		/**
		 * Filter for altering registered post options config.
		 *
		 * @param array $this->_settings Array of registered post options and their configuration in the following format.
		 * [
		 *    'option-slug' => [                                  // This key is used as slug for registering Post Option.
		 *        'label'            => 'Post Option Title'       // Used as label for registering post option.
		 *        'description'      => 'Post Option Description' // Used as description for registering post option.
		 *        'permalink_prefix' => 'permalink-prefix'        // This will be used to replace dates in the URL.
		 *        'news_sitemap'     => true | false,             // Determines whether to include the post in news sitemap.
		 *        'priority'         => 1, // Considered as a priority when more than one post options from this configuration are selected on the post. Higher number = higher priority.
		 *     ],
		 * ];
		 */
		return array_filter( (array) apply_filters( 'pmc_dateless_link_settings', $this->_settings ) );
	}
}
