<?php
/**
 * Class responsible for creating admin UI
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-08-11
 */

namespace PMC\Ceros_Embeds;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;


class Admin {

	use Singleton;

	const ID             = 'pmc_ceros_embeds';
	const MIN_CAPABILITY = 'publish_posts';
	const MENU_SLUG      = 'pmc-ceros-code-converter';

	protected $_page_title = '';
	protected $_menu_title = '';
	protected $_pagehook   = '';

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore This is class constructor. Method calls here have their own individual tests.
	 */
	protected function __construct() {
		$this->_setup_vars();
		$this->_setup_hooks();
	}

	/**
	 * Method to set up class vars with L10n strings
	 *
	 * @return void
	 */
	protected function _setup_vars() : void {

		$this->_page_title = __( 'Ceros Code to Shortcode Converter', 'pmc-ceros-embeds' );
		$this->_menu_title = __( 'PMC Ceros Code Converter', 'pmc-ceros-embeds' );

	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_stuff' ] );
		add_action( 'admin_menu', [ $this, 'add_page' ] );

	}

	/**
	 * Method to get the URL of an asset file
	 *
	 * @param string $asset_path
	 *
	 * @return string
	 */
	protected function _get_asset_url( string $asset_path = '' ) : string {

		return plugins_url(
			sprintf(
				'assets/%s',
				ltrim( $asset_path, '/' )
			),
			dirname( __FILE__ )
		);

	}

	/**
	 * Method to enqueue assets on admin page
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function enqueue_stuff( string $hook = '' ) : void {

		$hook_to_watch_for = sprintf( 'tools_page_%s', self::MENU_SLUG );

		if ( $hook !== $hook_to_watch_for ) {
			return;
		}

		wp_enqueue_style(
			sprintf( '%s-admin-tool-css', self::ID ),
			$this->_get_asset_url( 'build/css/admin-ui.css' ),
			[],
			PMC_CEROS_EMBEDS_VERSION
		);

		wp_enqueue_script(
			sprintf( '%s-admin-tool-js', self::ID ),
			$this->_get_asset_url( 'build/js/admin-ui.js' ),
			[ 'jquery', 'wp-i18n' ],
			PMC_CEROS_EMBEDS_VERSION,
			true
		);

		wp_localize_script(
			sprintf( '%s-admin-tool-js', self::ID ),
			sprintf( '%s_config', self::ID ),
			[
				'tag' => Shortcode::TAG,
			]
		);

		wp_set_script_translations(
			sprintf( '%s-admin-tool-js', self::ID ),
			'pmc-ceros-embeds'
		);

	}

	/**
	 * Method to set up admin page for addition
	 *
	 * @return void
	 */
	public function add_page() : void {

		$this->_pagehook = add_submenu_page(
			'tools.php',
			$this->_page_title,
			$this->_menu_title,
			self::MIN_CAPABILITY,
			self::MENU_SLUG,
			[ $this, 'render_page' ]
		);

	}

	/**
	 * Method to render the UI on admin page
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function render_page() : void {

		PMC::render_template(
			sprintf( '%s/templates/admin/tool-ui.php', PMC_CEROS_EMBEDS_ROOT ),
			[
				'page_title' => $this->_page_title,
			],
			true
		);

	}

}    // end class

//EOF
