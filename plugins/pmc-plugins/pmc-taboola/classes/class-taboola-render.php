<?php

namespace PMC\Taboola;

use \PMC\Global_Functions\Traits\Singleton;

class Taboola_Render {

	use Singleton;

	/**
	 * __construct function of class.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Set up actions and filters.
	 */
	protected function _setup_hooks() {

		add_action( 'wp_head', [ $this, 'taboola_header_content' ], 99 );

		add_action( 'pmc_render_taboola', [ $this, 'taboola_content_output' ] );

		add_filter( 'amp_post_template_analytics', [ $this, 'add_pmc_taboola_analytics' ] );

	}

	/**
	 * Determines if Today's Top Deal shortcode should be displayed.
	 *
	 * @return bool
	 */
	public function should_display_taboola() : bool {

		if (
			is_single() &&
			! empty( apply_filters( 'pmc_taboola_id', '' ) )
		) {
			return true;
		}

		return false;

	}

	/**
	 * Outputs Taboola header content.
	 *
	 * @return void
	 */
	public function taboola_header_content() : void {

		if ( ! $this->should_display_taboola() ) {
			return;
		}

		\PMC::render_template(
			sprintf( '%s/templates/header.php', untrailingslashit( PMC_TABOOLA_PLUGIN_DIR ) ),
			[
				'taboola_script_id' => apply_filters( 'pmc_taboola_id', '' ),
			],
			true
		);

	}

	/**
	 * Outputs shortcode template if conditions are met.
	 *
	 * @return void
	 */
	public function taboola_content_output() : void {

		if ( ! $this->should_display_taboola() ) {
			return;
		}

		global $post;

		\PMC::render_template(
			sprintf( '%s/templates/taboola.php', untrailingslashit( PMC_TABOOLA_PLUGIN_DIR ) ),
			[
				'canonical_url' => wp_get_canonical_url( $post->ID ),
			],
			true
		);

	}

	/**
	 * Add the Analytics Taboola Tags for sheknows.
	 * @param  string $analytics The array of all the analytics tags
	 * @return array             The array with all the analytics tags including the Taboola Analytics Tags.
	 */
	public function add_pmc_taboola_analytics( $analytics = [] ) : array {

		// If the pmc_taboola_id is empty, this condition will handle it.
		if ( ! $this->should_display_taboola() ) {
			return $analytics;
		}

		$analytics = ( ! is_array( $analytics ) ) ? [] : $analytics;
		
		// Get the taboola analytics tags from the filter.
		$taboola_id = apply_filters( 'pmc_taboola_id', '' );

		$analytics['taboola'] = [
			'type'        => 'taboola',
			'attributes'  => [],
			'config_data' => [
				'vars' => [
					'aid' => $taboola_id,
				],
			],
		];

		return $analytics;

	}

}

// EOF
