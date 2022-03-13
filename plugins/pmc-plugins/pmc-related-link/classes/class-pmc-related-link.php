<?php
/**
 * Class PMC Related Link creates a TinyMCE plugin which adds a new 'Related Link'
 * button to the TinyMCE editor displayed while editing any post. The code for this
 * is an exact copy from wpLink (also refered to as wp-link)--the default
 * link icon in the WordPress TinyMCE Editor. We copied/refactored wpLink's
 * html, js, and css. Instead of inserting an <a> tag, Related Link inserts a
 * shortcode, which, during the_content, is converted to a div-wrapped anchor tag.
 *
 * [pmc-related-link url="http://www.somelink.com" target="_blank" type="related"]Some Article[/related-link]
 *
 * <div class="related-link related">
 * 	   <strong>RELATED</strong> |
 * 	   <a target="_blank" href="http://www.somelink.com" title="Some Article">Some Article</a>
 * </div>
 *
 * Or maybe..
 *
 * [pmc-related-link url="http://www.mysite.com/photos/1234" type="photos"]Some Photos[/related-link]
 *
 * <div class="related-link photos">
 * 	   <strong>PHOTOS</strong> |
 * 	   <a target="_blank" href="http://www.mysite.com/photos/1234" title="Some Photos">Some Photos</a>
 * </div>
 */
use \PMC\Global_Functions\Traits\Singleton;

class PMC_Related_Link {

	use Singleton;

	/**
	 * Class Setup
	 *
	 * @return null
	 */
	protected function __construct() {
		// Create the admin settings page
		// This page is used to set the 'types' of
		// related links authors/editors can create
		add_action( 'admin_init', array( $this, 'admin_settings_page' ) );
		add_action( 'admin_menu', array( $this, 'admin_settings_menu' ) );

		// Register our TinyMCE Button and it's JavaScript code
		add_action( 'admin_init', array( $this, 'mce_buttons' ) );

		// Output our Related Link Popup Modal HTML into the admin footer
		// The modal provides a UI for the author/editor to insert the
		// related link [shortcode] into the post their' editing.
		//
		// We'll use JavaScript in the TinyMCE button to display this modal
		add_action( 'admin_footer', array( $this, 'inject_markup' ) );

		// Enqueue the Scripts & Styles needed by our popup modal
		// These include the functionality to search for posts and use
		// their permalink for the related link. This code is essentially
		// identical to the default wpLink (albeit a tad modernized and cleaned up)
		// The CSS Style is used to position the modal and make it pretty :)
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Register our [pmc-related-link] shortcode
		// so it can be output during the_content()
		add_shortcode( 'pmc-related-link', array( $this, 'shortcode_to_html' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register the Settings > Related Link menu
	 *
	 * @return void
	 */
	public function admin_settings_menu() {
		add_options_page(
			'Related Link', // Page title
			'Related Link', // Menu title
			'manage_options', // Capabilities needed to access
			'related-link', // Menu slug
			array( $this, 'render_admin_page' ) // Render page callback
		);
	}

	/**
	 * Build the Settings Page
	 *
	 * @return void
	 */
	public function admin_settings_page() {
		// Register the setting
		register_setting(
			'related-link-settings-group', // Option Group
			'related-link-types-setting', // Option Name
			'wp_kses_post'  // Sanitization Callback. This field will be a textarea, and we need to retain entered newlines and html
		);

		// Register the setting for Manual Type
		register_setting(
			'related-link-settings-group', // Option Group
			'related-link-manualtypes-setting', // Option Name
			'wp_kses_post'  // Sanitization Callback. This field will be a textarea, and we need to retain entered newlines and html
		);

		// Register the setting for Open In New type checked
		register_setting(
			'related-link-settings-group', // Option Group
			'related-link-openInNewTab-setting', // Option Name
			'wp_kses_post'  // Sanitization Callback. This field will be a textarea, and we need to retain entered newlines and html
		);

		// Register the setting for excluding the select one option in the drop down.
		register_setting(
			'related-link-settings-group', // Option Group
			'related-link-excludeselectone-setting', // Option Name
			'wp_kses_post'  // Sanitization Callback. This field will be a textarea, and we need to retain entered newlines and html
		);

		// Build the setting section
		add_settings_section(
			'related-link-types-section', // Section ID
			'', // Section Title
			array( $this, 'link_types_setting_section_callback' ), // Section Callback
			'related-link'			// Page ID (Menu Slug)
		);

		// Display a little instructional info for the user entering the link types
		// We'll use PHP's output buffering to make returning
		// our markup a tad easier / cleaner to read
		ob_start();
		?>

		<strong><?php esc_html_e( 'Related Link Types' ) ?></strong>
		<br />
		<div>
			<small><?php esc_html_e( 'One per line, example:' ) ?></small>
			<br /><br />
			<em><?php esc_html_e( 'RELATED' ) ?></em>
			<br />
			<em><?php esc_html_e( 'Photography' ) ?></em>
			<br />
			<em><?php esc_html_e( 'Important LINK' ) ?></em>
		</div><?php

		// Capture the output buffer and clean the buffer
		$settings_field_HTML = ob_get_contents();
		ob_end_clean();

		// Build the setting field
		add_settings_field(
			'related-link-types', // Field ID
			$settings_field_HTML, // Field Title
			array( $this, 'link_types_setting_field_callback' ), // Field Callback
			'related-link', // Page ID (Menu Slug)
			'related-link-types-section'	   // Section ID
		);

		// our markup for selecting manual type is a tad easier / cleaner to read
		ob_start();
		?>

		<strong><?php esc_html_e( 'Show Manual Type textbox' ) ?></strong>
		<br />
		<?php

		// Capture the output buffer and clean the buffer
		$manualtype_field_HTML = ob_get_contents();
		ob_end_clean();

		// Build the setting type field
		add_settings_field(
			'related-link-manualtypes', // Field ID
			$manualtype_field_HTML, // Field Title
			array( $this, 'link_types_setting_manualtype_callback' ), // Field Callback
			'related-link', // Page ID (Menu Slug)
			'related-link-types-section'	   // Section ID
		);

		// our markup for selecting manual type is a tad easier / cleaner to read
		ob_start();
		?>

		<strong><?php esc_html_e( 'Open In New Tab Checkbox selected :' ) ?></strong>
		<br />
		<?php
		// Capture the output buffer and clean the buffer
		$openInNewTab_field_HTML = ob_get_contents();
		ob_end_clean();

		// Build the setting type field
		add_settings_field(
			'related-link-openinnewtab_types', // Field ID
			$openInNewTab_field_HTML, // Field Title
			array( $this, 'link_types_setting_openInNewTabtype_callback' ), // Field Callback
			'related-link', // Page ID (Menu Slug)
			'related-link-types-section'	   // Section ID
		);

		// Build the setting type field
		add_settings_field(
			'related-link-exclude-select-one', // Field ID
			'Exclude "Select One" option from related link drop down', // Field Title
			array( $this, 'link_types_setting_excludeselectone_callback' ), // Field Callback
			'related-link', // Page ID (Menu Slug)
			'related-link-types-section'	   // Section ID
		);
	}

	/**
	 * Output any additional section description
	 *
	 * @return void
	 */
	public function link_types_setting_section_callback() {
		// silence is golden
		//
		// we don't need to output any section ui at this time.
	}

	/**
	 * Output the link types setting field
	 *
	 * @return void
	 */
	public function link_types_setting_field_callback() {
		$setting = get_option( 'related-link-types-setting' );
		?>

		<textarea
			name="related-link-types-setting"
			cols="20"
			rows="10"><?php echo esc_textarea( $setting ) ?></textarea>
		<?php
	}

	/**
	 * Output the link manualtypes setting field. Default is No
	 *
	 * @return void
	 */
	public function link_types_setting_manualtype_callback() {
		$setting = get_option( 'related-link-manualtypes-setting' );
		$setting = ( ! empty( $setting ) ) ? $setting : '0';

		?>
		<input type="radio" name="related-link-manualtypes-setting" value="0" <?php checked( $setting, '0' ); ?>/>No
		<input type="radio" name="related-link-manualtypes-setting" value="1" <?php checked( $setting, '1' ); ?>/>Yes
		<?php
	}

	/**
	 * Output the OpenInNewTab setting field. Default is No
	 *
	 * @return void
	 */
	public function link_types_setting_openInNewTabtype_callback() {

		$setting = get_option( 'related-link-openInNewTab-setting' );
		$setting = ( ! empty( $setting ) ) ? $setting : '0';

		?>
		<input type="radio" name="related-link-openInNewTab-setting" value="0" <?php checked( $setting, '0' ); ?>/>No
		<input type="radio" name="related-link-openInNewTab-setting" value="1" <?php checked( $setting, '1' ); ?>/>Yes
		<?php
	}

	/**
	 * Output the Exclude Select one setting field. Default it to No.
	 */
	public function link_types_setting_excludeselectone_callback() {
		$setting = get_option( 'related-link-excludeselectone-setting' );
		$setting = ( ! empty( $setting ) ) ? $setting : '0';
		?>

		<input type="radio" name="related-link-excludeselectone-setting" value="0" <?php checked( $setting, '0' ); ?>/>No
		<input type="radio" name="related-link-excludeselectone-setting" value="1" <?php checked( $setting, '1' ); ?>/>Yes
		<?php
	}

	/**
	 * Render the Admin Settings page
	 *
	 * @return void
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h2><?php echo esc_html( 'Related Link Options' ); ?></h2>
			<form action="options.php" method="POST">
			<?php settings_fields( 'related-link-settings-group' ); ?>
			<?php do_settings_sections( 'related-link' ); ?>
			<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Inject the link dialog box HTML into the admin footer
	 *
	 * @return void
	 */
	public function inject_markup() {
		// The following php file outputs the html markup
		// used to display the dialog/modal popup where users
		// select/build the related link and insert it into
		// the editor content.
		PMC::render_template(
			sprintf( '%s/templates/pmc-related-link-modal-markup.php', untrailingslashit( PMC_RELATED_LINK_PATH ) ),
			array(),
			true
		);
	}

	/**
	 * Enqueue the Related Link Javascript within the WordPress admin
	 *
	 * Called via WordPress Action admin_enqueue_scripts
	 *
	 * @param  string $hook The WordPress page being displayed.
	 *
	 * @return null
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only do the following when editing a post.
		if ( 'post.php' != $hook && 'post-new.php' != $hook ) {
			return;
		}

		// Enqueue the related link css.
		wp_enqueue_style(
			'pmc_related_link',
			sprintf( '%s/css/pmc-related-link.css', PMC_RELATED_LINK_URL )
		);

		// Enqueue the related link logic javascript.
		wp_register_script(
			'pmc_related_link',
			sprintf( '%s/js/pmc-related-link.js', PMC_RELATED_LINK_URL )
		);

		$showManualType = get_option( 'related-link-manualtypes-setting' );
		$openInNewTab = get_option( 'related-link-openInNewTab-setting' );

		// Localize some strings displayed in our popup modal for l10n.
		wp_localize_script( 'pmc_related_link', 'relatedLinkL10n', array(
			'title'          => 'Insert/edit Related link',
			'update'         => 'Update',
			'save'           => 'Add Related Link',
			'noTitle'        => '(no title)',
			'noMatchesFound' => 'No matches found.',
			'showManualType' => ( ! empty( $showManualType ) ) ? true : false,
			'openInNewTab'   => ( ! empty( $openInNewTab ) ) ? true : false,
		) );

		wp_enqueue_script( 'pmc_related_link' );
	}


	/**
	 * To enqueue style
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		// Enqueue the related link css.
		wp_enqueue_style(
			'pmc_related_link',
			sprintf( '%s/css/style.css', PMC_RELATED_LINK_URL )
		);
	}

	/**
	 * Filter WordPress MCE Hooks to insert our own MCE button
	 *
	 * Called via WordPress Action init
	 *
	 * @return void
	 */
	public function mce_buttons() {
		// Add the button to TinyMCE.
		add_filter( 'mce_buttons', array( $this, 'mce_add_buttons' ) );

		// Register the code behind our new TinyMCE button.
		add_filter( 'mce_external_plugins', array( $this, 'mce_button_code' ) );
	}

	/**
	 * Tell TinyMCE to load our custom 'Related Link' MCE button
	 *
	 * Called via WordPress filter mce_buttons
	 *
	 * @param  array $buttons An array of buttons to loaded in MCE
	 *
	 * @return array An array of buttons to loaded in MCE (with ours added to the list)
	 */
	public function mce_add_buttons( $buttons ) {
		// Add our new button to the TinyMCE buttons array.
		array_push( $buttons, 'pmc_related_link' );

		// Return the modified button array.
		return $buttons;
	}

	/**
	 * Inform TinyMCE about our new MCE plugin code
	 *
	 * Called via WordPress Filter mce_external_plugins
	 *
	 * @param  array $plugin_array An array of loaded MCE plugins
	 *
	 * @return array An array of loaded MCE plugins (with ours added to the list)
	 */
	public function mce_button_code( $mce_plugin_array ) {
		// Tell TinyMCE where it can find our new button's code.
		$mce_plugin_array['pmc_related_link'] = sprintf( '%s/js/pmc-related-link-tinymce-plugin.js', PMC_RELATED_LINK_URL );

		// Return the mce buttons array including our new button.
		return $mce_plugin_array;
	}

	/**
	 * Register the [pmc-related-link] shortcode
	 *
	 * Called as a callback via add_shortcode()
	 * This shortcode outputs a little markup around an anchor link with the class .related-link
	 *
	 * @param  array $attrs	 An array of attributes for the shortcode
	 * @param  string $content The content [shortcode]within[/shortcode] the shortcode
	 *
	 * @return null
	 */
	public function shortcode_to_html( $attrs, $content = '' ) {
		// Attribute defaults.
		$attrs = shortcode_atts( array(
			'href'   => get_bloginfo( 'url' ),
			'target' => '',
			'type'   => 'RELATED',
		), $attrs );

		// 'Slugify' the type attribute of the related link (in $attrs)
		$type_slug = sanitize_title( $attrs['type'] );
		$type_slug = preg_replace( '/[^A-Za-z0-9-]+/', '-', $type_slug );
		$type_slug = strtolower( $type_slug );

		/**
		 * Allow a site to set custom markup for this shortcode
		 *
		 * @ticket PPT-3095
		 * @since 2014-12-04 Amit Gupta
		 */
		$shortcode_output = apply_filters( 'pmc-related-link-shortcode-markup', '', $attrs, $content, $type_slug );

		if ( ! empty( $shortcode_output ) ) {
			return $shortcode_output;
		}

		// We'll use PHP's output buffering to make returning
		// our shortcode output a tad easier / cleaner to read.
		ob_start();

		/**
		 * NOTE!!!!
		 * If you adjust the markup below, you MUST also adjust the identical markup
		 * within tinymce-plugin.js (which is used for shortcode preview)
		 * and within pmc-related-link.js which inserts the shortcode to the content
		 */
		?><aside class="pmc-related-link <?php echo esc_attr( $type_slug ) ?>">
			<strong class="pmc-related-type"><?php echo wp_kses_post( $attrs['type'] ) ?></strong><a target="<?php echo esc_attr( $attrs['target'] ) ?>" href="<?php echo esc_url( $attrs['href'] ) ?>" title="<?php echo esc_attr( $content ) ?>"><?php echo wp_kses_post( $content ) ?></a>
		</aside><?php
		// Capture the output buffer and clean the buffer.
		$shortcode_output = ob_get_contents();
		ob_end_clean();

		// Return the shortcode output.
		return $shortcode_output;
	}

}

// EOF
