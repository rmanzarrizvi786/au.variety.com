<?php
namespace PMC\Gutenberg;

use PMC;
use PMC\Global_Functions\Traits\Singleton;
use PMC\Global_Functions\WP_REST_API\Manager;
use PMC\Larva;
use WP_Post;

/**
 * Main Class for Gutenberg
 */
class Gutenberg {
	use Singleton;

	// Blocks are added based on blocks.json in init_blocks
	// These are custom blocks.
	protected $_blocks = [];

	// The custom blocks to be registered by default on all brands.
	// Themes are responsible for registering others or removing
	// these via a filter, see Gutenberg::init_blocks()
	protected $_default_blocks = [
		'ad',
		'carousel',
		'jw-player',
		'one-off',
		'story',
	];

	// Modified blocks are core blocks we are routing to our
	// own template part.
	protected $_core_blocks_has_template = [
		'core/button',
		'core/column',
		'core/columns',
		'core/embed',
		'core/group',
		'core/heading',
		'core/list',
		'core/paragraph',
		'core/separator',
		'core/spacer',
	];

	// Built-in blocks are modified blocks where we "build in"
	// customizations to the block editor. In other words,
	// built-in blocks are modified blocks that require a script
	// enqueued. These can also be filtered by the theme.
	protected $_core_blocks_has_modified_editor = [
		'button',
		'column',
		'columns',
		'embed',
		'gallery',
		'group',
		'image',
		'paragraph',
		'separator',
	];

	// Unmodified blocks from core or third parties that we want
	// to include in the allowed block list.
	protected $_core_blocks_unmodified = [
		'core/buttons',
		'core/column',
		'core/cover',
	];

	protected $_sidebars = [
		'display',
		'distribution',
	];


	/**
	 * Add some hooks
	 */
	public function __construct() {
		$this->_register_aliases();
		$this->_register_endpoints();

		add_action( 'init', [ $this, 'init_blocks' ] );
		add_action( 'init', [ $this, 'init_sidebars' ] );
		add_action( 'init', [ $this, 'init_core_blocks_has_modified_editor' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_larva_css' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets_sidebars' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets_core_blocks_has_modified_editor' ] );

		add_filter( 'render_block', [ $this, 'render_block' ], 10, 2 );
		add_filter( 'allowed_block_types', [ $this, 'filter_allowed_block_types' ], 10, 2 );

		// Prevent WP from adding inline CSS that can contradict Larva.
		remove_filter( 'render_block', 'wp_render_layout_support_flag' );
	}

	/**
	 * Each of these classes was moved to support autoloading.
	 */
	public function _register_aliases(): void {
		$classes = [
			Blocks\One_Off::class,
			Blocks\Story::class,
			Blocks\Story_Influencers::class,
		];

		foreach ( $classes as $class ) {
			class_alias(
				$class,
				str_replace(
					'Blocks\\',
					'',
					$class
				),
				true // Previously loaded via require_once.
			);
		}
	}

	/**
	 * Register Gutenberg-only endpoints.
	 */
	public function _register_endpoints(): void {
		Manager::get_instance()->register_endpoint(
			REST_API\Carousel_Taxonomies::class
		);
	}

	/**
	 * Take a string with a block name, return the class name.
	 *
	 * @param string $string string to generate classname from
	 *
	 * @return string|null classname with namespace
	 */
	public function block_class_from_string( string $string ): ?string {
		$string = strtolower( $string );

		if ( false !== strpos( $string, '/' ) ) {
			if ( false === strpos( $string, 'pmc/' ) ) {
				return null;
			}
			$string = substr( $string, 4 );
		}

		$split = explode( '-', $string );

		// Upper Case Words when we join things back together
		// implode is used on the variable that is exploded above
		return 'PMC\Gutenberg\Blocks\\' . implode( '_', array_map( 'ucfirst', (array) $split ) );
	}

	/** Return an array of the default blocks without duplicates
	 * @return array
	 */

	public function create_default_blocks_array(): array {

		// The $_core_blocks_has_modified_editor arr does not contain the prefix,
		// adding it here so the arrays can be accurately compared.
		$core_blocks_modified = array_map(
			static function( string $name ): string {
				return 'core/' . $name;
			},
			(array) $this->_core_blocks_has_modified_editor
		);

		$all_default_blocks = array_merge(
			$this->_core_blocks_has_template,
			$core_blocks_modified,
			$this->_core_blocks_unmodified
		);

		return array_unique( (array) $all_default_blocks );
	}

	/**
	 * Return a list of allowed blocks according to a post type.
	 * Themes can filter the allowlist for any post type.
	 *
	 * Note:
	 * As we add more blocks and enable other post types, the
	 * composition of the allowlists will become more complex, and
	 * we will may want think of a more sophisticated way to build
	 * the allowlists dictionary.
	 *
	 * @param string The post type slug.
	 *
	 * @return array Containing a list of block names to allow on a given post type.
	 */
	public function get_block_allowlist_by_post_type( string $post_type_slug ): array {

		// The $_blocks arr does not contain the prefix,
		// it is added per block in register_block_type
		$custom_pmc_blocks = array_map(
			static function( string $name ): string {
				return 'pmc/' . $name;
			},
			$this->_blocks
		);

		$allowed_default_block_array = $this->create_default_blocks_array();
		/**
		 * The default list of allowed core and modified blocks.
		 *
		 * @param array $allowed Allowed blocks.
		 * @return array
		 */
		$default_allowlist = apply_filters(
			'pmc_gutenberg_block_allowlist_defaults',
			$allowed_default_block_array
		);

		/**
		 * Filter full allowlist dictionary, allowing themes and plugins to
		 * specify which blocks their post types support.
		 *
		 * @param array $allowed           Allowlist dictionary, keyed by post
		 *                                 type.
		 * @param array $default_allowlist Default allowed blocks.
		 * @param array $custom_pmc_blocks Blocks provided by this plugin.
		 */
		$allowlist_dictionary = apply_filters(
			'pmc_gutenberg_block_allowlist_dictionary',
			[],
			$default_allowlist,
			$custom_pmc_blocks
		);

		return $allowlist_dictionary[ $post_type_slug ] ?? $default_allowlist;
	}

	/**
	 * A wrapper method that returns our allowlist for the filter.
	 *
	 * @param array|bool An array of block slugs or a
	 *                   boolean to allow or disallow all
	 * @param WP_Post    WP Post object.
	 *
	 * @return array     Either an array of block slugs or boolean to
	 *                   allow/disallow all.
	 */
	public function filter_allowed_block_types( $allowed_blocks, WP_Post $post ): array {
		return $this->get_block_allowlist_by_post_type( $post->post_type );
	}

	/**
	 * Initialize and register all of our blocks.
	 *
	 * @codeCoverageIgnore This is registering blocks
	 *
	 * @return void
	 */
	public function init_blocks(): void {
		$this->_blocks = apply_filters( 'pmc_gutenberg_init_blocks', $this->_default_blocks );

		foreach ( $this->_blocks as $block_name ) {
			$class_name = $this->block_class_from_string( $block_name );
			$block      = $class_name::get_instance();
			$block->init();
		}
	}

	/**
	 * Initialize and register all of our blocks.
	 *
	 * @codeCoverageIgnore This is registering sidebars
	 *
	 * @return void
	 */
	public function init_sidebars() {

		$this->_sidebars = apply_filters( 'pmc_gutenberg_init_sidebars', $this->_sidebars );

		foreach ( $this->_sidebars as $sidebar ) {
			$script_asset_path = PMC_GUTENBERG_PLUGIN_PATH . PMC_GUTENBERG_BUILD_DIR_SLUG . $sidebar . '.asset.php';
			// Path does not include user input.
			// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			$script_asset = require $script_asset_path;
			$sidebar_js   = PMC_GUTENBERG_PLUGIN_URL . PMC_GUTENBERG_BUILD_DIR_SLUG . $sidebar . '.js';

			wp_register_script(
				'sidebar-' . $sidebar,
				$sidebar_js,
				$script_asset['dependencies'],
				$script_asset['version']
			);
		}
	}

	/**
	 * Customize built-in blocks.
	 *
	 * @codeCoverageIgnore This is configuring built-in blocks
	 *
	 * @return void
	 */
	public function init_core_blocks_has_modified_editor() {

		$this->_core_blocks_has_modified_editor = apply_filters( 'pmc_gutenberg_init_core_blocks_has_modified_editor', $this->_core_blocks_has_modified_editor );

		// Load overrides that apply to all built-in blocks, without allowing themes to disable these modifications.
		$this->_core_blocks_has_modified_editor[] = 'global-overrides';

		foreach ( $this->_core_blocks_has_modified_editor as $core_block_has_modified_editor ) {
			$script_asset_path = PMC_GUTENBERG_PLUGIN_PATH . PMC_GUTENBERG_BUILD_DIR_SLUG . $core_block_has_modified_editor . '.asset.php';
			// Path does not include user input.
			// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			$script_asset     = require $script_asset_path;
			$core_block_has_modified_editor_js = PMC_GUTENBERG_PLUGIN_URL . PMC_GUTENBERG_BUILD_DIR_SLUG . $core_block_has_modified_editor . '.js';

			wp_register_script(
				'builtin-' . $core_block_has_modified_editor,
				$core_block_has_modified_editor_js,
				$script_asset['dependencies'],
				$script_asset['version']
			);
		}
	}

	/**
	 * Enqueue Sidebar Scripts
	 *
	 * @codeCoverageIgnore This is enqueueing scripts
	 */
	public function enqueue_block_editor_assets_sidebars() {
		foreach ( $this->_sidebars as $sidebar ) {
			wp_enqueue_script( 'sidebar-' . $sidebar );
		}
	}

	/**
	 * Enqueue Larva CSS Scripts
	 */
	public function enqueue_larva_css() {
		$version = filemtime( PMC_LARVA_PLUGIN_PATH . '/_core/build/css/larva.css' );
		$tokens  = Larva\Config::get_instance()->get( 'tokens' );

		wp_enqueue_style( 'tokens-css', PMC_LARVA_PLUGIN_URL . '/_core/build/tokens/' . $tokens . '.custom-properties.css', [], $version );
		wp_enqueue_style( 'larva-css', PMC_LARVA_PLUGIN_URL . '/_core/build/css/larva.css', [], $version );
	}

	/**
	 * Enqueue Built-in Block Scripts
	 *
	 * @codeCoverageIgnore This is enqueueing scripts
	 */
	public function enqueue_block_editor_assets_core_blocks_has_modified_editor() {
		foreach ( $this->_core_blocks_has_modified_editor as $core_block_has_modified_editor ) {
			wp_enqueue_script( 'builtin-' . $core_block_has_modified_editor );
		}
	}

	/**
	 * Callback to render blocks
	 *
	 * Checks to see if the block has a larva_data method and if so, use that for view
	 * Otherwise return $block_content as is
	 *
	 * @see https://developer.wordpress.org/reference/hooks/render_block/
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array $block The full block, including name and attributes.
	 *
	 * @return string the content to be rendered
	 */
	public function render_block( string $block_content, array $block ) : string {

		// Bail early for empty blocks
		if ( ! isset( $block['blockName'] ) ) {
			return $block_content;
		}

		// Is this one of our modified blocks?
		// $this->modified_blocks is set in this class
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		if ( in_array( $block['blockName'], $this->_core_blocks_has_template, true ) ) {
			$modified_block = new Builtin( $block_content, $block );

			// These could be file reads, so store them in temp variables.
			$template_path = $modified_block->get_template();
			$template_data = $modified_block->get_template_data();

			if ( null === $template_path || null === $template_data ) {
				return $block_content;
			}

			return PMC::render_template( $template_path, $template_data );
		}

		// Return default block if no associated class or if class does
		// not follow Singleton pattern
		$class_name = $this->block_class_from_string( $block['blockName'] ?? '' );

		if ( null === $class_name || ! method_exists( $class_name, 'get_instance' ) ) {
			return $block_content;
		}

		$block_class = $class_name::get_instance();

		// Should we go to a template?
		if ( method_exists( $block_class, 'render_callback' ) ) {

			if ( method_exists( $block_class, 'larva_data' ) ) {
				$template_data = $block_class->larva_data( $block_content, $block );
				$template      = $block_class->template;

				if ( null === $template_data || null === $template ) {
					return $block_content;
				}

				return Larva\Pattern::render_pattern_template(
					$template,
					$template_data
				);
			}

			return $block_content;
		}

		// Should we go to a Larva template?
		if ( method_exists( $block_class, 'larva_data' ) ) {

			$template_data = $block_class->larva_data( $block_content, $block );
			$template      = $block_class->template;

			if ( null === $template_data || null === $template ) {
				return $block_content;
			}

			return Larva\Pattern::render_pattern_template(
				$template,
				$template_data
			);
		}

		return $block_content;
	}

}
