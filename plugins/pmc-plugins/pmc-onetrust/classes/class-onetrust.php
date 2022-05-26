<?php

/**
 * PMC Onetrust
 *
 * @author Reef Fanous <rfanous@pmc.com>
 *
 * @group pmc-onetrust
 */

namespace PMC\Onetrust;

use PMC;
use PMC\Global_Functions\Traits\Singleton;
use PMC_Cheezcap;

class Onetrust
{
	use Singleton;

	public $config;

	/**
	 * Construct
	 */
	protected function __construct()
	{
		$this->_setup_hooks();
	}

	/**
	 *Setting actions & filters
	 */
	protected function _setup_hooks()
	{
		add_action('init', [$this, 'setup_config']);
		add_action('wp_head', [$this, 'action_onetrust_header_tags'], 9); // This has to be called early on the page
		add_action('wp_footer', [$this, 'action_onetrust_footer_js']);
		add_action('wp_enqueue_scripts', [$this, 'action_wp_enqueue_scripts']);
		add_filter('pmc_cheezcap_groups', array($this, 'filter_pmc_cheezcap_groups'));
		add_filter('script_loader_tag', array($this, 'may_be_block_tag'), 11, 2); // This needs to be happen little later than normal
	}

	/**
	 * Set class $config with theme configs
	 *
	 * @return void
	 */
	public function setup_config(): void
	{
		$this->config = apply_filters('pmc_onetrust_config', []);
	}

	/**
	 * Adds OneTrust script tags
	 *
	 * @return void
	 */
	public function action_onetrust_header_tags(): void
	{

		if (
			$this->is_onetrust_enabled() &&
			!PMC::is_amp() &&
			is_array($this->config) &&
			!empty($this->config['site_id'])
		) {

			\PMC::render_template(
				PMC_ONETRUST_DIR . '/templates/header-tags.php',
				$this->config,
				true
			);
		}
	}

	/**
	 * Adds footer JavaScript for link hide/show functionality for OneTrust integration
	 *
	 * @return void
	 */
	public function action_onetrust_footer_js(): void
	{

		if (
			$this->is_onetrust_enabled() &&
			!PMC::is_amp() &&
			is_array($this->config) &&
			!empty($this->config['site_id'])
		) {
			\PMC::render_template(
				PMC_ONETRUST_DIR . '/templates/footer-js.php',
				$this->config,
				true
			);
		}
	}

	/**
	 * Added a cheezcap to enable the plugin
	 *
	 * @param array $cheezcap_groups List of cheezcap options.
	 *
	 * @return array $cheezcap_groups
	 */
	public function filter_pmc_cheezcap_groups(array $cheezcap_groups = [])
	{

		// Add an 'OneTrust' cheezcap group
		$cheezcap_groups[]     = new \CheezCapGroup(__('OneTrust', 'pmc-onetrust'), 'pmc-onetrust', [

			// Enable/disable
			new \CheezCapDropdownOption(
				wp_strip_all_tags(__('Enable OneTrust', 'pmc-onetrust'), true),
				wp_strip_all_tags(
					__('This option will enable OneTrust privacy banners', 'pmc-onetrust'),
					true
				),
				'pmc-onetrust-option',
				array('none', 'all'),
				0,
				array(
					wp_strip_all_tags(__('Disabled', 'pmc-onetrust'), true),
					wp_strip_all_tags(__('Enable OneTrust for All', 'pmc-onetrust'), true),
				)
			)
		]);

		return $cheezcap_groups;
	}

	/**
	 * Determine which Onetrust Cheezcap setting is enabled
	 *
	 * @return bool
	 */
	public function is_onetrust_enabled(): bool
	{
		if (false === apply_filters('pmc_onetrust', true)) {
			return false;
		}

		$onetrust = PMC_Cheezcap::get_instance()->get_option('pmc-onetrust-option');

		if ('all' === $onetrust) {
			return true;
		}

		return false;
	}

	/**
	 * Determine request geolocation
	 * Adjust cookie blocking type and class attributes per OneTrust
	 *
	 * @param string $cookie_category
	 *
	 * @return array $blocker_atts
	 */
	public function block_cookies_script_type($cookie_category = ''): array
	{
		$blocker_atts = [
			'type'  => 'text/javascript',
			'class' => $cookie_category,
		];

		if (class_exists('\PMC\Geo_Uniques\Plugin') && $this->is_onetrust_enabled()) {

			// If force block is set then there is no need to check for geo code.
			// This is for the sites that or not on WP VIP environments
			if (true === apply_filters('pmc_onetrust_force_block_cookie_scripts', false)) {
				$blocker_atts['type'] = 'text/plain';
				return $blocker_atts;
			}

			$region = \PMC\Geo_Uniques\Plugin::get_instance()->pmc_geo_get_region_code();

			$block_scripts = apply_filters('pmc_onetrust_block_cookie_scripts', true);

			if (
				('eu' === $region || 'eu' === PMC::filter_input(INPUT_GET, 'region')) &&
				true === $block_scripts
			) {
				$blocker_atts['type'] = 'text/plain';
			}
		}

		return $blocker_atts;
	}

	/**
	 * Enqueue PMC Onetrust js file
	 *
	 */
	public function action_wp_enqueue_scripts()
	{

		$region = '';

		if (class_exists('\PMC\Geo_Uniques\Plugin') && $this->is_onetrust_enabled() && !is_admin() && !\PMC::is_amp()) {
			$region = \PMC\Geo_Uniques\Plugin::get_instance()->pmc_geo_get_region_code();
			$region = ('eu' === PMC::filter_input(INPUT_GET, 'region')) ? 'eu' : $region; // local/qa env need this
		}

		if ('eu' === $region || true === apply_filters('pmc_onetrust_force_block_cookie_scripts', false)) {
			$js_extension = (\PMC::is_production()) ? '.min.js' : '.js';
			wp_enqueue_script('pmc-onetrust-js', plugins_url(sprintf('js/onetrust%s', $js_extension), __DIR__), [], PMC_ONETRUST_VERSION);
		}
	}


	/**
	 * Add script blocker attributes
	 *
	 * @param string $tag The tag.
	 * @param string $handle The handle.
	 * @return string|null
	 */
	public function may_be_block_tag($tag, $handle): ?string
	{

		if (!is_admin() && !PMC::is_amp()) {
			$scripts_to_block = [
				'yappa-comments-js',
				'pmc-async-outbrain-partner-js-js',
			];

			$blocker_atts = $this->block_cookies_script_type('optanon-category-C0004');

			if (in_array($handle, (array) $scripts_to_block, true)) {
				$replacement_str = sprintf('script type="%s" class="%s" ', $blocker_atts['type'], $blocker_atts['class']);
				$tag             = str_replace('type="text/javascript"', '', $tag);
				$tag             = str_replace('script ', $replacement_str, $tag);
			}
		}

		return $tag;
	}
}
