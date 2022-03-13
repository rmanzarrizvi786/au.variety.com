<?php
/**
 * Class to setup admin UI
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */

namespace PMC\Post_Reviewer;


use \PMC;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Global_Functions\Metabox;
use \WP_Post;


class Admin_UI {

	use Singleton;

	const PAGE_SLUG   = 'pmc-post-reviewer';
	const PAGE_ACTION = 'review';
	const PAGE_PARENT = 'tools.php';

	const PAGE_TITLE = 'Post Reviewer';
	const MENU_TITLE = 'PMC Post Reviewer';

	const CAPABILITY = 'edit_others_posts';    // editor role & above

	protected $_pagehook;
	protected $_page_url;
	protected $_post;

	/**
	 * @var \PMC\Post_Reviewer\Config
	 */
	protected $_config;

	/**
	 * @var array An array of metabox listings
	 */
	protected $_metaboxes = [

		'authors'            => [
			'id'       => 'authors',
			'title'    => 'Authors',
			'context'  => 'normal',
			'priority' => 'core',
			'css'      => '',
			'template' => '',
		],
		'linked-gallery'     => [
			'id'       => 'linked-gallery',
			'title'    => 'Linked Gallery',
			'context'  => 'normal',
			'priority' => 'core',
			'css'      => '',
			'template' => '',
		],
		'seo'                => [
			'id'       => 'seo',
			'title'    => 'SEO',
			'context'  => 'normal',
			'priority' => 'core',
			'css'      => '',
			'template' => '',
		],
		'canonical-override' => [
			'id'       => 'canonical-override',
			'title'    => 'Canonical Override',
			'context'  => 'normal',
			'priority' => 'core',
			'css'      => '',
			'template' => '',
		],
		'post-meta'          => [
			'id'       => 'post-meta',
			'title'    => 'Post Meta',
			'context'  => 'normal',
			'priority' => 'core',
			'css'      => 'post-meta',
			'template' => '',
		],
		'post-info'          => [
			'id'       => 'post-info',
			'title'    => 'Post Info',
			'context'  => 'side',
			'priority' => 'core',
			'css'      => '',
			'template' => '',
		],
		'featured-video'     => [
			'id'       => 'featured-video',
			'title'    => 'Featured Video',
			'context'  => 'side',
			'priority' => 'core',
			'css'      => '',
			'template' => '',
		],
		'featured-image'     => [
			'id'       => 'featured-image',
			'title'    => 'Featured Image',
			'context'  => 'side',
			'priority' => 'core',
			'css'      => '',
			'template' => '',
		],

	];

	/**
	 * class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		$this->_config = Config::get_instance();

		$this->_setup_hooks();

	}

	/**
	 * Method to setup hooks and listeners
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */
		add_action( 'admin_menu', [ $this, 'add_admin_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		/*
		 * Filters
		 */
		add_filter( 'post_row_actions', [ $this, 'maybe_add_review_quicklink' ], 10, 2 );
		add_filter( 'page_row_actions', [ $this, 'maybe_add_review_quicklink' ], 10, 2 );
		add_filter( 'screen_layout_columns', [ $this, 'maybe_set_screen_layout_columns' ], 10, 2 );
		add_filter( 'submenu_file', [ $this, 'hide_admin_menu' ] );

	}

	/**
	 * Conditional function to check if a post is a draft or not
	 *
	 * @param int $post_id
	 * @return bool Returns FALSE if post is not draft or pending review else TRUE
	 */
	public function is_post_draft( int $post_id ) : bool {

		if ( empty( $post_id ) || intval( $post_id ) < 1 ) {
			return true;
		}

		$post_statuses = [ 'draft', 'pending' ];

		if ( in_array( get_post_status( $post_id ), (array) $post_statuses, true ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Called by 'admin_enqueue_scripts', this method enqueues required JS
	 *
	 * @param string $hook
	 * @return bool  Returns TRUE if assets are enqueued else FALSE
	 */
	public function enqueue_assets( string $hook = '' ) : bool {

		if ( 'tools_page_pmc-post-reviewer' !== $hook ) {
			return false;
		}

		// ensure, that the needed javascripts been loaded to allow drag/drop,
		// expand/collapse and hide/show of boxes
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );

		wp_enqueue_style(
			sprintf( '%s-css', self::PAGE_SLUG ),
			sprintf( '%s/assets/css/admin-ui.css', untrailingslashit( PMC_POST_REVIEWER_URL ) )
		);

		return true;

	}

	/**
	 * @param array    $actions
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	public function maybe_add_review_quicklink( array $actions, WP_Post $post ) : array {

		if ( empty( $post->post_type ) || intval( $post->ID ) < 1 ) {

			// Bad post object, bail out
			return $actions;

		}

		// check if post type & user cap is supported or not
		if (
			! $this->_config->is_post_type_supported( $post->post_type )
			|| ! $this->_config->is_current_user_allowed_on_type( $post->post_type )
		) {
			// Post type is not supported or current user not allowed
			// bail out
			return $actions;
		}

		$review_url = add_query_arg(
			[
				'action'      => self::PAGE_ACTION,
				'post_id'     => $post->ID,
				'parent_menu' => ( ! empty( $GLOBALS['parent_file'] ) ) ? $GLOBALS['parent_file'] : 'edit.php',
				'sub_menu'    => ( ! empty( $GLOBALS['submenu_file'] ) ) ? $GLOBALS['submenu_file'] : 'edit.php',
			],
			$this->_page_url
		);

		$actions['review'] = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $review_url ),
			esc_attr(
				sprintf(
					/* translators: Post title */
					__( 'Review &#8220;%s&#8221;', 'pmc-post-reviewer' ),
					_draft_or_post_title( $post )
				)
			),
			esc_html__( 'Review', 'pmc-post-reviewer' )
		);

		return $actions;

	}

	/**
	 * Method to set screen columns to 2 on post reviewer screen
	 *
	 * @param array  $columns
	 * @param string $screen
	 *
	 * @return array
	 */
	public function maybe_set_screen_layout_columns( $columns, $screen = '' ) : array {

		if ( ! is_array( $columns ) ) {
			$columns = [];
		}

		if ( $screen === $this->_pagehook ) {
			$columns[ $this->_pagehook ] = 2;
		}

		return $columns;

	}

	/**
	 * Called on 'admin_menu' hook, this method adds the wp-admin page for post review
	 *
	 * @return void
	 */
	public function add_admin_page() : void {

		$this->_pagehook = add_submenu_page(
			self::PAGE_PARENT,
			self::PAGE_TITLE,           //$page_title
			self::MENU_TITLE,           //$menu_title
			self::CAPABILITY,           //$capability
			self::PAGE_SLUG,            //$menu_slug
			[ $this, 'render_page' ]    //$function
		);

		$this->_page_url = admin_url(
			sprintf(
				'%s?page=%s',
				self::PAGE_PARENT,
				self::PAGE_SLUG
			)
		);

		//this callback gets called prior to page render
		add_action( 'load-' . $this->_pagehook, [ $this, 'add_metaboxes' ] );

	}

	/**
	 * Method to hide the admin menu page for this plugin to make it inaccessible from menu
	 *
	 * @note Do not set this method's return type to string since this is hooked to a WP filter
	 * and another listener can return weird value which would cause fatal error here.
	 *
	 * @param string $submenu_file
	 *
	 * @return string
	 */
	public function hide_admin_menu( $submenu_file ) {

		// Remove our actual sub-menu page from nav menu
		// since we don't want this page to be accessible directly
		remove_submenu_page( self::PAGE_PARENT, self::PAGE_SLUG );

		$current_screen = get_current_screen();

		if ( empty( $current_screen->id ) || $current_screen->id !== $this->_pagehook ) {
			return $submenu_file;
		}

		/*
		 * Current page is post review page, so lets highlight the
		 * menu of the post type of post which is being reviewed
		 */

		$from_parent = PMC::filter_input( INPUT_GET, 'parent_menu', FILTER_SANITIZE_STRING );
		$from_screen = PMC::filter_input( INPUT_GET, 'sub_menu', FILTER_SANITIZE_STRING );

		if ( ! empty( $from_parent ) && ! empty( $from_screen ) ) {
			$GLOBALS['self']        = $from_parent;
			$GLOBALS['parent_file'] = $from_parent;
			$GLOBALS['plugin_page'] = $from_screen;
			$submenu_file           = $from_screen;
		}

		return $submenu_file;

	}

	/**
	 * Method which renders the wp-admin page for post review UI
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function render_page() : void {

		$page    = PMC::filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$action  = PMC::filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$post_id = PMC::filter_input( INPUT_GET, 'post_id', FILTER_VALIDATE_INT );

		$template = '';
		$vars     = [];

		if (
			self::PAGE_SLUG !== $page
			|| self::PAGE_ACTION !== $action
			|| intval( $post_id ) < 1
		) {

			$template = 'admin-ui-error';
			$vars     = [
				'admin_ui' => $this,
				'error'    => __( 'Invalid request', 'pmc-post-reviewer' ),
			];

		} else {

			$post = get_post( $post_id );

			if ( empty( $post ) || is_wp_error( $post ) || ! is_a( $post, '\WP_Post' ) ) {

				$template = 'admin-ui-error';
				$vars     = [
					'admin_ui' => $this,
					'error'    => __( 'Post ID is invalid, no post exists for this ID', 'pmc-post-reviewer' ),
				];

			} elseif (
				! $this->_config->is_post_type_supported( $post->post_type )
				|| ! $this->_config->is_current_user_allowed_on_type( $post->post_type )
			) {

				$template = 'admin-ui-error';
				$vars     = [
					'admin_ui' => $this,
					'error'    => __( 'Post review is not enabled for this post type', 'pmc-post-reviewer' ),
				];

			} else {

				$this->_post = $post;
				$template    = 'admin-ui';
				$vars        = [
					'admin_ui' => $this,
					'post'     => $post,
					'pagehook' => $this->_pagehook,
				];

			}

		}

		if ( ! empty( $template ) ) {

			$template = sprintf( '%s/templates/%s.php', PMC_POST_REVIEWER_ROOT, $template );
			$template = apply_filters(
				'pmc_post_reviewer_admin_ui_render_page_template',
				$template,
				$vars,
				$post_id
			);

			if ( ! PMC::is_file_path_valid( $template ) ) {
				return;
			}

			PMC::render_template( $template, $vars, true );

		}

	}

	/**
	 * Method to add all the metaboxes for the post review page
	 *
	 * @return bool Return TRUE on success else FALSE
	 * @throws \ErrorException
	 */
	public function add_metaboxes() : bool {

		$taxonomies = $this->_config->get_taxonomies();

		if ( ! empty( $taxonomies ) ) {

			/*
			 * Separating these out to add them after taxonomy metaboxes,
			 * otherwise these would show up above the taxonomy metaboxes
			 */
			$bottom_portion = [
				'featured-video' => $this->_metaboxes['featured-video'],
				'featured-image' => $this->_metaboxes['featured-image'],
			];

			unset( $this->_metaboxes['featured-video'], $this->_metaboxes['featured-image'] );

			foreach ( $taxonomies as $taxonomy => $mb_title ) {

				$this->_metaboxes[ $taxonomy ] = [
					'id'       => $taxonomy,
					'title'    => ucwords( $mb_title ),
					'context'  => 'side',
					'priority' => 'core',
					'css'      => '',
					'template' => '',
				];

			}

			$this->_metaboxes = array_merge( $this->_metaboxes, $bottom_portion );

			unset( $bottom_portion );

		}

		$this->_metaboxes = apply_filters( 'pmc_post_reviewer_admin_ui_add_metaboxes', $this->_metaboxes );

		if ( ! is_array( $this->_metaboxes ) ) {
			return PMC::maybe_throw_exception( '"pmc_post_reviewer_admin_ui_add_metaboxes" filter expects an array' );
		}

		$this->_metaboxes = array_filter( $this->_metaboxes );

		if ( empty( $this->_metaboxes ) || ! PMC::is_associative_array( $this->_metaboxes ) ) {
			return PMC::maybe_throw_exception( '"pmc_post_reviewer_admin_ui_add_metaboxes" filter expects a non-empty associative array' );
		}

		try {

			foreach ( $this->_metaboxes as $metabox ) {

				Metabox::create( sprintf( '%s-mb-%s', self::PAGE_SLUG, $metabox['id'] ) )
						->having_title( $metabox['title'] )
						->on_screen( $this->_pagehook )
						->in_context( $metabox['context'] )
						->of_priority( $metabox['priority'] )
						->render_via( [ $this, 'render_metabox' ], [ $metabox['id'] ] )
						->with_css_class( $metabox['css'] )
						->add();

			}

		} catch ( \ErrorException $e ) {

			if ( ! PMC::is_production() ) {
				// not production environment
				// re-throw the exception
				throw $e;
			}

		}

		return true;

	}

	/**
	 * Method which renders the metabox UIs
	 *
	 * @param string $mb_type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function render_metabox( string $mb_type ) : void {

		if ( empty( $this->_metaboxes[ $mb_type ] ) ) {
			return;
		}

		$template = '';

		if ( ! empty( $this->_metaboxes[ $mb_type ]['template'] ) ) {
			$template = $this->_metaboxes[ $mb_type ]['template'];
		}

		if ( empty( $template ) ) {

			$template = sprintf(
				'%s/templates/metaboxes/admin-ui-%s.php',
				PMC_POST_REVIEWER_ROOT,
				strtolower( str_replace( '_', '-', sanitize_title( $mb_type ) ) )
			);

		}

		if ( ! PMC::is_file_path_valid( $template ) ) {
			return;
		}

		PMC::render_template(
			$template,
			[
				'admin_ui' => $this,
				'post'     => $this->_post,
				'pagehook' => $this->_pagehook,
			],
			true
		);

	}

}    // end class



//EOF
