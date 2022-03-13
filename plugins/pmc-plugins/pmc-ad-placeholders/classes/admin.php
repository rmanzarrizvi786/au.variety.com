<?php

namespace PMC\Ad_Placeholders;

use \PMC\Global_Functions\Traits\Singleton;

/*
 * Render admin UI elements to assist with
 * creating ad placeholders.
 */
class Admin {

	use Singleton;

	/**
	 * Class instantiation.
	 *
	 * Hook into WordPress.
	 */
	protected function __construct() {

		add_filter( 'pmc_adm_locations', array( $this, 'filter_pmc_adm_locations' ) );

		// Add an 'Ad Placeholders' cheezcap group
		add_filter( 'pmc_cheezcap_groups', array( $this, 'filter_pmc_cheezcap_groups' ) );
	}

	/**
	 * Added a cheezcap to enable the plugin
	 *
	 * @param array $cheezcap_groups List of cheezcap options.
	 *
	 * @return array $cheezcap_groups
	 */
	public function filter_pmc_cheezcap_groups( array $cheezcap_groups = array() ) {

		// Add an 'Ad Placeholders' cheezcap group
		$native_unit_positions = range( 0, 10 );
		$cheezcap_groups[]     = new \CheezCapGroup( __( 'Ad Placeholders', 'pmc-ad-placeholders' ), 'pmc-ad-placeholders', array(

			// Enable/disable
			new \CheezCapDropdownOption(
				__( 'Create placeholder divs for ads?', 'pmc-ad-placeholders' ),
				__( 'This option will create empty divs for 3rd-party ad vendors to render ads into.', 'pmc-ad-placeholders' ),
				'pmc-ad-placeholders-enable',
				array( 'disabled', 'enabled' ),
				0,
				array( __( 'Disabled', 'pmc-ad-placeholders' ), __( 'Enabled', 'pmc-ad-placeholders' ) )
			),
			new \CheezCapTextOption(
				__( 'Desktop - Position 1', 'pmc-ad-placeholders' ),
				__( 'After how many characters first div should be inserted. Minimum 550 applies if nothing is set', 'pmc-ad-placeholders' ),
				'pmc-ad-placeholders-first-pos',
				550, // default value.
				false, // Not a textarea.
				array( $this, 'validate_cheezcap_numeric' )
			),
			new \CheezCapTextOption(
				__( 'Desktop - Position 2', 'pmc-ad-placeholders' ),
				__( 'After how many characters second div should be inserted. Minimum 2300 applies if nothing is set', 'pmc-ad-placeholders' ),
				'pmc-ad-placeholders-second-pos',
				2300, // default value.
				false, // Not a textarea.
				array( $this, 'validate_cheezcap_numeric' )
			),
			new \CheezCapTextOption(
				__( 'Desktop - Position X', 'pmc-ad-placeholders' ),
				__( 'After how many characters from second article the auto mid article should start', 'pmc-ad-placeholders' ),
				'pmc-ad-placeholders-x-pos',
				0, // default value.
				false, // Not a textarea.
				array( $this, 'validate_cheezcap_numeric' )
			),
			new \CheezCapTextOption(
				__( 'Mobile - Position 1', 'pmc-ad-placeholders' ),
				__( 'After how many characters first div should be inserted. Minimum 550 applies if nothing is set', 'pmc-ad-placeholders' ),
				'pmc-ad-placeholders-first-pos-mobile',
				550, // default value.
				false, // Not a textarea.
				array( $this, 'validate_cheezcap_numeric' )
			),

			new \CheezCapTextOption(
				__( 'Mobile - Position 2', 'pmc-ad-placeholders' ),
				__( 'After how many characters second div should be inserted. Minimum 2300 applies if nothing is set', 'pmc-ad-placeholders' ),
				'pmc-ad-placeholders-second-pos-mobile',
				2300, // default value.
				false, // Not a textarea.
				array( $this, 'validate_cheezcap_numeric' )
			),
			new \CheezCapTextOption(
				__( 'Mobile - Position X', 'pmc-ad-placeholders' ),
				__( 'After how many characters from second article the auto mid article should start', 'pmc-ad-placeholders' ),
				'pmc-ad-placeholders-x-pos-mobile',
				0, // default value.
				false, // Not a textarea.
				array( $this, 'validate_cheezcap_numeric' )
			),
			new \CheezCapDropdownOption(
				__( 'Homepage Native river ad unit', 'pmc-ad-placeholders' ),
				__( 'Select position for homepage native river ad unit', 'pmc-ad-placeholders' ),
				'pmc-homepage-native-river-unit',
				$native_unit_positions,
				0, // disabled by default
				$native_unit_positions
			),
			new \CheezCapDropdownOption(
				__( 'Vertical Native river ad unit', 'pmc-ad-placeholders' ),
				__( 'Select position for vertical native river ad unit', 'pmc-ad-placeholders' ),
				'pmc-vertical-native-river-unit',
				$native_unit_positions,
				0, // disabled by default
				$native_unit_positions
			),
		) );

		return $cheezcap_groups;
	}

	/**
	 * Adding new ad locations
	 * @param array $locations Ad locations.
	 *
	 * @return array Ad locations.
	 */
	public function filter_pmc_adm_locations( $locations = [] ) {
		$locations['native-river-ad'] = [
			'title'     => __( 'Native River Unit', 'pmc-ad-placeholders' ),
			'providers' => [ 'boomerang', 'google-publisher' ],
		];
		return $locations;
	}

	/**
	 * Validation callback function of cheez cap option.
	 *
	 * @param  string $id Cheez cap option id that need to validate.
	 * @param  mix    $value New value of cheez cap option.
	 *
	 * @return boolean|int New value of cheez cap option.
	 */
	public function validate_cheezcap_numeric( $id, $value ) {

		if ( ! empty( $value ) && is_numeric( $value ) ) {
			return absint( $value );
		}

		return false;
	}

}
