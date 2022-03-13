<?php
/**
 * Handle analytics for Digital Daily feature.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use PMC_Google_Universal_Analytics;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Analytics.
 */
class Analytics {
	use Singleton;

	/**
	 * Analytics constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action(
			'pmc_google_analytics_pre_send_js',
			[ $this, 'track_referrals_from_dd' ]
		);

		add_action(
			'wp_enqueue_scripts',
			[ $this, 'set_script_data' ],
			// Hooked later to ensure script is already enqueued.
			Assets::ENQUEUE_PRIORITY + 1
		);
	}

	/**
	 * Track referrals from DD issue to other parts of the site.
	 */
	public function track_referrals_from_dd(): void {
		if (
			is_post_type_archive( POST_TYPE )
			|| is_singular( POST_TYPE )
		) {
			return;
		}

		$prefix = get_post_type_archive_link( POST_TYPE );

		// Fallback for when archive is disabled.
		if ( false === $prefix ) {
			$prefix = user_trailingslashit( home_url( POST_TYPE ) );
		}

		?>
		if (0 === document.referrer.indexOf('<?php echo esc_js( $prefix ); ?>')
		) {
			ga( 'set', 'referrer', document.referrer );
		}
		<?php
	}

	/**
	 * Add settings for analytics tracking.
	 */
	public function set_script_data(): void {
		wp_localize_script(
			Assets::ASSET_HANDLE,
			'pmcDigitalDailyAnalyticsConfig',
			apply_filters(
				'pmc_digital_daily_analytics_settings',
				[
					'gaId'                 => Analytics::get_instance()
						->populate_ga_dev_id(),
					'anchorClickSelectors' => [],
					'blockClickSelectors'  => [],
					'pageviewSelectors'    => [],
					'viewType'             => Full_View::is()
						? 'full'
						: 'landing',
				]
			),
		);
	}

	/**
	 * Default to PMC's development Google Analytics account rather than sending
	 * data to a site's default GA account.
	 *
	 * @codeCoverageIgnore Cannot unload GA class.
	 *
	 * @return string|null
	 */
	public function populate_ga_dev_id(): ?string {
		if ( class_exists( PMC_Google_Universal_Analytics::class, false ) ) {
			return PMC_Google_Universal_Analytics::get_instance()
				->google_analytics_account_dev();
		}

		return null;
	}
}
