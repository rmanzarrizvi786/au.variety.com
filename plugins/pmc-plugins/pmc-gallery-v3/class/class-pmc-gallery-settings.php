<?php

/**
 * Add Options to Media Settings Page
 *
 * @package PMC Gallery Plugin
 * @since 1/1/2013 Vicky Biswas
 *
 * Holds code needed to add options to the Media Settings Page
 *
 * @codeCoverageIgnore
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Gallery_Settings
{

	use Singleton;

	/**
	 * Manages the Settings for PMC Gallery
	 */

	protected function __construct()
	{
		add_action('pmc_cheezcap_groups', array($this, "action_pmc_cheezcap_groups"));
	}

	public function action_pmc_cheezcap_groups($cheezcap_groups = array())
	{

		if (empty($cheezcap_groups) || !is_array($cheezcap_groups)) {
			$cheezcap_groups = array();
		}

		// Needed for compatibility with BGR_CheezCap
		if (class_exists('BGR_CheezCapGroup')) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';
		}

		$cheezcap_options = array(
			new CheezCapDropdownOption(
				'Add photo to beginning of gallery',
				'When enabled, new photo will be add to beginning of gallery',
				'pmc_gallery_prepend',
				array('disabled', 'enabled'),
				0, // First option => Disabled
				array('Disabled', 'Enabled')
			),
			new CheezCapDropdownOption(
				'Enable gallery interstitial',
				'When enabled, gallery interstitial ads will render see PMC Ad manager for ad placement',
				'pmc_gallery_interstitial',
				array('disabled', 'enabled'),
				0, // First option => Disabled
				array('Disabled', 'Enabled')
			),
			new CheezCapDropdownOption(
				'Display gallery interstitial \'Skip Ad\' link',
				'When enabled, users can see/click a \'Skip Ad\' link to advance the slideshow and effectively skip the ad.',
				'pmc_gallery_interstitial_skip_ad',
				array('disabled', 'enabled'),
				0, // First option => Disabled
				array('Disabled', 'Enabled')
			),
			new CheezCapTextOption(
				'Number of clicks before ad refresh',
				'Enter number > 0 to enable ad refresh',
				'pmc_gallery_ad_refresh_clicks',
				2
			),
			new CheezCapTextOption(
				'Number of clicks before interstitial ad is refresh',
				'Enter number > 0 to enable interstitial ad refresh',
				'pmc_gallery_interstitial_ad_refresh_clicks',
				25
			),
			new CheezCapTextOption(
				'Interstitial duration/countdown in seconds',
				'Enter number > 0 to enable interstitial coutdown in seconds',
				'pmc_gallery_interstitial_duration',
				0
			),
			new CheezCapDropdownOption(
				'Start gallery with interstitial',
				'When enabled, gallery interstitial will render before gallery image is display',
				'pmc_gallery_start_with_interstitial',
				array('no', 'yes'),
				0, // First option => Disabled
				array('No', 'Yes')
			),
			new CheezCapDropdownOption(
				'Hide other ads when interstitial is visible',
				'When enabled, all other ads will be hidden when interstitial ad is rendered',
				'pmc_gallery_interstital_hide_ads',
				array('no', 'yes'),
				0, // First option => Disabled
				array('No', 'Yes')
			),
			new CheezCapTextOption(
				"Don't show interstitials on these galleries",
				'Comma delimited post IDs, e.g.: 123,456,789',
				'pmc_gallery_interstitial_no_ads',
				null
			),
			new CheezCapTextOption(
				'Auto start delay in seconds',
				'Enter value in seconds > 0 to enable auto start. Image will auto advance.',
				'pmc_gallery_auto_start_delay',
				0
			),
			new CheezCapDropdownOption(
				'Continue cycle through images',
				'When enable, images are cycling through continuously',
				'pmc_gallery_continuous_cycle',
				array('no', 'yes'),
				0, // First option => Disabled
				array('No', 'Yes')
			),
			new CheezCapDropdownOption(
				'Enable Pinterest Description for images',
				'When enabled, creators can define a Pinterest Description for each image in the gallery.',
				'pmc_gallery_enable_pinterest_description',
				array('no', 'yes'),
				0, // First option => Disabled
				array('No', 'Yes')
			),
		);

		$cheezcap_options = apply_filters('pmc_gallery_cheezcap_options', $cheezcap_options);
		$cheezcap_groups[] = new $cheezcap_group_class("Gallery Options", "pmc_gallery_cheezcap", $cheezcap_options);

		return $cheezcap_groups;
	}

	public function get_options()
	{
		$options = array(
			'ad_refresh_clicks'           => intval(PMC_Cheezcap::get_instance()->get_option('pmc_gallery_ad_refresh_clicks')),
			'enable_interstitial'         => 'enabled' == PMC_Cheezcap::get_instance()->get_option('pmc_gallery_interstitial'),
			'skip_ad'                     => 'enabled' == PMC_Cheezcap::get_instance()->get_option('pmc_gallery_interstitial_skip_ad'),
			'interstitial_refresh_clicks' => intval(PMC_Cheezcap::get_instance()->get_option('pmc_gallery_interstitial_ad_refresh_clicks')),
			'interstitial_duration'       => intval(PMC_Cheezcap::get_instance()->get_option('pmc_gallery_interstitial_duration')),
			'start_with_interstitial'     => 'yes' == PMC_Cheezcap::get_instance()->get_option('pmc_gallery_start_with_interstitial'),
			'auto_start_delay'            => intval(PMC_Cheezcap::get_instance()->get_option('pmc_gallery_auto_start_delay')),
			'continuous_cycle'            => 'yes' == PMC_Cheezcap::get_instance()->get_option('pmc_gallery_continuous_cycle'),
			'interstital_hide_ads'        => 'yes' == PMC_Cheezcap::get_instance()->get_option('pmc_gallery_interstital_hide_ads'),
			'imageparts'                  => array(),
			'multiparts'                  => array(),
		);

		return $options;
	}

	/**
	 * Check if the current post is on a "no ads" blocklist
	 * @see PMC_Ads::no_ads_on_this_post()
	 * @param int $post_id Optional
	 * @return bool
	 */
	public function no_ads_on_this_post()
	{
		$no_ads_string = PMC_Cheezcap::get_instance()->get_option('pmc_gallery_interstitial_no_ads');
		$no_ads_array = explode(',', $no_ads_string);
		$no_ads_array = array_map('intval', $no_ads_array);
		if (in_array(get_queried_object_id(), $no_ads_array)) {
			return true;
		}
		return false;
	}
}

PMC_Gallery_Settings::get_instance();

//EOF
