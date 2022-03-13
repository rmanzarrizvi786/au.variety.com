<?php
/*
 * @since ?
 * @version 2014-06-19 Hau Vong:
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Ads {

	use Singleton;

	const POST_TYPE = 'pmc-ad';
	const ads_to_fetch = 160;
	const cache_group = 'pmc_adm';
	const cache_life = 300;	//5 minutes

	/**
	 * @var string Posts per page.
	 */
	const POSTS_PER_PAGE = 40;

	private $_default_ad_conditionals = array(
		'is_home',
		'is_single',
		'is_category',
		'is_tag',
		'has_category',
		'has_tag',
		'is_tax',
		'is_search',
		'is_singular',
		'is_page',
	);

	/**
	 * List of providers.
	 *
	 * @var array
	 */
	protected $_providers = array();

	/**
	 * List of ad locations.
	 *
	 * @var array
	 */
	public $locations = array(
		'interstitial'              => 'Interstitial',
		'prestitial'                => 'Prestitial',
		'widget'                    => 'Widget',
		'gallery-interstitial'      => 'Gallery Interstitial',
		'dfp-prestitial'            => 'DFP Prestitial',
		'responsive-skin-ad'        => 'Responsive Skin Ad',
		'mid-article'               => 'Between Article Paragraphs',
		'facebook-instant-articles' => 'Facebook Instant Articles',
	);

	/**
	 * Current timezone or GMT Offset set in WordPress
	 */
	public $timezone;

	/**
	 * Fallback timezone when none is set in the site's General settings
	 *
	 * @var string
	 */
	public $default_timezone = 'UTC+0';

	public $ads = array();

	/**
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'init' ), 9 );
		add_action( 'wp_head', array( $this, 'ad_block_variable' ), 1 );
		add_action( 'wp_head', array( $this, 'render_ad_lighting_wrapper_scripts' ) );
		add_action( 'wp_footer', array( $this, 'ad_block_detector' ), 1 );
		add_action( 'wp_head', array( $this, 'load_ias_script_tag' ), 1 );//This needs to be loaded as soon as possible

		add_action( 'wp_head', array( $this, 'add_index_exchange_wrapper' ), 10 );

		add_action( 'wp_footer', [ $this, 'load_blockthrough_script_tag' ] );

		// this filter need to add before init fire so cheezcap can see the filter during init.
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'filter_pmc_global_cheezcap_options' ) );

		add_filter( 'pmc-adm-fetch-ads', array( $this, 'no_ads_on_this_post' ) );
		add_filter( 'pmc_inject_content_paragraphs', array( $this, 'filter_pmc_inject_content_paragraphs' ), 10 );
		add_filter( 'pmc_cheezcap_groups', array( $this, 'add_lazy_load_cheezcap_group' ) );
		add_filter( 'pmc_adm_gpt_prepare_ad_data', [ $this, 'add_blockthrough_placements_ids' ] );

	}

	/**
	 * CheezCap settings for Lazy Load Ads.
	 *
	 * @param array $groups
	 *
	 * @return array
	 */
	public function add_lazy_load_cheezcap_group( $groups = array() ) {

		if ( ! is_array( $groups ) ) {
			$groups = array();
		}

		$groups[] = new CheezCapGroup(
			'Ads',
			'pmc-lazy-load-ads',
			[

				new CheezCapDropdownOption(
					'Enable or Disable Lazy Load option for ads?',
					'',
					'pmc_enable_disable_lazy_load',
					array( 'disable', 'enable' ),
					0, // 0 index => disable
					array( 'Disable', 'Enable' )
				),
				new CheezCapDropdownOption(
					__( 'Enable Auto refreshing ads', 'pmc-adm' ),
					__( 'Ads will be auto refresh for every X seconds. Direct sold ads are excluded.', 'pmc-adm' ),
					'pmc_adm_auto_refresh_ads',
					array( 'disable', 'enable' ),
					0, // 0 index => disable
					array( 'Disable', 'Enable' )
				),
				new CheezCapDropdownOption(
					__( 'Ad Refresh Time limit', 'pmc-adm' ),
					__( 'Global Time limit to refresh ad unit.', 'pmc-adm' ),
					'pmc_adm_global_ad_refresh_time_limit',
					[ 45, 40, 35, 30, 25, 20, 15 ],
					3, // 3 index => 30
					[ 45, 40, 35, 30, 25, 20, 15 ]
				),
				new \CheezCapTextOption(
					__( 'IndexExchange wrapper tag', 'pmc-header-bidding' ),
					__( 'Enter the IndexExchange wrapper script tag to include on page', 'pmc-header-bidding' ),
					'pmc_hb_index_exchange_wrapper_tag',
					''
				),
				new CheezCapDropdownOption(
					__( 'Enable IAS PET js', 'pmc-adm' ),
					__( 'IAS PET.js integration.', 'pmc-adm' ),
					'pmc_adm_ias_script',
					array( 'disable', 'enable' ),
					0, // 0 index => disable
					array( 'Disable', 'Enable' )
				),
				new CheezCapDropdownOption(
					__( 'Enable Blockthrough', 'pmc-adm' ),
					__( 'Blockthrough - Ad recovery script.', 'pmc-adm' ),
					'pmc_adm_blockthrough_script',
					array( 'disable', 'enable' ),
					0, // 0 index => disable
					array( 'Disable', 'Enable' )
				),
				new CheezCapDropdownOption(
					__( 'Dont remove Contextual Player', 'pmc-adm' ),
					__( 'By enabling this will overwrite the direct sold rule and display the contextual player along with takeovers', 'pmc-adm' ),
					'pmc_adm_overwrite_cp',
					array( 'disable', 'enable' ),
					0, // 0 index => disable
					array( 'Disable', 'Enable' )
				),
				new CheezCapDropdownOption(
					__( 'Core Web Vital Optimize Cumulative Layout Shift', 'pmc-adm' ),
					__( 'Turn on/off Optimize Cumulative Layout Shift', 'pmc-adm' ),
					'pmc_optimize_cls',
					[ 'disable', 'maxsize', 'minsize' ],
					0, // 0 index => disable
					[
						__( 'Disable', 'pmc-adm' ),
						__( 'Reserve Maximum Size', 'pmc-adm' ),
						__( 'Reserve Minimum Size', 'pmc-adm' ),
					]
				),
			]
		);

		return $groups;
	}

	/**
	 * filter to add cheezcap option to turn on/off features
	 */
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {

		$cheezcap_options[] = new CheezCapTextOption(
			"Don't show ads on these posts",
			'Comma delimited post IDs, e.g.: 123,456,789',
			'pmc_adm_no_ads',
			null
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			'Ad Manager - Enable Referrer Targeting:',
			"When enabled, a page-level targeting 'referrer' key/value.",
			'pmc_enable_disable_page_level_referrer_targeting',
			array( 'disable', 'enable' ),
			0, // 0 index => disable
			array( 'Disable', 'Enable' )
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			'Ads.txt',
			"When enabled, Ads.txt will be active",
			'pmc_ads_txt_file',
			array( 'disable', 'enable' ),
			0, // 0 index => disable
			array( 'Disable', 'Enable' )
		);

		// For adding ad lighting wrappers through code rather than through DFP creative wrappers, READS-1314.
		$cheezcap_options[] = new CheezCapDropdownOption(
			__( 'PMC ad lighting wrapper', 'pmc-adm' ),
			__( 'Adds the ad lightning wrapper directly through code instead of through a DFP Creative wrapper', 'pmc-adm' ),
			'pmc_adm_ad_lighting_wrapper',
			array( 'disable', 'enable' ),
			0, // 0 index => disable
			array( __( 'Disable', 'pmc-adm' ), __( 'Enable', 'pmc-adm' ) )
		);

		return $cheezcap_options;

	}

	/**
	 * Set bait variable high up in the page, so we don't have to worry about it being undefined.
	 */
	public function ad_block_variable() {
		?>
		<script>
			window.pmc_is_adblocked = false;
		</script>
		<?php
	}

	/**
	 * Adds ad lighting wrapper scripts. READS-1314.
	 *
	 * @since 2018-07-12 Kelin Chauhan <kelin.chauhan@rtcamp.com>
	 *
	 */
	public function render_ad_lighting_wrapper_scripts() {

		if ( 'enable' !== PMC_Cheezcap::get_instance()->get_option( 'pmc_adm_ad_lighting_wrapper' ) ) {
			return;
		}

		PMC::render_template( PMC_ADM_DIR . '/templates/adlighting-scripts.php', array(), true );

	}

	/**
	 * Bait script for ad blockers to know if an ad blocker is enabled.
	 */
	public function ad_block_detector() {
		?>
		<div id="pmc-ad-bait" class="pub_300x250 pub_300x250m pub_728x90 text-ad textAd text_ad text_ads text-ads text-ad-links" style="width: 0 !important; height: 0 !important; position: fixed !important; left: -99999px !important;">ad</div>

		<script>
			if ( 'undefined' !== typeof jQuery ) {
				var $pmc_ad_bait = jQuery('#pmc-ad-bait');
				if ( $pmc_ad_bait.length ) {
					if ( 'block' !== $pmc_ad_bait.css('display') ) {
						window.pmc_is_adblocked = true;
					}
				}
			}
		</script>
		<?php
	}

	/**
	 * Inject mid-article ad into content after first paragraph on mobile.
	 *
	 * @param $paragraphs
	 *
	 * @return mixed
	 */
	public function filter_pmc_inject_content_paragraphs( $paragraphs ) {
		if ( $ads = pmc_adm_render_ads( 'mid-article', '', false ) ) {
			$paragraphs[1][] = $ads;
		}

		return $paragraphs;
	}

	/**
	 * Check if the current post is on a "no ads" blocklist. If so, force PMC_Ads::get_ads_to_render() to find no ads.
	 * @param array $ad_posts From PMC_Ads::fetch_ads()
	 * @return array $ad_posts
	 */
	public function no_ads_on_this_post( $ad_posts = array() ) {
		$no_ads_string = PMC_Cheezcap::get_instance()->get_option( 'pmc_adm_no_ads' );
		$no_ads_array = explode( ',', $no_ads_string );
		$no_ads_array = array_map( 'intval', $no_ads_array );
		if ( is_singular() && ( false !== array_search( get_queried_object_id(), $no_ads_array ) ) ) {
			return array();
		}
		return $ad_posts;
	}

	/**
	 * This function returns an array containing functions for use
	 * in rendering condition based ads
	 */
	protected function _get_condition_functions() {
		$functions = apply_filters( 'pmc_adm_conditional_tags', $this->_default_ad_conditionals );

		if ( empty( $functions ) || ! is_array( $functions ) ) {
			$functions = $this->_default_ad_conditionals;
		}

		$functions = array_filter( array_unique( $functions ) );

		//weed out non-existent functions
		foreach ( $functions as $key => $function ) {
			if( ! is_callable( $function ) ) {
				//no point in using this function
				unset( $functions[ $key ] );
			}
		}

		sort( $functions );		//just in case someone makes it an associative array - lose those associative indices

		return array_merge( array( '' ), $functions );
	}

	/**
	 * Initialize things?
	 */
	public function init() {
		$this->timezone = get_option( 'timezone_string' );
		$this->timezone = ( empty( $this->timezone ) ) ? get_option( 'gmt_offset' ) : $this->timezone;
		$this->timezone = empty( $this->timezone ) ? $this->default_timezone : $this->timezone;

		$this->setup_post_type();

		$this->setup_admin();

		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts
	 *
	 * @since ?
	 *
	 * @version 2016-04-18 Archana Mandhare PMCVIP-1161 - Enable sending Criteo js data for US location since non-US location is throwing js error.
	 */
	public function action_wp_enqueue_scripts() {
		if ( ! is_admin() ) {
			wp_enqueue_script( 'pmc-hooks' );

			$js_extension = '.js';

			if ( \PMC::is_production() ) {
				$js_extension = '.min.js';
			}

			// Define the core dependencies which the ADM loader relies on.
			$adm_loader_deps = array( 'jquery', 'pmc-hooks' );

			if ( 'enabled' === PMC_Cheezcap::get_instance()->get_option( 'pmc_enable_disable_lazy_load' ) ) {
				$adm_loader_deps[] = 'pmc-intersection-observer-polyfill';
			}

			// Enqueue Sourcebuster JS
			// Ignoring coverage for the launch - https://jira.pmcdev.io/browse/ROP-2172
			// @codeCoverageIgnoreStart
			$sbjs = true;
			if ( class_exists( '\PMC\Onetrust\Onetrust' ) && class_exists( '\PMC\Geo_Uniques\Plugin' ) ) {
				if (
					\PMC\Onetrust\Onetrust::get_instance()->is_onetrust_enabled() &&
					( 'eu' === \PMC\Geo_Uniques\Plugin::get_instance()->pmc_geo_get_region_code() || 'eu' === \PMC::filter_input( INPUT_GET, 'region' ) )
				) {
					$sbjs = false;
				}
			}
			// @codeCoverageIgnoreEnd
			if ( 'enable' === PMC_Cheezcap::get_instance()->get_option( 'pmc_enable_disable_page_level_referrer_targeting' ) && true === $sbjs ) {
				wp_enqueue_script( 'pmc-adm-sourcebuster', plugins_url( sprintf( 'js/sourcebuster/sourcebuster%s', $js_extension ), __FILE__ ), array(), 'v1.1.0' );
				$adm_loader_deps[] = 'pmc-adm-sourcebuster';
			}

			/**
			 * @since ?
			 * @version 2019-10-23 Jignesh Nakrani ROP-1951 Host Polyfill locally instead of loading from 3rd party domain.
			 *
			 * Ref: https://github.com/w3c/IntersectionObserver
			 */
			wp_enqueue_script( 'pmc-intersection-observer-polyfill', plugins_url( 'js/polyfill/intersection-observer.min.js', __FILE__ ), [], '0.7.0', false );

			// Enqueue ADM Loader JS
			wp_enqueue_script( 'pmc-adm-loader', plugins_url( sprintf( 'js/loader%s', $js_extension ), __FILE__ ), $adm_loader_deps, '5.8' );

			// Enqueue IAS PET JS
			if ( 'enable' === PMC_Cheezcap::get_instance()->get_option( 'pmc_adm_ias_script' ) ) {
				wp_enqueue_script( 'pmc-adm-ias', plugins_url( sprintf( 'js/ias%s', $js_extension ), __FILE__ ), $adm_loader_deps, '1.0' );
			}

			wp_enqueue_script( 'pmc-adm-contextual-player', plugins_url( sprintf( 'js/contextual-player%s', $js_extension ), __FILE__ ), $adm_loader_deps, '1.2', true );

			/*
			 * Do not add any other pmc ad related setting.  Please add config to pmc_adm_config instead.
			 */
			wp_localize_script( 'pmc-adm-loader', 'pmc_adm_config', array(

				'dfp_skin_main_content'      => apply_filters( 'pmc_adm_dfp_skin_main_content', array( 'main-wrapper' ) ),

				/*
				 * Allow global override of Lazy Load in Cheezcap.
				 */
				'lazy_load_override' => PMC_Cheezcap::get_instance()->get_option( 'pmc_enable_disable_lazy_load' ),

				/**
				 * Optionally include a 'referrer' page-level targeting key/value
				 */
				'page_level_referrer_targeting' => PMC_Cheezcap::get_instance()->get_option( 'pmc_enable_disable_page_level_referrer_targeting' ),

				/**
				 * Allow auto refreshing ads
				 */
				'auto_refresh'                  => PMC_Cheezcap::get_instance()->get_option( 'pmc_adm_auto_refresh_ads' ),

				/**
				 * Display Contextual player along with Direct sold ads
				 */
				'pmc_adm_overwrite_cp'          => PMC_Cheezcap::get_instance()->get_option( 'pmc_adm_overwrite_cp' ),

			) );


			// @TODO: should move to pmc_adm_config above and access from js as pmc_adm_config.header_bidder
			wp_localize_script( 'pmc-adm-loader' , 'pmc_header_bidder', array(
				'active' => apply_filters( 'pmc_header_bidder_active' , false ),
			) );

			wp_enqueue_style( 'pmc-adm-style', plugins_url( 'css/pmc-adm-style.css', __FILE__ ), [], PMC_ADM_VERSION );

			/*Note: Below inline css is taken out from css/pmc-adm-style.css file
			by doing this Source Point can apply these styles after they recover ads*/
			wp_add_inline_style( 'pmc-adm-style', 'div.admz, div.admz-sp { margin-left: auto; margin-right: auto; text-align: center; } #skin-ad-inject-container { display: none; }' );
		}
	}

	/**
	 * Create custom post type.
	 */
	public function setup_post_type() {
		register_post_type( self::POST_TYPE, array(
			'label'               => 'Ad Manager',
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => false,
			'rewrite'             => false
		) );
	}

	/**
	 * Setup the admin interface.
	 */
	public function setup_admin() {
		add_action( 'admin_menu' , function() {
			add_submenu_page('tools.php', 'PMC Ad Manager', 'PMC Ad Manager', 'pmc_manage_ads_cap', 'ad-manager', array( PMC_Ads::get_instance(), 'render_admin'));
		});

		add_action( 'admin_enqueue_scripts', array( $this, 'setup_assets' ) );
		add_action( 'wp_ajax_adm_view', array( $this, 'handle_ajax' ) );
		add_action( 'wp_ajax_adm_crud', array( $this, 'handle_form' ) );
	}

	/**
	 * Add JS and CSS for manager page only.
	 */
	public function setup_assets( $hook ) {
		if ( $hook !== 'tools_page_ad-manager' ) {
			return;
		}

		wp_enqueue_style( 'pmc-adm-style-admin', plugins_url( 'css/pmc-adm-style-admin.min.css', __FILE__ ), '1.1', true );
		wp_enqueue_script( 'pmc-adm-manager', plugins_url( 'js/manager.js', __FILE__ ), array( 'jquery' ), '1.0', true );
		wp_localize_script( 'pmc-adm-manager', 'PMC_ADM', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
		wp_localize_script( 'pmc-adm-manager', 'pmcadm_floating_preroll_location', array( 'floating-video-preroll-ad' ) );
		wp_localize_script( 'pmc-adm-manager', 'pmcadm_contextual_player_location', array( 'contextual-matching-player-ad', 'amp-contextual-matching-player-ad' ) );
	}

	/**
	 * Fetches ads by location and title
	 *
	 * @param string $location
	 * @param string $title
	 * @param string $provider
	 *
	 * @return array
	 */
	public function fetch_ads( $location = '', $title = '', $provider = '' ) {
		if ( empty( $location ) ) {
			return;
		}

		$meta_query = array(
			array(
				'key'   => '_ad_location',
				'value' => sanitize_title_with_dashes( strtolower( $location ) ),
			),
		);

		if ( ! empty( $title ) && is_string( $title ) ) {
			$meta_query[] = array(
				'key'   => '_ad_title',
				'value' => sanitize_title( $title ),
			);
		}

		if ( ! empty( $provider ) && is_string( $provider ) ) {
			$meta_query[] = array(
				'key'   => '_ad_provider',
				'value' => sanitize_title( $provider ),
			);
		}

		$numberposts = intval( apply_filters( 'pmc_adm_fetch_ads_count', 25 ) );
		$numberposts = min( $numberposts, 50 );

		$args = array(
			'numberposts'      => $numberposts,
			'post_type'        => self::POST_TYPE,
			'suppress_filters' => true,
			'meta_query'       => $meta_query,
		);

		$cache_key = 'fetch_ads-' . md5( serialize( $args ) );

		$ad_posts = wp_cache_get( $cache_key, self::cache_group );

		if ( empty( $ad_posts ) ) {
			//no ads in cache, lets get some
			$ad_posts = get_posts( $args );

			if ( empty( $ad_posts ) ) {
				$ad_posts = 'empty';
			}

			//and lets cache result
			wp_cache_set( $cache_key, $ad_posts, self::cache_group, self::cache_life );
		}

		if ( 'empty' === $ad_posts ) {
			return array();
		}

		return $ad_posts;
	}

	/**
	 * Determine whether an ad should be rendered or not
	 *
	 * This function takes in a post object for an ad and based on the
	 * time frame and conditions set for the ad it determines if that ad should
	 * be rendered or not. If the ad should be rendered then this function
	 * returns TRUE else FALSE.
	 *
	 * @since 2013-10-18 Amit Gupta
	 * @version 2013-12-19 Amit Gupta
	 * @version 2014-06-19 Hau Vong
	 *
	 * @param array $ad see function _post_to_ad
	 * @return bool|array $ad
	 */
	public function should_render_ad( $ad ) {
		if ( empty( $ad ) || ! is_array( $ad ) || empty( $ad['device'] ) ) {
			return false;
		}

		if ( !empty( $ad['status'] ) && 'Disable' === $ad['status'] ) {
			return false;
		}

		// Skip the ad unit rendering if the provider for the ad unit is not set or exist.
		// Should not fatal and stop the execution.
		if ( empty( $ad['provider'] ) || false === $this->get_provider( $ad['provider'] ) ) {
			return false;
		}

		$ad['device'] = $this->get_current_applicable_device( $ad['device'] );

		if ( $ad['device'] === false ) {
			return false;
		}

		if ( ! empty( $ad['start'] ) && ! empty( $ad['end'] ) ) {
			$now = PMC_TimeMachine::create( $this->timezone )->format_as( 'U' );
			$start_time = PMC_TimeMachine::create( $this->timezone )->from_time( 'Y-m-d H:i', $ad['start'] )->format_as( 'U' );
			$end_time = PMC_TimeMachine::create( $this->timezone )->from_time( 'Y-m-d H:i', $ad['end'] )->format_as( 'U' );

			if ( $now < $start_time || $now > $end_time ) {
				//not the time for this ad
				return false;
			}

			unset( $end_time, $start_time, $now );
		}

		if ( ! empty( $ad['ad_conditions'] ) && is_array( $ad['ad_conditions'] ) ) {

			$ad_conditions = $ad['ad_conditions'];

			$logical_operator = 'or';
			if ( ! empty( $ad['logical_operator'] ) ) {
				$logical_operator = $ad['logical_operator'];
			}

			if ( PMC_Ad_Conditions::get_instance()->is_true( $ad_conditions, $logical_operator ) ) {
				return apply_filters( 'pmc_adm_should_render_ad', $ad );
			}

			return false;
		}

		// no conditions set for this ad, should render it
		if ( empty( $ad['ad_conditions'] ) ) {
			return apply_filters( 'pmc_adm_should_render_ad', $ad );
		}

		// condition(s) not met, don't render ad
		return false;
	}

	/**
	 * Return the current device type if its one of the device types applicable to an ad
	 *
	 * This function checks the applicable device types for an ad and returns the device type which
	 * matches the current device type else FALSE.
	 *
	 * @since 2013-12-19 Amit Gupta
	 *
	 * @param array $devices	Array containing device types applicable for an ad
	 * @return bool|string
	 */
	public function get_current_applicable_device( $devices ) {

		foreach ( (array) $devices as $device ) {

			switch ( $device ) {
				case 'Desktop':
					if ( ! PMC::is_mobile() && ! PMC::is_tablet() ) {
						return 'Desktop';
					}

					break;
				case 'Mobile':
					if ( PMC::is_mobile() ) {
						return 'Mobile';
					}

					break;
				case 'Tablet':
					if ( PMC::is_tablet() ) {
						return 'Tablet';
					}

					break;
			}
		}

		return false;
	}

	/**
	 * Private function to convert post content into ad array
	 * @param $post object | array
	 * @return array | false
	 */
	private function _post_to_ad( $post ) {

		if ( empty( $post ) ) {
			return false;
		}

		$ad = false;

		if ( is_object( $post ) ) {

			$ad = json_decode( $post->post_content, true );

			if ( empty( $ad ) ) {
				return false;
			}

			$ad['ID'] = $post->ID;

		} else if ( is_array( $post ) && !empty( $post['post_content'] ) ) {

			$ad = json_decode( $post['post_content'] );

			if ( empty( $ad ) || !is_array( $ad ) ) {
				return false;
			}

			$ad['ID'] = $post['ID'];
		}

		return apply_filters( 'pmc_adm_post_to_ad', $ad, $post );

	}

	/**
	 * Return an array containing ads with $ad_title organized by device (for responsive layouts).
	 *
	 * - Ads are filtered down by conditionals and timeframes
	 * - Ads are organized by device and priority
	 * - Ads are rotated and displayed via JS
	 *
	 * @param string $ad_location
	 * @param string $ad_title
	 * @param string $provider
	 *
	 * @return mixed
	 */
	public function get_ads_to_render( $ad_location, $ad_title = '', $provider = '' ) {
		// Fetch the ads
		$ads_all = apply_filters( 'pmc-adm-fetch-ads', $this->fetch_ads( $ad_location, $ad_title, $provider ) );

		if ( empty( $ads_all ) ) {
			return;
		}

		$ads = array();

		foreach ( $ads_all as $idx => $post ) {

			// Filter ad by device, conditions and time frame
			$ad = $this->should_render_ad( $this->_post_to_ad( $post ) );

			if ( empty( $ad ) ) {
				continue;
			}

			$ads[]    = $ad;
		}

		if ( empty( $ads ) ) {
			return array();
		}

		// Organize by device and priority
		$clean = array();

		$ad_defaults = [
			'width'     => 0,
			'height'    => 0,
			'priority'  => 10,
			'slot-type' => '',
			'css-style' => '',
		];

		foreach ( $ads as $ad ) {

			// apply some default just in case
			$ad = array_merge( $ad_defaults, $ad );

			$size     = $ad['width'] . 'x' . $ad['height'];
			$priority = $ad['priority'];

			if ( !empty( $ad['slot-type'] ) ) {
				$size = $size . '-' . $ad['slot-type'];
			} else {
				$size = $size . '-normal';
			}

			if ( !isset( $clean[$size] ) ) {
				$clean[$size] = array();
			}

			// Make sure values don't overwrite
			while ( isset( $clean[$size][$priority] ) ) {
				$priority++;
			}

			$clean[$size][$priority] = $ad;
		}

		// Sort by priority
		foreach ( $clean as $size => $ads ) {
			ksort( $ads );
			$ads = array_values( $ads );

			$clean[$size] = $ads[0];
		}

		// Notes: We need normal ads render first before oop ads
		$normal = array();
		$oop = array();
		foreach( $clean as $key => $ad ) {
			if ( !empty( $ad['slot-type'] ) && $ad['slot-type'] == 'oop' ) {
				$oop[$key] = $ad;
			}
			else {
				$normal[$key] = $ad;
			}
		}

		return array_merge($normal, $oop);

	}

	/**
	 * Render ads with $ad_title organized by device (for responsive layouts).
	 *
	 * - Ads are filtered down by conditionals and timeframes
	 * - Ads are organized by device and priority
	 * - Ads are rotated and displayed via JS
	 *
	 * @param string $ad_location
	 * @param string $ad_title
	 * @param bool $echo
	 * @param string $provider
	 * @return string
	 */
	public function render_ads( $ad_location, $ad_title = '', $echo = true, $provider = '' ) {

		$all_ads         = [];
		$site_served_ads = [];

		// filter hooks to allow development plugin to do debug
		$html = apply_filters( 'pmc_pre_render_ads', '', $ad_location, $ad_title, $provider );
		$ads_to_render = $this->get_ads_to_render( $ad_location, $ad_title, $provider );

		if ( empty( $ads_to_render ) || ! is_array( $ads_to_render ) ) {
			if ( $echo ) {
				echo wp_kses_post( $html );
			}
			return $html;
		}

		foreach ( $ads_to_render as $size => $ad ) {
			if ( 'site-served' === $ad['provider'] ) {
				$site_served_ads[ $size ] = $ad;
			} else {
				$all_ads[ $size ] = $ad;
			}
		}

		if ( ! empty( $all_ads ) ) {
			$args = array(
				// Make ad type id unique use on div id
				'ad_type_id' => sanitize_title( $ad_location . ( ! empty( $ad_title ) ? '-' : '' ) . $ad_title ),
				'ad_types'   => $all_ads,
				'manager'    => $this
			);

			$html .= PMC::render_template( PMC_ADM_DIR . '/templates/ad-type.php', $args );
		}

		if ( ! empty( $site_served_ads ) ) {
			foreach ( $site_served_ads as $size => $ad ) {
				$html .= $this->get_provider( $ad['provider'] )->render_ad( $ad );
			}
		}

		unset( $args, $ads_to_render );

		if ( ! $echo ) {
			return $html;
		}

		echo $html;
	}

	/**
	 * Render the view template for ad management.
	 */
	public function render_admin() {

		$total_post_count = wp_count_posts( self::POST_TYPE );
		$total_post_count = ( ! empty( $total_post_count->publish ) && intval( $total_post_count->publish ) > 0 ) ? intval( $total_post_count->publish ) : 0;

		$total_page_count = ceil( $total_post_count / self::POSTS_PER_PAGE );

		$paged = \PMC::filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );
		$paged = ( ! empty( $paged ) && intval( $paged ) > 0 ) ? intval( $paged ) : 1;
		$paged = min( $paged, 20 );

		$args = array(
			'numberposts' => self::POSTS_PER_PAGE,
			'paged'       => $paged,
		);

		PMC::render_template( PMC_ADM_DIR . '/templates/admin.php', array(
			'ads'          => $this->get_ads_based_on_role( $args ),
			'providers'    => $this->get_providers_based_on_roles(),
			'manager'      => $this,
			'post_count'   => $total_post_count,
			'current_page' => $paged,
			'total_page'   => $total_page_count,
		), true );
	}

	/**
	 * Fetch the Ads based on the role
	 *
	 * @param  array $args To modify argument which will pass in get_posts().
	 *
	 * @return array
	 */
	public function get_ads_based_on_role( $args = array() ) {

		$ads = [];

		$current_user = wp_get_current_user();

		foreach ( (array) $current_user->roles as $role ) {

			// Administrator will have access to all ads
			if ( 'administrator' === $role ) {

				$ads = array_merge( $ads, $this->get_ads( false, '', '', $args ) );
				break;

			} else {

				$providers = $this->get_providers_based_on_roles();

				if ( ! empty( $providers ) && is_array( $providers ) ) {
					foreach ( $providers as $key => $provider ) {
						$ads = array_merge( $ads, $this->get_ads( false, '', $key, $args ) );
					}
				}

			}

		}

		return $ads;

	}

	/**
	 * Fetch providers based on role
	 */
	public function get_providers_based_on_roles() {

		$current_providers = [];

		$current_user = wp_get_current_user();

		foreach ( (array) $current_user->roles as $role ) {
			$providers = PMC_Ads_Role::get_instance()->get_providers_for_role( $role );
			$current_providers = array_merge( $current_providers, $providers );
		}

		return $current_providers;
	}

	/**
	 * Render the form for creating/updating ads.
	 *
	 * @param string provider_id
	 * @param int $post_id
	 */
	public function render_form( $provider_id, $post_id = null ) {

		// use default if provider id is empty
		if ( defined( 'DEFAULT_AD_PROVIDER' ) && empty( $provider_id ) ) {
			$provider_id = DEFAULT_AD_PROVIDER;
		}

		$provider = $this->get_provider( $provider_id );

		// if provider isn't valid
		if ( false === $provider ) {
			// something really wrong
			echo '<div>Error locating ad provider: '. esc_html( $provider_id ) . '</div>';

			// temporarily code for debugging
			if ( !empty( $_GET['debug'] ) ) {
				echo "<pre>";
				echo 'GET: ';
				print_r( $_GET );
				echo 'POST: ';
				print_r( $_POST );
				echo "</pre>";
			}

			return;
		}

		$template_vars = array(
			'ad'                  => $this->get_ad( $post_id ),
			'provider'            => $provider,
			'manager'             => $this,
			'provider_locations'  => $this->get_locations( $provider_id ),
			'condition_functions' => PMC_Ad_Conditions::get_instance()->get_condition_functions(),
		);

		$file = PMC_ADM_DIR . "/templates/provider-form.php";

		PMC::render_template( $file, $template_vars, true );

	}

	/**
	 * Handle non-CRUD AJAX calls.
	 */
	public function handle_ajax() {
		$post_id = isset( $_GET['id'] ) ? $_GET['id'] : null;
		$provider_id = isset( $_GET['provider'] ) ? $_GET['provider'] : '';

		if ( empty( $provider_id ) ) {
			$provider_id = isset( $_GET['provider_id'] ) ? $_GET['provider_id'] : '';
		}

		switch ( $_GET['method'] ) {
			case 'provider-form':
				$this->render_form( $provider_id, $post_id);
			break;
		}

		exit();
	}

	/**
	 * Handle AJAX form submission: create ad, update ad, etc.
	 */
	public function handle_form() {
		$success = true;
		$message = null;

		try {

			$query = array( 'post_status' => 'publish', 'post_type' => self::POST_TYPE );
			$method = PMC::filter_input( INPUT_POST, 'method', FILTER_SANITIZE_STRING );
			$ad_id = PMC::filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );
			$ad_title = PMC::filter_input( INPUT_POST, 'title', FILTER_SANITIZE_STRING );

			switch ( sanitize_title( $method ) ) {

				case 'edit':
					$query['ID'] = $ad_id;
					// fall-through

				case 'add':
					$query['post_title'] = $ad_title;

					// Save shared data
					$data                    = array();
					$data['provider']        = PMC::filter_input( INPUT_POST, 'provider', FILTER_SANITIZE_STRING );
					$data['status']          = PMC::filter_input( INPUT_POST, 'status', FILTER_SANITIZE_STRING );
					$data['priority']        = PMC::filter_input( INPUT_POST, 'priority', FILTER_SANITIZE_NUMBER_INT );
					$data['start']           = null;
					$data['end']             = null;
					$data['css-class']       = PMC::filter_input( INPUT_POST, 'css-class', FILTER_SANITIZE_STRING );
					$data['is-ad-rotatable'] = PMC::filter_input( INPUT_POST, 'is-ad-rotatable', FILTER_SANITIZE_STRING );
					$data['ad-group']        = PMC::filter_input( INPUT_POST, 'ad-group', FILTER_SANITIZE_STRING );
					$data['width']           = PMC::filter_input( INPUT_POST, 'width', FILTER_SANITIZE_NUMBER_INT );
					$data['height']          = PMC::filter_input( INPUT_POST, 'height', FILTER_SANITIZE_NUMBER_INT );

					$data['duration']        = PMC::filter_input( INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT );
					$data['timegap']         = PMC::filter_input( INPUT_POST, 'timegap', FILTER_SANITIZE_NUMBER_INT );

					// @codeCoverageIgnoreStart
					$data['media-id']      = PMC::filter_input( INPUT_POST, 'media-id', FILTER_SANITIZE_STRING );
					$data['player-id']     = PMC::filter_input( INPUT_POST, 'player-id', FILTER_SANITIZE_STRING );
					$data['cap-frequency'] = PMC::filter_input( INPUT_POST, 'cap-frequency', FILTER_SANITIZE_NUMBER_INT );

					$data['ad-refresh-time']                = PMC::filter_input( INPUT_POST, 'ad-refresh-time', FILTER_SANITIZE_NUMBER_INT );
					$data['contextual-player-title']        = PMC::filter_input( INPUT_POST, 'contextual-player-title', FILTER_SANITIZE_STRING );
					$data['playlist-id']                    = PMC::filter_input( INPUT_POST, 'playlist-id', FILTER_SANITIZE_STRING );
					$data['contextual-player-id']           = PMC::filter_input( INPUT_POST, 'contextual-player-id', FILTER_SANITIZE_STRING );
					$data['contextual-player-position']     = PMC::filter_input( INPUT_POST, 'contextual-player-position', FILTER_SANITIZE_STRING );
					$data['contextual-enable-shelf-widget'] = PMC::filter_input( INPUT_POST, 'contextual-enable-shelf-widget', FILTER_SANITIZE_STRING );
					// @codeCoverageIgnoreEnd

					$ad_devices = filter_input( INPUT_POST, 'device', FILTER_DEFAULT, FILTER_FORCE_ARRAY );
					$data['device'] = ( ! empty( $ad_devices ) ) ? array_map( 'sanitize_text_field', $ad_devices ) : array( 'Desktop' );
					sort( $data['device'] );

					$data['title']    = sanitize_title( $query['post_title'] );	//for saving in post meta
					$data['location'] = sanitize_title_with_dashes( strtolower( PMC::filter_input( INPUT_POST, 'location', FILTER_SANITIZE_STRING ) ) );
					$data['provider'] = sanitize_title_with_dashes( strtolower( PMC::filter_input( INPUT_POST, 'provider', FILTER_SANITIZE_STRING ) ) );

					$data['is_lazy_load'] = sanitize_title( strtolower( PMC::filter_input( INPUT_POST, 'lazy-load', FILTER_SANITIZE_STRING ) ) );
					$data['adunit-order'] = PMC::filter_input( INPUT_POST, 'adunit-order', FILTER_SANITIZE_NUMBER_INT );

					$data['logical_operator'] = 'or';
					$ad_logical_operators = PMC::filter_input( INPUT_POST, 'pmc-adm-condition-logical-operator', FILTER_SANITIZE_STRING );
					if ( ! empty( $ad_logical_operators ) && in_array( $ad_logical_operators, [ 'or', 'and' ], true ) ) {
						$data['logical_operator'] = $ad_logical_operators;
					}

					//time magic
					$start = PMC::filter_input( INPUT_POST, 'start', FILTER_SANITIZE_STRING );
					$end = PMC::filter_input( INPUT_POST, 'end', FILTER_SANITIZE_STRING );

					if ( $start == date( 'Y-m-d H:i', strtotime( $start ) ) && $end == date( 'Y-m-d H:i', strtotime( $end ) ) ) {
						$data['start'] = $start;
						$data['end'] = $end;
					}

					// Save provider data
					foreach ( $this->get_provider( $data['provider'] )->get_fields() as $field => $title ) {
						$ad_field = PMC::filter_input( INPUT_POST, $field, FILTER_SANITIZE_STRING );
						$data[ $field ] = ( ! empty( $ad_field ) ) ? $ad_field : '';
					}

					$ad_condition_json = json_decode( wp_unslash( $_POST['pmc_ad_condition_json'] ), true );
					$json_error = json_last_error();
					if ( is_array( $ad_condition_json ) && JSON_ERROR_NONE === $json_error ) {
						$data['ad_conditions'] = $ad_condition_json;
					} else {
						$ad_conditions = json_decode( $ad_condition_json, true );
						$json_error    = json_last_error();
						if ( JSON_ERROR_NONE === $json_error ) {
							$data['ad_conditions'] = $ad_conditions;
						} else {
							$data['ad_conditions'] = [];
						}
					}

					//save targeting key/value pairs
					$ad_targeting_data = PMC::filter_input( INPUT_POST, 'targeting_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

					if ( ! empty( $ad_targeting_data['key'] ) ) {

						$count          = count( $ad_targeting_data['key'] );
						$targeting_data = array();

						for ( $i = 0; $i < $count; $i ++ ) {
							$key   = ( ! empty( $ad_targeting_data['key'][ $i ] ) ) ? sanitize_text_field( $ad_targeting_data['key'][ $i ] ) : '';
							$value = ( ! empty( $ad_targeting_data['value'][ $i ] ) ) ? sanitize_text_field( $ad_targeting_data['value'][ $i ] ) : '';

							if ( empty( $key ) || empty( $value ) ) {
								continue;
							}

							$targeting_data[] = array(
								'key'   => $key,
								'value' => $value
							);

							unset( $key, $value );
						}

						$data['targeting_data'] = $targeting_data;
					}

					// Add to query
					$query['post_content_filtered'] = implode( '|', $data['device'] );
					$query['post_content']          = json_encode($data);
					$query['post_excerpt']          = implode( '|', $data['device'] ) . '|' . $data['width'] . 'x' . $data['height'];
					$query['menu_order']            = $data['priority'];

					$this->save_post( $query, $data );
					break;

				case 'delete':

					$ad_ids = PMC::filter_input( INPUT_POST, 'post_ids', FILTER_SANITIZE_STRING );

					if ( ! empty( $ad_ids ) ) {

						$post_ids = explode( ",", $ad_ids );

						foreach ( $post_ids as $post_id ) {

							$post_id = intval( $post_id );
							if ( $post_id ) {
								$this->delete_post( $post_id );
							}

						}

					} else {
						$ad_id = PMC::filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );
						$this->delete_post( $ad_id );
					}
					break;

			}
		} catch (Exception $e) {

			$success = false;
			$message = $e->getMessage();

		}

		// Output response
		echo wp_json_encode(array(
			'success' => $success,
			'message' => $message
		));

		exit();

	}

	/**
	 * Insert or update the ad in the posts table.
	 * @param $query
	 * @param $data
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function save_post( $query, $data ) {

		if ( empty( $query['ID'] ) ) {
			$log = array();
			$response = wp_insert_post( $query, true );
		} else {
			$log = $this->get_last_modified_log( $query['ID'], true );
			$response = wp_update_post( $query, true );
		}

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		} else {
			update_post_meta( $response, '_ad_title', $data['title'] );
			update_post_meta( $response, '_ad_location', $data['location'] );
			update_post_meta( $response, '_ad_provider', $data['provider'] );
			update_post_meta( $response, '_ad_data', $data );
			update_post_meta( $response, '_ad_last_modified_log', $log );
		}

		return true;
	}

	/**
	 * Delete the post by ID.
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public function delete_post( $post_id ) {
		if ( $ad = $this->get_ad( $post_id ) ) {
			wp_delete_post( $ad->ID, true );
		}

		return true;
	}

	/**
	 * Return an array containing a list of last 10 user IDs (with timestamps) who modified the ad config
	 *
	 * @param int $post_id
	 * @return array
	 * @since 2014-02-21 Amit Gupta
	 */
	public function get_last_modified_log( $post_id, $to_save = false ) {
		$post_id = intval( $post_id );

		if ( $post_id < 1 ) {
			return array();
		}

		$log = get_post_meta( $post_id, '_ad_last_modified_log', true );

		if ( empty( $log ) || ! is_array( $log ) ) {
			$log = array();
			$ad = $this->get_ad( $post_id );
			$last_modified_by = get_post_meta( $post_id, '_ad_modified_by', true );

			if ( ! empty( $ad ) && ! empty( $last_modified_by ) ) {
				$last_modified_time = PMC_TimeMachine::create( $this->timezone )->from_time( 'Y-m-d H:i:s', $ad->post_modified )->format_as( 'U' );
				$log[ $last_modified_time ] = $last_modified_by;
			}
		}

		if ( $to_save !== true ) {
			return $log;
		}

		$log[ PMC_TimeMachine::create( $this->timezone )->format_as( 'U' ) ] = get_current_user_id();
		ksort( $log );
		$log = array_slice( $log, -10, 10, true );	//just keep last 10 at most

		//delete old meta key, not needed anymore
		if ( ! empty( $last_modified_by ) ) {
			delete_post_meta( $post_id, '_ad_modified_by' );
		}

		return $log;
	}

	/**
	 * Add a provider into the system.
	 *
	 * @param PMC_Ad_Provider $provider
	 * @return PMC_Ads
	 */
	public function add_provider(PMC_Ad_Provider $provider) {
		$this->_providers[$provider->get_id()] = $provider;

		return $this;
	}

	/**
	 * Add ad locations into the system.
	 * eg. PMC_Ads::get_instance()->add_locations( array(
	 *            'slug1' => 'ad location 1',
	 *            'slug2' => 'ad location 2',
	 *          ) );
	 * ref: https://confluence.pmcdev.io/display/pmcdocs/PMC-Adm
	 *
	 * @param associated array $locations
	 *
	 * @return PMC_Ads
	 */
	public function add_locations( array $locations ) {

		$locations = array_merge( $this->locations, $locations );
		$locations = apply_filters( 'pmc_adm_locations', $locations );

		$this->locations = $this->standardize_location_structure( $locations );

		ksort( $this->locations );

		return $this;
	}

	/**
	 * Get an ad by ID.
	 *
	 * @param int $post_id | WP_Post object
	 * @return WP_Post | null
	 */
	public function get_ad( $post_id ) {
		if ( ! $post_id ) {
			return null;
		}

		$post = get_post( $post_id );
		if( $post ) {
			$post->post_content = json_decode( $post->post_content, true );
			if ( empty( $post->post_content['status'] ) ) {
				$post->post_content['status'] = 'Active';
			}
			return $post;
		}

		return null;
	}

	/**
	 * Return all posts with the type of "pmc-ad" and unserialize their data.
	 *
	 * @param  bool   $cache Whether from cache or not.
	 * @param  string $location For which location.
	 * @param  string $provider Provider.
	 * @param  array  $args To modify arugment which will pass in get_posts().
	 *
	 * @return array
	 */
	public function get_ads( $cache = false, $location = '', $provider = '', array $args = [] ) {

		$default_args = array(
			'numberposts'      => self::ads_to_fetch,
			'post_type'        => self::POST_TYPE,
			'orderby'          => 'post_excerpt',
			'order'            => 'ASC',
			'suppress_filters' => true,
			'cache_results'    => ( true === $cache ),
		);

		$args = wp_parse_args( $args, $default_args );

		// if location(s) passed as param then filter ads for defined location(s).
		if ( ! empty( $location ) ) {
			if ( is_array( $location ) ) {
				$location = array_unique( array_map( 'sanitize_title_with_dashes', array_map( 'strtolower', $location ) ) );
			} else {
				$location = sanitize_title_with_dashes( strtolower( $location ) );
			}

			$meta_query = array(
				'key' => '_ad_location',
				'value' => $location,
			);

			if ( is_array( $location ) ) {
				$meta_query['compare'] = 'IN';
			}

			$args['meta_query'] = array( $meta_query );

		} elseif ( ! empty( $provider ) ) {

			if ( is_array( $provider ) ) {
				$provider = array_unique( array_map( 'sanitize_title_with_dashes', array_map( 'strtolower', $provider ) ) );
			} else {
				$provider = sanitize_title_with_dashes( strtolower( $provider ) );
			}

			$meta_query = array(
				'key' => '_ad_provider',
				'value' => $provider,
			);

			if ( is_array( $provider ) ) {
				$meta_query['compare'] = 'IN';
			}

			$args['meta_query'] = array( $meta_query );
		}

		if ( $cache === true ) {
			$cache_key = 'get_ads-' . md5( serialize( $args ) );

			$posts = wp_cache_get( $cache_key, self::cache_group );

			if ( $posts !== false && is_array( $posts ) ) {
				return $posts;
			}

			unset( $posts );
		}

		$posts = get_posts( $args );

		if ( $posts ) {
			foreach ( $posts as $post ) {
				$post->post_content = json_decode( $post->post_content, true );
				if ( empty( $post->post_content['status'] ) ) {
					$post->post_content['status'] = 'Active';
				}
				if ( ! empty( $post->post_content['provider'] ) ) {
					update_post_meta( $post->ID, '_ad_provider', $post->post_content['provider'] );
				}
			}

			// do custom sorting: provider, location, post_excerpt, post_title
			usort( $posts, function( $a, $b ) {

				$a_str = ! empty( $a->post_content['provider'] ) ? $a->post_content['provider'] : '';
				$b_str = ! empty( $b->post_content['provider'] ) ? $b->post_content['provider'] : '';

				$value = strcmp( $a_str, $b_str );

				unset( $a_str );
				unset( $b_str );

				if( 0 === $value ) {

					$a_str = ! empty( $a->post_content['location'] ) ? $a->post_content['location'] : '';
					$b_str = ! empty( $b->post_content['location'] ) ? $b->post_content['location'] : '';

					$value = strcmp( $a_str, $b_str );

					unset( $a_str );
					unset( $b_str );

					if ( 0 === $value ) {
						$value = strcmp( $a->post_excerpt, $b->post_excerpt );

						if ( 0 === $value ) {
							$value = strcmp( $a->post_title, $b->post_title );
						}

					}

				}

				return $value;
			} ); // usort

			if ( $cache === true ) {
				//lets cache these ad(s)
				wp_cache_set( $cache_key, $posts, self::cache_group, self::cache_life );
			}
		}

		return $posts;
	}

	/**
	 * Return a single provider.
	 *
	 * @param string $key
	 *
	 * @return PMC_Ad_Provider|bool if found, otherwise false
	 */
	public function get_provider( $key ) {
		return $this->_providers[ $key ] ?? false;
	}

	/**
	 * Return a list of providers.
	 *
	 * @return array
	 */
	public function get_providers() {
		return $this->_providers;
	}

	/**
	 * get all locations for the provider. Get only key-value pair of location in the below format
	 *
	 * $locations = array(
	 *	'leaderboard-ad'          => 'Leaderboard Ad',
	 *	'banner-ad'               => 'Banner Ad',
	 *	'homepage-mid-river-ad'   => 'HomePage Mid River',
	 *	'banner-ad'               => 'Banner Ad',
	 * );
	 *
	 * @param string provider
	 *
	 * @return array
	 */
	public function get_locations( $provider = '' ) {

		$provider_locations = [];

		if ( defined( 'DEFAULT_AD_PROVIDER' ) && empty( $provider ) ) {
			$provider = DEFAULT_AD_PROVIDER;
		}

		$locations = apply_filters( 'pmc_adm_locations', $this->locations );

		$locations = $this->standardize_location_structure( $locations );

		foreach ( $locations as $key => $location ) {

			$location_key = sanitize_title_with_dashes( $key );

			if ( is_array( $location ) && is_string( $location_key ) ) {

				if ( array_key_exists( 'providers', $location ) && in_array( $provider, $location['providers'], true ) ) {

					if ( array_key_exists( 'title', $location ) ) {

						$provider_locations[ $location_key ] = sanitize_text_field( $location['title'] );

					} else {

						$provider_locations[ $location_key ] = sanitize_text_field( $key );

					}
				}
			} elseif ( DEFAULT_AD_PROVIDER === $provider ) {

				$provider_locations[ $location_key ] = sanitize_text_field( $location );

			}
		}

		return $provider_locations;
	}

	/**
	 * Create a standard uniform locations array
	 * The incoming locations array could be in any format as below
	 *
	 * $locations = array(
	 * 	'leaderboard-ad'         => __( 'Leaderboard Ad'),
	 *	'banner-ad'              => array(
	 * 		'title'       => __( 'Banner Ad'),
	 * 		'providers'   => array( 'site-served', 'google-publisher' ),
	 * 	 ),
	 * 	'homepage-mid-river-ad'  => array(
	 * 		'title'       => __( 'HomePage Mid River'),
	 * 	 ),
	 * 'banner-ad'               => array(
	 * 		'title'       => __( 'Banner Ad'),
	 * 		'providers'   => array( 'site-served' ),
	 * 	 ),
	 * );
	 *
	 * If there are no providers specified for a location then google-publisher is the default provider
	 *
	 * The standard array should always look like
	 *
	 * $locations = array(
	 * 	'banner-ad'              => array(
	 * 		'title'       => __( 'Banner Ad'),
	 * 		'providers'   => array( 'site-served', 'google-publisher' ),
	 * 	 ),
	 * 	'leaderboard-ad'              => array(
	 * 		'title'       => __( 'Leaderboard ad'),
	 * 		'providers'   => array( 'google-publisher' ),
	 * 	 ),
	 * 'homepage-mid-river-ad'   => array(
	 * 		'title'       => __( 'Banner Ad'),
	 * 		'providers'   => array( 'google-publisher' ),
	 * 	 ),
	 * );
	 *
	 * The key is the location slug and value should be an array containing title as string having key 'title'
	 * and provider as an array of providers having 'providers' as key
	 *
	 * @param array $locations
	 *
	 * @return array
	 */
	public function standardize_location_structure( array $locations = array() ) {

		$clean_locations = [];

		if ( empty( $locations ) ) {
			return [];
		}

		foreach ( $locations as $key => $location ) {

			$clean_provider_locations = [];

			if ( empty( $key ) || ! is_string( $key ) ) {
				// Continue with processing other locations and ignore this location
				// We don't want to throw error but continue processing all locations and create the locations array
				continue;
			}

			$sanitized_location_key = sanitize_title_with_dashes( $key );

			if ( is_string( $location ) ) {

				$clean_provider_locations['title']     = sanitize_text_field( $location );
				$clean_provider_locations['providers'] = [ DEFAULT_AD_PROVIDER ];

			} elseif ( is_array( $location ) ) {

				foreach ( $location as $location_key => $location_data ) {

					$sanitized_key = sanitize_title_with_dashes( $location_key );

					if ( is_string( $sanitized_key ) ) {

						if ( is_string( $location_data ) ) {

							$clean_provider_locations[ $sanitized_key ] = sanitize_text_field( $location_data );

						} elseif ( is_array( $location_data ) && ( count( $location_data ) === count( $location_data, COUNT_RECURSIVE ) ) ) {

							$clean_provider_locations[ $sanitized_key ] = array_map( 'sanitize_text_field', $location_data );
						}
					}
				}
			}

			if ( ! empty( $clean_provider_locations ) ) {
				$clean_locations[ $sanitized_location_key ] = $clean_provider_locations;
			}

		}

		return $clean_locations;

	}

	/**
	 * Get the property of the ad for the admin provider form
	 *
	 * @param $key string
	 * @param $ad WP_Post object | null
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get_ad_property( $key, $ad, $default = '' ) {

		if ( empty( $ad ) ) {
			return $default;
		} else {

			if ( is_a( $ad, 'WP_Post' ) ) {
				$var = ( ! empty( $ad->post_content[ $key ] ) ) ? $ad->post_content[ $key ] : $default;
				return $var;
			} else {
				return $default;
			}

		}
	}

	/**
	* Get the property of the ad for the admin provider form
	*
	*/
	public function add_index_exchange_wrapper() {

		$wrapper_tag = \PMC_Cheezcap::get_instance()->get_option( 'pmc_hb_index_exchange_wrapper_tag' );

		if ( ! empty( $wrapper_tag ) ) {

			$ix_template = PMC_ADM_DIR . '/templates/index-wrapper-tag.php';

			$url = sprintf(
				'https://js-sec.indexww.com/ht/p/182698-%s.js',
				$wrapper_tag
			);

			\PMC::render_template( $ix_template, [ 'url' => $url ], true );

		}
	}

	/**
	 * Add IAS script tag in header
	 */
	public function load_ias_script_tag() {

		if ( 'enable' === PMC_Cheezcap::get_instance()->get_option( 'pmc_adm_ias_script' ) ) {

			PMC::render_template( PMC_ADM_DIR . '/templates/ias-script.php', array(), true );
		}
	}

	/**
	 * Add blockthrough script tag in header
	 */
	public function load_blockthrough_script_tag() {

		if ( 'enable' === PMC_Cheezcap::get_instance()->get_option( 'pmc_adm_blockthrough_script' ) ) {

			PMC::render_template( PMC_ADM_DIR . '/templates/blockthrough-script.php', [], true );
		}
	}

	/**
	 * Add blockthrough placement ids to ad config.
	 *
	 * @param $ad array Ad configuration
	 *
	 * @return array Ad configuration
	 */
	public function add_blockthrough_placements_ids( $ad ) {

		//Example filter 'pmc_adm_blockthrough_placement_ids' output
		/**
		 * $blockthrough_placements = [
		 *      'desktop' => [
		 *        '970x250' => '5d9d0fc80a-238',
		 *        '728x90'  => '5d9d0fc80a-238',
		 *        '300x250' => '5d9d0fd774-238',
		 *        '300x600' => '5d9d0fd774-238',
		 *    ],
		 *    'mobile' => [
		 *        '320x50'  => '5d9d0fe184-238',
		 *        '320x250' => '5d9d0fe184-238',
		 *    ],
		 * ];
		 */

		if ( 'enable' === PMC_Cheezcap::get_instance()->get_option( 'pmc_adm_blockthrough_script' ) ) {

			$blockthrough_placements = apply_filters( 'pmc_adm_blockthrough_placement_ids', [] );
			$gpt_provider            = $this->get_provider( 'google-publisher' );

			if ( ! empty( $ad ) && ! empty( $ad['ad-width'] ) && false !== $gpt_provider ) {

				$device   = \PMC::is_mobile() ? 'mobile' : 'desktop';
				$ad_sizes = $gpt_provider->parse_ad_widths( $ad['ad-width'] );

				if ( is_array( $ad_sizes ) ) {

					foreach ( $ad_sizes as $ad_size ) {

						$ad_size_key = $ad_size[0] . 'x' . $ad_size[1];

						if (
							is_array( $blockthrough_placements )
							&& ! empty( $blockthrough_placements[ $device ] )
							&& is_array( $blockthrough_placements[ $device ] )
							&& array_key_exists( $ad_size_key, $blockthrough_placements[ $device ] )
						) {

							$ad['blockthrough_placement_id'] = $blockthrough_placements[ $device ][ $ad_size_key ];

						}
					}
				}
			}
		}

		return $ad;
	}

}

//EOF
