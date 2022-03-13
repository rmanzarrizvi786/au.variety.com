<?php
/**
 * Class to add and handle post options for this plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-04-15
 */

namespace PMC\Tags\Components;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Post_Options\API;


class Post_Options {

	use Singleton;

	/**
	 * @var \PMC\Post_Options\API
	 */
	protected $_api;

	/**
	 * @var array Array containing Global Post Options that are to be registered
	 */
	protected $_global_options = [

		// NOTE: Terms MUST, at the very least; include a label.
		//       Though, a description is also preferred.

		// Option to disable skimlinks on a post
		'disable-skimlinks' => [
			'label'       => 'Disable Skimlinks',
			'description' => 'Posts with this term will not load Skimlinks.',
		],

	];

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		$this->_api = API::get_instance();

		$this->_setup_hooks();

	}

	/**
	 * Method to setup listeners on WP hooks
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */
		add_action( 'admin_init', [ $this, 'register_global_options' ], 12 );    // set on 12 because post-options plugin adds defaults at 11
		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_disable_skimlinks' ], 0 );    // set this on 0 so that filter override is done before JS is output

	}

	/**
	 * Called on 'admin_init' hook, this method adds post options
	 *
	 * @return bool
	 */
	public function register_global_options() : bool {

		if ( empty( $this->_global_options ) ) {
			return false;
		}

		$this->_api->register_global_options( $this->_global_options );

		return true;

	}

	/**
	 * Called on 'wp_enqueue_scripts' hook, this method disables Skimlinks on current post if post option is selected
	 *
	 * @return bool
	 */
	public function maybe_disable_skimlinks() : bool {

		if ( ! is_singular() ) {

			// not a post page
			// bail out
			return false;

		}

		$current_post = get_post();

		if ( empty( $current_post ) ) {
			return false;
		}

		if ( ! $this->_api->post( $current_post )->has_option( 'disable-skimlinks' ) ) {

			// Current post does not have Skimlinks disabled
			// bail out
			return false;

		}

		// Disable Skimlinks
		add_filter( 'pmc_tags_skimlink_domain_id', '__return_empty_string', 19 );

		return true;

	}

}    //end class

//EOF
