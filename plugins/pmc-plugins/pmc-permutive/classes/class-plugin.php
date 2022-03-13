<?php
/**
 * PMC Permutive
 *
 * @author Vinod Tella <vtella@pmc.com>
 *
 * @group pmc-permutive
 */
namespace PMC\Permutive;

use PMC;
use PMC\Global_Functions\Traits\Singleton;

class Plugin {
	use Singleton;

	/**
	 * Construct
	 */
	protected function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'action_wp_enqueue_scripts' ] );
	}

	/**
	 * Enqueue required js file
	 *
	 */
	public function action_wp_enqueue_scripts() {

		if ( ! is_admin() && ! \PMC::is_amp() ) {
			$js_extension   = ( \PMC::is_production() ) ? '.min.js' : '.js';
			$permutive_data = apply_filters( 'pmc_permutive_data', [] );

			if ( ! empty( $permutive_data ) ) {
				wp_register_script( 'pmc-permutive-js', plugins_url( sprintf( 'js/permutive%s', $js_extension ), __DIR__ ), [ 'jquery' ], PMC_PERMUTIVE_VERSION );
				wp_localize_script( 'pmc-permutive-js', 'pmc_permutive_data', $permutive_data );
				wp_enqueue_script( 'pmc-permutive-js' );
			}
		}
	}

}
