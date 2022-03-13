<?php

namespace PMC\Piano;

use PMC\Global_Functions\Traits\Singleton;
/**
 * Handles all 3rd-party reporting integrations.
 */
class Reporting {

	use Singleton;

	/**
	 * Singleton initializations.
	 */
	protected function __construct() {
		// WordPress 'init' action
		add_action( 'init', [ $this, 'action_init' ] );
	}

	/**
	 * WordPress 'init' action
	 */
	public function action_init() {
		if ( ! is_admin() ) {
			// Google Analytics - Custom Dimensions
			add_action( 'pmc_google_analytics_custom_dimensions_js', [ $this, 'action_pmc_google_analytics_custom_dimensions_js' ], 10, 2 );
		}
	}

	/**
	 * Insert subscription-related Custom Dimensions into
	 * the Google Analytics Pageview event.
	 *
	 * @uses filter::pmc_google_analytics_custom_dimensions_js
	 * @see PMC_Google_Universal_Analytics
	 *
	 * @param array $dimensions List of custom dimensions
	 * @param array $dimension_map Indexes of custom dimensions
	 */
	function action_pmc_google_analytics_custom_dimensions_js( $dimensions, $dimension_map ) {
		$dim_entitlement          = 'dimension' . $dimension_map['paywall-entitlement'];
		$dim_user_type            = 'dimension' . $dimension_map['user-type'];
		$dim_acct_type            = 'dimension' . $dimension_map['paywall-acct-type'];
		$dim_acct_id              = 'dimension' . $dimension_map['paywall-acct-id'];
		$dim_org_id               = 'dimension' . $dimension_map['paywall-org-id'];
		$dim_org_name             = 'dimension' . $dimension_map['paywall-org-name'];
		$dim_auth_provider        = 'dimension' . $dimension_map['paywall-auth-provider'];
		$dim_special_product_code = 'dimension' . $dimension_map['paywall-special-product-code'];
		$dim_product_code         = 'dimension' . $dimension_map['paywall-product-code'];
		$dim_sub_level_required   = 'dimension' . $dimension_map['paywall-sub-level-required'];
		$dim_sub_roadblock_hit    = 'dimension' . $dimension_map['paywall-sub-roadblock-hit'];
		$dim_paywall_logged_in    = 'dimension' . $dimension_map['paywall-logged-in'];
		?>

		try {
			<?php /* @see pmc-piano.js pmcPiano.setUserData to see where and how this cookie is set.  */ ?>
			var pmc_piano_reporting_cookie = JSON.parse(pmc.cookie.get('pmc_piano_reporting')) || {};

			dim[<?php echo wp_json_encode( $dim_entitlement ); ?>]          = pmc_piano_reporting_cookie.entitlements || false;
			dim[<?php echo wp_json_encode( $dim_user_type ); ?>]            = <?php echo is_user_logged_in() ? "'STAFF'" : 'false'; ?> || pmc_piano_reporting_cookie.user_type || 'ANONYMOUS';
			dim[<?php echo wp_json_encode( $dim_acct_type ); ?>]            = pmc_piano_reporting_cookie.acct_type || false;
			dim[<?php echo wp_json_encode( $dim_acct_id ); ?>]              = pmc_piano_reporting_cookie.acct_id || false;
			dim[<?php echo wp_json_encode( $dim_org_id ); ?>]               = pmc_piano_reporting_cookie.org_id || false;
			dim[<?php echo wp_json_encode( $dim_org_name ); ?>]             = pmc_piano_reporting_cookie.org_name || false;
			dim[<?php echo wp_json_encode( $dim_auth_provider ); ?>]        = pmc_piano_reporting_cookie.paywall_logged_in ? 'piano' : false;
			dim[<?php echo wp_json_encode( $dim_special_product_code ); ?>] = false;
			dim[<?php echo wp_json_encode( $dim_product_code ); ?>]         = false;
			dim[<?php echo wp_json_encode( $dim_sub_level_required ); ?>]   = false;
			dim[<?php echo wp_json_encode( $dim_sub_roadblock_hit ); ?>]    = false;
			dim[<?php echo wp_json_encode( $dim_paywall_logged_in ); ?>]    = pmc_piano_reporting_cookie.paywall_logged_in ? 'yes' : 'no';
		} catch (err) {
			if (window.pmcPianoData.canDebug) {
				console.warn('PMC: Piano: pmc_google_analytics_custom_dimensions_js', err);
			}
		}

		<?php
	}
}
