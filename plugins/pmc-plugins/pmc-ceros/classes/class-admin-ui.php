<?php

namespace PMC\Ceros;

use \PMC\Global_Functions\Traits\Singleton;

class Admin_UI {

	use Singleton;

	/**
	 * __construct function of class.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Set up actions and filters.
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() {

		/**
		 * Actions.
		 */
		add_action( 'admin_init', [ $this, 'tinymce_button_init' ] );
		add_action( 'admin_footer', [ $this, 'admin_footer' ] );
	}

	/**
	 * Helper function to determine if Ceros interface should be loaded.
	 *
	 * @return bool
	 */
	public function maybe_include_ceros() : bool {
		$screen     = get_current_screen();
		$post_types = apply_filters( 'pmc_ceros_post_types', [ 'post', 'page' ] );
		if ( isset( $screen->post_type ) && is_array( $post_types ) && in_array( $screen->post_type, (array) $post_types, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * TinyMCE button init.
	 *
	 * @return void
	 */
	public function tinymce_button_init() : void {

		if ( ! current_user_can( 'edit_posts' ) && get_user_option( 'rich_editing' ) === 'true' ) {
			return;
		}

		add_filter( 'mce_external_plugins', [ $this, 'tinymce_register_plugin' ] );
		add_filter( 'mce_buttons', [ $this, 'tinymce_add_button' ] );
	}

	/**
	 * Add Ceros to TinyMCE plugin array.
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function tinymce_register_plugin( $plugins = [] ) : array {
		if ( ! $this->maybe_include_ceros() ) {
			return $plugins;
		}

		$plugins['pmcceros'] = PMC_CEROS_PLUGIN_URL . 'assets/js/tinymce-ceros.js';

		return $plugins;
	}

	/**
	 * Append modal template to footer on edit/new post pages.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function admin_footer() : void {
		if ( ! $this->maybe_include_ceros() ) {
			return;
		}

		\PMC::render_template(
			sprintf( '%s/templates/modal.php', untrailingslashit( PMC_CEROS_PLUGIN_DIR ) ),
			[],
			true
		);
	}

	/**
	 * Add Ceros to TinyMCE button array.
	 *
	 * @param $buttons
	 *
	 * @return array
	 */
	public function tinymce_add_button( $buttons = [] ) : array {
		if ( ! $this->maybe_include_ceros() ) {
			return $buttons;
		}

		$buttons[] = 'pmcceros';

		return $buttons;
	}

}

// EOF
