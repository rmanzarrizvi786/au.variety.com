<?php
namespace PMC\Gift_Guide;

use \PMC\Global_Functions\Traits\Singleton;

class Common {

	use Singleton;

	const POST_SLUG = 'pmc-gift-guide';
	const TAXONOMY_SLUG = 'pmc-gift-guide-tax';

	/**
	 * Constructor
	 */
	protected function __construct() {
		add_filter( 'init', array( $this, 'init' ) );
		add_filter( 'template_include', array( $this, 'include_taxonomy_template' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'simple_page_ordering_is_sortable', array( $this, 'filter_simple_page_ordering_is_sortable' ), 20, 2 );
		add_action( 'fm_post_' . self::POST_SLUG, array( $this, 'fm_fields' ) );
		add_action( 'fm_term_' . self::TAXONOMY_SLUG, array( $this, 'fm_fields' ) );
		add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 );
		add_action( 'after_setup_theme', array( $this, 'define_image_sizes' ) );
		add_action( 'widgets_init', array( $this, 'register_sidebar' ) );
		add_action( 'restrict_manage_posts', [ $this, 'add_term_filter' ] );
		add_filter( 'pmc_sitemaps_taxonomy_whitelist', [ $this, 'whitelist_taxonomy_for_sitemaps' ] );
	}

	public function define_image_sizes() {
		add_image_size( 'pmc-gift-large', 735, 504, true );
		add_image_size( 'pmc-gift-medium', 546, 374, true );
	}


	public function init() {

		$labels = array(
			'name'               => 'Gift Guides',
			'singular_name'      => 'Gift Guide',
			'add_new_item'       => 'Add New Gift Guide',
			'edit_item'          => 'Edit Gift Guide',
			'new_item'           => 'New Gift Guide',
			'view_item'          => 'View Gift Guide',
			'search_items'       => 'Search Gift Guide',
			'not_found'          => 'No Gift Guides found.',
			'not_found_in_trash' => 'No Gift Guides found in Trash.',
			'all_items'          => 'Gift Guides',
		);

		register_post_type( self::POST_SLUG, array(
			'labels'       => $labels,
			'public'       => false,
			'show_ui'      => true,
			'supports'     => array(
				'title',
				'editor',
				'author',
				'revisions',
				'thumbnail',
				'excerpt',
				'page-attributes',
				'category',
			),
			'hierarchical' => true,
			'rewrite'      => false,
		) );

		$labels = array(
			'name'               => 'Gift Guide Taxonomies',
			'singular_name'      => 'Gift Guide Taxonomy',
			'add_new_item'       => 'Add New Gift Guide',
			'edit_item'          => 'Edit Gift Guide',
			'new_item'           => 'New Gift Guide',
			'view_item'          => 'View Gift Guide',
			'search_items'       => 'Search Gift Guide',
			'not_found'          => 'No Gift Guides found.',
			'not_found_in_trash' => 'No Gift Guides found in Trash.',
			'all_items'          => 'Gift Guides',
		);

		register_taxonomy( self::TAXONOMY_SLUG, self::POST_SLUG, array(
			'labels'       => $labels,
			'public'       => true,
			'show_ui'      => true,
			'rewrite'      => array( 'slug' => 'gift-guide' ),
			'hierarchical' => true,
		) );

		register_nav_menu( 'pmc-gift-guide-menu', 'Gift Guide Menu' );

	}

	public function include_taxonomy_template( $template ) {

		if ( is_tax( self::TAXONOMY_SLUG ) ) {
			$template = dirname( __DIR__ ) . '/templates/taxonomy-gift-guide.php';

			return apply_filters( 'pmc_gift_guide_template', $template );
		}

		return $template;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function pre_get_posts( $query ) {
		if ( $query->is_tax( self::TAXONOMY_SLUG ) && $query->is_main_query() ) {
			$query->set( 'post_type', self::POST_SLUG );
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'asc' );
			$query->set( 'posts_per_page', '50' );
		}
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'slider', plugins_url( 'assets/css/gift-guide.css', __DIR__ ) );
	}

	public function filter_simple_page_ordering_is_sortable( $sortable, $post_type = '' ) {
		if ( self::POST_SLUG === $post_type ) {
			return true;
		}

		return $sortable;
	}

	public function fm_fields() {
		if ( ! class_exists( 'Fieldmanager_Group' ) ) {
			return;
		}
		$fm = new \Fieldmanager_Group( array(
			'name'     => 'gift_info',
			'children' => array(
				'price'    => new \Fieldmanager_Textfield( 'Price' ),
				'retailer' => new \Fieldmanager_Textfield( 'Retailer' ),
				'link'     => new \Fieldmanager_Link( 'Link' ),
				'featured' => new \Fieldmanager_Checkbox( 'Featured' ),
			),
		) );

		$fm->add_meta_box( 'Gift Information', array( self::POST_SLUG ) );

		$fm_tax = new \Fieldmanager_Group( array(
			'name'     => 'gift_info',
			'children' => array(
				'featured_image' => new \Fieldmanager_Media( 'Featured Image' ),

			),
		) );

		$fm_tax->add_term_form( 'Gift Information', array( self::TAXONOMY_SLUG ) );

	}

	public function get_data( $id = '', $key = '' ) {
		$post_data = get_post_meta( $id, 'gift_info', true );
		if ( ! empty( $post_data[ $key ] ) ) {
			return $post_data[ $key ];
		}
		$term_data = fm_get_term_meta( $id, self::TAXONOMY_SLUG, 'gift_info', true );
		if ( ! empty( $term_data[ $key ] ) ) {
			return $term_data[ $key ];
		}
	}

	public function post_class( $classes, $class, $post_id ) {
		if ( ! empty( $this->get_data( $post_id, 'featured' ) ) ) {
			$classes[] = 'featured';
		}

		return $classes;
	}

	public function get_title() {
		global $page, $paged;

		$page_title = wp_title( '', false, 'right' );
		if ( ! empty( $page_title ) ) {
			$page_title .= ' | ';
		}

		// Add the blog name.
		$page_title .= get_bloginfo( 'name' );

		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$page_title .= " | $site_description";
		}

		// Add a page number if necessary:
		if ( $paged >= 2 || $page >= 2 ) {
			$page_title .= ' - ' . sprintf( __( 'Page %s', 'pmc-gift-guide' ), max( $paged, $page ) );
		}

		return $page_title;
	}

	public function register_sidebar() {

		register_sidebar( array(
			'name'          => 'PMC Gift Guide Sidebar',
			'id'            => 'pmc-gift-guide',
			'description'   => 'Widgets in this area will be shown below header on gift guide template.',
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
			'after_widget'  => '</li>',
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>',
		) );
	}

	/**
	 * To add term filter for gift guide post type.
	 *
	 * @param string $post_type Post type.
	 *
	 * @return void
	 */
	public function add_term_filter( $post_type ) {

		if ( empty( $post_type ) || self::POST_SLUG !== $post_type ) {
			return;
		}

		$allow_taxonomies_in_filter = apply_filters( 'pmc_gift_guide_allow_taxonomies_in_filter', [] );

		if ( empty( $allow_taxonomies_in_filter ) || ! is_array( $allow_taxonomies_in_filter ) ) {
			return;
		}

		$allow_taxonomies_in_filter = array_filter( $allow_taxonomies_in_filter, 'taxonomy_exists' );

		foreach ( $allow_taxonomies_in_filter as $taxonomy ) {
			$this->_render_taxonomy_filter_markup( $taxonomy );
		}

	}

	/**
	 * To render filter for each taxonomy.
	 *
	 * @param string $taxonomy registered taxonomy.
	 *
	 * @return void
	 */
	protected function _render_taxonomy_filter_markup( $taxonomy ) {

		if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$args = [
			'taxonomy'        => $taxonomy,
			'suppress_filter' => true,
			'hide_empty'      => false,
		];

		$terms = get_terms( $args );

		if ( empty( $terms ) || is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return;
		}

		$selected = \PMC::filter_input( INPUT_GET, $taxonomy, FILTER_SANITIZE_STRING );
		$selected = ( ! empty( $selected ) ) ? $selected : false;

		\PMC::render_template( sprintf( '%s/templates/admin-taxonomy-filter.php', untrailingslashit( PMC_GIFT_GUIDE_DIR ) ), [
			'taxonomy' => $taxonomy,
			'terms'    => $terms,
			'selected' => $selected,
		], true );

	}

	/**
	 * Add/Remove taxonomy for sitemaps.
	 *
	 * @param  array $taxonomies List allowed taxonomy in site maps.
	 *
	 * @return array List allowed taxonomy in site maps.
	 */
	public function whitelist_taxonomy_for_sitemaps( $taxonomies ) {

		$taxonomies = ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) ? $taxonomies : [];

		$taxonomies = array_merge(
			$taxonomies,
			[
				self::TAXONOMY_SLUG,
			]
		);

		$taxonomies = array_unique( array_values( $taxonomies ) );

		return $taxonomies;
	}

}

//EOF
