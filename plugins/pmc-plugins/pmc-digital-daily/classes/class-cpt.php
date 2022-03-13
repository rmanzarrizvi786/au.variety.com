<?php
/**
 * Post types for Digital Daily feature.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use PMC\Global_Functions\Traits\Singleton;
use WP_Post;
use WP;

/**
 * Class CPT.
 */
class CPT {
	use Singleton;

	/**
	 * Prefix for admin menu parent slug, used to nest Special Editions below
	 * main Digital Daily post type.
	 */
	protected const ADMIN_MENU_PARENT_SLUG_PREFIX = 'edit.php?post_type=';

	/**
	 * Check if a user has a given capability for any of Digital Daily's post
	 * types.
	 *
	 * @param string $capability Primitive capability.
	 * @return bool
	 */
	public static function current_user_can( string $capability ): bool {
		$primary_cpt  = get_post_type_object( POST_TYPE )->cap;
		$specials_cpt = get_post_type_object(
			POST_TYPE_SPECIAL_EDITION_ARTICLE
		)->cap;

		return current_user_can( $primary_cpt->{$capability} )
			|| current_user_can( $specials_cpt->{$capability} );
	}

	/**
	 * CPT constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'init', [ $this, 'register' ] );
		add_action(
			'admin_menu',
			[ $this, 'add_special_editions_menu_items' ]
		);
		add_filter(
			'enter_title_here',
			[ $this, 'modify_title_placeholder' ],
			10,
			2
		);

		add_action(
			'parse_request',
			[ $this, 'limit_access_to_special_edition_articles' ]
		);

		add_filter(
			'single_template_hierarchy',
			[ $this, 'override_special_edition_template' ]
		);

		add_filter(
			'amp_supportable_post_types',
			[ $this, 'remove_amp_support' ]
		);

		add_action( 'parse_request', [ $this, 'maybe_redirect_archive' ] );
	}

	/**
	 * Register post type.
	 */
	public function register(): void {
		register_post_type(
			POST_TYPE,
			[
				'labels'            => [
					'name'               => __( 'Digital Dailies', 'pmc-digital-daily' ),
					'singular_name'      => __( 'Digital Daily', 'pmc-digital-daily' ),
					'add_new'            => __( 'Add New Digital Daily', 'pmc-digital-daily' ),
					'add_new_item'       => __( 'Add New Digital Daily', 'pmc-digital-daily' ),
					'edit_item'          => __( 'Edit Digital Daily', 'pmc-digital-daily' ),
					'new_item'           => __( 'New Digital Daily', 'pmc-digital-daily' ),
					'view_item'          => __( 'View Digital Daily', 'pmc-digital-daily' ),
					'search_items'       => __( 'Search Digital Dailies', 'pmc-digital-daily' ),
					'not_found'          => __( 'No Digital Dailies found', 'pmc-digital-daily' ),
					'not_found_in_trash' => __( 'No Digital Dailies found in Trash', 'pmc-digital-daily' ),
					'parent_item_colon'  => __( 'Parent Digital Daily:', 'pmc-digital-daily' ),
					'menu_name'          => __( 'Digital Dailies', 'pmc-digital-daily' ),
				],
				'public'            => true,
				'show_in_menu'      => true,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'show_ui'           => true,
				'supports'          => [
					'title',
					'editor',
					'thumbnail',
					'revisions',
					'custom-fields', // Required to access registered meta.
				],
				'menu_icon'         => 'dashicons-layout',
			]
		);

		/**
		 * Cannot set `public` or `publicly_queryable` to `false` as doing so
		 * prevents previewing these articles.
		 */
		register_post_type(
			POST_TYPE_SPECIAL_EDITION_ARTICLE,
			[
				'labels'                => [
					'name'               => __( 'Special Edition Articles', 'pmc-digital-daily' ),
					'singular_name'      => __( 'Special Edition Article', 'pmc-digital-daily' ),
					'name_admin_bar'     => __( 'DD Special Edition Article', 'pmc-digital-daily' ),
					'add_new'            => __( 'Add New', 'pmc-digital-daily' ),
					'add_new_item'       => __( 'Add New Special Edition Article', 'pmc-digital-daily' ),
					'edit_item'          => __( 'Edit Special Edition Article', 'pmc-digital-daily' ),
					'new_item'           => __( 'New Special Edition Article', 'pmc-digital-daily' ),
					'view_item'          => __( 'View Special Edition Article', 'pmc-digital-daily' ),
					'search_items'       => __( 'Search Special Edition Articles', 'pmc-digital-daily' ),
					'not_found'          => __( 'No Special Edition Articles found', 'pmc-digital-daily' ),
					'not_found_in_trash' => __( 'No Special Edition Articles found in Trash', 'pmc-digital-daily' ),
					'parent_item_colon'  => __( 'Parent Special Edition Article:', 'pmc-digital-daily' ),
					'menu_name'          => __( 'Special Edition Articles', 'pmc-digital-daily' ),
				],
				'public'                => true,
				'has_archive'           => false,
				'show_in_menu'          =>
					static::ADMIN_MENU_PARENT_SLUG_PREFIX . POST_TYPE,
				'show_in_nav_menus'     => false,
				'show_in_rest'          => true,
				'rest_controller_class' =>
					REST_API\Special_Edition_Articles_Controller::class,
				'show_ui'               => true,
				'exclude_from_search'   => true,
				'rewrite'               => false,
				'supports'              => [
					'title',
					'excerpt',
					'editor',
					'thumbnail',
					'revisions',
				],
			]
		);
	}

	/**
	 * Restore "Add new" menu item for Special Editions, as WP does not add it
	 * when the post list is moved under an existing menu item.
	 */
	public function add_special_editions_menu_items(): void {
		$cpt = get_post_type_object( POST_TYPE_SPECIAL_EDITION_ARTICLE );

		if ( null === $cpt ) {
			return;
		}

		$labels = get_post_type_labels( $cpt );

		add_submenu_page(
			static::ADMIN_MENU_PARENT_SLUG_PREFIX . POST_TYPE,
			$labels->add_new_item,
			$labels->add_new_item,
			$cpt->cap->edit_posts,
			'post-new.php?post_type=' . POST_TYPE_SPECIAL_EDITION_ARTICLE
		);
	}

	/**
	 * Modify the placeholder text shown in the title input.
	 *
	 * @param string  $text Placeholder text.
	 * @param WP_Post $post Post object.
	 * @return string
	 */
	public function modify_title_placeholder( string $text, WP_Post $post ): string {
		if ( POST_TYPE === $post->post_type ) {
			$text = __(
				'Enter Digital Daily name here',
				'pmc-digital-daily'
			);
		}

		if ( POST_TYPE_SPECIAL_EDITION_ARTICLE === $post->post_type ) {
			$text = __(
				'Enter article title here',
				'pmc-digital-daily'
			);
		}

		return $text;
	}

	/**
	 * Prevent unauthorized access to Special Edition articles.
	 *
	 * @param WP $wp WP object after request is parsed.
	 */
	public function limit_access_to_special_edition_articles( WP $wp ): void {
		if (
			! isset( $wp->query_vars[ POST_TYPE_SPECIAL_EDITION_ARTICLE ] )
		) {
			return;
		}

		if ( static::current_user_can( 'edit_posts' ) ) {
			return;
		}

		// While this should be a 401, WP doesn't natively support it.
		$wp->set_query_var( 'error', 403 );
	}

	/**
	 * Special Edition Articles don't have their own template as they're only
	 * output as part of a Digital Daily post.
	 *
	 * @param array $templates
	 * @return array
	 */
	public function override_special_edition_template(
		array $templates
	): array {
		if (
			POST_TYPE_SPECIAL_EDITION_ARTICLE
				!== get_post_type(
					get_queried_object()
				)
		) {
			return $templates;
		}

		return [
			'single-' . POST_TYPE . '.php',
			'single.php',
		];
	}

	/**
	 * Remove AMP support from Digital Daily post types.
	 *
	 * @param array $post_types AMP-enabled post types.
	 * @return array
	 */
	public function remove_amp_support( array $post_types ): array {
		return array_diff(
			$post_types,
			[
				POST_TYPE,
				POST_TYPE_SPECIAL_EDITION_ARTICLE,
			]
		);
	}

	/**
	 * Maybe redirect Digital Daily archive.
	 *
	 * If there are no Digital Daily issues in WP, or if an archive is not
	 * desired, consider redirecting the visitor.
	 *
	 * @param WP $wp WP object after request is parsed.
	 */
	public function maybe_redirect_archive( WP $wp ): void {
		if ( POST_TYPE !== $wp->request ) {
			return;
		}

		$destination = apply_filters(
			'pmc_digital_daily_cpt_archive_redirect_destination',
			null
		);

		if ( empty( $destination ) ) {
			return;
		}

		wp_redirect(
			$destination,
			302,
			'pmc-digital-daily-cpt'
		);
		// Cannot cover termination of execution.
		exit; // @codeCoverageIgnore
	}
}
