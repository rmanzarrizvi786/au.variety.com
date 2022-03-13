<?php
/**
 * Class for Theme settings.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2018-04-11
 *
 * @package pmc-maz
 */

namespace PMC\Maz;

use PMC\Global_Functions\Traits\Singleton;

class Settings {

	use Singleton;

	/**
	 * Const for option name for header selector of site.
	 *
	 * @var string
	 */
	const HEADER_SELECTOR_OPTION_NAME = 'pmc_maz_header_selector';

	/**
	 * Construct Method.
	 */
	protected function __construct() {
		add_filter( 'pmc_cheezcap_groups', array( $this, 'add_cheezcap_group' ) );
	}

	/**
	 * To add new tab in theme settings and cheezcap option in that.
	 *
	 * @param  array $cheezcap_groups Cheez cap group.
	 *
	 * @return \CheezCapGroup Cheez cap group.
	 */
	public function add_cheezcap_group( $cheezcap_groups = array() ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		$options = array();

		$options[] = new \CheezCapTextOption(
			wp_strip_all_tags( __( 'Header selector', 'pmc-maz' ), true ),
			wp_strip_all_tags( __( 'Css selector for header element, so that will be hidden in mobile application. E.g. "#site-wrap > header"', 'pmc-maz' ), true ),
			self::HEADER_SELECTOR_OPTION_NAME,
			''
		);

		$options = apply_filters( 'pmc_maz_cheezcap_options', $options );

		$cheezcap_groups[] = new \CheezCapGroup( 'PMC Maz', 'pmc_maz_group', $options );

		return $cheezcap_groups;
	}

	/**
	 * To get setting value.
	 *
	 * @return string
	 */
	public function get_header_selector() {
		return \PMC_Cheezcap::get_instance()->get_option( self::HEADER_SELECTOR_OPTION_NAME );
	}

}
