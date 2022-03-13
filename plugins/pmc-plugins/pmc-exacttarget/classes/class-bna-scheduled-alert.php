<?php
/**
 * Class handling functionality to display BNA status for scheduled posts
 *
 * @package pmc-exacttarget
 */

namespace PMC\Exacttarget;

use PMC\Global_Functions\Traits\Singleton;

class Bna_Scheduled_Alert {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Initializes hooks.
	 */
	public function _setup_hooks() {

		add_filter( 'pmc_global_cheezcap_options', [ $this, 'get_cheezcap_option_to_show_alert' ], 10, 2 );
		add_filter( 'display_post_states', [ $this, 'maybe_add_bna_scheduled_state' ], 10, 2 );

		add_action( 'post_submitbox_misc_actions', [ $this, 'show_bna_scheduled_alert' ], 11 ); // Keep priority 11 to display alert message just before post publish button.
	}

	/**
	 * Add cheezcap options for 'Breaking News Alerts'
	 *
	 * @ticket PMCP-3129
	 *
	 * @param  array $cheezcap_options List of cheezcap options.
	 *
	 * @return array \CheezCapDropdownOption
	 */
	public function get_cheezcap_option_to_show_alert( $cheezcap_options ) {

		$cheezcap_options[] = new \CheezCapDropdownOption(
			__( 'WordPress admin notifications for scheduled posts with Breaking News Alerts', 'pmc-exacttarget' ),
			__( 'When set to YES, WordPress admin notifications for scheduled posts with Breaking News Alerts are displayed on the All Post and Edit Post pages.', 'pmc-exacttarget' ),
			'pmc_exacttarget_bna_scheduled_alert',
			array( 'no', 'yes' ),
			0, // 1sts option => no by default
			array( 'No', 'Yes' )
		);

		return $cheezcap_options;
	}

	/**
	 * Check post is Scheduled for breaking news alert or not.
	 *
	 * @param object $post The post object.
	 *
	 * @return bool True if post is Scheduled for breaking news alert, else false
	 */
	public function is_bna_scheduled_for_post( $post ) : bool {

		if ( empty( $post->ID ) || 1 > intval( $post->ID ) ) {
			return false;
		}

		$options         = get_post_meta( $post->ID, '_sailthru_breaking_news_post_data', true );
		$scheduled_array = get_post_meta( $post->ID, '_sailthru_breaking_news_meta_data', true );

		/*
		* No Breaking News is set, bail out.
		*/
		return ( empty( $options ) || empty( $scheduled_array ) ) ? false : true;
	}

	/**
	 * To get 'Breaking News Alerts' is enable or not.
	 *
	 * @return bool true if 'Breaking News Alert' is enable for theme otherwise false.
	 */
	public function is_bna_scheduled_alert_enabled() : bool {
		return ( 'yes' === strtolower( \PMC_Cheezcap::get_instance()->get_option( 'pmc_exacttarget_bna_scheduled_alert' ) ) );
	}

	/**
	 * Show Breaking news alert
	 *
	 * @param WP_Post $post The post object.
	 */
	public function show_bna_scheduled_alert( $post ) {

		if ( $this->is_bna_scheduled_alert_enabled() && $this->is_bna_scheduled_for_post( $post ) && 'future' === get_post_status( $post ) ) {
			\PMC::render_template(
				sprintf( '%s/templates/bna-scheduled-alert.php', untrailingslashit( PMC_EXACTTARGET_PATH ) ),
				[],
				true
			);
		}

	}

	/**
	 * Adds 'BNA Scheduled' as a potential post state for displaying on the post list page
	 *
	 * @param array $post_states Current list of post states
	 * @param object $post The current post
	 *
	 * @return array $post_states The list of posts states with ours now added
	 *
	 */
	public function maybe_add_bna_scheduled_state( $post_states, $post ) {

		if ( $this->is_bna_scheduled_alert_enabled() && $this->is_bna_scheduled_for_post( $post ) && 'future' === get_post_status( $post ) ) {
			$post_states[] = __( 'BNA Scheduled', 'pmc-exacttarget' );
		}

		return $post_states;
	}
}

//EOF
