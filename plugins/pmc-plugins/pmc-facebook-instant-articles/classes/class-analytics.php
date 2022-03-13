<?php

namespace PMC\Facebook_Instant_Articles;

use PMC\Global_Functions\Traits\Singleton;

class Analytics {

	use Singleton;

	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'pmc_fbia_embed_scripts', [ $this, 'add_comscore' ] );
	}

	/**
	 * Add Comscore tag to the FB IA markup via Analytics element
	 *
	 * @param Plugin $pmc_fbia
	 *
	 * @return void
	 *
	 * @since BR-1310
	 */
	public function add_comscore( Plugin $pmc_fbia ): void {

		// Don't double track with Comscore.
		// Five brands currently add comscore and six add GA tracking in the instant-articles-wizard
		$settings_analytics = \Instant_Articles_Option_Analytics::get_option_decoded();
		if ( isset( $settings_analytics['embed_code_enabled'] ) && ! empty( $settings_analytics['embed_code'] ) && false !== strpos( $settings_analytics['embed_code'], '_comscore.push({ c1: "2", c2: "6035310" });' ) ) {
			return;
		}

		$comscore_scripts = \PMC::render_template( sprintf( '%s/templates/comscore.php', untrailingslashit( PMC_FACEBOOK_INSTANT_ARTICLES_ROOT ) ), [], false );
		$pmc_fbia->add_embed_script( $comscore_scripts );

	}
}
