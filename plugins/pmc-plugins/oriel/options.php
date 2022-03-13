<?php

class OrielSettingsPage {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin',
			'Oriel',
			'manage_options',
			'oriel-settings-admin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		global $cache;
		// Set class property
		$this->options = get_option( 'oriel_options' );
		?>
		<style>
			.wrap {
				width:800px;
			}
			.wrap h1 {
				margin-bottom: 20px !important;
			}
			.oriel-notice {
				padding: 20px;
				background-color: #6BC2B9;
				border: 0;
				color: #FFF;
				font-size: 16px;
				border-radius: 5px;
				overflow: hidden;
			}
			.oriel-notice.oriel-notice h2 {
				margin: 5px 0 10px 0;
				color: #FFF;
			}
			.oriel-action:visited, .oriel-action:link{
				display: inline-block;
				padding: 15px 25px;
				color: #333;
				background-color: #FED47A;
				border: 3px solid #FFF;
				text-decoration: none;
				font-size: 16px;
				font-weight: bold;
				border-radius: 5px;
				float:right;
			}
			.oriel-action:hover {
				background-color: #feb963;
				border-color: #EEE;
			}
			.oriel-container {
				background: #FFF;
				padding: 20px 20px 5px 20px;
				border-radius: 5px;
			}
		</style>
		<div class="wrap">
			<h1>Oriel Configuration</h1>
			<?php
			if ( ! $cache->get( 'head_key' ) && ! Oriel\API::get( '/domain' ) ) {
				?>
			<div class="notice oriel-notice">
				<div style="float:left">
					<h2>Oriel Not Active</h2>
					You must first login or signup to Oriel in order to enable it on your website.
				</div>
			</div>
				<?php
			} else {
				?>
				<div class="notice oriel-notice">
					<h2>Oriel is active!</h2>
				</div>
			<?php } ?>
			<div class="oriel-container">
				<div style="font-size:12pt;">Please change your settings below:</div>
				<form method="post" action="options.php">
					<?php
					// This prints out all hidden setting fields
					settings_fields( 'oriel_option_group' );
					do_settings_sections( 'oriel-settings-admin' );
					submit_button( 'Save' );
					?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			'oriel_option_group', // Option group
			'oriel_options', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			'', // Title
			array( $this, 'print_section_info' ), // Callback
			'oriel-settings-admin' // Page
		);

		add_settings_field(
			'api_key',
			'Website API Key',
			array( $this, 'api_key_callback' ),
			'oriel-settings-admin',
			'setting_section_id'
		);

		add_settings_field(
			'disable_oriel_param',
			'Disable Oriel Keyword',
			array( $this, 'disable_oriel_param_callback' ),
			'oriel-settings-admin',
			'setting_section_id'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param  array $input Contains all settings fields as array keys
	 * @return array Sanitized text fields as array keys
	 */
	public function sanitize( $input ) {
		global $cache;

		$new_input = array();
		if ( isset( $input['api_key'] ) ) {
			$new_input['api_key'] = sanitize_text_field( $input['api_key'] );
		}

		if ( isset( $input['disable_oriel_param'] ) ) {
			$new_input['disable_oriel_param'] = sanitize_text_field( $input['disable_oriel_param'] );
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		print '';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function api_key_callback() {
		printf(
			'<input type="text" id="api_key" name="oriel_options[api_key]" value="%s" style="width:550px;"/>',
			isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function disable_oriel_param_callback() {
		printf(
			'<input type="text" id="disable_oriel_param" placeholder="disable_oriel" name="oriel_options[disable_oriel_param]" value="%s" style="width:200px;"/>
                    <p><small style="font-style: italic;color: #9e9e9e;">Note: For quick testing purposes, Oriel can be deactivated by adding the <strong>disable_oriel=1</strong> query parameter in the URL.</small></p>',
			isset( $this->options['disable_oriel_param'] ) ? esc_attr( $this->options['disable_oriel_param'] ) : ''
		);
	}
}

if ( is_admin() ) {
	$oriel_settings_page = new OrielSettingsPage();
}
