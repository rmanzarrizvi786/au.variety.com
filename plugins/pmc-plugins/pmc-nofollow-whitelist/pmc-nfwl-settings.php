<?php
/**
 * Static class for plugin settings
 * @since 2011-11-03 Amit Gupta
 * @version 2011-11-04 Amit Gupta
 */

class PMC_NFWL_Settings extends PMC_Nofollow_White_List {

	/* constructor private, this is a static class, so no initialization */
	private function __construct() { }
	/* destructor required to be public, this is a static class, so no initialization */
	public function __destruct() { }

	/**
	 * function to add menu page in admin settings
	 */
	public static function add_page() {
		add_options_page('PMC NoFollow Whitelist Settings', 'PMC NoFollow Whitelist', 'manage_options', parent::$settings['page_slug'], 'PMC_NFWL_Settings::generate_ui');
	}

	/**
	 * function to add admin settings
	 */
	public static function admin_init() {
		//register the settings
		register_setting(parent::$settings['opt_group'], parent::$settings['opt_name'], 'PMC_NFWL_Settings::validate_settings');
		//add settings
		add_settings_section('pmc_nfwl_settings_main', 'Whitelist Settings', 'PMC_NFWL_Settings::wlui_section_text', parent::$settings['page_slug']);
		//add whitelist settings field
		add_settings_field(parent::$form_fields['wl_id'], 'NoFollow Whitelist', 'PMC_NFWL_Settings::wlui_wl_field', parent::$settings['page_slug'], 'pmc_nfwl_settings_main');
	}

	/**
	 * function to generate settings page UI
	 */
	public static function generate_ui() {
?>
	<div>
	<h2>PMC NoFollow Whitelist Settings</h2>
	Settings relating to the PMC NoFollow Whitelist Plugin.
	<form action="options.php" method="post">
	<?php settings_fields(PMC_Nofollow_White_List::$settings['opt_group']); ?>
	<?php do_settings_sections(PMC_Nofollow_White_List::$settings['page_slug']); ?>

	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		<?php wp_nonce_field( __FILE__ ,  'pmc_nfwl_setting' ); ?>
	</p>
	</form>
	</div>
<?php
	}

	/**
	 * function to output settings description
	 */
	public static function wlui_section_text() {
?>
	<p>
		Tips:
		<ol>
			<li>Whitelist should contain only one domain per line. Do not prefix protocol (like http://) before the domain.</li>
			<li>If you want to whitelist all sub-domains of a domain then just put the Second Level Domain and Top Level Domain.</li>
			<li>If you want to whitelist only selected sub-domain(s) then you will need to mention each of them &amp; make sure that domain is not in the whitelist without any sub-domains.</li>
		</ol>
	</p>
<?php
	}

	/**
	 * function to output the whitelist field HTML
	 */
	public static function wlui_wl_field() {
		$txt_wl = esc_html( implode("\n", parent::$_options[parent::$settings['opt_name_wl']]) );
		print("<textarea id='".esc_attr( parent::$form_fields['wl_id'] )."' name='".esc_attr( parent::$settings['opt_name'] ."[".parent::$form_fields['wl']."]" )."' rows='10' cols='80'>{$txt_wl}</textarea>");
	}

	/**
	 * function to handle the form data sent received by wordpress
	 */
	public static function validate_settings($input) {
		if ( ! empty( $input ) ) {
			check_admin_referer( __FILE__ ,  'pmc_nfwl_setting' );
		}

		if ( empty( $input ) ) {
			return parent::$_options;
		}

		$arr_wl = array();

		$data[ parent::$form_fields['wl'] ] = explode( "\n", trim( stripslashes( $input[ parent::$form_fields['wl'] ] ) ) );

		foreach( $data[ parent::$form_fields['wl'] ] as $domain ) {
			$domain = parent::get_domain( trim( $domain ), false );
			if ( empty( $domain ) ) { continue; }
			if ( ! in_array( $domain, $arr_wl ) ) {
				$arr_wl[] = $domain;
			}
		}

		parent::$_options[ parent::$settings['opt_name_wl'] ] = $arr_wl;

		unset( $arr_wl );

		return parent::$_options;
	}

	/* End of Class */
}


//add admin option page
add_action('admin_menu', 'PMC_NFWL_Settings::add_page');
//register & add admin settings
add_action('admin_init', 'PMC_NFWL_Settings::admin_init');



//EOF
