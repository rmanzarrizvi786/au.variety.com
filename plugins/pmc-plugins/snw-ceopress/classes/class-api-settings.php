<?php
/**
 * API_Settings class will create the admin setting panel under theme option
 * And allows to get configuration data.
 */

namespace SNW\CEO_Press;

use \PMC\Global_Functions\Traits\Singleton;

class API_Settings {

	use Singleton;

	/**
	 * Class instantiation.
	 *
	 * Hook into WordPress.
	 */
	protected function __construct() {
		// Add an 'Ad Placeholders' cheezcap group
		add_filter( 'pmc_cheezcap_groups', [ $this, 'add_ceo_cheezcap' ] );
	}

	/**
	 * Added a cheezcap to enable the plugin
	 *
	 * @param array $cheezcap_groups List of cheezcap options.
	 *
	 * @return array $cheezcap_groups
	 */
	public function add_ceo_cheezcap( $cheezcap_groups = [] ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = [];
		}

		// Add an 'Ad Placeholders' cheezcap group
		$client_codes = [ '', 'wwd', 'fn', 'var', 'rob' ];
		$client_names = [ '-- Select Publication --', 'Womens Wear Daily', 'Footwear News', 'Variety', 'Robb Report' ];

		$cheezcap_groups[] = new \CheezCapGroup( 'CEO API Settings', 'ceo-api-settings', [

			new \CheezCapTextOption(
				'API Key',
				'To find your publications API key login to CEO and navigate to Settings > Developer Access. Copy the private API key into this field.',
				'snw-ceopress-key',
				''
			),

			new \CheezCapDropdownOption(
				'Active Publication',
				'Select the active publication.',
				'snw-ceopress-client-code',
				$client_codes,
				0,
				$client_names
			),

		] );

		return $cheezcap_groups;

	}

	/**
	 * Function to get Configuration API key and URL.
	 *
	 * @return array|false returns false if plugin not configured else returns array of Configuration API
	 */
	public function get_config() {

		$key    = \PMC_Cheezcap::get_instance()->get_option( 'snw-ceopress-key' );
		$client = \PMC_Cheezcap::get_instance()->get_option( 'snw-ceopress-client-code' );

		$config = [
			'key'    => $key,
			'client' => $client,
			'url'    => sprintf( 'https://%s.ceo.getsnworks.com/v3/', $client ),
		];

		if ( ! empty( $key ) && ! empty( $client ) ) {
			return $config;
		}

		return false;

	}

}


//EOF
