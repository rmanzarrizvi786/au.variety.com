<?php

/**
 * Handles Smart App Banner,
 *
 * If enabled the user will be prompted with a banner to checkout the app
 * related to the website, and as per the id provided.
 *
 * @since 2018-04-27 PMCEED-456
 */

namespace PMC\Global_Functions;

use PMC\Global_Functions\Traits\Singleton;

class Smart_App_Banners
{

	use Singleton;

	/**
	 * Smart Banner cheezcap options,
	 *
	 * @var $_smart_banner_options
	 */
	protected $_smart_banner_options = [];

	/**
	 * Constructor.
	 */
	protected function __construct()
	{

		$this->_setup_hooks();
	}

	/**
	 * Sets up the actions/filters.
	 *
	 */
	protected function _setup_hooks()
	{

		// Render meta tags for the banner.
		add_action('wp_head', [$this, 'output_meta_tags'], 8);

		// Add CheezCap options.
		add_filter('pmc_cheezcap_groups', [$this, 'create_smart_banner_cheezcap_group']);

		add_action('init', [$this, 'set_options']);
		add_action('parse_query', [$this, 'manifest_load']);
		add_filter('query_vars', [$this, 'add_query_vars']);
	}

	/**
	 * Initialize cheezcap settings for Smart Banner, and required functions.
	 *
	 * @since 2018-06-04 PMCEED-456
	 */
	public function set_options()
	{

		$this->_set_smart_options();
		$this->manifest_init();
	}

	/**
	 * Set required cheezcap settings in a protected variable to be reused later on.
	 *
	 * @since 2018-06-04 PMCEED-456
	 */
	protected function _set_smart_options()
	{

		$cheezcap_instance = \PMC_Cheezcap::get_instance();

		$this->_smart_banner_options = [
			'ios_app_id'              => $cheezcap_instance->get_option('smart_banner_app_ios_app_id'),
			'android_app_id'          => $cheezcap_instance->get_option('smart_banner_app_android_app_id'),
			'is_smart_banner_enabled' => ('enabled' === $cheezcap_instance->get_option('smart_banner_app_activated')),
		];
	}

	/**
	 * Helper function to check if the smart banner is enabled and should it show on android.
	 *
	 * @since 2018-06-04 PMCEED-456
	 *
	 * @return bool
	 */
	protected function _maybe_show_banner()
	{

		if (
			isset($this->_smart_banner_options['is_smart_banner_enabled'])
			&& true === $this->_smart_banner_options['is_smart_banner_enabled']
		) {
			return true;
		}

		return false;
	}

	/**
	 * @param $cheezcap_groups
	 *
	 * @return array
	 */
	public function create_smart_banner_cheezcap_group($cheezcap_groups)
	{

		if (empty($cheezcap_groups) || !is_array($cheezcap_groups)) {
			$cheezcap_groups = [];
		}

		$cheezcap_options = [
			new \CheezCapDropdownOption(
				esc_html__('Enable Smart Banner for iOS', 'pmc-global-functions'),
				esc_html__('Enable Smart Banner for the brand which will show up on mobile.', 'pmc-global-functions'),
				'smart_banner_app_activated',
				array('disabled', 'enabled'),
				0, // First option => Disabled
				['Disabled', 'Enabled']
			),
			new \CheezCapTextOption(
				esc_html__('iOS App ID', 'pmc-global-functions'),
				esc_html__('Enter iOS app id, this is the 9 digit id given in the app store.', 'pmc-global-functions'),
				'smart_banner_app_ios_app_id',
				'',
				false
			),
			new \CheezCapTextOption(
				esc_html__('Android App ID', 'pmc-global-functions'),
				esc_html__('Enter Android app id, this is the complete play store url, i.e., com.google.example.app.id', 'pmc-global-functions'),
				'smart_banner_app_android_app_id',
				'',
				false
			),
		];

		$cheezcap_groups[] = new \CheezCapGroup(esc_html__('Smart Banner Options', 'pmc-global-functions'), 'pmc_smart_banner_options', $cheezcap_options);

		return $cheezcap_groups;
	}

	/**
	 * Renders meta tags for the smartbanner.js to utilise.
	 *
	 * @since 2018-04-21 PMCEED-456
	 */
	public function output_meta_tags()
	{

		if (!$this->_smart_banner_options['is_smart_banner_enabled']) {
			return;
		}

		\PMC::render_template(
			sprintf('%s/templates/smart-banner.php', untrailingslashit(PMC_GLOBAL_FUNCTIONS_PATH)),
			['smart_banner_options' => $this->_smart_banner_options],
			true
		);
	}

	/**
	 * Initialise Manifest.json rewrite rules, this is web manifest,
	 * chrome uses this file to show install app banners, set on homescreen options.
	 *
	 * This works with chrome and other vendors on iOS/Android. Chrome decides when to show
	 * this banners based on outlined criteria.
	 *
	 */
	public function manifest_init()
	{

		if (!$this->_maybe_show_banner()) {
			return;
		}

		//register rewrite rule for manifest.json request
		add_rewrite_rule('manifest\.json$', 'index.php?manifest_json=1', 'top');
	}

	/**
	 * Add 'manifest_json' query variable to public query variable array.
	 *
	 * @param $qv
	 *
	 * @return array
	 */
	public function add_query_vars($qv)
	{

		if (!$this->_maybe_show_banner()) {
			return $qv;
		}

		$query_vars = ['manifest_json'];

		return array_merge($qv, $query_vars);
	}

	/**
	 * Check if current page is manifest.json or not.
	 *
	 * @return bool
	 */
	public function is_manifest_json()
	{

		if ('1' === get_query_var('manifest_json')) {
			return true;
		}

		return false;
	}

	/**
	 * load manifest.json content.
	 *
	 * The web app manifest is a JSON text file, which provides information about
	 * the application. Web app manifest is required by chrome to show 'Add to Home Screen'
	 * prompt and also 'App Install' banners.
	 *
	 * This works for iOS as well as Android, chrome browsers.
	 *
	 */
	public function manifest_load()
	{

		if (!$this->_maybe_show_banner()) {
			return;
		}

		if ($this->is_manifest_json()) {
			$this->manifest_json();
		}
	}

	/**
	 * Generates web app manifest file based on the information provided.
	 *
	 * Will use the blavatar if available, else will look for icons in '/assets/build/images/favicons/', of
	 * theme directory.
	 * We use 'favicon-{size}x{size}.png' for default file names for the icons.
	 *
	 * @param bool $return_data_array Return manifest data as an array, rather than echoing JSON.
	 */
	public function manifest_json($return_data_array = false)
	{

		if (!$this->_maybe_show_banner()) {
			if ($return_data_array) {
				return [];
			}

			// Cannot cover due to `die` call.
			wp_send_json([]); // @codeCoverageIgnore
		}

		$is_blavatar_available = false;

		// Make sure we have blavatar support.
		if (
			function_exists('blavatar_current_domain') &&
			function_exists('blavatar_exists') &&
			function_exists('blavatar_url')
		) {
			$is_blavatar_available = blavatar_exists(blavatar_current_domain());
		}

		$icons      = [];
		$icon_sizes = ['48', '96', '192', '512'];

		if ($is_blavatar_available) {

			foreach ($icon_sizes as $blavatar_size) {
				$icons[] = [
					'src'   => blavatar_url(blavatar_current_domain(), 'img', $blavatar_size),
					'sizes' => "{$blavatar_size}x{$blavatar_size}",
					'type'  => 'image/png',
				];
			}
		} else {

			/**
			 * Filter to update the favicon path for different file structures.
			 *
			 */
			$icon_dir_path = apply_filters('pmc_manifest_favicon_path', '/assets/build/images/favicons/');
			$icon_dir_uri  = untrailingslashit(get_stylesheet_directory_uri()) . $icon_dir_path;
			$icon_dir_path = untrailingslashit(get_stylesheet_directory()) . $icon_dir_path;

			foreach ($icon_sizes as $icon_size) {

				$file_path = sprintf("%s/favicon-{$icon_size}x{$icon_size}.png", untrailingslashit($icon_dir_path));
				$file_uri  = sprintf("%s/favicon-{$icon_size}x{$icon_size}.png", untrailingslashit($icon_dir_uri));

				if (!file_exists($file_path)) {
					continue;
				}

				$icons[] = [
					'src'   => $file_uri,
					'sizes' => "{$icon_size}x{$icon_size}",
					'type'  => 'image/png',
				];
			}
		}

		$support_for = [];
		$platforms   = [
			'ios'     => 'itunes',
			'android' => 'play',
		];

		foreach ($platforms as $platform => $store) {

			if ($this->_smart_banner_options["{$platform}_app_id"]) {

				$support_for[] = [
					'platform' => $store,
					'id'       => $this->_smart_banner_options["{$platform}_app_id"],
				];
			}
		}

		/**
		 * [
		 * 'name'|'short_name' => short_name is used on the user's home screen, launcher,
		 *                        or other places where space may be limited. name is used on the app install prompt.,
		 * 'scope' => set of urls that browser considers withing our app,
		 * 'icons' => icons is an array of image objects, each object should include the src, a sizes property, and the type of image.
		 * 'start_url' => The start_url tells the browser where your application should start when it is launched.
		 * ]
		 *
		 * @see https://developers.google.com/web/fundamentals/web-app-manifest/
		 *
		 */
		$manifest = [
			'name'                        => get_bloginfo('name'),
			'short_name'                  => get_bloginfo('name'),
			'scope'                       => '/',
			'icons'                       => $icons,
			'prefer_related_applications' => true,
			'related_applications'        => $support_for,
			'start_url'                   => '/',
			'display'                     => 'standalone',
		];

		/**
		 * Filter manifest JSON file, for any custom requirements.
		 */
		$manifest = apply_filters('pmc_manifest_json', $manifest);

		if ($return_data_array) {
			return $manifest;
		}

		// Cannot cover due to `die` call.
		wp_send_json($manifest); // @codeCoverageIgnore

	}
}

Smart_App_Banners::get_instance();
