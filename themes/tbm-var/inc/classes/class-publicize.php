<?php
/**
 * Class to handle Jetpack Publicize related functionality
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-09-11
 */

namespace Variety\Inc;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;
use \WP_Post;

class Publicize {

	use Singleton;

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		/*
		 * Filters
		 */
		add_action( 'init', [ $this, 'variety_publicize_add_cpt_support' ], 99 ); // init later after other post types have been registered.

		// Yes the question mark is part of the filter name
		// Its not a typo, don't "fix" this by removing the punctuation
		add_filter( 'wpas_submit_post?', [ $this, 'maybe_publicize_post' ], 10, 2 );

	}

	/**
	 * Method, hooked to wpas_submit_post?, to decide whether to publicize a post or not.
	 *
	 * @param bool $should_publicize
	 * @param int  $post_id
	 *
	 * @return bool
	 */
	public function maybe_publicize_post( bool $should_publicize, int $post_id ) : bool {

		if ( 'publish' !== get_post_status( $post_id ) ) {

			// Post isn't published yet, so we don't want to check anything
			// bail out
			return $should_publicize;

		}

		/*
		 * Do not publicize post if its in Dirt vertical
		 */
		if ( PMC::in_vertical( 'dirt', $post_id ) ) {
			$should_publicize = false;
		}

		return $should_publicize;

	}

	/**
	 * Ensures the Video and List post types support `publicize`
	 *
	 * This feature support flag is used by the REST API.
	 */
	public function variety_publicize_add_cpt_support() : void {

		$available_post_types = [
			'variety_top_video',
			'pmc_list',
		];

		foreach ( $available_post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				add_post_type_support( $post_type, 'publicize' );
			}
		}

	}

}    // end class

//EOF
