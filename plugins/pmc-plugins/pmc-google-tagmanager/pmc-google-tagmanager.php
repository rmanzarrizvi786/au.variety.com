<?php
/**
 * Plugin Name: PMC Google Tag Manager
 * Description: Adds script and  settings field for GTM
 * Author: Adaeze Esiobu|PMC
 * Version: 1.0.0.0
 * License: PMC Proprietary.  All rights reserved.
 */
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Google_Tagmanager {

	use Singleton;

	protected function __construct() {

		add_action( 'pmc-tags-top',array( $this, 'render_gtm_script' ) );

		add_action( 'admin_init', array( $this, 'add_tagmanager_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_datalayer_js_object' ), 1 ); // do NOT change this priority
		add_action( 'pmc_enqueue_scripts_using_pmc_page_meta', array( $this, 'add_datalayer_js_values' ) ); // do NOT change the hook

	}

	/**
	 * register settings and add settings field
	 */
	public function add_tagmanager_settings(){
		register_setting( 'general', 'pmc_google_tag_manager_account', array($this, 'validate_account_id') );

		add_settings_field(
			'pmc_google_tag_manager_account',
			'Google Tag Manager account',
			array($this, 'gtm_settings_field'),
			'general',
			'default'
		);
	}

	/**
	 * Add the dataLayer object to the top of the <head> tag (or as close as this hook allows)
	 *
	 * This must run before the `pmc_enqueue_scripts_using_pmc_page_meta` hook when we add information from PMC_Page_Meta
	 *
	 * @since 2015-07-01 Corey Gilmore
	 *
	 * @see PMC_Page_Meta
	 * @uses action::wp_enqueue_scripts:1
	 *
	 * @version 2015-07-01 Corey Gilmore PPT-5136
	 *
	 */
	public function add_datalayer_js_object() {
		echo '<script type="text/javascript">dataLayer = window.dataLayer || []; /* Google Tag Manager */</script>' . PHP_EOL;
	}

	/**
	 * Push meta information from the `pmc_meta` JS object into into the GTM `dataLayer` object.
	 *
	 * This must fire *after* we add pmc_meta to the HEAD, which is why we use the `pmc_enqueue_scripts_using_pmc_page_meta` action
	 *
	 * @since 2015-07-01 Corey Gilmore
	 *
	 * @see PMC_Page_Meta
	 * @uses action::pmc_enqueue_scripts_using_pmc_page_meta
	 *
	 * @version 2015-07-01 Corey Gilmore PPT-5136
	 *
	 */
	public function add_datalayer_js_values() {
		?>
		<script type="text/javascript">
		if( window.hasOwnProperty( 'pmc_meta' ) ) {
			if( !window.hasOwnProperty( 'dataLayer' ) || !window.dataLayer.hasOwnProperty('push') ) {
				window.dataLayer = [];
			}
			window.dataLayer.push( pmc_meta );
		}
		</script>

		<?php
	}

	/**
	 * render settings field
	 */
	public function gtm_settings_field(){
		echo '<input id="pmc_google_tag_manager_account" name="pmc_google_tag_manager_account" type="text" value="' . esc_attr( get_option('pmc_google_tag_manager_account') ) . '" />';

	}

	/**
	 * @param $account_id
	 * @return mixed|null|string|void
	 * Validation for google tag account ID. ID must be in format GTM-XXXX
	 */
	public function validate_account_id( $account_id ){

		if ( empty( $account_id ) ) {
			return null;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error( 'pmc_google_tag_manager_account', 'pmc_google_tag_manager_account', __( 'Only administrators can change the Google Tag Manager account.' ), 'error' );

			return get_option('pmc_google_tag_manager_account');
		}

		if ( ! preg_match( '/^GTM-[a-z\d]{4,9}$/i', $account_id ) ) {
			add_settings_error( 'pmc_google_tag_manager_account', 'pmc_google_tag_manager_account', __( 'Google Tag Manager account must be in the format: GTM-XXXXX' ), 'error' );

			return get_option('pmc_google_tag_manager_account');
		}


		$account_id = strtoupper( $account_id );

		return $account_id;

	}


	/**
	 * check if GTM is activated. if it is, then add GTM script.
	 */
	public function render_gtm_script(){

		if( $this->is_gtm_active() ){

 			$this->output_universal_analytics_code();
		}

	}

	/**
	 * @return bool
	 * checks the  option to see if GTM is activated. and empty option means
	 * GTM is disabled
	 */
	public function is_gtm_active(){

		$gtm_id = get_option('pmc_google_tag_manager_account');

		$active = ! empty( $gtm_id );

		return $active;
	}

	/**
	 * render GTM script
	 */
	public function output_universal_analytics_code(){
		$gtm_id = get_option('pmc_google_tag_manager_account');

		$blocker_atts = [
			'type'  => 'text/javascript',
			'class' => '',
		];

		if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
			$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
		}
		?>
	<!-- Google Tag Manager -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_js( $gtm_id ); ?>"
					  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');</script>
	<!-- End Google Tag Manager -->

	<?php

	}
}

PMC_Google_Tagmanager::get_instance();
