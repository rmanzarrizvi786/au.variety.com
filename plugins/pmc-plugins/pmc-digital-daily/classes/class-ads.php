<?php
/**
 * Digital Daily ad configuration.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use PMC;
use PMC_Ad_Conditions;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Ads.
 */
class Ads {
	use Singleton;

	/**
	 * Ads constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'init', [ $this, 'register_conditions' ] );
		add_filter(
			'pmc_adm_prepare_boomerang_global_settings',
			[ $this, 'add_issue_date_to_targeting_data' ]
		);
		add_filter( 'pmc_adm_dfp_skin_enabled', [ $this, 'disable_skins' ] );
		add_filter(
			'pmc_adm_optimize_cls',
			[ $this, 'enable_cls_optimization' ]
		);
	}

	/**
	 * Register ad conditions.
	 */
	public function register_conditions(): void {
		PMC_Ad_Conditions::get_instance()->register(
			'digital_daily_is_full_view',
			[ $this, 'is_full_view' ]
		);

		PMC_Ad_Conditions::get_instance()->register(
			'digital_daily_is_landing_view',
			[ $this, 'is_landing_view' ]
		);

		PMC_Ad_Conditions::get_instance()->register(
			'digital_daily_issue_date',
			[ $this, 'is_matching_issue_date' ],
			[
				'date',
			]
		);
	}

	/**
	 * Add issue date to Boomerang's targeting data.
	 *
	 * @param array $data Boomerang settings array.
	 * @return array
	 */
	public function add_issue_date_to_targeting_data( array $data ): array {
		if ( ! is_dd() || ! is_singular() ) {
			return $data;
		}

		$data['targeting_data']['issue'] = get_the_date( 'mdy' );

		return $data;
	}

	/**
	 * Prevent skins from appearing on Digital Daily pages.
	 *
	 * @param bool $enabled Are skins enabled.
	 * @return bool
	 */
	public function disable_skins( bool $enabled ): bool {
		if (
			is_singular( POST_TYPE )
			|| is_post_type_archive( POST_TYPE )
		) {
			return false;
		}

		return $enabled;
	}

	/**
	 * Enable CLS optimization on Digital Daily if not enabled site-wide.
	 *
	 * @param bool|string $value Value of Cheezcap `pmc_optimize_cls` option.
	 * @return bool|string
	 */
	public function enable_cls_optimization( $value ) {
		$desired_value = PMC::is_desktop() ? 'maxsize' : 'minsize';

		if ( $desired_value === $value ) {
			return $value;
		}

		if ( is_singular( POST_TYPE ) ) {
			return $desired_value;
		}

		return $value;
	}

	/**
	 * Ad condition for targeting full-content view.
	 *
	 * @return bool
	 */
	public function is_full_view(): bool {
		return is_singular( POST_TYPE ) && Full_View::is();
	}

	/**
	 * Ad condition for targeting landing-page view.
	 *
	 * @return bool
	 */
	public function is_landing_view(): bool {
		return is_singular( POST_TYPE ) && ! Full_View::is();
	}

	/**
	 * Ad condition for targeting issue's publish date.
	 *
	 * @param string|null $condition_date Targeted date, in a format that
	 *                                    `strtotime()` can decipher.
	 * @return bool
	 */
	public function is_matching_issue_date(
		?string $condition_date = null
	): bool {
		if ( null === $condition_date ) {
			return false;
		}

		if ( ! is_singular( POST_TYPE ) ) {
			return false;
		}

		$format = 'Y-m-d';

		$issue_date      = get_the_date( $format, get_queried_object() );
		$comparison_date = gmdate( $format, strtotime( $condition_date ) );

		return $issue_date === $comparison_date;
	}
}
