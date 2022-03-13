<?php

namespace PMC\Affiliate_Links;

use \PMC\Global_Functions\Traits\Singleton;
use \CheezCapDropdownOption;
use \CheezCapTextOption;


class Bootstrap {

	use Singleton;

	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_filter( 'pmc_cheezcap_groups', array( $this, 'add_cheezcap_options' ) );
	}

	public function add_cheezcap_options( $cheezcap_groups = array() ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		// Needed for compatibility with BGR_CheezCap
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';
		}

		$cheezcap_options = array(

			// Amazon
			new CheezCapDropdownOption(
				'Enable Amazon Affiliate Link Tagging',
				'When enabled, existing Amazon links will automatically convert to tagged referral links',
				'pmc_affiliate_links_amazon_status',
				array( 0, 1 ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new CheezCapTextOption(
				'Amazon Tag',
				'Enter the amazon tag.',
				'pmc_affiliate_links_amazon_tag',
				''
			),

			// iTunes
			new CheezCapDropdownOption(
				'Enable iTunes Affiliate Link Tagging',
				'When enabled, existing iTunes links will automatically convert to tagged referral links',
				'pmc_affiliate_links_itunes_status',
				array( 0, 1 ),
				0, // First option => Disabled
				array( 'Disabled', 'Enabled' )
			),
			new CheezCapTextOption(
				'iTunes Tag',
				'Enter the iTunes tag.',
				'pmc_affiliate_links_itunes_tag',
				''
			),

		);

		//make sure namespace reference is alright
		$cheezcap_group_class = ( strpos( $cheezcap_group_class, '\\' ) === false ) ? '\\' . $cheezcap_group_class : $cheezcap_group_class;

		$cheezcap_groups[] = new $cheezcap_group_class( 'Affiliate Links', 'pmc_affiliate_link_tagger', $cheezcap_options );

		return $cheezcap_groups;

	}	//add_cheezcap_options()

}	//end class



//EOF
