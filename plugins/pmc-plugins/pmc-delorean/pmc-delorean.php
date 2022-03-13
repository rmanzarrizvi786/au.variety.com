<?php
/*
Plugin Name: PMC Delorean
Description: Requires confirmation before publishing a post with a publish date that has already passed. Prevents accidential publishing of a post that should be scheduled.
Version: 1.1.0
Author: PMC, BGR, Corey Gilmore

*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

if( !class_exists('PMC_Scripts') ) {
	if( is_admin() && class_exists( 'PMC_Admin_Notice' ) ) {
		PMC_Admin_Notice::add_admin_notice( 'PMC_Scripts was not found, PMC_Delorean will not function.', array(
			'dismissible'      => true,
			'snooze_time'      => DAY_IN_SECONDS,
			'notice_classes'   => array('error'),
		));
	}
	return false;
}

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Delorean {

	use Singleton;

	public static $config = array(
		'show_clock'      => false,
		'clock_seconds'   => false,
	);

	protected function __construct() {
		add_action( 'admin_init', array( 'PMC_Delorean', 'add_settings' ) );

		if( get_option('pmc_delorean_enabled') ) {
			add_action( 'admin_init', array( 'PMC_Delorean', 'admin_init' ) );
			add_action( 'transition_post_status', array( 'PMC_Delorean', 'transition_post_status' ), 10, 3 );
		}
	}

	public static function admin_init() {
		if( !is_admin() ) {
			return;
		}

		$current_user_id = get_current_user_id();

		// Set our defaults based on the users preferences
		static::$config['show_clock'] = intval( get_user_attribute($current_user_id, 'pmc_show_ab_clock') );
		static::$config['clock_seconds'] = intval( get_user_attribute($current_user_id, 'pmc_show_ab_clock_seconds') );

		$filtered_config = apply_filters('pmc_delorean_config', static::$config);
		static::$config = wp_parse_args($filtered_config, static::$config);

		$classname = get_called_class();

		// Enable extra profile fields for editing
		add_action( 'personal_options', array($classname, 'add_profile_fields') );

		// Hook onto profile fields for updating
		add_action( 'personal_options_update', array($classname, 'save_profile_fields') );
		add_action( 'edit_user_profile_update', array($classname, 'save_profile_fields') );

		add_action( 'admin_head', array($classname, 'admin_head') );

		// Prevent publishing of posts from Quick Edit
		add_action( 'wp_ajax_inline-save', array($classname, 'quickedit_publish_check'), 0 );

		if( static::$config['show_clock'] ) {
			add_action( 'admin_bar_menu', array($classname, 'admin_bar_menu') );
		}
	}

	public static function quickedit_publish_check() {
		check_ajax_referer( 'inlineeditnonce', '_inline_edit' );

		if ( !isset($_POST['post_ID']) || !( $post_id = (int)$_POST['post_ID'] ) || !isset($_POST['_status']) ) {
			return;
		}

		if ( $last = wp_check_post_lock( $post_id ) ) {
			$last_user = get_userdata( $last );
			$last_user_name = $last_user ? $last_user->display_name : __( 'Someone', 'pmc-delorean' );

			if( $_POST['post_type'] == 'page' ) {
				$format_str = __( 'Saving is disabled: %s is currently editing this page.', 'pmc-delorean' );
			} else {
				$format_str = __( 'Saving is disabled: %s is currently editing this post.', 'pmc-delorean' );
			}
			esc_html_e( sprintf( $format_str, $last_user_name ) );
			wp_die();
		}

		$data = &$_POST;
		$post_data = _wp_translate_postdata($data);

		$current_status = get_post_status( $post_id );
		$new_status = isset($data['_status']) ? $data['_status'] : false;

		if( empty($post_data['post_date_gmt']) ) {
			return;
		}

		$gmt_date = strtotime( $post_data['post_date_gmt'] );
		$now = time();
		$diff = $gmt_date - $now;
		$status_change_to_publish = $new_status == 'publish' && $current_status != 'publish';

		if( ($status_change_to_publish || $new_status == 'future') && $now && $gmt_date && $diff <= 0 ) {
			esc_html_e( "Publish date occurs in the past. Please publish this from the full Edit Post screen. Cur: $current_status, New: $new_status, diff: $diff", 'pmc-delorean' );
			wp_die();
		}

	}

	public static function admin_head() {
		if( static::$config['show_clock'] ) {
			static::print_admin_styles();
		}

		$tz = get_option('timezone_string');
		try {
			$date = new DateTime( 'now', new DateTimeZone($tz) );
		} catch ( Exception $e ) {
			$date = new DateTime( 'now' );
		}
		$screen = get_current_screen();

		// moment.js is used for date handling. This is built from the develop branch so that zone() works as getter AND setter
		wp_enqueue_script( 'moment', plugins_url( 'moment.min.js' , __FILE__ ), array(), '2.0.0-2013-06-18', true );
		pmc_js_libraries_enqueue_script( 'pmc-jquery-deparam' );
		wp_enqueue_script( 'pmc-delorean', plugins_url( 'pmc-delorean.js' , __FILE__ ), array( 'jquery', 'moment'), false, true );

		PMC_Scripts::add_script( 'pmc_delorean', array(
			'show_clock'        => static::$config['show_clock'],
			'clock_seconds'     => static::$config['clock_seconds'],
			'screen_id'         => $screen->id,
			'screen_base'       => $screen->base,
			'screen_action'     => $screen->action,
			'screen_post_type'  => $screen->post_type,
		), 'admin_head', 15 );

		PMC_Scripts::add_script( 'pmc_site_time', array(
			'gmt_offset'     => $date->format( 'P' ), // In format �[hh]:[mm] for use in an ISO 8601 string
			'tz_abbrev'      => $date->format( 'T' ),
			'tz_string'      => $date->format( 'e' ),
			'gmt_num_offset' => $date->format( 'O' ),
		), 'admin_head', 15 );

	}

	public static function admin_bar_menu() {
		add_action( 'admin_bar_menu', function() {
			global $wp_admin_bar;
			$tz = get_option('timezone_string');

			try {
				$date = new DateTime( 'now', new DateTimeZone($tz) );
			} catch ( Exception $e ) {
				$date = new DateTime( 'now' );
			}

			$wp_admin_bar->add_node(array(
				'parent'  => 'top-secondary',
				'id'      => 'pmc-delorean-time',
				'title'   => $date->format("H:i:s T"),
				'meta'    => array(
				  'class'   => 'pmc-delorean',
				),

			));

		}, 9999); // place it to the left of debug and dev bars
	}

	public static function print_admin_styles() {
		?>
		<style>
		#wpadminbar .ab-top-menu > li.pmc-delorean:hover > .ab-item,
		#wpadminbar .ab-top-menu > li.pmc-delorean.hover > .ab-item,
		#wpadminbar .ab-top-menu > li.pmc-delorean > .ab-item:focus,
		#wpadminbar.nojq .quicklinks .ab-top-menu > li.pmc-delorean > .ab-item:focus {
		    background-color: inherit;
		    background-image: inherit;
		    color: inherit;
		}
		#wpadminbar .ab-top-menu > li.pmc-delorean .local {
			display: none;
			font-style: italic;
			color: #999999;
			margin-left: 8px;
		}

		</style>
		<?php

	}

	public static function add_profile_fields($user) {
		$show_clock = get_user_attribute($user->ID, 'pmc_show_ab_clock');
		$show_seconds = get_user_attribute($user->ID, 'pmc_show_ab_clock_seconds');

		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Admin Bar Clock', 'pmc-delorean' )?></th>
			<td>
				<p>
					<label for="pmc_show_ab_clock"><input name="pmc_show_ab_clock" type="checkbox" id="pmc_show_ab_clock" value="1" <?php checked('1', $show_clock); ?> /> <?php esc_html_e( 'Show the time (in the site\'s timezone) in the admin menu bar', 'pmc-delorean' ); ?></label><br />
					<label for="pmc_show_ab_clock_seconds"><input name="pmc_show_ab_clock_seconds" type="checkbox" id="pmc_show_ab_clock_seconds" value="1" <?php checked('1', $show_seconds); ?> /> <?php esc_html_e( 'Display the time with seconds', 'pmc-delorean' ); ?></label>
				</p>
			</td>
		</tr>
		<?php

	}

	public static function save_profile_fields( $user_id ) {
		// sanity/safety checks (from wp-admin/user-edit.php)
		check_admin_referer('update-user_' . $user_id);

		if ( !current_user_can('edit_user', $user_id) ) {
			// @log Corey Gilmore 2012-08-22 Add logging here
			wp_die( esc_html_( 'You do not have permission to edit this user.', 'pmc-delorean' ) );
		}

		$pmc_show_ab_clock = empty( $_POST['pmc_show_ab_clock'] ) ? 0 : $_POST['pmc_show_ab_clock'];
		$pmc_show_ab_clock_seconds = empty( $_POST['pmc_show_ab_clock_seconds'] ) ? 0 : $_POST['pmc_show_ab_clock_seconds'];

		update_user_attribute( $user_id, 'pmc_show_ab_clock', intval( $pmc_show_ab_clock ) );
		update_user_attribute( $user_id, 'pmc_show_ab_clock_seconds', intval( $pmc_show_ab_clock_seconds ) );

	}

	/**
	 * If a schedule post is switched to draft or pending review, unset the dates to clear the schedule.
	 * This prevents accidental back-publishing of posts when they are rescheduled.
	 *
	 */
	public static function transition_post_status( $new_status, $old_status, $post ) {
		if( $post->post_type != 'post' ) {
			return;
		}

		if( $old_status == 'future' && ($new_status == 'pending' || $new_status == 'draft') ) {

			$update_post = array(
				'ID'             => $post->ID,
				'post_date'      => '0000-00-00 00:00:00',
				'post_date_gmt'  => '0000-00-00 00:00:00',
				'edit_date'      => true,
			);
			$new_id = wp_update_post( $update_post );
		}

	}

	/**
	 * @since 2014-07-03 Corey Gilmore
	 *
	 * @return void
	 */
	public static function add_settings() {
		register_setting( 'general', 'pmc_delorean_enabled' );
		add_settings_field(
			'pmc_delorean_enabled',
			'PMC Delorean',
			array( 'PMC_Delorean', 'settings_field'),
			'general',
			'default'
		);
	}

	/**
	 * @since 2014-07-03 Corey Gilmore
	 *
	 * @return void
	 */
	public static function settings_field() {
		echo '<fieldset><label for="pmc_delorean_enabled"><input type="checkbox" name="pmc_delorean_enabled" id="pmc_delorean_enabled" value="1" '. checked( get_option('pmc_delorean_enabled'), '1', false ) .'> Enable Post Publishing Safety Checks</label></fieldset>';
	}


}

PMC_Delorean::get_instance();

// EOF
