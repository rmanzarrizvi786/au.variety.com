<?php
/**
 * Google's Web Vitals measurements.
 *
 * @link https://github.com/GoogleChrome/web-vitals
 *
 * @package pmc-plugins
 */

namespace PMC\Performance_Metrics;

use PMC;
use PMC_Google_Universal_Analytics;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Web_Vitals.
 */
class Web_Vitals {
	use Singleton;

	/**
	 * Filter tag to set GA tracking ID.
	 */
	public const FILTER_TAG_ANALYTICS_ID = 'pmc_performance_metrics_analytics_id';

	/**
	 * Filter tag to modify sampling threshold.
	 */
	public const FILTER_TAG_SAMPLE_THRESHOLD = 'pmc_performance_metrics_sampling_threshold';

	/**
	 * Filter tag to omit the script from a particular context.
	 */
	public const FILTER_TAG_SKIP_RENDER = 'pmc_performance_metrics_skip_render';

	/**
	 * Name of dedicated Google Analytics trcker.
	 */
	protected const GA_NAMED_TRACKER = 'pmcPerfTracker';

	/**
	 * Reporting category for analytics provider.
	 */
	protected const EVENT_CATEGORY = 'Web Vitals';

	/**
	 * Web_Vitals constructor.
	 */
	protected function __construct() {
		add_action( 'wp_loaded', [ $this, 'setup' ] );
	}

	/**
	 * Prepare metrics gathering after environment is fully set up, to support
	 * dependencies on other PMC plugins.
	 */
	public function setup(): void {
		if (
			! class_exists( 'PMC_Google_Universal_Analytics', false )
			|| ! PMC_Google_Universal_Analytics::get_instance()->can_show()
		) {
			return;
		}

		$this->_setup_hooks();
	}

	/**
	 * Add our hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'pmc-tags-bottom', [ $this, 'render' ] );
		add_filter( static::FILTER_TAG_ANALYTICS_ID, [ $this, 'set_analytics_id' ] );
	}

	/**
	 * Output the Web Vitals script.
	 */
	public function render(): void {
		$tracking_id = apply_filters(
			static::FILTER_TAG_ANALYTICS_ID,
			null
		);

		if ( empty( $tracking_id ) ) {
			return;
		}

		if ( apply_filters( static::FILTER_TAG_SKIP_RENDER, false ) ) {
			return;
		}

		$data = [
			'config' => [
				'eventCategory'   => static::EVENT_CATEGORY,
				'blockGA'         => is_user_logged_in(),
				'gaId'            => $tracking_id,
				'sampleThreshold' => (int) apply_filters(
					static::FILTER_TAG_SAMPLE_THRESHOLD,
					6
				),
				'sendToGA'        => false,
				'tracker'         => static::GA_NAMED_TRACKER,
			],
			'queue'  => (object) [],
		];

		?>
		<script>
			window.pmc = window.pmc || {};
			window.pmc.webVitals = <?php echo wp_json_encode( $data ); ?>;
		<?php

		PMC::render_template(
			PLUGIN_PATH . 'assets/build/index.js',
			[],
			true
		);

		echo '</script>';
	}

	/**
	 * Set GA ID for the current site.
	 *
	 * @param string|null $id Tracking ID.
	 * @return string|null
	 */
	public function set_analytics_id( ?string $id ): ?string {
		if ( ! PMC::is_production() ) {
			return null;
		}

		if ( ! defined( 'PMC_SITE_NAME' ) ) {
			return $id;
		}

		// Cannot cover due to constants.
		// @codeCoverageIgnoreStart
		switch ( PMC_SITE_NAME ) {
			case 'artnews':
				$id = 'UA-166767228-4';
				break;

			case 'bgr':
				$id = 'UA-166767228-5';
				break;

			case 'billboard':
				$id = 'UA-166767228-18';
				break;

			case 'deadline':
				$id = 'UA-166767228-6';
				break;

			case 'dirt':
				$id = 'UA-166767228-16';
				break;

			case 'footwearnews':
				$id = 'UA-166767228-7';
				break;

			case 'hollywoodlife':
				$id = 'UA-166767228-8';
				break;

			case 'indiewire':
				$id = 'UA-166767228-9';
				break;

			case 'robbreport':
				$id = 'UA-166767228-15';
				break;

			case 'rollingstone';
				$id = 'UA-166767228-3';
				break;

			case 'sheknows':
				$id = 'UA-166767228-13';
				break;

			case 'sportico':
				$id = 'UA-166767228-14';
				break;

			case 'spy':
				$id = 'UA-166767228-10';
				break;

			case 'thr':
				$id = 'UA-166767228-17';
				break;

			case 'tvline':
				$id = 'UA-166767228-11';
				break;
			// @codeCoverageIgnoreEnd

			case 'variety':
				$id = 'UA-166767228-2';
				break;

			// Cannot cover due to constants.
			// @codeCoverageIgnoreStart
			case 'vibe':
				$id = 'UA-166767228-19';
				break;

			case 'wwd':
				$id = 'UA-166767228-12';
				break;
		}
		// @codeCoverageIgnoreEnd

		return $id;
	}
}
