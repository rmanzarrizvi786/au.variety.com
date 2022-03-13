<?php
namespace PMC\Zoninator_Extended;

use \PMC;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Init
 * Class to initialize plugin.
 *
 * @package pmc-zoninator-extended
 */
class Init {

	use Singleton;

	/**
	 * Plugin templates directory path.
	 *
	 * @access    private
	 * @var       string
	 * @since     1.0.0
	 */
	private $_template_dir = '';

	/**
	 * Function to initialize class.
	 *
	 * @access    protected
	 * @since     1.0.0
	 * @return    boolean
	 */
	protected function __construct() {

		// Main plugin directory path and URI.
		$this->_template_dir = untrailingslashit( PMC_ZONINATOR_EXTENDED_DIR . DIRECTORY_SEPARATOR . 'templates' );

		$this->_setup_actions();

		return true;
	}

	/**
	 * Function to setup all actions/filters.
	 *
	 * @access    private
	 * @since     1.0.0
	 * @return    void
	 */
	private function _setup_actions() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Action to add custom content on 'zoninator' admin page.
		add_action( 'toplevel_page_zoninator', array( $this, 'add_quick_post_edit_modal' ), 15 );

		// Filters.
		add_filter( 'heartbeat_received', array( $this, 'heartbeat_received' ), 10 , 3 );

	}

	/**
	 * Function to add post quick edit modal in `zoninator` admin page.
	 *
	 * @global    string   $pagenow
	 * @access    private
	 * @since     1.0.0
	 * @return    void
	 */
	public function add_quick_post_edit_modal() {
		global $pagenow;
		$current_page = ( ! empty( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
		$action       = ( ! empty( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false;

		if ( is_admin() && 'admin.php' === $pagenow && 'zoninator' === $current_page && 'edit' === $action ) {

			// Load quick post edit modal.
			echo PMC::render_template( $this->_template_dir . DIRECTORY_SEPARATOR . 'quick-post-edit-modal.php' );

		}
	}

	/**
	 * Function to setup all actions/filters.
	 *
	 * @access    public
	 * @since     1.0.0
	 * @return    void
	 */
	public function enqueue() {
		global $pagenow;
		$current_page = ( ! empty( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
		$action       = ( ! empty( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false;
		$zone_id      = ( ! empty( $_GET['zone_id'] ) ) ? sanitize_text_field( wp_unslash( $_GET['zone_id'] ) ) : false;
		$zone_id      = absint( $zone_id );

		$prefix = '.min';
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$prefix = '';
		}

		if ( is_admin() && 'admin.php' === $pagenow && 'zoninator' === $current_page && 'edit' === $action ) {
			wp_enqueue_style( 'jquery-ui', PMC_ZONINATOR_EXTENDED_URI . '/assets/css/jquery-ui.css' );
			wp_enqueue_style( 'pmc-admin-zoninator-extended', PMC_ZONINATOR_EXTENDED_URI . '/assets/css/admin-zoninator-extended' . $prefix . '.css' );
			wp_register_script( 'pmc-admin-zoninator-extended', PMC_ZONINATOR_EXTENDED_URI . '/assets/js/admin-zoninator-extended' . $prefix . '.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog', 'zoninator-js' ), false, true );
			wp_localize_script(
				'pmc-admin-zoninator-extended', 'pmc_admin_zoninator_options', array(
					'zone_id'      => $zone_id,
					'get_nonce'    => wp_create_nonce( 'pmc_zoninator_get_post-' . $zone_id ),
					'update_nonce' => wp_create_nonce( 'pmc_zoninator_update_post-' . $zone_id ),
					'security'     => wp_create_nonce( 'pmc_zoninator_manage_post' ),
				)
			);
			wp_enqueue_script( 'pmc-admin-zoninator-extended' );
		}
	}

	/**
	 * To add post lock status in heartbeat response data.
	 *
	 * @hook  heartbeat_received
	 *
	 * @param array  $response Heartbeat response.
	 * @param array  $data Heartbeat data.
	 * @param string $screen_id Screen id.
	 *
	 * @return array Heartbeat response.
	 */
	public function heartbeat_received( $response, $data, $screen_id ) {

		if ( 'toplevel_page_zoninator' !== $screen_id ) {
			return $response;
		}

		$post_ids = ( ! empty( $data['post_ids'] ) && is_array( $data['post_ids'] ) ) ? $data['post_ids'] : array();
		$post_ids = array_map( 'absint', $post_ids );

		$response['post_lock_status'] = array();

		foreach ( $post_ids as $post_id ) {

			$data = array();
			$lock_holder = wp_check_post_lock( $post_id );

			$data['post_id'] = absint( $post_id );
			$data['lock_holder'] = absint( $lock_holder );
			$data['message'] = '';

			if ( $lock_holder ) {
				$lock_holder = get_userdata( $lock_holder );
				$data['message'] = esc_html( sprintf( __( '%s is currently editing' ), $lock_holder->display_name ) );
			}

			$response['post_lock_status'][] = $data;
		}

		return $response;
	}

}
