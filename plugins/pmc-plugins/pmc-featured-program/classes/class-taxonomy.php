<?php
/**
 * Class taxonomy
 *
 * @package pmc-featured-program
 */


/**
 * Abstract class for taxonomy classes
 *
 * <pre>
 * class Taxonomy_Example extends Taxonomy {
 *
 *    const NAME = 'example_tax';
 *
 *    public $object_types = [ 'post' ];
 *
 *    protected $parent_file = 'taxonomy';
 *
 *    public function create_taxonomy() {
 *       register_taxonomy( self::NAME, $this->object_types, [
 *           'labels' => [
 *               'name'                  => __( 'Example Types', 'pmc-featured-program' ),
 *               'singular_name'         => __( 'Example Type', 'pmc-featured-program' ),
 *               'search_items'          => __( 'Search Example Types', 'pmc-featured-program' ),
 *               'popular_items'         => null,
 *               'all_items'             => __( 'All Example Types', 'pmc-featured-program' ),
 *               'parent_item'           => __( 'Parent Example Type', 'pmc-featured-program' ),
 *               'parent_item_colon'     => __( 'Parent Example Type', 'pmc-featured-program' ),
 *               'edit_item'             => __( 'Edit Example Type', 'pmc-featured-program' ),
 *               'view_item'             => __( 'View Example Type', 'pmc-featured-program' ),
 *               'update_item'           => __( 'Update Example Type', 'pmc-featured-program' ),
 *               'add_new_item'          => __( 'Add New Example Type', 'pmc-featured-program' ),
 *               'new_item_name'         => __( 'New Example Type Name', 'pmc-featured-program' ),
 *               'add_or_remove_items'   => __( 'Add or remove Example Types', 'pmc-featured-program' ),
 *               'choose_from_most_used' => __( 'Choose from most used Example Types', 'pmc-featured-program' ),
 *               'menu_name'             => __( 'Example Types', 'pmc-featured-program' ),
 *           ],
 *           'public' => false,
 *           'show_ui' => true,
 *           'show_admin_column' => true,
 *       ] );
 *    }
 *
 * }
 * </pre>
 *
 */

namespace PMC\Featured_Program;

/**
 * @codeCoverageIgnore Ignored in pmc-featured-program
 */
abstract class Taxonomy {

	/**
	 * Object types for this taxonomy
	 *
	 * @var array
	 */
	public $object_types = [];

	/**
	 * Parent file to which to move this taxonomy's menu item.
	 *
	 * @var string
	 */
	protected $parent_file;

	/**
	 * Taxonomy constructor.
	 */
	protected function __construct() {

		// Create the taxonomy.
		add_action( 'init', [ $this, 'create_taxonomy' ] );

		// Add taxonomy menu.
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		if ( $this->parent_file ) {
			add_action( 'admin_head', [ $this, 'activate_parent_menu' ] );
		}
	}

	/**
	 * Create the taxonomy.
	 */
	abstract public function create_taxonomy();

	/**
	 * Add this taxonomy as a submenu of some top-level page
	 */
	public function admin_menu() {

		$tax = get_taxonomy( $this->name );

		if ( empty( $this->parent_file ) ) {
			return;
		}

		add_submenu_page(
			$this->parent_file,
			$tax->labels->all_items,
			$tax->labels->menu_name,
			$tax->cap->manage_terms,
			'edit-tags.php?taxonomy=' . $this->name
		);
	}

	/**
	 * Highlight the parent menu if this submenu item is active.
	 */
	public function activate_parent_menu() {

		global $parent_file, $taxonomy; //phpcs:ignore
		if ( $this->name === $taxonomy ) {
			$parent_file = $this->parent_file; //phpcs:ignore
		}
	}
}
