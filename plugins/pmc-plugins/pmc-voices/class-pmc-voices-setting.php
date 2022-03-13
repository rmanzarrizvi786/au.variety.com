<?php

use PMC\Global_Functions\Traits\Singleton;

class PMC_Voices_Setting {

	use Singleton;

	protected function __construct() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function admin_menu() {

		add_submenu_page(
			'tools.php', 'Voices Settings', 'PMC Voices Menu', 'manage_options', 'pmc_voice_setting_menu', array(
				$this,
				'options_page'
			)
		);
	}

	public function enqueue_scripts_styles() {

		if ( ! isset( $_GET['page'] ) || "pmc_voice_setting_menu" != $_GET['page'] ) {
			return;
		}
		$url = plugins_url( '', __FILE__ );
		wp_enqueue_script( "pmc-voices-setting", $url . "/js/admin-script.js", array( 'jquery-ui-sortable' ) );
		wp_enqueue_style( "pmc-voices-setting-css", $url . "/css/style.css" );
	}


	public function admin_init() {
		register_setting(
			'pmc_voice_setting_grp', 'pmc_voice_setting', array(
				$this,
				'sanitize_option'
			)
		);
	}

	public function sanitize_option( $input ) {

		if ( empty( $input ) ) {
			return;
		}
		$input = json_decode( $input, true );

		foreach ( $input as $post_id => $menu_order ) {

			if ( intval( $menu_order ) > 100 ) {
				continue;
			}

			wp_update_post(
				array(
					'ID'         => intval( $post_id ),
					'menu_order' => intval( $menu_order ),
				)
			);
		}

		return;
	}

	public function options_page() {
		require_once( __DIR__ . "/templates/voices-author-ordering.php" );
	}
}


PMC_Voices_Setting::get_instance();
//EOF
