<?php

/**
 * Cheezcap plugin config
 *
 */

namespace PMC\Core\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;
use \CheezCapGroup;
use \CheezCapDropdownOption;
use \CheezCapTextOption;

class Cheezcap
{

	use Singleton;

	protected function __construct()
	{

		// We use priority 11 here so our code runs after the CheezCap init (priority 10)
		// PMC Core sites also rely on the pmc-content-publishing checklist enforcement popup
		// specifically it's ability to display that popup when advancing to specified post
		// statuses. However, for that to work (which relies on Edit Flow) this init must be 11
		// See pmc-plugins/pmc-content-publishing/classes/checklist.php:89
		add_action('init', [$this, 'action_init'], 11);

		// We use priority 11 here so our code runs after CheezCap (priority 10)
		// adds it's top-level menu page
		add_action('admin_menu', [$this, 'move_cheezcap_menu'], 11);

		add_filter('pmc_global_cheezcap_options', [$this, 'add_global_cheezcap_options']);
		add_filter('pmc_cheezcap_groups', [$this, 'cheezcap_groups']);
	}

	/**
	 * Method to set up CheezCap on the site
	 *
	 * @return void
	 */
	public function action_init()
	{
		\PMC_Cheezcap::get_instance()->register();
	}

	/**
	 * Move the default CheezCap 'Theme Settings' top-level page under 'Settings'
	 *
	 * After this function executes 'Theme Settings' has become 'Settings > Theme Settings'.
	 *
	 * We simply remove the top-level page and create an options page using the same callbacks.
	 *
	 * @uses @cap - Global CheezCap object
	 *
	 * @param null
	 *
	 * @return null
	 */
	public function move_cheezcap_menu()
	{

		// Grab the global instance of CheezCap
		global $cap;

		// Remove the cheezcap 'Theme Settings' top-level menu page
		remove_menu_page('theme');

		// Remove the dependencies tied to the Cheezcap menu page hook
		remove_action('admin_print_scripts-toplevel_page_theme', array($cap, 'admin_js_libs'));
		remove_action('admin_footer-toplevel_page_theme', array($cap, 'admin_js_footer'));
		remove_action('admin_print_styles-toplevel_page_theme', array($cap, 'admin_css'));

		// Add the page back as a submenu page below 'Settings'
		// We'll now have a 'Settings > Theme Settings'
		$page_hook = add_options_page(
			'Theme Settings',
			'Theme Settings',
			'manage_options',
			// This page slug MUST be 'theme' for all this to work
			// 'theme' is the same slug which CheezCap uses for the top-level page
			// and their code contains checks to ensure they only save options for
			// this page slug.
			'theme',
			array($cap, 'display_admin_page')
		);

		// Recreate the dependencies needed for the page, using the settings page hook
		add_action("admin_print_scripts-$page_hook", array($cap, 'admin_js_libs'));
		add_action("admin_footer-$page_hook", array($cap, 'admin_js_footer'));
		add_action("admin_print_styles-$page_hook", array($cap, 'admin_css'));
	}

	/**
	 * Adds new theme settings tabs ( cheezcap group ).
	 *
	 * @param $pmc_core_cheezcap_groups
	 *
	 * @return array
	 */
	public function cheezcap_groups($pmc_core_cheezcap_groups)
	{

		if (empty($pmc_core_cheezcap_groups) || !is_array($pmc_core_cheezcap_groups)) {
			$pmc_core_cheezcap_groups = [];
		}

		$pmc_core_cheezcap_groups[] = new CheezCapGroup(
			__('Core Theme Options', 'pmc-core'),
			'pmc_core_options',
			[
				new CheezCapTextOption(
					'Newsletter URL',
					'Example "Exact Target Signup URL".',
					'pmc_core_signup_url',
					'',
					false,
					[$this, 'validate_url']
				),
				new \CheezCapTextOption(
					'Tip Page\'s URL',
					'Add tip page url on which user will redirect when click on \'Have a Tip?\' from header/footer.',
					'pmc_core_tip_us_url',
					'',
					false,
					[$this, 'validate_url']
				),
				new \CheezCapBooleanOption(
					'PMC Core Elastic Search Enabled',
					'Option to control Elastic Search query for Parent theme. Note: child theme and pmc-plugins control their own.',
					'pmc_core_es_enabled',
					false
				),
			]
		);

		return $pmc_core_cheezcap_groups;
	}

	/**
	 * Register Cheezcap settings
	 *
	 * @param array $cheezcap_options
	 * @return array $cheezecap_options
	 */
	public function add_global_cheezcap_options($cheezcap_options = array())
	{

		$this->_cheezcap_options_active = true;

		$cheezcap_options[] = new CheezCapTextOption(
			'Set max locking on curation page',
			'Set number of minutes. Leaving empty or setting to 0 disables max locking time.',
			'curation-max-time-lock',
			'',
			false,
			false
		);

		return $cheezcap_options;
	}

	/**
	 * Validate domain for singup url
	 *
	 * @param $input_id
	 * @param $url
	 *
	 * @return string
	 */
	public function validate_url($input_id, $url)
	{

		$domain = parse_url($url, PHP_URL_HOST);

		if (empty($domain)) {
			return '';
		}

		return esc_url_raw($url);
	}
}    //end of class

//EOF
