<?php
namespace PMC\Hub;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Plugin Class for Hub
 */
class Post_Type {
	use Singleton;

	public const POST_TYPE = 'pmc-hub';

	/**
	 * Post_Type constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_filter( 'pmc_gutenberg_block_allowlist_dictionary', [ $this, 'allow_blocks' ], 10, 3 );
		add_filter( 'pmclinkcontent_post_types', [ $this, 'filter_add_post_type' ] );
		add_filter( 'pmc_field_override_post_types', [ $this, 'filter_add_post_type' ] );
		add_filter( 'pmc_contextual_player_enable', [ $this, 'disable_contextual_player' ] );
		add_filter( 'single_template', [ $this, 'single_template_fallback' ] );
	}

	/**
	 * Register our custom post type
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Hubs', 'pmc-hub' ),
			'singular_name'      => __( 'Hub', 'pmc-hub' ),
			'menu_name'          => __( 'Hubs', 'pmc-hub' ),
			'name_admin_bar'     => __( 'Hub', 'pmc-hub' ),
			'add_new'            => __( 'Add New', 'pmc-hub' ),
			'add_new_item'       => __( 'Add New Hub', 'pmc-hub' ),
			'new_item'           => __( 'New Hub', 'pmc-hub' ),
			'edit_item'          => __( 'Edit Hub', 'pmc-hub' ),
			'view_item'          => __( 'View Hub', 'pmc-hub' ),
			'all_items'          => __( 'All Hubs', 'pmc-hub' ),
			'search_items'       => __( 'Search Hubs', 'pmc-hub' ),
			'parent_item_colon'  => __( 'Parent Hub', 'pmc-hub' ),
			'not_found'          => __( 'No Hubs Found', 'pmc-hub' ),
			'not_found_in_trash' => __( 'No Hubs Found in Trash', 'pmc-hub' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'menu_position'       => 28, // Just above “Videos”
			'menu_icon'           => 'dashicons-layout',
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'zoninator_zones' ),
			'has_archive'         => true,
			'rewrite'             => array(
				'slug'       => 'h',
				'with_front' => false,
			),
			'query_var'           => true,
		);

		register_post_type( static::POST_TYPE, $args );

	}

	/**
	 * Enable custom blocks.
	 *
	 * @param array $allowlist     Allowed blocks, keyed by post type.
	 * @param array $defaults      Default blocks enabled if a post type doesn't
	 *                             specify its own list.
	 * @param array $custom_blocks Custom blocks from the `pmc-gutenberg`
	 *                             plugin.
	 * @return array
	 */
	public function allow_blocks(
		array $allowlist,
		array $defaults,
		array $custom_blocks
	): array {
		$allowlist[ static::POST_TYPE ] = array_merge(
			$custom_blocks,
			$defaults
		);

		return $allowlist;
	}

	/**
	 * Enable Field Overrides for hub posts.
	 *
	 * @param array $post_types Post types supporting Field Overrides.
	 * @return mixed
	 */
	public function filter_add_post_type( $post_types ) {
		$post_types[] = static::POST_TYPE;
		return $post_types;
	}

	/**
	 * Suppress the contextual video ad on hub posts.
	 *
	 * @param bool $enabled Whether or not to show contextual player.
	 * @return bool
	 */
	public function disable_contextual_player( bool $enabled ): bool {
		if ( is_singular( static::POST_TYPE ) ) {
			return false;
		}

		return $enabled;
	}

	/**
	 * Intercept the template hierarchy to fallback to a default template
	 * in this plugin instead of single.php when a single-pmc-hub.php
	 * is not present in the theme.
	 *
	 * @param string
	 *
	 * @return string Path to the single template.
	 */
	public function single_template_fallback( $path ): string {

		$is_single_hub_template = strpos( $path, self::POST_TYPE );
		$is_custom_template     = strpos( $path, '/page-' );

		if ( self::POST_TYPE !== get_post_type() ) {
			return $path;
		}

		if ( ! $is_single_hub_template && ! $is_custom_template ) {
			return PMC_HUB_TEMPLATE_PATH . '/single-pmc-hub.php';
		}

		return $path;
	}

}
