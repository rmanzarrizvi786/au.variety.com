<?php
/**
 * Class for PMC Floating Video
 *
 * @since 2017-04-07 - Mike Auteri - PMCRS-283
 * @version 2017-04-07 - Mike Auteri - PMCRS-283
 * @version 2019-12-16 - Jignesh Nakrani - ROP-1942
 * @version 2021-03-01 - Rich Haynie - ROP-2299
 */
namespace PMC\Floating_Video;

use \PMC;
use \PMC_Cheezcap;
use \CheezCapGroup;
use PMC\Global_Functions\Traits\Singleton;

class Setup {

	use Singleton;

	const VERSION        = '1.3';
	const CHEEZCAP_ID    = 'pmc-floating-video';
	const CHEEZCAP_LABEL = 'Floating Video';

	/**
	 * constructor
	 */
	protected function __construct() {
		add_filter( 'pmc_cheezcap_groups', [ $this, 'add_cheezcap_group' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'jwplayer_js_embed', [ $this, 'update_jwplayer_attributes' ], 11, 1 );
	}

	/**
	 * Add CheezCap group and settings.
	 *
	 * @param array $groups
	 *
	 * @return array
	 */
	public function add_cheezcap_group( $groups = [] ) {

		$groups[] = new CheezCapGroup(
			self::CHEEZCAP_LABEL,
			self::CHEEZCAP_ID,
			[
				new \CheezCapMultipleCheckboxesOption(
					__( 'Enable floating player', 'pmc-floating-video' ),
					__( 'Enable floating player for specific device', 'pmc-floating-video' ),
					sprintf( '%s-enabled', self::CHEEZCAP_ID ),
					[ 'mobile', 'desktop' ],
					[ 'Mobile', 'Desktop' ],
					'',
					[ 'PMC_Cheezcap', 'sanitize_cheezcap_checkboxes' ]
				),
				new \CheezCapDropdownOption(
					__( 'JWPlayer - Keep player active if preroll fails', 'pmc-floating-video' ),
					__( 'If this setting is activated, the floating jw player implamentation will not remove itself if no preroll ad is found', 'pmc-floating-video' ),
					sprintf( '%s-preroll_not_required', self::CHEEZCAP_ID ),
					[ 0, 1 ],
					0, // disabled by default.
					[
						__( 'Disabled', 'pmc-floating-video' ),
						__( 'Enabled', 'pmc-floating-video' ),
					]
				),
				new \CheezCapDropdownOption(
					__( 'JWPlayer - Use new floating video styling', 'pmc-floating-video' ),
					__( 'This activated a new frotend style for the JWPlayer floating video.', 'pmc-floating-video' ),
					sprintf( '%s-jwplayer_style_v2', self::CHEEZCAP_ID ),
					[ 0, 1 ],
					0, // disabled by default.
					[
						__( 'Disabled', 'pmc-floating-video' ),
						__( 'Enabled', 'pmc-floating-video' ),
					]
				),
				new \CheezCapDropdownOption(
					__( 'JWPlayer - Floating Player Placement', 'pmc-floating-video' ),
					__( 'Choose location for the floating video player', 'pmc-floating-video' ),
					sprintf( '%s-jwplayer_floating_placement', self::CHEEZCAP_ID ),
					[ 0, 1, 2, 3, 4 ],
					0, // disabled by default.
					[
						__( 'Top Full Width', 'pmc-floating-video' ),
						__( 'Bottom Right', 'pmc-floating-video' ),
						__( 'Bottom With Stripe', 'pmc-floating-video' ),
						__( 'Top Left', 'pmc-floating-video' ),
						__( 'Top Right', 'pmc-floating-video' ),
					]
				),
			]
		);

		return $groups;
	}
	/**
	 * Check if Floating Video feature is enabled in CheezCap.
	 *
	 * @return boolean
	 */
	public function is_enabled() {

		$device  = null;
		$devices = (array) PMC_Cheezcap::get_instance()->get_option( sprintf( '%s-enabled', self::CHEEZCAP_ID ) );

		if ( PMC::is_mobile() ) {
			$device = 'mobile';
		} elseif ( PMC::is_desktop() ) {
			$device = 'desktop';
		}

		return (bool) apply_filters(
			'pmc_floating_video_player_is_enabled',
			is_singular() && in_array( $device, (array) $devices, true ),
			$device,
			$devices
		);
	}

	/**
	 * Enqueue the necessary scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		if ( $this->is_enabled() ) {
			wp_register_script( 'pmc-floating-video', plugins_url( '../assets/js/floating-jwplayer.min.js', __FILE__ ), [ 'jquery' ], self::VERSION, true );
			$translation_array = array(
				'preroll_not_required'        => \PMC_Cheezcap::get_instance()->get_option( self::CHEEZCAP_ID . '-preroll_not_required' ),
				'jwplayer_style_v2'           => \PMC_Cheezcap::get_instance()->get_option( self::CHEEZCAP_ID . '-jwplayer_style_v2' ),
				'jwplayer_floating_placement' => \PMC_Cheezcap::get_instance()->get_option( self::CHEEZCAP_ID . '-jwplayer_floating_placement' ),
			);
			wp_localize_script( 'pmc-floating-video', 'pmcFloatingVideoOptions', $translation_array );
			wp_enqueue_script( 'pmc-floating-video', array(), PMC_FLOATING_VIDEO_VERSION );

			wp_enqueue_style( 'pmc-floating-video', plugins_url( '../assets/css/floating-jwplayer.min.css', __FILE__ ), array(), PMC_FLOATING_VIDEO_VERSION );
		}

	}

	/**
	 * Enable Jwplayer's built in floating option for mobile only
	 *
	 * @param string $output
	 *
	 * @return string output
	 */
	public function update_jwplayer_attributes( $output = '' ) {

		if ( $this->is_enabled() ) {
			$output = str_replace( '"playlist":', '"floating":true,"playlist":', $output );
		}

		return $output;
	}

}
