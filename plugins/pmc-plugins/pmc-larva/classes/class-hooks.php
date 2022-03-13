<?php
/**
 * Common hooks used across Larva.
 *
 * @package pmc-larva
 */

namespace PMC\Larva;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Hooks.
 */
class Hooks {
	use Singleton;

	/**
	 * Hooks constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'pmc_larva_do_the_content', [ $this, 'output_the_content' ] );
		add_action( 'pmc_fbia_load_rules', [ $this, 'action_pmc_fbia_load_rules' ] );
	}

	/**
	 * Call `the_content()` for the given post ID.
	 *
	 * @param int $id Post ID.
	 */
	public function output_the_content( int $id = 0 ): void {
		global $post;

		if ( empty( $id ) ) {
			return;
		}

		// `setup_postdata()` doesn't set this, it happens in `the_post()`.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post = get_post( $id );
		setup_postdata( $post );
		the_content();
		wp_reset_postdata();
	}

	/**
	 * Add FBIA's translation rules
	 *
	 * @param \PMC\Facebook_Instant_Articles\Plugin $pmc_fbia
	 * @return void
	 */
	public function action_pmc_fbia_load_rules( $pmc_fbia ) : void {
		if ( is_callable( [ $pmc_fbia, 'add_rules' ] ) ) {
			$pmc_fbia->add_rules( [ '.post-content-image' => 'PassThroughRule' ] );
		}
	}
}
