<?php

/**
 * Plugin Name: Rubicon Real Time Pricing
 * Description: Rubicon Real Time Pricing
 * Author: PMC
 * Version: 1.0
 * License: PMC Proprietary.  All rights reserved.
 *
 * activate plugin via pmc_load_plugin
 *
 * Add rtp settings examples:
 *
 *	PMC_Rubicon::get_instance()
 *			->set_rtp_default( array(
 *					'api'  => 'valuation',
 *					'site' => '11714/37948',
 *					'zone' => '158878',
 *				) )
 *			->add_ad_slot_sizes( array( '300x250', '728x90', '160x600' ) );
 *
 * PMC_Rubicon::get_instance()->add_rtp(
 *				array(
 *					'api'  => 'valuation',
 *					'site' => '11714/37948',
 *					'zone' => '158878',
 *					'ad_slot_size' => '300x250',
 *				)
 *		);
 */

use \PMC\Global_Functions\Traits\Singleton;

final class PMC_Rubicon {

	use Singleton;

	private $_rtp_list    = array();
	private $_rtp_default = array();

	protected function __construct() {
		add_filter( 'pmc_global_cheezcap_options', array( $this, "filter_pmc_global_cheezcap_options" ) );
		add_action( 'init', array( $this, 'action_init' ) );
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function action_init() {
		add_action( 'pmc_tags_head', array( $this, 'action_pmc_tags_head' ) );
	}

	/**
	 * Add Rubicon cheezcap settings
	 */
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {

		if ( empty( $cheezcap_options ) || ! is_array( $cheezcap_options ) ) {
			$cheezcap_options = array();
		}

		$cheezcap_options[] = new CheezCapDropdownOption(
					'Rubicon RTP',
					'Toggle Rubicon real time pricing scripts',
					'pmc_rubicon_rtp_option',
					array( 'disabled', 'enabled' ),
					0, // First option => Disabled
					array( 'Disabled', 'Enabled' )
				);

		return $cheezcap_options;
	}

	/**
	 * Add rubicon rtp script configuration
	 * @param array $args (
	 *           'api'           => string,
	 *           'site'          => string,
	 *           'zone'          => string,
	 *           'ad_slot_size'  => string,
	 *        )
	 * @return object $this
	 */
	public function add_rtp( $args ) {
		$this->_rtp_list[] = $args;
		return $this;
	}

	/**
	 * @see PMC_Rubicon:add_rtp
	 * @return object $this;
	 */
	public function set_rtp_default( $args ) {
		$this->_rtp_default = $args;
		return $this;
	}

	/**
	 * @param array $sizes
	 * @return object $this
	 */
	public function add_ad_slot_sizes( $sizes ) {
		if ( empty( $sizes ) ) {
			return;
		}

		foreach ( $sizes as $size ) {
			$this->add_rtp( array( 'ad_slot_size' => $size ) );
		}

		return $this;
	}

	// wp action 'pmc_tags_head'.
	public function action_pmc_tags_head() {
		if ( empty( $this->_rtp_list ) ) {
			return;
		}

		$rubicon_rtp = cheezcap_get_option( 'pmc_rubicon_rtp_option', false );

		if ( empty( $rubicon_rtp ) || 'enabled' != $rubicon_rtp ) {
			return;
		}

		foreach ( $this->_rtp_list as $item ) {
			$this->_render_rtp_script( $item );
		}
	}

	/**
	 * function to render javascript for Rubicon RTP script
	 * @see PMC_Rubicon::add_rtp
	 */
	private function _render_rtp_script( $args ) {
		if ( empty( $args ) ) {
			return;
		}
		?>

<script type="text/javascript">
<?php
			$valid_keys = array( 'api', 'site', 'zone', 'ad_slot_size' );
			$site = '';

			$args = wp_parse_args( $args, $this->_rtp_default );

			foreach ( $args as $key => $value ) {

				if ( !in_array( $key, $valid_keys ) ) {
					continue;
				}

				printf ("	oz_%s=\"%s\";\n", sanitize_file_name( $key ), esc_js( $value ) );
			}
		?>
</script>
<script type="text/javascript" src="<?php echo PMC::esc_url_ssl_friendly( 'http://tap-cdn.rubiconproject.com/partner/scripts/rubicon/dorothy.js'. ( !empty( $args['site'] ) ? '?pc=' . $args['site'] : '' ) ); ?>"></script>
<?php
	} // function

} // class

PMC_Rubicon::get_instance();
