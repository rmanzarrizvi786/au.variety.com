<?php
/**
 * Configuration file for pmc-subscription-v2 plugin.
 *
 * @author  SPPe
 *
 * @since   2020-04-21 SPP-3050
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;
use Variety\Plugins\Variety_VIP\Content;
use Variety\Plugins\Variety_VIP\VIP;

class PMC_Subscription_V2 {

	use Singleton;

	/**
	 * PMC_Subscription_V2 constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() {

		add_filter(
			'pmc_subscription_paywall_content_eligibility_conditions',
			[ $this, 'filter_content_eligibility_conditions' ]
		);
		add_filter(
			'pmc_subscription_wp_provider',
			[ $this, 'filter_pmc_subscription_wp_provider' ]
		);
		add_filter(
			'pmc_subscription_messages',
			[ $this, 'filter_pmc_subscription_messages' ]
		);
		add_filter(
			'pmc_subscription_login_callback_url',
			[ $this, 'filter_pmc_subscription_login_callback_url' ],
			10,
			2
		);

		add_filter( 'pmc_subscription_entitlements_map', [ $this, 'filter_pmc_subscription_entitlements_map' ] );
	}

	/**
	 * This method returns conditions that determine what entitlement is required for a specific content.
	 *
	 * @return array
	 */
	public function filter_content_eligibility_conditions() {
		return [
			[
				'entitlement_required_to_view' => [ 'Variety.VarietyVIP' ],
				'conditions'                   => [

					// Is this VIP+ content?
					[
						'description' => '%s VIP+ content',
						'condition'   => function ( $post ) {
							$is_vip_post = in_array(
								get_post_type( $post ),
								(array) [
									'variety_vip_report',
									'variety_vip_video',
									'variety_vip_post',
								],
								true
							);

							// Verify that content is manually set as a VIP+ article
							//
							// Turning on/off this meta key can be done as the following:
							// Edit post > 'Article Type' Meta Box > 'Is VIP Variety Article'
							// @see inc/classes/class-fields.php:add_meta()
							$is_fake_vip_post = 'Y' === get_post_meta( $post->ID, 'variety_post_vip', true );

							return $is_vip_post || $is_fake_vip_post;
						},
					],
				],
			],
		];
	}

	/**
	 * Add support to use WordPress as a provider to authenticate the user
	 * @param  array $options The array of options
	 * @return array
	 */
	public function filter_pmc_subscription_wp_provider( $options ) {
		$options['active']       = true; // we want to activate the WordPress authenticated user
		$options['entitlements'] = [ 'Variety.VarietyVIP' ]; // the entitlements to grant the user access if authorized
		return $options;
	}

	/**
	 * See https://confluence.pmcdev.io/x/kw2eAw
	 *
	 * @param array $messages
	 *
	 * @return array
	 */
	public function filter_pmc_subscription_messages( array $messages = [] ) : array {
		return array_merge(
			$messages,
			[
				'account_login_contact_agents'       => __( 'For questions regarding your Variety VIP+ subscription, please email customerservicevip@variety.com or call 888-222-0276 (US & Canada) or 332-219-2192 (International).', 'pmc-variety' ),
				'account_login_contact_corporate'    => __( 'Need help contacting your corporate administrator regarding your Variety VIP+ access? Please email customerservicevip@variety.com or call 888-222-0276 (US & Canada) or 332-219-2192 (International).', 'pmc-variety' ),
				'account_login_contact_site_license' => __( 'Need help contacting your corporate administrator regarding your Variety VIP+ access? Please email customerservicevip@variety.com or call 888-222-0276 (US & Canada) or 332-219-2192 (International).', 'pmc-variety' ),
				'support_text'                       => __( 'For assistance, please contact Customer Service at customerservicevip@variety.com, 888-222-0276 (US & Canada) or 332-219-2192 (International).', 'pmc-variety' ),
			]
		);
	}

	/**
	 * Override callback URL to bring user to VIP landing page.
	 *
	 * @param string $callback_url
	 * @param array  $query_vars
	 *
	 * @return string
	 */
	public function filter_pmc_subscription_login_callback_url( string $callback_url, array $query_vars ) : string {

		if ( Content::is_vip_page() ) {
			return $callback_url;
		}

		return add_query_arg( $query_vars, trailingslashit( VIP::vip_url() ) );

	}

	/**
	 * @param $entitlements
	 *
	 * @return array
	 */
	public function filter_pmc_subscription_entitlements_map( $entitlements ) : array {
		return [
			'vyvip' => [
				'entitlements' => [ 'Variety.VarietyVIP' ],
				'text'         => 'VIP Subscriber',
			],
		];
	}

}
