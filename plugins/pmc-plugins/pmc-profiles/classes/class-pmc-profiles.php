<?php
namespace PMC\PMC_Profiles;

use \PMC\PMC_Profiles\Post_Type;
use \PMC\Global_Functions\Traits\Singleton;


/**
 * The admin-specific functionality of the plugin.
 */
class PMC_Profiles {

	use Singleton;

	/**
	 * Post type for profiles.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $profiles_post_type The Post type for the profiles.
	 */
	private $profiles_post_type;

	/**
	 * The Post type for the landing page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $landing_post_type The Post type for the landing page.
	 */
	private $landing_post_type;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	protected function __construct() {

		$this->profiles_post_type = Post_Type::get_instance()->get_profile_post_type_slug();
		$this->landing_post_type  = Post_Type::get_instance()->get_landing_page_post_type_slug();;

		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_filter( 'widgets_init', [ $this, 'register_sidebars' ] );
		add_action( 'init', [ $this, 'register_nav_menus' ] );

		add_filter( 'pmc_core_rest_api_data', [ $this, 'get_json_data' ], 10, 3 );
	}

	/**
	 * Register menu.
	 */
	public function register_nav_menus() {

		$menus = [
			'pmc_profiles_header' => esc_html__( 'PMC Profiles Header', 'pmc-profiles' ),
		];

		register_nav_menus( $menus );
	}

	/**
	 * Register sidebars.
	 *
	 * @return void
	 */
	public function register_sidebars() {

		register_sidebar(
			[
				'name'          => esc_html__( 'PMC Profiles Right Sidebar', 'pmc-profiles' ),
				'id'            => 'pmc_profiles_right',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);
	}

	/**
	 * Get Terms name list of given post ID.
	 *
	 * @param int    $post_id
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public static function get_term_list( $post_id, $taxonomy ) {

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( is_wp_error( $terms ) ) {
			return false;
		}

		if ( empty( $terms ) ) {
			return false;
		}

		$term_list = [];

		foreach ( $terms as $term ) {

			if ( true === apply_filters( 'pmc_profiles_term_exclude_parent', false ) ) {

				if ( empty( get_term_children( $term->term_id, $taxonomy ) ) ) {
					$term_list[] = $term->name;
				}
			} else {
				$term_list[] = $term->name;
			}
		}

		return $term_list;
	}

}
