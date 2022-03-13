<?php
/**
 * Class to add [pmc-labelled-badge] shortcode which renders a badge with custom label on frontend.
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2021-06-29
 */

namespace PMC\Frontend_Components\Badges;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;

class Label {

	use Singleton;

	const TAG = 'pmc-labelled-badge';

	/**
	 * @var array An array of badge styles which can be overridden via a filter
	 */
	protected $_badge_styles = [
		'badge-label-bg-color'            => '#333333',
		'badge-label-text-color'          => '#FFFFFF',
		'page-bg-color'                   => '#FFFFFF',
		'badge-label-text-font-family'    => 'Georgia,Times,Times New Roman,serif',
		'badge-label-text-font-size'      => '14px',
		'badge-label-text-font-weight'    => '700',
		'badge-label-text-letter-spacing' => 'normal',
		'badge-label-text-transform'      => 'uppercase',
	];

	/**
	 * Class constructor
	 */
	protected function __construct() {

		$this->_setup_hooks();
		$this->_register_shortcode();
	}

	/**
	 * Method to setup listeners on WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'maybe_enqueue_admin_stuff' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_stuff' ] );
		add_action( 'amp_post_template_css', [ $this, 'serve_assets_for_amp' ] );

		/*
		 * Filters
		 */
		add_filter( 'pmc_strip_shortcode', [ $this, 'get_preserved_shortcode' ], 10, 3 );

	}

	/**
	 * Method to register shortcode with WP
	 *
	 * @return void
	 */
	protected function _register_shortcode() : void {

		add_shortcode( static::TAG, [ $this, 'parse_shortcode' ] );

	}

	/**
	 * Method to get frontend styles
	 *
	 * @return array
	 */
	protected function _get_frontend_styles() : array {

		$styles = (array) apply_filters( 'pmc_frontend_components_badges_label_styles', $this->_badge_styles );
		$styles = PMC::parse_allowed_args( $styles, $this->_badge_styles );

		return $styles;

	}

	/**
	 * Method to check determine if admin stuff should be loaded on current admin page or not
	 *
	 * @return bool
	 */
	protected function _should_load_admin_stuff() : bool {

		$allowed_pages = [
			'post.php',
			'post-new.php',
		];

		$allowed_pages = (array) apply_filters( 'pmc_frontend_components_badges_label_allowed_admin_pages', $allowed_pages );

		if ( ! in_array( $GLOBALS['pagenow'], (array) $allowed_pages, true ) ) {
			return false;
		}

		if ( ! ( current_user_can( 'edit_posts' ) && get_user_option( 'rich_editing' ) === 'true' ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Method to enqueue frontend stuff
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function enqueue_stuff() : void {

		// Output style vars
		PMC::render_template(
			sprintf( '%s/templates/badges/label/frontend-style-vars.php', PMC_FRONTEND_COMPONENTS_ROOT ),
			[
				'styles' => $this->_get_frontend_styles(),
			],
			true
		);

		// Enqueue styles
		wp_enqueue_style(
			sprintf( '%s-styles', static::TAG ),
			sprintf( '%s/assets/build/css/badges/label/frontend.css', untrailingslashit( PMC_FRONTEND_COMPONENTS_URL ) ),
			[],
			PMC_FRONTEND_COMPONENTS_VERSION
		);

	}

	/**
	 * Method to inline assets on AMP pages
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function serve_assets_for_amp() : void {

		// Output style vars
		PMC::render_template(
			sprintf( '%s/templates/badges/label/frontend-style-vars.php', PMC_FRONTEND_COMPONENTS_ROOT ),
			[
				'styles' => $this->_get_frontend_styles(),
				'is_amp' => true,
			],
			true
		);

		// Output styles
		PMC::render_template(
			sprintf( '%s/assets/build/css/badges/label/frontend.css', PMC_FRONTEND_COMPONENTS_ROOT ),
			[],
			true
		);

	}

	/**
	 * Method to enqueue admin stuff
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function maybe_enqueue_admin_stuff() : void {

		if ( ! $this->_should_load_admin_stuff() ) {
			return;
		}

		add_filter( 'mce_buttons', [ $this, 'register_tinymce_button' ] );
		add_filter( 'mce_external_plugins', [ $this, 'register_tinymce_button_plugin' ] );

		PMC::render_template(
			sprintf( '%s/templates/badges/label/admin-ui.php', untrailingslashit( PMC_FRONTEND_COMPONENTS_ROOT ) ),
			[
				'data' => [
					'buttonTitle'  => __( 'Add Label', 'pmc-frontend-components' ),
					'modalTitle'   => __( 'Add Label Badge', 'pmc-frontend-components' ),
					'fieldLabel'   => __( 'Add Label Text', 'pmc-frontend-components' ),
					'buttonOk'     => __( 'Ok', 'pmc-frontend-components' ),
					'buttonCancel' => __( 'Cancel', 'pmc-frontend-components' ),
					'shortcodeTag' => static::TAG,
				],
			],
			true
		);

		wp_enqueue_style(
			sprintf( '%s-admin-styles', static::TAG ),
			sprintf( '%s/assets/build/css/badges/label/admin-ui.css', untrailingslashit( PMC_FRONTEND_COMPONENTS_URL ) ),
			[],
			PMC_FRONTEND_COMPONENTS_VERSION
		);

	}

	/**
	 * Method to register our custom button with TinyMCE Editor
	 *
	 * @param array $buttons
	 *
	 * @return array
	 */
	public function register_tinymce_button( array $buttons = [] ) : array {

		$buttons[] = 'pmcfcBadgeLabelButton';

		return $buttons;

	}

	/**
	 * Method to register our custom button JS with TinyMCE Editor
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function register_tinymce_button_plugin( array $plugins = [] ) : array {

		$plugins['pmcfcBadgeLabelButton'] = add_query_arg(
			[
				'ver' => PMC_FRONTEND_COMPONENTS_VERSION,
			],
			sprintf( '%s/assets/build/js/badges/label/admin-ui.js', untrailingslashit( PMC_FRONTEND_COMPONENTS_URL ) )
		);

		return $plugins;

	}

	/**
	 * Method to parse the shortcode
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function parse_shortcode( $atts = [], string $content = '' ) : string {

		$html = '';

		$content = wp_strip_all_tags( $content, true );
		$content = trim( $content );

		if ( empty( $content ) ) {
			return $html;
		}

		return PMC::render_template(
			sprintf( '%s/templates/badges/label/shortcode.php', PMC_FRONTEND_COMPONENTS_ROOT ),
			[
				'label' => $content,
			]
		);

	}

	/**
	 * Method to prevent our shortcode from being stripped out in feeds
	 *
	 * @param string $content
	 * @param string $current_shortcode
	 * @param string $originial_content
	 *
	 * @return string
	 */
	public function get_preserved_shortcode( string $content = '', string $current_shortcode = '', string $originial_content = '' ) : string {

		if ( is_feed() && static::TAG === $current_shortcode ) {
			$content = $originial_content;
		}

		return $content;

	}

}  // end class

//EOF
