<?php

namespace PMC\Quantcast_CMP;

use PMC\Global_Functions\Traits\Singleton;

class Admin {
	use Singleton;
	private $version = '1.0';

	/**
	 * Set up hooks
	 *
	 * @since 2018-10-30
	 * @version 2018-10-30 Dan Berko PEP-1545
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_filter( 'pmc_cheezcap_groups', array( $this, 'filter_add_cmp_cheezcap' ) );
		add_action( 'init', array( $this, 'load_cmp_scripts' ), 12 );
		add_filter( 'wp_resource_hints', [ $this, 'preconnect_collector' ], 10, 2 );
		add_filter( 'body_class', array( $this, 'filter_add_cmp_waiting' ) );
	}

	/**
	 * Added a cheezcap to enable the plugin
	 *
	 * @param array $cheezcap_groups List of cheezcap options.
	 *
	 * @return array $cheezcap_groups
	 */
	public function filter_add_cmp_cheezcap( array $cheezcap_groups = array() ) {
		$cheezcap_groups[] = new \CheezCapGroup(
			wp_strip_all_tags(
				__(
					'Quantcast Consent Management',
					'pmc-quantcast-cmp'
				),
				true
			),
			'pmc-quantcast-cmp',
			array(
				// Enable/disable
				new \CheezCapDropdownOption(
					wp_strip_all_tags( __( 'Enable Consent Management Modal', 'pmc-quantcast-cmp' ), true ),
					wp_strip_all_tags(
						__( 'This option will enable the consent management modal js', 'pmc-quantcast-cmp' ),
						true
					),
					'pmc_quantcast_cmp',
					array( 'no', 'yes' ),
					0,
					array(
						wp_strip_all_tags( __( 'No', 'pmc-quantcast-cmp' ), true ),
						wp_strip_all_tags( __( 'Yes', 'pmc-quantcast-cmp' ), true ),
					)
				),
				new \CheezCapTextOption(
					wp_strip_all_tags( __( 'Publisher Name', 'pmc-quantcast-cmp' ), true ),
					wp_strip_all_tags( __( 'Publisher name as it should appear in modal', 'pmc-quantcast-cmp' ), true ),
					'pmc_quantcast_cmp_publisher',
					get_bloginfo( 'name' ) //default
				),
				new \CheezCapTextOption(
					wp_strip_all_tags( __( 'Publisher Logo', 'pmc-quantcast-cmp' ), true ),
					wp_strip_all_tags( __( 'Logo for modal', 'pmc-quantcast-cmp' ), true ),
					'pmc_quantcast_cmp_publisher_logo',
					''//default
				),
			)
		);

		return $cheezcap_groups;
	}

	public function filter_add_cmp_waiting( $classes ) {
		if ( $this->is_cmp_enabled() ) {
			$classes[] = 'waitingForCmp';
		}

		return $classes;
	}

	public function load_cmp_scripts() {
		// Ignoring coverage temporarily
		// @codeCoverageIgnoreStart
		if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
			if ( \PMC\Onetrust\Onetrust::get_instance()->is_onetrust_enabled() ) {
				return;
			}
		}
		// @codeCoverageIgnoreEnd

		if ( $this->is_cmp_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}

	/**
	 * Preconnect to Frisbee connector URL to speed up loading.
	 *
	 * Lighthouse regularly reports 200-300ms delays in critical path loading
	 * attributable to this endpoint.
	 *
	 * @param array  $urls     Configured resource hint URLs.
	 * @param string $relation Resource hint type.
	 * @return array
	 */
	public function preconnect_collector( array $urls, string $relation ): array {
		if ( ! $this->is_cmp_enabled() ) {
			return $urls;
		}

		if ( 'preconnect' === $relation ) {
			$urls['pmc-quantcast-cmp-collector'] = 'https://collector.sheknows.com';
		}

		return $urls;
	}

	public function is_cmp_enabled() {
		$cmp_opt = ( \PMC_Cheezcap::get_instance()->get_option( 'pmc_quantcast_cmp' ) );

		return 'yes' === $cmp_opt;
	}


	public function enqueue_scripts() {
		// localize the qc-cmp-init script and pass in the $cmp_init_params values
		$cmp_init_params = $this->get_cmp_init_values();
		wp_enqueue_script( 'qc-cmp-init', CMP_INIT_SRC, array(), $this->version, true );
		wp_localize_script( 'qc-cmp-init', 'cmp_init_params', $cmp_init_params );

		$script_src = ( \PMC::is_production() ) ? CMP_REPORT_SRC_MIN : CMP_REPORT_SRC;
		wp_enqueue_script(
			'pmc-cmp-report-js',
			$script_src,
			array(),
			$this->version,
			true
		);
	}

	//  This is largely the default parameters from Quantcast, with the name and logo coming from Cheezcap. If we change the options, we need to provide some defaults.
	public function get_cmp_init_values() {
		$publisher_name = \PMC_Cheezcap::get_instance()->get_option( 'pmc_quantcast_cmp_publisher' );
		$publisher_name = empty( $publisher_name ) ? 'PMC' : $publisher_name;
		$publisher_logo = \PMC_Cheezcap::get_instance()->get_option( 'pmc_quantcast_cmp_publisher_logo' );
		$publisher_logo = empty( $publisher_logo ) ? 'https://pmc.com/wp-content/uploads/2018/05/pmc_color.png' : $publisher_logo;

		$params = array(
			'Language'                             => 'en',
			'Initial Screen Reject Button Text'    => 'Deny All',
			'Initial Screen Accept Button Text'    => 'Accept and Move On',
			'Initial Screen Purpose Link Text'     => 'Manage My Consents',
			'Purpose Screen Header Title Text'     => 'Privacy settings',
			'Purpose Screen Body Text'             => 'You can set your consent preferences and determine how you want your data to be used based on the purposes below. You may set your preferences for us independently from those of third-party partners. Each purpose has a description so that you know how we and partners use your data.',
			'Vendor Screen Body Text'              => 'You can set consent preferences for each individual third-party company below. Expand each company list item to see what purposes they use data for to help make your choices. In some cases, companies may disclose that they use your data without asking for your consent, based on their legitimate interests. You can click on their privacy policies for more information and to opt out.',
			'Vendor Screen Accept All Button Text' => 'Accept all',
			'Vendor Screen Reject All Button Text' => 'Reject all',
			'Initial Screen Body Text'             => 'By clicking below, you accept our use of cookies and other online technologies to send you targeted ads, for social media, data analytics and to understand your use of the site.  To learn more about the cookies we use, <b><a href="https://pmc.com/privacy-policy/#cookies" target="_blank">click here</a></b>. <br> <br> If you wish to set your vendor preferences, please click Manage My Consents below.',
			'Initial Screen Body Text Option'      => 1,
			'Publisher Name'                       => $publisher_name,
			'Publisher Logo'                       => $publisher_logo,
			'Publisher Purpose IDs'                => array( 1, 2, 3, 4, 5 ),
			'Display UI'                           => 'inEU',
		);

		return wp_json_encode( $params );
	}
}

//EOF
