<?php
namespace PMC\Outbrain;

use \PMC\Global_Functions\Traits\Singleton;

class Setup {

	use Singleton;

	/**
	 * Setup constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() : void {

		add_action( 'wp_head', [ $this, 'pixel_code' ] );

		add_filter( 'pmc_global_cheezcap_options', [ $this, 'filter_pmc_global_cheezcap_options' ] );

	}

	/**
	 * Render Outbrain pixel code if pixel ID is set.
	 *
	 * @throws \Exception
	 */
	public function pixel_code() : void {

		$pixel_id = \PMC_Cheezcap::get_instance()->get_option( 'pmc_outbrain_pixel_id' );

		if ( ! empty( $pixel_id ) ) {
			\PMC::render_template(
				PMC_OUTBRAIN_ROOT . '/templates/outbrain-pixel.php',
				[
					'pixel_id' => $pixel_id,
				],
				true
			);
		}

	}

	/**
	 * Add Cheezcap for Outbrain Pixel ID.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function filter_pmc_global_cheezcap_options( array $options = [] ) : array {

		$options[] = new \CheezCapTextOption(
			__( 'Outbrain Pixel ID', 'pmc-outbrain' ),
			__( 'In the Outbrain Pixel code find the OB_ADV_ID value and paste it here to enable.', 'pmc-outbrain' ),
			'pmc_outbrain_pixel_id',
			'', // Default value.
			false // Not a textarea.
		);

		return $options;

	}

}

// EOF
