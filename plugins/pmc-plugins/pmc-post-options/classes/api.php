<?php
/**
 * API class for PMC Post Options plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Post_Options;

use \PMC\Global_Functions\Traits\Singleton;

class API {

	use Singleton;

	protected const CACHE_GROUP = 'pmc-post-options';

	protected const CACHE_GROUP_INCREMENTOR = 'pmc-post-opts-incr';

	protected const CACHE_LIFE = 12 * \HOUR_IN_SECONDS;

	/**
	 * Dummy ID used to generate incrementors for the `get_posts_having_option()`
	 * method.
	 */
	protected const FAUX_ID_POSTS_HAVING_OPTION = -1;

	/**
	 * @var PMC\Post_Options\Taxonomy
	 */
	protected $_taxonomy;

	protected $_post = null;

	/**
	 * Class Constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		$this->_taxonomy = Taxonomy::get_instance();

		$this->_setup_hooks();
	}

	/**
	 * Reset class properties.
	 */
	protected function _init_vars(): void {
		$this->_post = null;
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action(
			'set_object_terms',
			[ $this, 'invalidate_object_option_cache' ],
			10,
			4
		);

		add_action(
			'deleted_term_relationships',
			[ $this, 'invalidate_object_option_cache' ],
			10,
			3
		);
	}

	/**
	 * Invalidate an object's options cache.
	 *
	 * This method is used as a callback for two Core hooks that pass similar,
	 * but inconsistent data. Fortunately, for our needs, the relevant arguments
	 * are always the first and last passed to this function. Accordingly, we
	 * use `func_get_args()` to dynamically retrieve the values needed to
	 * invalidate the cache.
	 *
	 * @throws \ErrorException Invalid arguments.
	 */
	public function invalidate_object_option_cache(): void {
		// See docblock comment regarding this approach.
		$args      = func_get_args();
		$object_id = array_shift( $args );
		$taxonomy  = array_pop( $args );

		if ( ! is_numeric( $object_id ) || ! is_string( $taxonomy ) ) {
			throw new \ErrorException(
				sprintf(
					/* translators: 1. Name of the current function. */
					__(
						'Invalid arguments passed to %1$s',
						'pmc-post-options'
					),
					__FUNCTION__
				)
			);
		}

		if ( Base::NAME !== $taxonomy ) {
			return;
		}

		$this->post( $object_id )->_get_incrementor( true );
		$this->post( static::FAUX_ID_POSTS_HAVING_OPTION )->_get_incrementor( true );
		$this->_init_vars();
	}

	/**
	 * Return cache incrementor for current post.
	 *
	 * @param bool $refresh_cache Invalidate existing cache by updating value.
	 * @return int
	 * @throws \ErrorException
	 */
	protected function _get_incrementor( bool $refresh_cache = false ): int {
		$post_id = $this->_get_post_id();

		if ( empty( $post_id ) ) {
			return $this->get_random_incrementor();
		}

		$incrementor = new \PMC_Cache( (string) $post_id, static::CACHE_GROUP_INCREMENTOR );
		$incrementor
			->expires_in( static::CACHE_LIFE )
			->updates_with( [ $this, 'get_random_incrementor' ] );

		if ( $refresh_cache ) {
			$incrementor->invalidate();
		}

		return $incrementor->get();
	}

	/**
	 * Ensure incrementor is random even for rapid requests.
	 *
	 * Unit tests showed that `time()` wasn't sufficient if tests executed too
	 * quickly.
	 *
	 * @return int
	 */
	public function get_random_incrementor(): int {
		$value  = time();
		$value .= wp_rand( 999, 999999 );

		return (int) $value;
	}

	/**
	 * Get post options from a given post.
	 *
	 * @return array
	 */
	public function get_post_options(): array {

		if ( is_a( $this->_post, '\WP_Post' ) ) {
			$post_options = get_the_terms( $this->_post->ID, Taxonomy::NAME );

			if ( is_array( $post_options ) ) {
				return $post_options;
			}
		}

		return [];
	}

	/**
	 * Allows registration of custom options by plugins and/or themes.
	 * Its just a wrapper for PMC\Post_Options\Taxonomy::maybe_add_terms()
	 *
	 * @codeCoverageIgnore Underlying method is covered.
	 *
	 * @param array $options An array of terms which must be added
	 * @return void
	 */
	public function register_options( array $options = array() ) {
		return $this->_taxonomy->maybe_add_terms( $options );
	}

	/**
	 * Allows registration of custom options by plugins and/or themes under Global Options.
	 * This function would not accept any parent options and its children, it would only
	 * accept child options as per the data structure defined in Taxonomy class.
	 *
	 * @param array $options An array of terms which must be added as children of Global Options parent term
	 * @return void
	 */
	public function register_global_options( array $options ) {

		$global_options = array(
			Taxonomy::PARENT_TERM => array(
				'label'    => Taxonomy::PARENT_TERM_LABEL,
				'children' => $options,
			),
		);

		return $this->_taxonomy->maybe_add_terms( $global_options );

	}

	/**
	 * Set the post object for the current request.
	 *
	 * @param \WP_Post|int $post Post ID or object.
	 * @return $this
	 */
	public function post( $post ): self {
		$this->_post = $post;

		return $this;
	}

	/**
	 * Get the ID from the given post data.
	 *
	 * @return int|null
	 */
	protected function _get_post_id(): ?int {
		if ( is_object( $this->_post ) ) {
			$id = (int) $this->_post->ID;
		} elseif ( is_numeric( $this->_post ) ) {
			$id = (int) $this->_post;
		} else {
			$id = null;
		}

		return $id;
	}

	/**
	 * Check if the set post has a given option.
	 *
	 * @param string $option_name Name of post option.
	 * @return bool
	 * @throws \ErrorException Invalid cache-update callback.
	 */
	public function has_option( string $option_name ): bool {
		$post_id = $this->_get_post_id();

		if ( empty( $post_id ) ) {
			return false;
		}

		$key = sprintf(
			'%1$d-%2$s-%3$d',
			$post_id,
			$option_name,
			$this->_get_incrementor()
		);

		$cache = new \PMC_Cache( $key, static::CACHE_GROUP );
		$cache
			->expires_in( static::CACHE_LIFE )
			->updates_with(
				[ $this, 'has_option_uncached' ],
				[
					$option_name,
				]
			);

		// This must happen before the call to `_init_vars()`!
		$has_option = $cache->get();

		$this->_init_vars();

		/**
		 * Before PMC_Cache's handling of boolean false values was fixed,
		 * has_option_uncached() return y/n rather than a boolean.
		 */
		if ( is_string( $has_option ) ) {
			return bool_from_yn( $has_option );
		}

		return $has_option;
	}

	/**
	 * PMC_Cache callback for checking if a post has an option set.
	 *
	 * @param string $option_name Name of post option.
	 * @return bool
	 * @throws \ErrorException Invalid option name.
	 */
	public function has_option_uncached( string $option_name ): bool {
		$option_id = $this->_taxonomy->get_term_id( $option_name, $this->_post );

		if ( ! empty( $option_id ) ) {
			$returnable_value = $this->_taxonomy->post_has_term( $this->_post, $option_id );
		} else {
			$returnable_value = false;
		}

		$this->_init_vars();

		return $returnable_value;
	}

	/**
	 * Method to fetch posts which have a specific post option added to them.
	 *
	 * @param string $option_name
	 * @param array  $config Optional query parameters to filter out posts fetched. These are the same as those accepted by get_posts() in WP API.
	 * @return array An array of post objects on success else an empty array
	 */
	public function get_posts_having_option( $option_name, array $config = array() ) {

		$default_config = array(
			'posts_per_page'   => 1,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => $this->_taxonomy->get_post_types(),
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'           => '',
			'post_status'      => 'publish',
			'suppress_filters' => false,
		);

		$tax_query = array();

		if ( ! empty( $config ) ) {

			if ( ! empty( $config['post_type'] ) && ! is_array( $config['post_type'] ) ) {
				$config['post_type'] = array( $config['post_type'] );
				unset( $default_config['post_type'] );
			}

			if ( ! empty( $config['tax_query'] ) && is_array( $config['tax_query'] ) ) {
				//extract any taxonomy queries specified, we'll merge ours into this and put it back
				$tax_query = $config['tax_query'];
				unset( $config['tax_query'] );
			}

		}

		if ( ! empty( $config['post_type'] ) && is_array( $config['post_type'] ) ) {

			$config['post_type'] = array_filter( array_unique( $config['post_type'] ) );

			if ( empty( $config['post_type'] ) ) {
				$config['post_type'] = ( empty( $default_config['post_type'] ) ) ? $this->_taxonomy->get_post_types() : $default_config['post_type'];
			}
		}

		$config = wp_parse_args( $config, $default_config );

		sort( $config['post_type'] );

		$option_ids = array();

		for ( $i = 0; $i < count( $config['post_type'] ); $i ++ ) {

			$option_ids[] = $this->_taxonomy->get_term_id_by_post_type( $option_name, $config['post_type'][ $i ] );

		}

		$option_ids = array_filter( array_unique( array_map( 'intval', $option_ids ) ) );

		if ( empty( $option_ids ) ) {
			return array();
		}

		if ( ! empty( $tax_query ) && empty( $tax_query['relation'] ) ) {
			$tax_query['relation'] = 'AND';
		}

		$tax_query[] = array(
			'taxonomy'         => Taxonomy::NAME,
			'field'            => 'term_id',
			'terms'            => $option_ids,
			'include_children' => true,
			'operator'         => 'IN',
		);

		$config['tax_query'] = $tax_query;

		$return_type      = $config['fields'] ?? 'objects';
		$config['fields'] = 'ids';

		$this->post( static::FAUX_ID_POSTS_HAVING_OPTION );
		$incrementor = $this->_get_incrementor();
		$this->_init_vars();
		$key = md5( $incrementor . $option_name . wp_json_encode( $config ) );

		$posts = wp_cache_get( $key, static::CACHE_GROUP );

		if ( false === $posts ) {
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
			$posts = get_posts( $config );

			wp_cache_set( $key, $posts, static::CACHE_GROUP, 3600 );
		}

		$posts = array_map(
			// `get_posts()` only returns an array, and we only cache an array.
			// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
			'ids' === $return_type ? 'absint' : 'get_post',
			$posts
		);

		if ( 'id=>parent' === $return_type ) {
			$posts = wp_list_pluck( $posts, 'post_parent', 'ID' );
		}

		return $posts;
	}

}
