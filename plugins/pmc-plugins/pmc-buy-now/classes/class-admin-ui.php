<?php

namespace PMC\Buy_Now;

use \PMC\Global_Functions\Traits\Singleton;

class Admin_UI {

	use Singleton;

	/**
	 * Store button type settings.
	 *
	 * @var null
	 */
	private $_button_type_settings = null;

	/**
	 * __construct function of class.
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Set up actions and filters.
	 *
	 */
	protected function _setup_hooks() {

		/**
		 * Actions.
		 */
		add_action( 'admin_init', [ $this, 'tinymce_button_init' ] );
		add_action( 'admin_footer', [ $this, 'admin_footer' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		/**
		 * Filters.
		 */
		add_filter( 'pmc_buy_now_options', [ $this, 'default_options' ] );
	}

	/**
	 * Helper function to determine if Buy Now interface should be loaded.
	 *
	 * @return bool
	 */
	public function maybe_include_buy_now() : bool {
		global $pagenow;

		$pages = apply_filters( 'pmc_buy_now_pages', [ 'post.php', 'post-new.php' ] );

		if ( in_array( $pagenow, (array) $pages, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() : void {
		if ( ! $this->maybe_include_buy_now() ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		wp_enqueue_style( 'pmc-buy-now-admin-ui-css', PMC_BUY_NOW_PLUGIN_URL . 'assets/build/css/admin-ui.css', [], PMC_BUY_NOW_VERSION );
	}

	/**
	 * TinyMCE button init.
	 *
	 * @return void
	 */
	public function tinymce_button_init() : void {
		if ( ! $this->maybe_include_buy_now() ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) && get_user_option( 'rich_editing' ) === 'true' ) {
			return;
		}

		add_filter( 'mce_external_plugins', [ $this, 'tinymce_register_plugin' ] );
		add_filter( 'mce_buttons', [ $this, 'tinymce_add_button' ] );
	}

	/**
	 * Add Buy Now to TinyMCE plugin array.
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function tinymce_register_plugin( $plugins = [] ) : array {
		if ( ! $this->maybe_include_buy_now() ) {
			return $plugins;
		}

		$plugins['pmc_buy_now_button'] = add_query_arg(
			[
				'ver' => PMC_BUY_NOW_VERSION,
			],
			PMC_BUY_NOW_PLUGIN_URL . 'assets/build/js/admin-ui.js'
		);

		return $plugins;
	}

	/**
	 * Add Buy Now to TinyMCE button array.
	 *
	 * @param $buttons
	 *
	 * @return array
	 */
	public function tinymce_add_button( $buttons = [] ) : array {
		if ( ! $this->maybe_include_buy_now() ) {
			return $buttons;
		}

		$buttons[] = 'pmc_buy_now_button';

		return $buttons;
	}


	/**
	 * Append modal template to footer on edit/new post pages.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function admin_footer() : void {
		if ( ! $this->maybe_include_buy_now() ) {
			return;
		}

		\PMC::render_template(
			sprintf( '%s/templates/modal.php', untrailingslashit( PMC_BUY_NOW_PLUGIN_DIR ) ),
			[],
			true
		);
	}

	/**
	 * Get button settings.
	 *
	 * @return array
	 */
	public function get_button_type_settings() : array {

		if ( null === $this->_button_type_settings ) {
			$this->_button_type_settings = $this->_set_button_type_settings();
		}

		return $this->_button_type_settings;

	}

	/**
	 * Apply filters and set the button type.
	 *
	 * @return array
	 */
	private function _set_button_type_settings() : array {

		return (array) apply_filters(
			'pmc_buy_now_button_types',
			[
				'default' => [
					'label'  => __( 'Default', 'pmc-buy-now' ),
					'fields' => [
						'text',
						'link',
						'price',
						'orig_price',
						'target',
					],
				],
			]
		);

	}

	/**
	 * Set default options for Buy Now modal.
	 *
	 * @param $options
	 *
	 * @return array
	 */
	public function default_options( array $options = [] ) : array {
		return array_merge(
			[
				[
					'title'       => __( 'Text', 'pmc-buy-now' ),
					'name'        => 'text',
					'type'        => 'text',
					'default'     => \PMC::is_amp() ? '' : __( 'BUY NOW:', 'pmc-buy-now' ),
					'placeholder' => __( 'Buy Now!', 'pmc-buy-now' ),
				],
				[
					'title'       => __( 'Link', 'pmc-buy-now' ),
					'name'        => 'link',
					'type'        => 'text',
					'placeholder' => __( 'https://', 'pmc-buy-now' ),
				],
				[
					'title'       => __( 'Current Price', 'pmc-buy-now' ),
					'name'        => 'price',
					'type'        => 'text',
					'placeholder' => __( '$89', 'pmc-buy-now' ),
				],
				[
					'title'       => __( 'Original Price', 'pmc-buy-now' ),
					'name'        => 'orig_price',
					'type'        => 'text',
					'placeholder' => __( '$109', 'pmc-buy-now' ),
				],
				[
					'title'          => __( 'Target', 'pmc-buy-now' ),
					'name'           => 'target',
					'type'           => 'select',
					'default'        => '_blank',
					'select_options' => [
						'_blank' => __( 'New window', 'pmc-buy-now' ),
						'_self'  => __( 'Same window', 'pmc-buy-now' ),
					],
				],
			],
			$options
		);
	}

}

// EOF
