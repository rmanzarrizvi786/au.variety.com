<?php
/**
 * Featured program Plugin main class.
 *
 * @package pmc-featured-program
 *
 */

namespace PMC\Featured_Program;

use \PMC\Global_Functions\Traits\Singleton;

class Plugin {

	use Singleton;

	/**
	 * Featured programs post count.
	 *
	 * @var int
	 */
	public $featured_programs_post_count = 10;

	/**
	 * Featured programs tags.
	 *
	 * @var array
	 */
	private $_featured_program_tags = [];

	/**
	 * Cache expire time.
	 */
	const CACHE_LIFE = 900; // 15 minutes

	/**
	 * Article constructor.
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Setup actions and filters
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	protected function _setup_hooks() {
		add_action( 'pmc_core_global_curation_modules', [ $this, 'featured_program_module' ] );
		add_action( 'after_setup_theme', [ $this, 'plugin_init' ] );
		add_filter( 'single_template', [ $this, 'single_template_override' ] );
		add_filter( 'archive_template', [ $this, 'archive_template_override' ] );
	}

	function archive_template_override( $template ) {
		if ( Config::get_instance()->post_type() === get_post_type() ) {
			$template = apply_filters( 'pmc_fp_archive_template', $template );
		}
		return $template;
	}

	function single_template_override( $template ) {
		if ( Config::get_instance()->post_type() === get_post_type() ) {
			$template = PMC_FP_ROOT . 'templates/featured-program-single.php';
			$template = apply_filters( 'pmc_fp_single_template', $template );
		}
		return $template;
	}

	/**
	 * Initialize the plugin.
	 * 
	 * @return void
	 */
	public function plugin_init() {

		$classes = [
			Config::class,
			Post_Type_Featured_Program::class,
			Taxonomy_Featured_Program_Group::class,
			Taxonomy_Tag_Group::class,
			Utils::class,
		];

		// Initialize all the classes.
		foreach ( $classes as $class ) {
			$class::get_instance();
		}
		
		return $classes;
	}

	/**
	 * Adds a Featured Program module.
	 *
	 * @param array $modules Default global curation modules.
	 * @return array
	 */
	public function featured_program_module( $modules ) {
		$modules          = is_array( $modules ) ? $modules : [];
		$featured_program = $this->prepare_featured_program_module();

		return array_merge(
			[
				'featured_program' => [
					'label'    => __( 'Featured Program', 'pmc-featured-program' ),
					'children' => [
						'featured_program' => new \Fieldmanager_Group(
							[
								'children' => $featured_program,
							]
						),
					],
				],
			],
			$modules
		);
	}

	/**
	 * Prepares the Featured Program curation module.
	 *
	 * @return array
	 */
	private function prepare_featured_program_module() {
		return [
			'post' => new \Fieldmanager_Autocomplete(
				[
					'label'      => __( 'Search for a Featured Program to display on the home page', 'pmc-featured-program' ),
					'name'       => 'post',
					'datasource' => new \Fieldmanager_Datasource_Post(
						[ 'query_args' => [ 'post_type' => Config::get_instance()->post_type() ] ]
					),
				]
			),
		];
	}

	/**
	 * Gets Featured Program data which is added from `Global Curation > Featured Program`.
	 *
	 * @return array Featured Program data.
	 */
	public function get_featured_program() {
		$featured_program = [];
		$settings         = get_option( 'global_curation', [] );
		
		if (
			! empty( $settings )
			&& isset( $settings['tab_featured_program'] )
			&& isset( $settings['tab_featured_program']['featured_program'] )
			&& isset( $settings['tab_featured_program']['featured_program']['post'] )
		) {
			$meta_key            = Config::get_instance()->prefix() . '_fp_banner_image';
			$featured_program_id = $settings['tab_featured_program']['featured_program']['post'];
			$featured_program    = [
				'id'        => $featured_program_id,
				'banner_id' => get_post_meta( $featured_program_id, $meta_key, true ),
				'posts'     => $this->get_featured_program_posts( $featured_program_id ),
			];
		}

		return $featured_program;
	}

	/**
	 * Gets Featured Program posts for the provided ID.
	 *
	 * @param int $post_id ID of the Featured Program post.
	 *
	 * @return array Posts assodiated with the Featured Program.
	 */
	public function get_featured_program_posts( $post_id ) {
		// Get the posts associated via Featured Content meta and Tag Groups.
		$curated_posts = $this->get_featured_program_featured_content_posts( $post_id );
		$group_posts   = $this->get_tag_group_posts( $post_id );

		// Get the IDs of the associated posts.
		$curated_post_ids = ( ! empty( $curated_posts ) ) ? wp_list_pluck( $curated_posts, 'ID' ) : [];
		$group_post_ids   = ( ! empty( $group_posts ) ) ? wp_list_pluck( $group_posts, 'ID' ) : [];

		// Merge the arrays of IDs.
		$featured_post_ids = array_unique( array_merge( $curated_post_ids, $group_post_ids ) );

		// Return an empty array if there are no IDs.
		if ( empty( $featured_post_ids ) ) {
			return [];
		}

		$args = [
			'post_type'              => [ 'post', 'pmc-gallery', 'pmc_top_video' ],
			'post_status'            => 'publish',
			'post__in'               => $featured_post_ids,
			'orderby'                => 'post__in',
			'showposts'              => count( $featured_post_ids ),
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'suppress_filters'       => false,
		];

		return $this->get_cached_query_posts( $args, self::CACHE_LIFE ); // 15 minutes
	}

	/**
	 * Get featured program post type tag ids.
	 *
	 * @param int $post_id Post id.
	 *
	 * @return array
	 */
	public function get_featured_program_tags( $post_id ) {

		if ( ! empty( $this->_featured_program_tags ) ) {
			return $this->_featured_program_tags;
		}

		$term_list = wp_get_post_terms( $post_id, Config::get_instance()->tag_group() );   

		if ( empty( $term_list ) || is_wp_error( $term_list ) ) {
			return $this->_featured_program_tags;
		}
		
		foreach ( $term_list as $term ) {
			
			// A tag group is a group of tag terms assigned to a {prefix}-tag-group term.
			$term_tags = wp_get_object_terms( $term->term_id, 'post_tag' );
			$term_tags = ( ! empty( $term_tags ) ) ? wp_list_pluck( $term_tags, 'term_id' ) : [];
			
			$this->_featured_program_tags = ( empty( $term_tags ) ) ? $this->_featured_program_tags : array_merge( $this->_featured_program_tags, $term_tags );
		}
		
		return $this->_featured_program_tags;
	}

	/**
	 * Get featured program posts from tag groups.
	 *
	 * @param int $post_id Post ID.
	 * @param int $paged   Paged number.
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	public function get_tag_group_posts( $post_id, $paged = 1 ) {

		$tags = $this->get_featured_program_tags( $post_id );

		if ( empty( $tags ) ) {
			return [];
		}

		$post_types = apply_filters( 'pmc_fp_post_types', [ 'post', 'pmc-gallery', 'pmc_top_video' ] );

		$args = [
			'post_type'              => $post_types,
			'post_status'            => 'publish',
			'paged'                  => $paged,
			'tag__in'                => $tags,
			'posts_per_page'         => $this->featured_programs_post_count,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'suppress_filters'       => false,
		];
		return $this->get_cached_query_posts( $args, static::CACHE_LIFE );

	}

	/**
	 * Get cached query posts.
	 *
	 * @param array $args        Arguments.
	 * @param int   $expire_time Expire time in seconds.
	 *
	 * @return array
	 */
	public function get_cached_query_posts( $args, $expire_time ) {

		if ( empty( $args ) || ! is_array( $args ) || empty( $expire_time ) ) {
			return [];
		}

		$pmc_cache   = new \PMC_Cache( $this->get_cache_key( $args ) );
		$query_posts = $pmc_cache->expires_in( $expire_time )->updates_with( 'get_posts', [ $args ] )->get();

		return ( ! is_wp_error( $query_posts ) ) ? $query_posts : [];

	}

	/**
	 * Generate cache key.
	 *
	 * @param string|array $unique base on that cache key will generate.
	 *
	 * @return string Cache key.
	 */
	public function get_cache_key( $unique = '' ) {

		$cache_key = 'pmc_featured_program_cache_';

		if ( is_array( $unique ) ) {
			ksort( $unique );
			$unique = wp_json_encode( $unique );
		}

		$md5 = md5( $unique );
		$key = $cache_key . $md5;

		return $key;

	}

	/**
	 * Get featured program featured content post query.
	 *
	 * @param int $post_id Post ID.
	 * @param int $paged   Paged number.
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	public function get_featured_program_featured_content_posts( $post_id, $paged = 1 ) {

		$meta_key         = Config::get_instance()->prefix() . '_featured_program_contents';
		$featured_content = get_post_meta( $post_id, $meta_key, true );

		if ( empty( $featured_content ) ) {
			return [];
		}

		$args = [
			'post_type'              => [ 'post', 'pmc-gallery', 'pmc_top_video' ],
			'post_status'            => 'publish',
			'post__in'               => $featured_content,
			'paged'                  => $paged,
			'posts_per_page'         => $this->featured_programs_post_count,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'suppress_filters'       => false,
		];

		return $this->get_cached_query_posts( $args, static::CACHE_LIFE );

	}

}
