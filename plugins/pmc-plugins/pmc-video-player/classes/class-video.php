<?php
/**
 * Includes All cheezcap settings related to plugin.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-03-01 READS-923
 */

namespace PMC\Video_Player;

use CheezCapDropdownOption;
use CheezCapGroup;
use CheezCapTextOption;
use PMC\Global_Functions\Traits\Singleton;

class Video {

	use Singleton;

	const PMC_VIDEO_PLAYER = 'pmc_video_player_event_tracking';

	/**
	 * Class instantiation.
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to setup listeners to hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		/**
		 * Filters.
		 */
		add_filter( 'pmc_cheezcap_groups', array( $this, 'filter_pmc_cheezcap_groups' ) );

		/**
		 * Actions.
		 */
		add_action( 'pmc-tags-footer', array( $this, 'localize_video_events' ) );
	}

	/**
	 * Adds new theme settings tabs ( cheezcap group ).
	 *
	 * @param array $cheezcap_groups list of cheezcap groups.
	 *
	 * @return array $cheezcap_groups
	 */
	public function filter_pmc_cheezcap_groups( $cheezcap_groups ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		$events = array(
			array(
				wp_strip_all_tags( __( 'Youtube Video Event Tracking', 'pmc-video-player' ), true ),
				'Enables Youtube events set to enabled below. This does not enable all events.',
				self::PMC_VIDEO_PLAYER . '_youtube_video',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'JW Player Video Event Tracking', 'pmc-video-player' ), true ),
				'Enables JW Player events set to enabled below. This does not enable all events.',
				self::PMC_VIDEO_PLAYER . '_jwplayer',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Basic Setting - Autoplay', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'A video will only autoplay when the user lands on the page from another page on the site. This event is tracked when a video autoplays on the page.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_autoplay',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Basic Setting - Content Consumed', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 30 seconds.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_content_consumed',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Basic Setting - Play', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when play button is clicked.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_play',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Basic Setting - Show Ad', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when anytime an ad is shown during the video playback.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_show_ad',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Basic Setting - Skip Ad', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when anytime someone skips an ad in the video.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_skip_ad',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Basic Setting - 100% Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'This event is tracked when the whole video has been watched (excluding duration of ads).', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_100_percent_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Basic Setting - Error', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when an error occurs interrupting playback.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_error',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Pause', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when pause button is clicked.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_pause',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 5 Seconds Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 5 seconds.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_5_seconds_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 10 Seconds Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 10 seconds.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_10_seconds_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 30 Seconds Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 30 seconds. ', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_30_seconds_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 60 Seconds Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 60 seconds.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_60_seconds_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 90 Seconds Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 90 seconds.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_90_seconds_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 120 Seconds Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 120 seconds. ', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_120_seconds_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 300 Seconds Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 300 seconds.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_300_seconds_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 600 Seconds Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 600 seconds.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_600_seconds_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 5% Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 5% of total video time.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_5_percent_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 10% Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 10% of total video time.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_10_percent_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 25% Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 25% of total video time.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_25_percent_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 50% Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 50% of total video time.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_50_percent_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 75% Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 75% of total video time.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_75_percent_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - 95% Played', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the cumulative play time of the video (excluding duration of ads) passes 95% of total video time.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_95_percent_played',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Jump Forward', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the users jumps forward from a specific point (excluding duration of ads) in the video (X) to a specific point (Y) in the video.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_jump_forward',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Jump Backward', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the users jumps backward from a specific point (excluding duration of ads) in the video (X) to a specific point (Y) in the video.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_jump_backward',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Mute', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the user presses mute.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_mute',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Unmute', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the user presses unmute.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_unmute',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Settings', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the user presses settings button.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_settings',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Full Screen', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the user presses full screen button. ', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_full_screen',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Volume Up', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the user presses volume button to increase volume.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_volume_up',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Volume Down', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the user presses volume button to decrease volume.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_volume_down',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
			array(
				wp_strip_all_tags( __( 'Advanced Setting - Watch On YouTube', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'The event is tracked when the user presses the watch on youtube button.', 'pmc-video-player' ), true ),
				self::PMC_VIDEO_PLAYER . '_watch_on_youtube',
				array( 0, 1 ),
				0, // disabled by default.
				array( wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ), wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ) ),
			),
		);

		$cheezcap_options = array();

		foreach ( $events as $event ) {
			$cheezcap_options[] = new CheezCapDropdownOption( $event[0], $event[1], $event[2], $event[3], $event[4], $event[5] );
		}

		$cheezcap_options[] = new CheezCapDropdownOption(
			wp_strip_all_tags( __( 'JWPlayer - Enable captions', 'pmc-video-player' ), true ),
			wp_strip_all_tags( __( 'To enable closed-captioning for video in JW Player.', 'pmc-video-player' ), true ),
			'pmc_video_player_jw_player_cc',
			[ 0, 1 ],
			0, // disabled by default.
			[
				wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ),
			]
		);

		$cheezcap_options[] = new CheezCapTextOption(
			__( 'CatapultX Group ID', 'pmc-video-player' ),
			__( 'Add Group ID for Catapult.  Enables Catapult on JWPlayer', 'pmc-video-player' ),
			'pmc_video_player_catapultx_group_id'
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			wp_strip_all_tags( __( 'JWPlayer - Enable comscore tracking', 'pmc-video-player' ), true ),
			wp_strip_all_tags( __( 'To enable comscore tracking for video in JW Player.', 'pmc-video-player' ), true ),
			'pmc_video_player_jw_player_comscore',
			[ 0, 1 ],
			0, // disabled by default.
			[
				wp_strip_all_tags( __( 'Disabled', 'pmc-video-player' ), true ),
				wp_strip_all_tags( __( 'Enabled', 'pmc-video-player' ), true ),
			]
		);

		$pmc_video_player = new CheezCapGroup(
			wp_strip_all_tags( __( 'Video Players/GA Video Events', 'pmc-video-player' ), true ),
			self::PMC_VIDEO_PLAYER,
			$cheezcap_options
		);

		$cheezcap_groups[] = $pmc_video_player;

		return $cheezcap_groups;

	}

	/**
	 * To check youtube tracking enabled.
	 *
	 * @return boolean
	 */
	public static function is_ytplayer_enabled() {

		$is_ytplayer_enabled = \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_youtube_video' );
		return ( '1' === $is_ytplayer_enabled ) ? true : false;
	}

	/**
	 * To enqueue scripts & localize GA Event Tracking Options.
	 *
	 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
	 *
	 * @since 2018-02-20 READS-923
	 */
	public function localize_video_events() {

		$events = array(
			'basic'    => array(
				'autoplay'            => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_autoplay' ),
				'content_consumed'    => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_content_consumed' ),
				'play'                => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_play' ),
				'_100_percent_played' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_100_percent_played' ),
				'error'               => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_error' ),
				'show_ad'             => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_show_ad' ),
				'skip_ad'             => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_skip_ad' ),
			),
			'advanced' => array(
				'pause'            => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_pause' ),
				'seconds_played'   => array(
					'_5'   => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_5_seconds_played' ),
					'_10'  => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_10_seconds_played' ),
					'_30'  => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_30_seconds_played' ),
					'_60'  => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_60_seconds_played' ),
					'_90'  => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_90_seconds_played' ),
					'_120' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_120_seconds_played' ),
					'_300' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_300_seconds_played' ),
					'_600' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_600_seconds_played' ),
				),
				'percent_played'   => array(
					'_5'  => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_5_percent_played' ),
					'_10' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_10_percent_played' ),
					'_25' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_25_percent_played' ),
					'_50' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_50_percent_played' ),
					'_75' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_75_percent_played' ),
					'_95' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_95_percent_played' ),
				),
				'jump_forward'     => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_jump_forward' ),
				'jump_backward'    => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_jump_backward' ),
				'mute'             => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_mute' ),
				'unmute'           => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_unmute' ),
				'volume_up'        => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_volume_up' ),
				'volume_down'      => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_volume_down' ),
				'settings'         => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_settings' ),
				'full_screen'      => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_full_screen' ),
				'watch_on_youtube' => \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_watch_on_youtube' ),
			),
		);

		\PMC::render_template(
			sprintf( '%s/templates/footer-tags.php', untrailingslashit( PMC_VIDEO_PLAYER_ROOT ) ),
			[
				'pmc_video_player' => self::PMC_VIDEO_PLAYER,
				'events'           => $events,
			],
			true
		);

	}

	/**
	 * To check jwplayer ga tracking enabled.
	 *
	 * @return boolean
	 */
	public static function is_jwplayer_ga_enabled() {

		$is_jwplayer_enabled = \PMC_Cheezcap::get_instance()->get_option( self::PMC_VIDEO_PLAYER . '_jwplayer' );
		return ( '1' === $is_jwplayer_enabled ) ? true : false;
	}

}
