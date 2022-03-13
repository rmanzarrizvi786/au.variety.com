<?php
namespace PMC\CCPA;

use PMC;
use PMC\Global_Functions\Traits\Singleton;
use PMC_Cheezcap;

class Plugin {

	use Singleton;

	/**
	 * Set up hooks
	 *
	 * @since 2019-12-30 Vinod Tella ROP-2021
	 */
	protected function __construct() {

		add_filter( 'pmc_global_cheezcap_options', [ $this, 'filter_add_cmp_cheezcap' ] );
		add_action( 'wp_head', [ $this, 'ccpa_inline_scripts' ], 4 );
	}

	/**
	 * Added a cheezcap to enable CCPA on sites
	 *
	 * @param array $cheezcap_options List of cheezcap options.
	 *
	 * @return array $cheezcap_options
	 */
	public function filter_add_cmp_cheezcap( array $cheezcap_options = [] ): array {

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags( __( 'Enable CCPA', 'pmc-ccpa' ), true ),
			wp_strip_all_tags(
				__( 'This option will enable CCPA IAB framework API', 'pmc-ccpa' ),
				true
			),
			'pmc-ccpa-api',
			array( 'no', 'yes' ),
			0,
			array(
				wp_strip_all_tags( __( 'No', 'pmc-ccpa' ), true ),
				wp_strip_all_tags( __( 'Yes', 'pmc-ccpa' ), true ),
			)
		);

		return $cheezcap_options;
	}

	/**
	 * Inline CCPA script - To make sure this is available on page immediately
	 *
	 * @throws \Exception
	 */
	public function ccpa_inline_scripts(): void {

		if ( 'yes' === PMC_Cheezcap::get_instance()->get_option( 'pmc-ccpa-api' ) ) {

			echo '<script src="https://iabusprivacy.pmc.com/geo-info.js"></script>';//geo data from fastly synthetic response
			echo '<script>';
			$js_ext        = ( PMC::is_production() ) ? '.min.js' : '.js';
			$template_path = PMC_CCPA_ROOT . '/assets/js/ccpa' . $js_ext;
			PMC::render_template( $template_path, [], true );
			echo '</script>';
		}
	}
}

//EOF

