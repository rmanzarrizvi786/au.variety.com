<?php

namespace PMC\Sonobi;

use \PMC\Global_Functions\Traits\Singleton;

class Loader {

	use Singleton;

	const PMC_SONOBI_OPTION = 'pmc_sonobi_enable';

	private static $_scripts_rendered = false;

	protected function __construct() {
		$this->_setup_hooks();
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'filter_pmc_global_cheezcap_options' ) );
	}


	private function _setup_hooks()
	{
		add_action( 'wp_print_scripts', array( $this, 'head_tag' ), 4 );
	}


	public function head_tag()
	{
		if ( ! empty( self::$_scripts_rendered ) ) {
			return;
		}

		if ( \PMC_Cheezcap::get_instance()->get_option( self::PMC_SONOBI_OPTION ) && ! is_admin() ) {
			?>
				<!-- Sonbi tag start -->
				<script src="//mtrx.go.sonobi.com/morpheus.penske.2508.js" async></script>
				<!-- Sonbi tag end -->
			<?php
			self::$_scripts_rendered = true;
		}
	}


	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() )
	{

		if ( empty( $cheezcap_options ) || ! is_array( $cheezcap_options ) ) {
			$cheezcap_options = array();
		}

		$cheezcap_options[] = new \CheezCapDropdownOption(
			'Enable Sonobi',
			'Enable/Disable Sonobi header script',
			self::PMC_SONOBI_OPTION,
			array( 0, 1 ),
			0,
			array( 'Disabled', 'Enabled' )
		);

		return $cheezcap_options;

	}


}
