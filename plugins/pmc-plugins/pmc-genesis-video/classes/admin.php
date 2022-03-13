<?php
namespace PMC\Genesis_Video;

use PMC\Global_Functions\Traits\Singleton;
use \CheezCapDropdownOption;


class Admin {

	use Singleton;

	protected function __construct() {
        add_filter( 'pmc_cheezcap_groups', array( $this, 'set_cheezcap_group' ) );
    }

    /**
     * @param array $cheezcap_groups
     * @return array
     * add cheezcap options to enable and disable the positions of Genesis ads.
     */
    public function set_cheezcap_group( $cheezcap_groups = array() ){

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
            new CheezCapDropdownOption(
                'Enable Genesis Ad Position 1',
                'Enable or Disable Genesis Ad Position 1',
                'pmc_genesis_ad_position_one',
                array(0, 1),
                1, // First option => Enabled
                array('Disabled', 'Enabled')
            ),
            new CheezCapDropdownOption(
                'Enable Genesis Ad Position 2',
                'Enable or Disable Genesis Ad Position 2',
                'pmc_genesis_ad_position_two',
                array(0, 1),
                1, // First option => Enabled
                array('Disabled', 'Enabled')
            )
        );
        $cheezcap_groups[] = new $cheezcap_group_class( "Genesis Ads Settings", "pmc_genesis_adblock_group", $cheezcap_options );


        return $cheezcap_groups;
    }
}
