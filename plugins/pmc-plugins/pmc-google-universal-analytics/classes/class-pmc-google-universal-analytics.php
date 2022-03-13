<?php
/*
Plugin Name: PMC Google Universal Analytics
Description: Adds Google Universal Analytics and custom event tracking.
Author: Corey Gilmore, PMC
License: PMC Proprietary.  All rights reserved.
*/

/**
 * @TODO
 * Rewrite get_pre_trackpageview() -- used only by DL
 * Rewrite get_track_pageview() -- used by BGR (track user agent), VY (secondary tracking id)
 * Rewrite do_action( 'pmc_google_analytics_output_analytics_code' ) -- used by BGR, VY
 * Remove BGR dependence on `pmc_google_analytics_post_author` filter
 *
 * @since 2015-08-03 Corey Gilmore
 *
 * @version 2015-08-03 Corey Gilmore
 *   Changes between PMC_Google_Analytics and PMC_Google_Universal_Analytics
 *     - Removed unused get_push() function and `pmc_google_analytics_push` filter
 *
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Google_Universal_Analytics {

	use Singleton;

	/**
	 * Other plugins can grab this constant and possibly
	 * output something immediately before/after the
	 * analytics tracking is output.
	 *
	 * @since 1.3.2.0 2016-10-13 James Mehorter
	 *
	 * @var int The priority which we hook into wp_print_scripts
	 *          to output the analytics tracking code.
	 */
	const PRINT_SCRIPTS_PRIORITY = 5;

	const GA_ARRAY_DELIMITER = '|'; // The glue arrays are imploded with, before sending to GA
	const INTERNAL_VERSION   = 2.4;

	protected $_ga_account_id = false;
	protected $_ga_disable_display_advertising = false;
	protected $_event_tracking_default = array(
		'event'    => 'click',
		'category' => '',
		'action'   => 'click',
		'label'    => 'label',
	);

	// we need to track if scripts has been render do we don't output duplicate code
	protected $_scripts_rendered = false;

	/**
	 * PMC_Google_Universal_Analytics constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup wp hooks
	 */
	protected function _setup_hooks() {

		// Run after default priority so that it overrides on dev
		add_filter( 'pre_option_pmc_google_analytics_account', array( $this, 'google_analytics_account_dev' ), 11 );
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'filter_pmc_global_cheezcap_options' ) );

		if ( $this->can_show() ) {
			add_action( 'wp', array( $this, 'init' ) );

			// use action wp_print_scripts instead of wp_head to make scripts rendered after stylesheets
			// Run before other JS loads, for performance reasons
			// PEP-1545: conditional to determine which code to output to minimize impact to other sites.
			// If pmc-quantcast-cmp plugin isn't active, load GA right away, otherwise quantcast=cmp will handle it.;
			$cmp_opt = ( \PMC_Cheezcap::get_instance()->get_option( 'pmc_quantcast_cmp' ) );
			if ( empty( $cmp_opt ) || 'no' === $cmp_opt ) {
				add_action( 'wp_print_scripts', array( $this, 'output_analytics_code' ), self::PRINT_SCRIPTS_PRIORITY );
			} else {
				add_action( 'wp_print_scripts', array( $this, 'output_gdpr_analytics_code' ), self::PRINT_SCRIPTS_PRIORITY );
			}

		}

		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_event_tracking' ) );
		add_action( 'wp_head', array( $this, 'ga_record_ad_block' ) );

		add_action(
			'wp_enqueue_scripts',
			function() {
			wp_enqueue_script( 'pmc-hooks' );
			}
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_outbrain_event_tracking' ) );

		// Larva compatible action to render raw html data
		add_action( 'pmc_do_render_custom_ga_tracking_attr', [ $this, 'render_custom_ga_tracking_attr' ], 10, 1 );
		add_action( 'pmc_do_render_custom_ga_data_attr', [ $this, 'render_custom_ga_data_attr' ], 10, 1 );

		add_filter( 'wp_kses_allowed_html', [ $this, 'filter_wp_kses_allowed_html' ], 10, 2 );

	}

	/**
	 * Extending the wp_kses_allowed_html to include custom-a-data/track attributes
	 * @param $allowedtags
	 * @param $context
	 * @return mixed
	 */
	function filter_wp_kses_allowed_html( $allowedtags, $context ) {
		if ( 'post' === $context ) {
			$allowedtags['a']['custom-ga-data']  = true;
			$allowedtags['a']['custom-ga-track'] = true;
		}
		return $allowedtags;
	}

	/**
	 * Record in Google Analytics if ads are being blocked by the browser.
	 *
	 * @return void
	 */
	public function ga_record_ad_block() {
		?>
		<script>
			try {
				// Don't rely on jQuery being loaded yet.
				document.addEventListener( 'DOMContentLoaded', function() {
					// Record in GA event if ads are blocked.
					if ( true === window.pmc_is_adblocked ) {
						ga( 'send', 'event', 'ad_blocker', 'blocked', 'ads_blocked', {
							nonInteraction: true
						});
					}
				});
			} catch( err ) {
				// Do nothing...
			}
		</script>
		<?php
	}

	/**
	 * Filter the 'Global Theme Options' cheezcap group
	 *
	 * @param array $cheezcap_options The cheezcap options displayed in this group
	 *
	 * @return array The *possibly* modified cheezcap group of options
	 */
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {

		// Add a toggle to enable/disable cross domain tracking
		$cheezcap_options[] = new \CheezCapDropdownOption(
			'GA - Enable Cross Domain Tracking',
			'When enabled, a user\'s GA session will extend through configured domains. Engineers can add domains via the pmc_google_analytics_cross_domains filter.',
			'pmc_ga_enable_cross_domain_tracking',
			array(
				'disabled',
				'enabled',
			),
			0,
			array( 'Disabled', 'Enabled' )
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			esc_html__( 'GA - Enable OUTBRAIN Tracking', 'pmc-google-universal-analytics' ),
			esc_html__( 'When enabled, Analytics events will be added for OUTBRAIN widget.', 'pmc-google-universal-analytics' ),
			'pmc_ga_enable_outbrain_tracking',
			array(
				'disabled',
				'enabled',
			),
			0,
			array( esc_html__( 'Disabled', 'pmc-google-universal-analytics' ), esc_html__( 'Enabled', 'pmc-google-universal-analytics' ) )
		);

		$cheezcap_options[] = new \CheezCapTextOption(
			esc_html__( 'Optimize - Container ID', 'pmc-google-universal-analytics' ),
			esc_html__( 'Provide Optimize Container ID for using with Optimize Tag', 'pmc-google-universal-analytics' ),
			'pmc_optimize_container_id',
			'', // Default Value.
			false // Not a Textarea.
		);

		return $cheezcap_options;
	}

	/**
	 * Short Circuit analytics option for development environment.
	 */
	public function google_analytics_account_dev() {

		// For development environment return dev analytics code
		$return_val = 'UA-1915907-52';

		if ( PMC::is_production() ) {
			$return_val = false;
		}

		return apply_filters( 'pmc_google_analytics_account_dev', $return_val );
	}

	public function init() {
		// Do nothing - yet
	}

	public function can_show() {
		if ( ! $this->_ga_account_id ) {
			$this->_ga_account_id = get_option( 'pmc_google_analytics_account' );
			$this->_ga_disable_display_advertising = get_option( 'pmc_ga_disable_display_advertising' );
		}

		return ( ! empty( $this->_ga_account_id ) && ! is_admin() && ! is_preview() );
	}

	// PEP-1545: output_gdpr_analytics_code and output_analytics_code are kopypasta duplicates.
	// Output_gdpr_analytics_code is modified to work with the pmc-quantcast-cmp plugin in order to only load GA with
	// GDPR consent or if GDPR does not apply.
	// output_analytics_code is the original function. This approach hits the sheknows deadline and minimizes impact on the rest of the sites.
	public function output_analytics_code() {

		//Had to do this here since pmc-groups is not available before.
		if ( class_exists( '\PMC\Onetrust\Onetrust' ) && class_exists( '\PMC\Geo_Uniques\Plugin' ) ) {
			if (
				\PMC\Onetrust\Onetrust::get_instance()->is_onetrust_enabled() &&
				( 'eu' === \PMC\Geo_Uniques\Plugin::get_instance()->pmc_geo_get_region_code() || 'eu' === \PMC::filter_input( INPUT_GET, 'region', FILTER_SANITIZE_STRING ) )
			) {
				$this->output_gdpr_analytics_code();
				return;
			}
		}

		/*
		 * Prevent scripts inlining in the AMP context.
		 * Google Analytics for AMP is handled in the `pmc-google-amp` plugin.
		 */
		if ( function_exists( 'is_amp_endpoint' ) && did_action( 'parse_query' ) && is_amp_endpoint() ) {
			return;
		}

		// prevent script from rendering multiple time
		if ( ! empty( $this->_scripts_rendered ) ) {
			return;
		}

		$this->_scripts_rendered = true;
		$dimensions              = $this->get_mapped_dimensions();
		$dimension_map           = $this->_get_dimension_map();

		$page_title = apply_filters( 'pmc_google_analytics_page_title', false );

		$create_cmd_fields_obj = array();

		// Cross domain tracking
		// @see https://support.google.com/analytics/answer/1034342?hl=en
		$cross_domain_tracking_enabled = \PMC_Cheezcap::get_instance()->get_option( 'pmc_ga_enable_cross_domain_tracking' );

		// Optimize Container ID to use with Optimize tag.
		$pmc_optimize_container_id = \PMC_Cheezcap::get_instance()->get_option( 'pmc_optimize_container_id' );

		/**
		 * Filter the list of GA cross domains.
		 *
		 * When populated with domains, a user's GA session will extend through configured domains.
		 * e.g. we add 'buysub.com' to the list on WWD so that a user session will continue
		 * when they leave wwd.com to buysub.com to purchase a subscription.
		 *
		 * @see https://support.google.com/analytics/answer/1034342?hl=en
		 *
		 * @param array An array of domains to include in cross domain tracking.
		 */
		$cross_domains = apply_filters( 'pmc_google_analytics_cross_domains', array() );

		$do_cross_domain_tracking = 'enabled' === $cross_domain_tracking_enabled && ! empty( $cross_domains ) && is_array( $cross_domains );

		if ( $do_cross_domain_tracking ) {
			$create_cmd_fields_obj['allowLinker'] = true;
		}

		/**
		 * To identifiy AMP page and non-AMP page.
		 *
		 * @since 2017-09-12 - Dhaval Parekh - CDWE-645
		 *
		 * @see   https://support.google.com/analytics/answer/7486764?hl=en&ref_topic=7378717
		 */
		$create_cmd_fields_obj['useAmpClientId'] = true;

		/**
		 * Filter the fields object sent to GA in the 'create' command
		 *
		 * @param array $create_cmd_fields_obj An array of fields to send the create command.
		 *                                     Keyed by the field name. e.g.
		 *                                     $create_cmd_fields_obj['allowLinker'] = true;
		 */
		$create_cmd_fields_obj = apply_filters( 'pmc_google_analytics_create_cmd_fields_obj', $create_cmd_fields_obj );

		// @TODO: Add support for debug output of dimensions (eg, include a display name, not just dimension#)
		$blocker_atts = [
			'type'  => 'text/javascript',
			'class' => '',
		];

		if ( true === apply_filters( 'pmc_onetrust_force_block_cookie_scripts', false ) ) {
			$blocker_atts = [
				'type'  => 'text/plain',
				'class' => 'optanon-category-C0004',
			];
		}
		?>

		<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			<?php
			// PEP-1545: Dan Berko 11-09-2018
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			// disabled because of time constraints; it looks like PMC_Scripts handles it anyway
			echo PMC_Scripts::localize_script( 'pmc_ga_dimensions', $dimensions, false );
			echo PMC_Scripts::localize_script( 'pmc_ga_mapped_dimensions', $this->_get_dimension_map(), false );
			// phpcs:enable
			?>
			pmc_ga_dimensions[<?php echo wp_json_encode( 'dimension' . $dimension_map['protocol'] ); ?>] = document.location.protocol.replace(':', '');
			pmc_ga_dimensions[<?php echo wp_json_encode( 'dimension' . $dimension_map['pageview-id'] ); ?>] = window._skmPageViewId;
			(function(dim){
				<?php
				/**
				 * Allow themes to output custom javascript for setting/overriding custom dimensions
				 * JQUERY IS NOT AVAILABLE HERE
				 * JS vars available in this action:
				 *  dim for the pmc_ga_dimensions object
				 *
				 * @version 2015-08-03 Corey Gilmore
				 *
				 */
				do_action( 'pmc_google_analytics_custom_dimensions_js', $dimensions, $dimension_map );
				?>
			})(pmc_ga_dimensions);

			<?php
			// PEP-1545: Dan Berko 11-09-2018
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			// disabled because of time constraints; it looks like PMC_Scripts handles it anyway
			echo PMC_Scripts::localize_script( 'pmc_ga_fields_obj', $create_cmd_fields_obj, false );
			// phpcs:enable
			?>

			ga('create', <?php echo wp_json_encode( $this->_ga_account_id ); ?>, 'auto', pmc_ga_fields_obj);
			ga('set', 'forceSSL', true);

			<?php
			/**
			 * Filter to allow not to remove campaign tracking code from browser URL.
			 */
			$pmc_remove_tracking = apply_filters( 'pmc_google_analytics_remove_tracking_from_browser_url', true );

			if ( true === $pmc_remove_tracking ) {
				?>
				if ( window.pmc && pmc.tracking ) {
					ga('set', 'hitCallback', function() { pmc.tracking.do_call_events(); });
					var utms = pmc.tracking.get_properties_string();

					if ( utms !== '' ) {
						// the utm params are gone already so add them back
						ga('set', 'location', location.href.split('#')[0] + ( location.search ? '&' : '?' ) + utms);
					}
				}
			<?php
			}
			if ( ! empty( $page_title ) ) :
				?>
				ga('set', 'title', <?php echo wp_json_encode( $page_title ); ?>);<?php endif; ?>
			ga('require', 'linkid', 'linkid.js');
			<?php
			if ( ! empty( $pmc_optimize_container_id ) ) {
				// Add Optimize Plugin. PMCEED-782.
				?>
				ga('require', <?php echo wp_json_encode( $pmc_optimize_container_id ); ?> );

				<?php

			}

			if ( empty( $this->_ga_disable_display_advertising ) ) :
				?>
				ga('require', 'displayfeatures');<?php endif; ?>
			ga('set', pmc_ga_dimensions);
			window.pmcGaCustomDimensions = pmc_ga_dimensions;
			<?php
			/**
			 * Allow themes to make arbitrary GA JS calls.
			 *
			 * @param array  $dimensions                    The current GA dimmensions.
			 * @param array  $dimension_map                 The current mapping of GA dimmensions to their named values.
			 * @param string $ga_dimmensions_JS_object_name The name of the JS object which contains the GA dimmensions
			 */
			do_action( 'pmc_google_analytics_pre_send_js', $dimensions, $dimension_map, 'pmc_ga_dimensions' );
			?>

			<?php if ( $do_cross_domain_tracking ) : ?>

				ga('require', 'linker');
				ga('linker:autoLink', <?php echo wp_json_encode( $cross_domains ); ?>);

			<?php endif; ?>

			ga('send', 'pageview');

			<?php
			/**
			 * Allow themes to hook into the below action to render callback
			 * JQUERY IS NOT AVAILABLE HERE
			 * JS vars available in this action
			 */
			do_action( 'pmc_google_analytics_after_pageview_js', $dimensions, $dimension_map, 'pmc_ga_dimensions' );
			?>

		</script>

		<?php
	}

	// @codeCoverageIgnoreStart
	// PEP-1545: output_gdpr_analytics_code and output_analytics_code are kopypasta duplicates.
	// Output_gdpr_analytics_code is modified to work with the pmc-quantcast-cmp plugin in order to only load GA with
	// GDPR consent or if GDPR does not apply.
	// output_analytics_code is the original function. This approach hits the sheknows deadline and minimizes impact on the rest of the sites.
	public function output_gdpr_analytics_code() {

		/*
		 * Prevent scripts inlining in the AMP context.
		 * Google Analytics for AMP is handled in the `pmc-google-amp` plugin.
		 */
		if ( function_exists( 'is_amp_endpoint' ) && did_action( 'parse_query' ) && is_amp_endpoint() ) {
			return;
		}

		// prevent script from rendering multiple time
		if ( ! empty( $this->_scripts_rendered ) ) {
			return;
		}

		$this->_scripts_rendered = true;
		$dimensions = $this->get_mapped_dimensions();
		$dimension_map = $this->_get_dimension_map();

		$page_title = apply_filters( 'pmc_google_analytics_page_title', false );

		$create_cmd_fields_obj = array();

		// Cross domain tracking
		// @see https://support.google.com/analytics/answer/1034342?hl=en
		$cross_domain_tracking_enabled = \PMC_Cheezcap::get_instance()->get_option( 'pmc_ga_enable_cross_domain_tracking' );

		// Optimize Container ID to use with Optimize tag.
		$pmc_optimize_container_id = \PMC_Cheezcap::get_instance()->get_option( 'pmc_optimize_container_id' );

		/**
		 * Filter the list of GA cross domains.
		 *
		 * When populated with domains, a user's GA session will extend through configured domains.
		 * e.g. we add 'buysub.com' to the list on WWD so that a user session will continue
		 * when they leave wwd.com to buysub.com to purchase a subscription.
		 *
		 * @see https://support.google.com/analytics/answer/1034342?hl=en
		 *
		 * @param array An array of domains to include in cross domain tracking.
		 */
		$cross_domains = apply_filters( 'pmc_google_analytics_cross_domains', array() );

		$do_cross_domain_tracking = 'enabled' === $cross_domain_tracking_enabled && ! empty( $cross_domains ) && is_array( $cross_domains );

		if ( $do_cross_domain_tracking ) {
			$create_cmd_fields_obj['allowLinker'] = true;
		}

		/**
		 * To identifiy AMP page and non-AMP page.
		 *
		 * @since 2017-09-12 - Dhaval Parekh - CDWE-645
		 *
		 * @see   https://support.google.com/analytics/answer/7486764?hl=en&ref_topic=7378717
		 */
		$create_cmd_fields_obj['useAmpClientId'] = true;

		/**
		 * Filter the fields object sent to GA in the 'create' command
		 *
		 * @param array $create_cmd_fields_obj An array of fields to send the create command.
		 *                                     Keyed by the field name. e.g.
		 *                                     $create_cmd_fields_obj['allowLinker'] = true;
		 */
		$create_cmd_fields_obj = apply_filters( 'pmc_google_analytics_create_cmd_fields_obj', $create_cmd_fields_obj );

		// @TODO: Add support for debug output of dimensions (eg, include a display name, not just dimension#)
		// Instantiate GA object first so we can collect all other tracking info as usual.
		// If we get measurement permissions from CMP or GDPR doesn't apply, we load the analytics
		// script below in the loadGA function. If we don't get permission, nothing gets sent anywhere.
		// This splits up the Google-recommended analytics loading script into two parts,
		// 1)instantiating the analytics object and2)  loading the javascript file
		?>
		<script type="text/javascript">
			(function(i,r){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();})(window,'ga');

			function loadGA(cookieOk){
				// Default to true so the non-gated version still sets cookies
				cookieOk = (typeof cookieOk !== 'undefined') ?  cookieOk : true;
				// Load the GA script.
				(function(s,o,g,a,m){a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(document,'script','//www.google-analytics.com/analytics.js');

				<?php echo PMC_Scripts::localize_script( 'pmc_ga_dimensions', $dimensions, false ); ?>
				pmc_ga_dimensions[<?php echo wp_json_encode( 'dimension' . $dimension_map['protocol'] ); ?>] = document.location.protocol.replace(':', '');
				if ( cookieOk ) {
					(function(dim){
						<?php
						/**
						 * Allow themes to output custom javascript for setting/overriding custom dimensions
						 * JQUERY IS NOT AVAILABLE HERE
						 * JS vars available in this action:
						 *  dim for the pmc_ga_dimensions object
						 *
						 * @version 2015-08-03 Corey Gilmore
						 *
						 */
						do_action( 'pmc_google_analytics_custom_dimensions_js', $dimensions, $dimension_map );
						?>
					})(pmc_ga_dimensions);
				}
				<?php echo PMC_Scripts::localize_script( 'pmc_ga_fields_obj', $create_cmd_fields_obj, false ); ?>
				if ( cookieOk === false ) {
					// if storage consent is not granted, don't set cookies.
					pmc_ga_fields_obj['storage'] = 'none';
					pmc_ga_fields_obj['storeGac'] = false;
				}
				ga('create', <?php echo wp_json_encode( $this->_ga_account_id ); ?>, 'auto', pmc_ga_fields_obj);
				ga('set', 'forceSSL', true);

				<?php
				/**
				 * Filter to allow not to remove campaign tracking code from browser URL.
				 */
				$pmc_remove_tracking = apply_filters( 'pmc_google_analytics_remove_tracking_from_browser_url', true );

				if ( true === $pmc_remove_tracking ) {
					?>
					if ( window.pmc && pmc.tracking ) {
						ga('set', 'hitCallback', function() { pmc.tracking.do_call_events(); });
						var utms = pmc.tracking.get_properties_string();

						if ( utms !== '' ) {
							// the utm params are gone already so add them back
							ga('set', 'location', location.href.split('#')[0] + ( location.search ? '&' : '?' ) + utms);
						}
					}
				<?php } ?>
				<?php if ( ! empty( $page_title ) ) : ?>ga('set', 'title', <?php echo wp_json_encode( $page_title ); ?>);<?php endif; ?>
				ga('require', 'linkid', 'linkid.js');
				<?php if ( empty( $this->_ga_disable_display_advertising ) ) : ?>ga('require', 'displayfeatures');<?php endif; ?>
				ga('set', pmc_ga_dimensions);
				window.pmcGaCustomDimensions = pmc_ga_dimensions;
				<?php
				/**
				 * Allow themes to make arbitrary GA JS calls.
				 *
				 * @param array  $dimensions                    The current GA dimmensions.
				 * @param array  $dimension_map                 The current mapping of GA dimmensions to their named values.
				 * @param string $ga_dimmensions_JS_object_name The name of the JS object which contains the GA dimmensions
				 */
				do_action( 'pmc_google_analytics_pre_send_js', $dimensions, $dimension_map, 'pmc_ga_dimensions' );
				?>

				<?php if ( $do_cross_domain_tracking ) : ?>

					ga('require', 'linker');
					ga('linker:autoLink', <?php echo wp_json_encode( $cross_domains ); ?>);

				<?php endif; ?>
				<?php if ( ! empty( $pmc_optimize_container_id ) ) : ?>

				ga('require', <?php echo wp_json_encode( $pmc_optimize_container_id ); ?>);
				ga('linker:autoLink', <?php echo wp_json_encode( $cross_domains ); ?>);

				<?php endif; ?>

				ga('send', 'pageview');

				<?php
				/**
				 * Allow themes to hook into the below action to render callback
				 * JQUERY IS NOT AVAILABLE HERE
				 * JS vars available in this action
				 */
				do_action( 'pmc_google_analytics_after_pageview_js', $dimensions, $dimension_map, 'pmc_ga_dimensions' );
				?>
			}

		</script>

		<?php
	}
	// @codeCoverageIgnoreEnd

	protected function _get_dimension_map() {
		$dimension_map = array(
			'page-type'                    => 1,
			'page-subtype'                 => 2,
			'id'                           => 3,
			'author'                       => 4,
			'category'                     => 5,
			'tag'                          => 6,
			'vertical'                     => 7,
			'primary-category'             => 8,
			'primary-vertical'             => 9,
			'publish-year'                 => 10,
			'publish-month'                => 11,
			'publish-day'                  => 12,
			'publish-hour'                 => 13,
			'publish-minute'               => 14,
			'protocol'                     => 15, // set by JS, any value here will be ignored.
			'paywall-entitlement'          => 16, // Set by LOB only.
			'paywall-sub-level-required'   => 17, // Set by LOB only.
			'paywall-sub-roadblock-hit'    => 18, // Set by LOB only.
			'paywall-acct-type'            => 19, // Set by LOB only.
			'paywall-acct-id'              => 20, // Set by LOB only.
			'paywall-org-name'             => 21, // Set by LOB only.
			'paywall-org-id'               => 22, // Set by LOB only.
			'paywall-auth-provider'        => 23, // Set by LOB only.
			'paywall-logged-in'            => 24, // Set by LOB only.
			'publish-timestamp-gmt'        => 25,
			'publish-timestamp'            => 26,
			'publish-day-of-week'          => 27,
			'omni-visit-id'                => 28, // set by JS using pmc_meta page object in pmc-omni plugin.
			'user-type'                    => 29,
			'a-b-test'                     => 30, // set by LOB or Plugin using A/B Test.
			'experiment-name'              => 31, // set by LOB or Plugin using A/B Test.
			'paywall-special-product-code' => 32, // Set by LOB(WWD) only - ULS cookie value as the Special Product Code of CDS user.
			'paywall-product-code'         => 33, // Set by LOB(WWD) only - ULS cookie value as the CDS Product id of CDS user.
			'post-options'                 => 34,
			'child-post-id'                => 35,
			'page-variant-name'            => 36, // This was used by RS for some reason, need to find out why/how it was added
			'pageview-id'                  => 37,
		);

		/**
		 * Most brands shouldn't filter the dimemsion map but SheKnows needs to.
		 * SheKnows uses SKM dimensions for 1-112, and then PMC dimensions for 113+.
		 *
		 * @param array $dimension_map Map of existing dimensions.
		 */
		$dimension_map = apply_filters( 'pmc_google_analytics_dimension_map', $dimension_map );

		$offset = intval( apply_filters( 'pmc_google_analytics_dimension_offset', 0 ) );

		if ( $offset > 0 ) {

			foreach ( array_keys( (array) $dimension_map ) as $key ) {

				$dimension_map[ $key ] = intval( $dimension_map[ $key ] ) + $offset;

			}

		}

		return $dimension_map;

	}

	protected function _get_dimension_keys() {
		static $dimension_keys = false;

		if ( false === $dimension_keys ) {
			$dimension_keys = $this->_get_dimension_map();
			// Fill every key with null, which we check for to avoid outputting unset values
			// @see PMC_Google_Universal_Analytics::get_mapped_dimensions()
			$dimension_keys = array_fill_keys( array_keys( $dimension_keys ), null );
		}

		return $dimension_keys;
	}

	public function get_mapped_dimensions( string $key_prefix = 'dimension', array $overrides = [] ) : array {
		$dimension_map = $this->_get_dimension_map();
		$dimensions = $this->get_custom_dimensions();
		$mapped = array();

		foreach ( $dimension_map as $dim => $num ) {
			if ( 'protocol' === $dim ) {
				continue;
			}
			if ( isset( $overrides[ $key_prefix . $num ] ) ) {
				$mapped[ $key_prefix . $num ] = $overrides[ $key_prefix . $num ];
				unset( $overrides[ $key_prefix . $num ] );
				continue;
			}
			if ( isset( $dimensions[ $dim ] ) && ! is_null( $dimensions[ $dim ] ) ) {
				$val = $dimensions[ $dim ];
				if ( is_array( $val ) ) {
					$val = implode( $this::GA_ARRAY_DELIMITER, $val );
				}
				$mapped[ $key_prefix . $num ] = $val;
			}
		}

		if ( ! empty( $overrides ) ) {
			foreach ( $overrides as $key => $val ) {
				$mapped[ $key ] = $val;
			}
		}

		return $mapped;
	}

	public function get_custom_dimensions() {
		$meta = PMC_Page_Meta::get_page_meta();
		$dimensions = $this->_get_dimension_keys();

		// Populate $dimensions with non-empty $meta values
		foreach ( $meta as $mk => $mv ) {
			if ( array_key_exists( $mk, $dimensions ) && ! empty( $mv ) ) {
				$dimensions[ $mk ] = $mv;
			}
		}

		// @TODO: Properly set all this for deep links to a gallery slide
		// post, custom post type, but no attachment or page
		if ( is_single() ) {
			$p = get_post();
			if ( ! empty( $p ) ) {

				$ts = strtotime( $p->post_date );
				$dimensions['id'] = $p->ID;
				$dimensions['publish-day']            = (string) date( 'd', $ts );
				$dimensions['publish-month']          = (string) date( 'm', $ts );
				$dimensions['publish-year']           = (string) date( 'Y', $ts );
				$dimensions['publish-hour']           = (string) date( 'H', $ts );
				$dimensions['publish-minute']         = (string) date( 'i', $ts );
				$dimensions['publish-timestamp-gmt']  = get_post_time( 'c', true, $p->ID );
				$dimensions['publish-timestamp']      = (string) $p->post_date;
				$dimensions['publish-day-of-week']    = (string) date( 'l', $ts );

				// Reset this, PMC_Post_Meta uses display name, which isn't good for analytics
				$dimensions['author'] = null;
				$authors = $this->_get_post_authors( $p->ID );
				if ( ! empty( $authors ) ) {
					$authors = implode( $this::GA_ARRAY_DELIMITER, $authors );
					$dimensions['author'] = $authors;
				}

			}
		} else {
			// hide authors for pages, attachments, everything else
			$dimensions['author'] = null;
		}

		// PMCVIP-2456. Set user type
		if ( current_user_can( 'edit_posts' ) ) {
			$dimensions['user-type'] = 'staff';
		} else {
			$dimensions['user-type'] = 'anonymous';
		}

		$dimensions = $this->_slugify_dimension_values( $dimensions );

		// filter $dimensions here
		$dimensions = apply_filters( 'pmc_google_analytics_get_custom_dimensions', $dimensions );

		return $dimensions;

	}

	/**
	 * Converts dimension values into slugs
	 *
	 * @param $dimensions
	 * @return mixed
	 */
	protected function _slugify_dimension_values( $dimensions ) {

		// Slugify dimensions
		$dims_to_slugify = array(
			'primary-category',
			'category',
			'tag',
		);

		foreach ( $dims_to_slugify as $dim ) {

			if ( ! empty( $dimensions[ $dim ] ) ) {
				$category = $dimensions[ $dim ];
				if ( is_array( $category ) ) {
					foreach ( $category as $k => $v ) {
						$dimensions[ $dim ][ $k ] = sanitize_title( $v );
					}
				} else {
					$dimensions[ $dim ] = sanitize_title( $dimensions[ $dim ] );
				}
			}

		}

		return $dimensions;
	}

	protected function _get_post_authors( $post_id ) {
		$post_authors = PMC::get_post_authors_list( $post_id, 'all', 'user_login', 'user_nicename' );   // CA+ compatibility - get 'user_login' fallback to 'user_nicename'
		if ( empty( $post_authors ) ) {
			$post_authors = '';
		}

		$post_authors = explode( ',', $post_authors );
		$post_authors = apply_filters( 'pmc_google_analytics_post_authors', $post_authors, $post_id );

		return $post_authors;
	}

	/**
	 * @since 1.0.0.0 2011-01-27 Gabriel Koen
	 * @version 1.0.0.0 2011-01-27 Gabriel Koen
	 *
	 * @return void
	 */
	public function add_settings() {
		register_setting( 'general', 'pmc_google_analytics_account', array( $this, 'validate_account_id' ) );
		register_setting( 'general', 'pmc_ga_disable_display_advertising', array( $this, 'validate_ga_disable_display_advertising' ) );
		add_settings_field(
			'pmc_google_analytics',
			'Google Universal Analytics UA',
			array( $this, 'settings_field' ),
			'general',
			'default'
		);
	}

	/**
	 * @since 1.0.0.0 2011-01-27 Gabriel Koen
	 * @version 1.0.0.0 2011-01-27 Gabriel Koen
	 *
	 * @return void
	 */
	public function settings_field() {
		echo '<input id="pmc_google_analytics" name="pmc_google_analytics_account" type="text" value="' . esc_attr( get_option( 'pmc_google_analytics_account' ) ) . '" />';
		echo '<div><label><input type="checkbox" name="pmc_ga_disable_display_advertising" value="1" ' . checked( get_option( 'pmc_ga_disable_display_advertising' ), '1', false ) . '>&nbsp;Disable <a href="https://support.google.com/analytics/answer/3450482" target="_blank">Demographic Reporting / Display Advertiser Support</label><a/></div>';
	}

	/**
	 * @since 1.0.0.0 2011-01-27 Gabriel Koen
	 * @version 1.0.0.0 2011-01-27 Gabriel Koen
	 *
	 * @param $account_id
	 * @return null|string
	 */
	public function validate_account_id( $account_id ) {
		if ( empty( $account_id ) ) {
			return null;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error( 'pmc_google_analytics_account', 'pmc_google_analytics', __( 'Only administrators can change the Google Analytics account.' ), 'error' );

			return get_option( 'pmc_google_analytics_account' );
		}

		if ( ! preg_match( '/^UA-\d{4,9}-\d{1,2}$/i', $account_id ) ) {
			add_settings_error( 'pmc_google_analytics_account', 'pmc_google_analytics', __( 'Google Analytics account must be in the format: UA-XXXXX-YY' ), 'error' );

			return get_option( 'pmc_google_analytics_account' );
		}

		$account_id = strtoupper( $account_id );

		return $account_id;
	}

	/*
	 * @since 1.3.1.0 2013-10-23 Hau Vong
	 * @param $value
	 * @return null | bool
	 */
	public function validate_ga_disable_display_advertising( $value ) {
		if ( empty( $value ) ) {
			return null;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error( 'pmc_ga_disable_display_advertising', 'pmc_google_analytics', 'Only administrators can change the Google Analytics account.' );

			return get_option( 'pmc_ga_disable_display_advertising' );
		}

		$value = (bool) $value;

		return $value;
	}

	public function get_event_tracking_data_string( $options ) {

		if ( empty( $options ) ) {
			return '';
		}

		if ( is_array( $options ) ) {
			// we have a single one dimention array
			if ( empty( $options[0] ) ) {
				$options = array( $options );
			}

			$events = array();
			foreach ( $options as $option ) {

				$option = array_merge( $this->_event_tracking_default, $option );

				$event    = isset( $option['event'] ) ? $option['event'] : 'click';
				$category = isset( $option['category'] ) ? $option['category'] : '';
				$action   = isset( $option['action'] ) ? $option['action'] : '';
				$label    = isset( $option['label'] ) ? $option['label'] : 'click';

				if ( empty( $category ) || empty( $action ) ) {
					continue;
				}

				$events[] = implode( ',', array( $event, $category, $action, $label ) );
			}

			if ( empty( $events ) ) {
				return '';
			}

		} else {
			$events = array( $options );
		}

		return implode( '|', $events );

	} // function

	public function __call( $method, $args ) {
		// Legacy magic method, class is mostly untested.
		return $this->missing_method_handler( $method, $args ); // @codeCoverageIgnore

	}

	public static function __callStatic( $method, $args ) {
		// Legacy magic method, class is mostly untested.
		return static::get_instance()->missing_method_handler( $method, $args ); // @codeCoverageIgnore

	}

	public function missing_method_handler( $method, $args ) {
		if ( ! defined( 'WPCOM_IS_VIP_ENV' ) || false === WPCOM_IS_VIP_ENV ) {
			$classname = get_called_class();
			do_action( 'pmc_ga_ua_missing_method', $method, $args, $classname );

			/**
			 * Trigger an error. Return 0/false to bypass. Must be E_USER_* class.
			 * This exists because we cannot assume that devs will use the `pmc_ga_ua_missing_method` hook.
			 * Note that E_USER_ERROR will not be picked up by Debug Bar.
			 *
			 * @see http://php.net/manual/en/errorfunc.constants.php
			 *
			 * @version 2015-08-18 Corey Gilmore
			 *
			 */
			$error_type = apply_filters( 'pmc_ga_ua_missing_method_error_type', E_USER_WARNING, $method, $args, $classname );
			if ( ! empty( $error_type ) ) {
				$stack = debug_backtrace();
				$caller = $stack[2];
				$message = sprintf(
					'Invalid method %s::%s called from %s on line %d',
					$classname,
					$method,
					$caller['file'],
					$caller['line']
				);
				trigger_error( $message, $error_type );
			}
		}

		return do_action( 'pmc_ga_ua_missing_method_return_value', '', $method, $args, $classname );
	}

	/**
	 * Custom Google Analytics event tracking.
	 *
	 * @codeCoverageIgnore
	 *
	 * @uses pmc_ga_event_tracking
	 *
	 * @return void
	 */
	public function enqueue_event_tracking() {

		$is_production = \PMC::is_production();

		// default supported custom click event
		$events = [
			[
				'action'   => 'click',
				'selector' => 'a[custom-ga-track=click]',
				'custom'   => true,
			],
			[
				'action'   => 'click',
				'selector' => 'article a[href*="mktp.cc"]',
				'category' => 'affiliate_link',
				'custom'   => true,
				'url'      => true,
			],
		];

		$events = apply_filters( 'pmc_ga_event_tracking', $events );

		$script_extension = '.js';

		if ( $is_production ) {
			$script_extension = '.min.js';
		}

		// Allow event selectors to utilize some custom/pmc selectors
		// Use dev mode when not on production (use source vs min versions when present)
		pmc_js_libraries_enqueue_script( 'pmc-jquery-extensions', '', array(), '', '', true, ( ! $is_production ) );

		// Allow event selectors to utilize some custom/pmc selectors
		// Use dev mode when not on production (use source vs min versions when present)
		pmc_js_libraries_enqueue_script( 'jquery-inview', '', array(), '', '', true, ( ! $is_production ) );

		// The ga tracking script depends underscore js library
		if ( ! wp_script_is( 'underscore' ) ) {
			wp_enqueue_script( 'underscore' );
		}

		wp_enqueue_script(
			'pmc-ga-event-tracking',
			PMC_GAUA_URL . 'js/event-tracking' . $script_extension,
			array(
				'jquery',
				'underscore',
				'pmc-jquery-extensions',
				'jquery-inview',
			),
			self::INTERNAL_VERSION,
			true
		);

		if ( ! empty( $events ) && is_array( $events ) ) {
			$device = '[D]';
			if ( \PMC::is_mobile() ) {
				$device = '[M]';
			} else if ( \PMC::is_tablet() ) {
				$device = '[T]';
			}

			$payload = array(
				'events' => $events,
				'device' => $device,
			);

			wp_localize_script( 'pmc-ga-event-tracking', 'pmc_ga_event_tracking', $payload );
		}
	}

	/**
	 * Enqueue script to add GA analytics for OUTBRAIN.
	 * Enqueue script only if cheezcap settings is enabled.
	 *
	 * @return void
	 */
	public function enqueue_outbrain_event_tracking() {

		$outbrain_tracking_setting = \PMC_Cheezcap::get_instance()->get_option( 'pmc_ga_enable_outbrain_tracking' );

		if ( 'enabled' !== $outbrain_tracking_setting ) {
			return;
		}

		wp_enqueue_script(
			'pmc-ga-outbrain-event-tracking',
			sprintf( '%1$s/js/outbrain-event-tracking.min.js', untrailingslashit( PMC_GAUA_URL ) ),
			array(),
			self::INTERNAL_VERSION,
			true
		);
	}

	/**
	 *
	 * Render the custom ga click event attribute use for ga event tracking
	 *
	 * IMPORTANT: Do not call this function directly
	 * usage: do_action( 'pmc_do_render_custom_ga_tracking_attr', $ga_tracking );
	 *
	 * @param array $ga_tracking @see render_custom_ga_data_attr
	 */
	public function render_custom_ga_tracking_attr( $ga_tracking = [] ) : void {
		if ( $this->render_custom_ga_data_attr( (array) $ga_tracking ) ) {
			echo ' custom-ga-track="click"';
		}
	}

	/**
	 * Render the ga custom data attribute use by ga event tracking
	 *
	 * IMPORTANT: Do not call this function directly
	 * usage: do_action( 'pmc_do_render_custom_ga_data_attr', $ga_tracking );
	 *
	 * $ga_tracking = [
	 *  'details' => false, // if set to true, auto detect from a link text, set to string to customize
	 *  'id' => false, // if set to true, auto detect from ga mapping, otherwise auto to current post_id
	 *  'label' => '',
	 *  'location' => false, // if set to true, auto detect from canonical meta tag
	 *  'url' => false, // include target url in tracking event
	 *  'product' => [
	 *      'id' => '', // string id of product, can be product URL
	 *      'name' => '', // string of the product name/title
	 *      'brand' => '', // product's brand
	 *      'category' => '', /// product's category
	 *      'price' => '', // numeric string
	 *    ],
	 *  ];
	 *
	 * @param array $ga_tracking
	 */
	public function render_custom_ga_data_attr( $ga_tracking = [] ) : bool {
		if ( empty( $ga_tracking ) ) {
			return false;
		}

		// We need at to make sure at least one property is enabled before proceed
		$ga_tracking = $this->filter_empty( (array) $ga_tracking );
		if ( empty( $ga_tracking ) ) {
			return false;
		}

		// We need to make sure location & current post id are included

		if ( empty( $ga_tracking['post_id'] ) ) {
			$ga_tracking['id'] = get_the_ID();
		}

		if ( empty( $ga_tracking['location'] ) ) {
			$ga_tracking['location'] = true;
		}

		if ( ! empty( $ga_tracking['product'] ) ) {
			// Sanitize the product's price fields
			foreach ( [ 'price', 'original_price' ] as $key ) {
				if ( ! empty( $ga_tracking['product'][ $key ] ) ) {
					$ga_tracking['product'][ $key ] = trim( str_replace( '$', '', $ga_tracking['product'][ $key ] ) );
				}
			}
			$ga_tracking['product'] = apply_filters( 'pmc_ga_mapped_ec_product_field', $ga_tracking['product'] );
		}

		printf( 'custom-ga-data="%s"', esc_attr( wp_json_encode( $ga_tracking, JSON_UNESCAPED_SLASHES ) ) );

		return true;
	}

	/**
	 * Helper function to filter out all empty value from array recursively
	 * @param array $args
	 * @return array
	 */
	public function filter_empty( array $args ) : array {
		$result = [];
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) || is_object( $value ) ) {
				$value = $this->filter_empty( (array) $value );
			}
			if ( is_string( $value ) ) {
				$value = trim( $value );
			}
			if ( ! empty( $value ) ) {
				$result[ $key ] = $value;
			}
		}
		return $result;
	}

}

PMC_Google_Universal_Analytics::get_instance();

//EOF
