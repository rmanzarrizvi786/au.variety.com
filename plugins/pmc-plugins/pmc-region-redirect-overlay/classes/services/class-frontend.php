<?php
/**
 * Service class to set things up for the plugin on frontend.
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-11-25
 */

namespace PMC\Region_Redirect_Overlay\Services;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Region_Redirect_Overlay\Config;
use \PMC;

class Frontend {

	use Singleton;

	const ID = 'pmc-reg-rd-overlay';

	/**
	 * @var \PMC\Region_Redirect_Overlay\Config
	 */
	protected $_config;

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore Ignoring coverage here because this is class constructor. Method calls here have their own individual tests.
	 */
	protected function __construct() {

		$this->_config = Config::get_instance();

		$this->_setup_hooks();

	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_assets' ] );
		add_action( 'pmc_region_redirect_overlay_render', [ $this, 'maybe_render_overlay_container' ] );

	}

	/**
	 * Method to check if functionality is enabled or not
	 *
	 * @return bool
	 */
	protected function _is_enabled() : bool {

		return ( ! empty( $this->_config->get_selected_countries() ) );

	}

	/**
	 * Method to load assets if functionality is enabled
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() : void {

		if ( ! $this->_is_enabled() ) {

			// No need to load assets if functionality is disabled
			return;

		}

		wp_enqueue_style(
			sprintf( '%s-css', self::ID ),
			sprintf( '%s/assets/build/css/overlay.css', untrailingslashit( PMC_REGION_REDIRECT_OVERLAY_URL ) ),
			[],
			PMC_REGION_REDIRECT_OVERLAY_VERSION
		);

		wp_enqueue_script(
			sprintf( '%s-js', self::ID ),
			sprintf( '%s/assets/build/js/overlay.js', untrailingslashit( PMC_REGION_REDIRECT_OVERLAY_URL ) ),
			[ 'jquery' ],
			PMC_REGION_REDIRECT_OVERLAY_VERSION,
			true
		);

		wp_localize_script(
			sprintf( '%s-js', self::ID ),
			'pmc_region_redirect_overlay',
			[
				'dnd_duration' => $this->_config->get_dnd_duration(),
				'countries'    => $this->_config->get_selected_countries(),
				'overlay_html' => $this->_config->get_overlay_html_for_selected_countries(),
			]
		);

	}

	/**
	 * Method to render overlay banner if functionality is enabled
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function maybe_render_overlay_container() : void {

		if ( ! $this->_is_enabled() ) {

			// No need to render overlay if functionality is disabled
			return;

		}

		PMC::render_template(
			sprintf( '%s/templates/overlay.php', untrailingslashit( PMC_REGION_REDIRECT_OVERLAY_ROOT ) ),
			[
				'overlay_id' => sprintf( '%s-banner', self::ID ),
			],
			true
		);

	}

}  // end class

//EOF
