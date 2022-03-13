<?php

/**
 * This class implement workaround to fix issue where wp-admin/menu.php is using global variable $cap
 * colliding with cheezcap global variable $cap. To fix this, we intercept the $cap variable at init action
 * then restored it back in admin_init action.
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Cheezcap
{

	use Singleton;

	private $_cheezcap_instance = false;

	protected function __construct()
	{
		add_action('init', array($this, 'action_init'), 9999);
		add_action('admin_init', array($this, 'action_admin_init'), 1);
		add_filter('pmc_global_cheezcap_options', array($this, 'filter_pmc_global_cheezcap_options'));
	}

	public function action_init()
	{
		if (isset($GLOBALS['cap'])) {
			// store the cheezcap variable
			$this->_cheezcap_instance = $GLOBALS['cap'];
		}
	}

	public function action_admin_init()
	{
		// restore cheezcap instance in case where wp-admin/menu.php override via global variable $cap
		if (!isset($GLOBALS['cap']) && !empty($this->_cheezcap_instance)) {
			$GLOBALS['cap'] = $this->_cheezcap_instance;
		}
	} // function

	/**
	 * Function to do fall back on if cheezcap has not been initialized
	 * This is to work around on case where option need to retreive prior to cheezcap init
	 * Usually during wp-admin screen. This function is not to be call outside of this class.
	 * @see function PMC_Cheezcap::get_option
	 */
	private function _fallback_get_option($option, $echo = false, $sanitize_callback = '')
	{
		$value = get_option('cap_' . $option);

		if ($sanitize_callback && is_callable($sanitize_callback)) {
			$value = call_user_func($sanitize_callback, $value);
		}

		if ($echo) {
			echo wp_kses_post($value);
		} else {
			return $value;
		}
	}

	/**
	 * Return cheezcap option value
	 * @param  string  $option            The cheezcap field name
	 * @param  boolean $echo              Echo the value?
	 * @param  string  $sanitize_callback Optional function use to santize the value
	 * @return mixed
	 */
	function get_option($option, $echo = false, $sanitize_callback = '')
	{
		if (empty($this->_cheezcap_instance)) {
			if (!isset($GLOBALS['cap'])) {
				return $this->_fallback_get_option($option, $echo, $sanitize_callback);
			}
			$this->_cheezcap_instance = $GLOBALS['cap'];
		}

		// Cheezcap object should have __get method
		// if not, need to check isset property, this should never happen but this can happen if Cheezcap have not been initialized or code overridden the global $cap variable
		if (!is_object($this->_cheezcap_instance) || !method_exists($this->_cheezcap_instance, '__get') && !isset($this->_cheezcap_instance->{$option})) {
			return false;
		}

		try {
			$value = $this->_cheezcap_instance->{$option};
		} catch (Exception $e) {
			return false;
		}

		if ($sanitize_callback && is_callable($sanitize_callback)) {
			$value = call_user_func($sanitize_callback, $value);
		}

		if ($echo) {
			echo wp_kses_post($value);
		} else {
			return $value;
		}
	} // function

	/**
	 * Allow temporarily override the cheezcap option
	 * @param string $option The option to override
	 * @param mixed $value   The value to set
	 */
	public function set_option($option, $value)
	{
		if (empty($this->_cheezcap_instance)) {
			if (!isset($GLOBALS['cap'])) {
				return $this;
			}
			$this->_cheezcap_instance = $GLOBALS['cap'];
		}
		$this->_cheezcap_instance->{$option} = $value;
		return $this;
	}

	// helper function to register cheezcap setting
	// each lob may have their own registration.
	// @TODO: Update all LOB to use this class then we can auto register from init function
	/**
	 * Cheezcap plugin config
	 *
	 * @author Amit Gupta <agupta@pmc.com>
	 * @since 2014-06-05
	 * @note: code moved to this class on 2014-11-13
	 */
	public function register()
	{
		if (!class_exists('CheezCap')) {
			return;
		}

		$cheezcap_groups = apply_filters('pmc_cheezcap_groups', array());

		if (empty($cheezcap_groups) || !is_array($cheezcap_groups)) {
			//no Cheezcap groups, no point in proceeding further
			//bail out
			return false;
		}

		/*
		 * Cheezcap object needs to be set in global $cap because Cheezcap is
		 * effin stupid and relies on a var to be set in theme
		 */
		$GLOBALS['cap'] = $this->_cheezcap_instance = new CheezCap(
			$cheezcap_groups,
			array(
				'themename'         => 'Theme', // used on the title of the custom admin page
				'req_cap_to_edit'   => 'manage_options', // the user capability that is required to access the CheezCap settings page
				'cap_menu_position' => 99, // OPTIONAL: This value represents the order in the dashboard menu that the CheezCap menu will display in. Larger numbers push it further down.
				'cap_icon_url'      => '', // OPTIONAL: Path to a custom icon for the CheezCap menu item. ex. $cap_icon_url = WP_CONTENT_URL . '/your-theme-name/images/awesomeicon.png'; Image size should be around 20px x 20px.
			)
		);

		return $this;
	} // function

	public function filter_pmc_global_cheezcap_options($cheezcap_options)
	{
		$cheezcap_options[] = new CheezCapTextOption(
			'PMC development preview users',
			'Commas delimited wordpress username/id',
			'pmc_dev_preview_users',
			'',
			true
		);

		// @TODO: SADE-517 to be removed
		$cheezcap_options[] = new CheezCapTextOption(
			'Feeds Pages: Allowed Hosts (To be removed, DO NOT use)',
			'Hostnames allowed in external links in all feeds, one per line.',
			'pmc_feeds_external_url_whitelist',
			'',
			true,
			false
		);

		$cheezcap_options[] = new CheezCapTextOption(
			'Feeds Pages: Allowed Hosts',
			'Hostnames allowed in external links in all feeds, one per line.',
			'pmc_feeds_external_url_allowlist',
			'',
			true,
			false
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			'Global Terms',
			'global terms support',
			'pmc_global_terms',
			array('yes', 'no'),
			0, // 1sts option => yes
			array('Yes', 'No')
		);
		$cheezcap_options[] = new CheezCapDropdownOption(
			'Elasticsearch Support for PMC Plugins',
			'Turn on Elasticsearch support for PMC plugins that implement Elasticsearch',
			'pmc_plugin_es_support',
			array('no', 'yes'),
			0, // 1st option => No
			array('No', 'Yes')
		);

		return $cheezcap_options;
	}

	/**
	 * Sanitize an array coming from CheezCapMultipleCheckboxesOption
	 *
	 * Cheezcap simply runs call_user_func() on the sanitization callback
	 * you provide when creating checkbox options. However, they don't
	 * take into account that this class supplies an array.
	 *
	 * In the sanitization callback supply the following to sanitize:
	 * array( 'PMC_Cheezcap', 'sanitize_cheezcap_checkboxes' )
	 *
	 * @param string $input_id   The cheezcap id for the current field
	 * @param array  $input_data The array of values from selected checkboxes
	 *
	 * @return array The sanitized input array
	 */
	static function sanitize_cheezcap_checkboxes($input_id, $input_data)
	{
		if (is_array($input_data)) {
			return array_map('sanitize_text_field', $input_data);
		}
		return $input_data;
	}
}

PMC_Cheezcap::get_instance();
