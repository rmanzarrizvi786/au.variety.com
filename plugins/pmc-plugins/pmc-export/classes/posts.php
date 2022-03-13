<?php
/**
 * Export Posts report.
 * Main class for Posts report. Supported post-types are defined in the class.
 *
 * If you need to add new report fields ( e.g. SEO Titles ) to the report then simply add an entry in $_supported_reporting_fields
 * and add proper data handling in Stream_Csv_Posts::get_rows().
 */
namespace PMC\Export;
use PMC\Global_Functions\Traits\Singleton;

class Posts {

	use Singleton;

	const MENU_SLUG_EXPORT_POSTS = 'pmc-export-posts';

	const WORD_COUNT_META_SLUG       = '_pmc_word_count';
	const IMAGE_COUNT_META_SLUG      = '_pmc_image_count';
	const CATEGORIZATION_META_SLUG   = '_pmc_post_categorization';
	const ATTACHED_GALLERY_META_SLUG = 'pmc-gallery-linked-gallery'; // Defined in pmc-gallery-v4

	/**
	 * Supported post types.
	 *
	 * @var array
	 */
	private $_supported_post_types = array(
		'post',
		'pmc-gallery',
		'pmc-list',
		'pmc-video',
		'pmc-gift-guide',
		'pmc-lst-gallery',
		'pmc_top_video',
		'sk-baby-name',
	);

	/**
	 * Supported Reporting Data Fields.
	 *
	 * @var array
	 */
	private $_supported_reporting_fields = array(
		'Post ID',
		'URL',
		'Title',
		'Published Date',
		'Author',
		'Category + Sub Category',
		'Vertical',
		'Word Count',
		'Number of Image',
		'Attached Galleries',
		'Tasks Completed',
	);

	/**
	 * Default fields to include in the report.
	 *
	 * @var array
	 */
	private $_default_reporting_fields = array(
		'Post ID',
		'URL',
		'Title',
		'Published Date',
		'Author',
		'Category + Sub Category',
		'Vertical',
		'Word Count',
		'Number of Image',
		'Attached Galleries',
		'Tasks Completed',
	);

	protected function __construct() {

		add_action( 'init', [ $this, 'action_init' ] );

		// Setup word count, image count, categorization updates whenever a post is published or published post is updated. Need to hook later so it can capture all data properly.
		add_action( 'save_post', array( $this, 'action_save_post' ), 99 );
	}

	public function action_init() {

		// Only if user have properly permission may continue
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'admin_init', [ $this, 'action_admin_init' ] );
		add_action( 'admin_menu', [ $this, 'action_admin_menu' ] );

		// We want to activate the CSV download support for this report type.
		Stream_Csv_Posts::get_instance();

	}

	/**
	 * Save wordcount, image count and post categorization in post meta when a post is published or updated.
	 *
	 * @param int $post_id
	 */
	public function action_save_post( $post_id ) {

		$post = get_post( $post_id );

		if ( ! $post ) {
			return false;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			|| ! current_user_can( 'edit_post', $post->ID )
			|| wp_is_post_revision( $post->ID )
			|| 'publish' !== get_post_status( $post->ID )
			|| ! in_array( $post->post_type, (array) $this->_supported_post_types, true )
		) {
			return false;
		}

		// Get word count.
		$word_count = Helper::get_post_word_count( $post->ID );
		$meta_value = get_post_meta( $post->ID, self::WORD_COUNT_META_SLUG, true );

		if ( (int) $word_count !== (int) $meta_value ) {
			update_post_meta( $post->ID, self::WORD_COUNT_META_SLUG, (int) $word_count );
		}

		// Get image count.
		$image_count = Helper::get_image_count( $post->ID );
		$meta_value  = get_post_meta( $post->ID, self::IMAGE_COUNT_META_SLUG, true );

		if ( (int) $image_count !== (int) $meta_value ) {
			update_post_meta( $post->ID, self::IMAGE_COUNT_META_SLUG, (int) $image_count );
		}

		// Get taxonomy categorization.
		$categories = implode( ', ', Helper::get_post_taxonomy_categorization( $post, 'category' ) );
		$verticals  = implode( ', ', Helper::get_post_taxonomy_categorization( $post, 'vertical' ) );
		$meta_value = get_post_meta( $post->ID, static::CATEGORIZATION_META_SLUG, true );
		$json_value = wp_json_encode(
			[
				'category' => $categories,
				'vertical' => $verticals,
			]
		);

		if ( $meta_value !== $json_value ) {
			update_post_meta( $post->ID, static::CATEGORIZATION_META_SLUG, $json_value );
		}

	}

	public function action_admin_init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'action_admin_enqueue_scripts' ] );
	}

	public function action_admin_menu() {

		// Add our menu under Reporting main menu
		add_submenu_page( PMC_Export::MENU_SLUG, 'Export Posts', 'Export Posts', 'manage_options', static::MENU_SLUG_EXPORT_POSTS, [ $this, 'render_export_posts' ] );
	}

	public function action_admin_enqueue_scripts( $hook ) {

		// Enqueue assets only if it's concerned page.
		if ( 'reporting_page_' . static::MENU_SLUG_EXPORT_POSTS !== $hook ) {
			return;
		}

		// Enqueue chosen.
		\PMC::enqueue_chosen();
	}

	/**
	 * Helper function to get supported post types.
	 */
	public function get_supported_post_types() {

		// We can't use public = true as there are custom post type might be hidden but need to generate reports
		// Any hidden custom post type that is visible in the admin bar should be allow to generate the reports
		$post_types = get_post_types( array( 'show_in_admin_bar' => true ) );

		// Global white list these for all LOBs as long they are registered
		$post_types = array_intersect(
			$post_types,
			$this->_supported_post_types
		);

		// allow theme to override this post type list, used by template form drop down
		return apply_filters( 'pmc_export_posts_post_types', $post_types );
	}

	public function get_default_reporting_fields() {
		return $this->_default_reporting_fields;
	}

	/**
	 * Helper to get the supported reporting fields.
	 */
	public function get_supported_reporting_fields() {
		return $this->_supported_reporting_fields;
	}

	/**
	 * Function responsible to render the admin UI input form
	 */
	public function render_export_posts() {
		global $wpdb;

		$post_types   = [];
		$dates_filter = [];

		$items = $this->get_supported_post_types();

		// We want to use the friendly name for drop down input selection
		foreach ( $items as $item ) {
			$post_types[ $item ] = get_post_type_object( $item )->label;
		}

		// We need to apply the post type $items through $wpdb->prepare to properly
		// escape the string to be use in $sql statements below
		$sql_safe_items = array_map(
			function( $value ) {
					global $wpdb;
					return $wpdb->prepare( '%s', $value );
			},
			(array) $items
		);

		// We need to use direct SQl to find out the date range we have data for the filtered post types
		$sql = "SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts 
			WHERE post_status = 'publish'
			AND post_type in ( " . join( ',', $sql_safe_items ) . ')
			order by 1 desc ,2 desc
			';

		$items    = $wpdb->get_results( $sql ); // phpcs:ignore
		$old_year = 0;

		// Generating the date range filter for the input drop down selection
		foreach ( $items as $item ) {
			if ( $old_year !== $item->year ) {
				$old_year                                       = $item->year;
				$dates_filter[ sprintf( '%04d', $item->year ) ] = sprintf( 'Year of %04d', $item->year );
			}
			$dates_filter[ sprintf( '%04d%02d', $item->year, $item->month ) ] = sprintf( 'Month of %s', gmdate( 'F Y', strtotime( sprintf( '%d-%d-1', $item->year, $item->month ) ) ) );
		}

		$variables = [
			'title'                   => 'Exporting posts report',
			'post_types'              => $post_types,
			'dates_filter'            => $dates_filter,
			'reporting_fields_filter' => $this->get_supported_reporting_fields(),
			'default_fields'          => $this->get_default_reporting_fields(),
		];

		\PMC::render_template( sprintf( '%s/templates/posts-admin-ui.php', PMC_EXPORT_PLUGIN_DIR ), $variables, true );
	}

}
