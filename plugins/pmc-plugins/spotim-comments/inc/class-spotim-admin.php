<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SpotIM_Admin
 *
 * Plugin settings page.
 *
 * @since 1.0.2
 */
class SpotIM_Admin {

    /**
     * Options
     *
     * @since  1.0.2
     *
     * @access private
     * @static
     *
     * @var SpotIM_Options
     */
    private static $options;

    /**
     * Launch
     *
     * @since  2.0.0
     *
     * @access public
     *
     * @param SpotIM_Options $options Plugin options.
     *
     * @return void
     */
    public function __construct( $options ) {
        self::$options = $options;
        new SpotIM_Meta_Box( $options );

        add_action( 'admin_menu', array( __CLASS__, 'create_admin_menu' ), 20 );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
        add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
        add_action( 'wp_ajax_start_import', array( __CLASS__, 'import_callback' ) );
        add_action( 'wp_ajax_cancel_import', array( __CLASS__, 'cancel_import_callback' ) );

    }

    /**
     * Admin Notice
     *
     * @since  4.3.0
     *
     * @access public
     * @static
     *
     * @return void
     */
    public static function admin_notice() {

        if ( 'closed' === get_default_comment_status() ) {
            printf(
                esc_html__( '%1$sTo properly run %2$sSpot.IM%3$s please visit your sites %4$sDiscussion Settings%5$s and turn on "%2$sAllow people to post comments on new articles%3$s". %6$s', 'spotim-comments' ),
                '<div class="notice notice-warning"><p>',
                '<strong>',
                '</strong>',
                '<a href="' . esc_url( admin_url( 'options-discussion.php' ) ) . '">',
                '</a>',
                '</p></div>'
            );
        }

    }

    /**
     * Admin Assets
     *
     * @since  3.0.0
     *
     * @access public
     * @static
     *
     * @param string $hook The current admin page.
     *
     * @return void
     */
    public static function admin_assets( $hook ) {
        if ( 'toplevel_page_wp-spotim-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'admin_stylesheet', self::$options->require_stylesheet( 'admin.css', true ) );
        wp_enqueue_script( 'admin_javascript', self::$options->require_javascript( 'admin.js', true ), array( 'jquery' ) );

        $nonce = wp_create_nonce( 'sync_nonce' );

        wp_localize_script( 'admin_javascript', 'spotimVariables', array(
            'pageNumber'          => self::$options->get( 'page_number' ),
            'sync_nonce'          => $nonce,
            'errorMessage'        => esc_html__( 'Oops something got wrong. Please lower your amount of Posts Per Request and try again or send us an email to support@spot.im.', 'spotim-comments' ),
            'cancelImportMessage' => esc_html__( 'Cancel importing...', 'spotim-comments' )
        ) );
    }

    /**
     * Admin Menu
     *
     * @since  1.0.2
     *
     * @access public
     * @static
     *
     * @return void
     */
    public static function create_admin_menu() {

        /**
         * User capability to display Spot.IM menu.
         *
         * Allows developers to filter the required capability to display Spot.IM settings.
         *
         * @since 4.0.4
         */
        $capability = apply_filters( 'spotim_menu_display_capability', 'manage_options' );

        $menu_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTciIHZpZXdCb3g9IjAgMCAxNiAxNyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48dGl0bGU+Y2hhdCBjb3B5PC90aXRsZT48cGF0aCBkPSJNLjc0IDE1LjkxbC42MzQtMi42MTVjLjA1Ni0uMjMuMDEtLjQ4LS4xMy0uNzA3Qy0xLjg0NyA3LjU3OCAxLjE0NSAxLjAzIDYuNjY1LjExYzUuMzg2LS44OTYgMTAuMDkgMy43OTMgOS4yMzMgOS40MjItLjc4NiA1LjE2Ny02LjE5NCA4LjMxLTEwLjk3IDYuMjYtLjI1LS4xMS0uNTE4LS4xMS0uNzM0LS4wMDNMMS45NCAxNi45MWMtLjY1LjMyMi0xLjM3My0uMjc3LTEuMi0xem0yLjE5LTQuMzFjLjIzLjM3My4zLjguMjA2IDEuMjA1TDIuNjEzIDE1bDEuODU3LS45NGMuMzczLS4xOS44Mi0uMTk1IDEuMjMtLjAxNiAzLjU3IDEuNTU4IDcuNjM1LS44MjIgOC4yMjUtNC43Ny42MzQtNC4yNDUtMi44MjctNy44ODItNi45My03LjE5QzIuODI1IDIuNzk1LjYzIDcuODAyIDIuOTMgMTEuNnoiIGZpbGw9IiNGRkYiIGZpbGwtcnVsZT0iZXZlbm9kZCIvPjwvc3ZnPg==';

        add_menu_page(
            esc_html__( 'Spot.IM Settings', 'spotim-comments' ), // Page title
            esc_html__( 'Spot.IM', 'spotim-comments' ),          // Menu title
            $capability,
            self::$options->slug,
            array( __CLASS__, 'admin_page_callback' ),
            $menu_icon
        );

    }

    /**
     * Register Settings
     *
     * @since  1.0.2
     *
     * @access public
     * @static
     *
     * @return void
     */
    public static function register_settings() {
        $settings_fields = new SpotIM_Settings_Fields( self::$options );
        $settings_fields->register_settings();

        // Register settings fields only for the active tab
        switch ( self::$options->active_tab ) {
            case 'import':
                $settings_fields->register_import_section();
                break;
            case 'advanced':
                $settings_fields->register_advanced_section();
                break;
            case 'display':
                $settings_fields->register_display_section();
                break;
            case 'general':
            default:
                $settings_fields->register_general_section();
                break;
        }
    }

    /**
     * Admin Page Callback
     *
     * @since  1.0.2
     *
     * @access public
     * @static
     *
     * @return void
     */
    public static function admin_page_callback() {
        self::$options->require_template( 'admin-template.php' );
    }

    /**
     * Import Callback
     *
     * @since  3.0.0
     *
     * @access public
     * @static
     *
     * @return void
     */
    public static function import_callback() {

        check_ajax_referer( 'sync_nonce', 'security' );

        $import = new SpotIM_Import( self::$options );

        $spot_id           = filter_input( INPUT_POST, 'spotim_spot_id', FILTER_SANITIZE_STRING );
        $import_token      = filter_input( INPUT_POST, 'spotim_import_token', FILTER_SANITIZE_STRING );
        $page_number       = filter_input( INPUT_POST, 'spotim_page_number', FILTER_SANITIZE_NUMBER_INT );
        $force             = filter_input( INPUT_POST, 'force', FILTER_SANITIZE_STRING );
        $posts_per_request = filter_input( INPUT_POST, 'spotim_posts_per_request', FILTER_SANITIZE_NUMBER_INT );

        // check for spot id
        if ( empty( $spot_id ) ) {
            $import->response( array(
                'status'  => 'error',
                'message' => esc_html__( 'Spot ID is missing.', 'spotim-comments' )
            ) );

            // check for import token
        } else if ( empty( $import_token ) ) {
            $import->response( array(
                'status'  => 'error',
                'message' => esc_html__( 'Import token is missing.', 'spotim-comments' )
            ) );

            //  else start the comments importing process
        } else {
            $page_number = ( ! empty( $page_number ) ) ? absint( $page_number ) : 0;
            $force       = ( ! empty( $force ) ) ? true : false;

            if ( ! empty( $posts_per_request ) ) {
                $posts_per_request = absint( $posts_per_request );
                $posts_per_request = ( 0 === $posts_per_request ) ? 1 : $posts_per_request;
            } else {
                $posts_per_request = 1;
            }

            $import->start( $spot_id, $import_token, $page_number, $posts_per_request, $force );
        }
    }

    /**
     * Cancel Import Callback
     *
     * @since  3.0.0
     *
     * @access public
     * @static
     *
     * @return void
     */
    public static function cancel_import_callback() {

        check_ajax_referer( 'sync_nonce', 'security' );

        $import      = new SpotIM_Import( self::$options );
        $page_number = isset( $_POST['spotim_page_number'] ) ? absint( $_POST['spotim_page_number'] ) : 0; // WPCS: input var ok.

        update_option( "wp-spotim-settings_total_changed_posts", null );
        self::$options->update( 'page_number', $page_number );
        self::$options->reset( 'is_force_sync' );

        $import->response( array(
            'status' => 'cancel'
        ) );
    }

}
