<?php
namespace PMC\Partner_Scroll;
use PMC\Global_Functions\Traits\Singleton;
use PMC_Cheezcap;

/**
 * Main plugin class.
 */
class Plugin {

	use Singleton;

	/**
	 * Maintain flag of if outbrain script is loaded or not.
	 *
	 * @var bool
	 */
	protected static $_is_outbrain_enqueued = false;

	const SCROLL_CONTROL = 'amp_pmc_partner_scroll_control';

	const SCROLL_JWPLAYER_ID = 'amp_pmc_partner_scroll_jwplayer_id';

	/**
	 * Flag to determine whether outbrain is required to load.
	 *
	 * @var bool
	 */
	protected static $_should_load_outbrain = false;

	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Add callbacks for hooks.
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		// Need to hook this function early to make sure scroll tag option is available when a theme filters the available tags.
		add_filter( \PMC\Tags\Tags::TAGS_FILTER, [ $this, 'filter_tag_options' ], 9 );
		add_filter( 'script_loader_tag', [ $this, 'replace_outbrain_js' ], 10, 3 );
		add_filter( 'pmc_global_cheezcap_options', [ $this, 'filter_pmc_global_cheezcap_options' ] );
		add_filter( 'pmc_google_amp_ad_slot_attributes', [ $this, 'filter_pmc_google_amp_ad_slot_attributes' ] );

		/**
		 * Actions
		 */
		// Need to hook this late to make sure it fires after all the scripts are enqueued.
		add_action( 'wp_footer', [ $this, 'load_outbrain_js' ], 9999 );
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
		add_action( 'amp_post_template_head', [ $this, 'wp_enqueue_scripts' ] );
		add_action( 'amp_post_template_head', [ $this, 'action_amp_post_template_head' ] );

	}

	/**
	 * Filter tag options to include scroll tag.
	 *
	 * @param array $options Options.
	 *
	 * @return array
	 */
	public function filter_tag_options( $options ) {

		// Tests are already written for this line but pipeline says otherwise.
		$options['scroll'] = [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Scroll',
			'positions'   => [ 'footer' ],
			'slug'        => 'partner-scroll',
			'template'    => PMC_PARTNER_SCROLL_PLUGIN_DIR . '/templates/scroll-javascript.php',
			'values'      => [],
		];

		return $options;
	}

	/**
	 * Remove Outbrain JS if enqueued so we can load it via javascript,
	 * this would enable us to load outbrain only for non-scroll users.
	 *
	 * @param string $tag    Tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @param string $src    The script's source URL.
	 *
	 * @return string
	 */
	public function replace_outbrain_js( $tag, $handle, $src ) { // phpcs:ignore

		if ( false !== strpos( $src, 'widgets.outbrain.com/outbrain.js' ) ) {
			static::$_should_load_outbrain = true;
			return '';
		}

		return $tag;
	}

	/**
	 * Load outbrain script with scroll users check.
	 */
	public function load_outbrain_js() {

		// Load outbrain script only if required and not already loaded.
		if ( false === static::$_should_load_outbrain || true === static::$_is_outbrain_enqueued ) {
			return;
		}

		// The script is loaded via php because it needs to be rendered only if an outbrain js is enqueued.
		\PMC::render_template( sprintf( '%s/templates/outbrain-widget-js.php', PMC_PARTNER_SCROLL_PLUGIN_DIR ), [], true );

		// Set flag so that script won't enqueue multiple times.
		static::$_is_outbrain_enqueued = true;
	}

	/**
	 * Adds a configuration option to Theme Settings.
	 *
	 * @param  array $cheezcap_options
	 *
	 * @return array
	 */
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = [] ) {

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags( __( 'Enable Scroll Ad Blocking', 'pmc-partner-scroll' ), true ),
			wp_strip_all_tags( __( 'All ads will be disabled for Scroll user if "Yes" is selected.', 'pmc-partner-scroll' ), true ),
			self::SCROLL_CONTROL,
			[ 'no', 'yes' ],
			0, // first option => No.
			[ wp_strip_all_tags( __( 'No', 'pmc-partner-scroll' ), true ), wp_strip_all_tags( __( 'Yes', 'pmc-partner-scroll' ), true ) ]
		);

		$cheezcap_options[] = new \CheezCapTextOption(
			wp_strip_all_tags( __( 'Scroll JWPlayer ID', 'pmc-partner-scroll' ), true ),
			wp_strip_all_tags( __( 'Enter an ID of a JWPlayer without ads, this is currently being used on AMP pages ', 'pmc-partner-scroll' ), true ),
			self::SCROLL_JWPLAYER_ID,
			''
		);

		return $cheezcap_options;
	}

	/**
	 * Configure scroll module.
	 */
	public function action_amp_post_template_head() {

		if ( is_single() && $this->is_scroll_enabled() ) {
			echo '<script id="amp-access" type="application/json">{"vendor": "scroll", "namespace": "scroll"}</script>';
		}
	}

	/**
	 * Configure scroll module.
	 */
	public function filter_pmc_google_amp_ad_slot_attributes( $attributes = [] ) {

		if ( $this->is_scroll_enabled() ) {
			$attributes['amp-access'] = 'NOT scroll.scroll';
		}

		return $attributes;
	}

	/**
	 * Load required scripts & styles for scroll users.
	 *
	 */
	public function wp_enqueue_scripts() {

		if ( ! $this->is_scroll_enabled() ) {
			return;
		}

		// Load amp-access and amp-access-scroll if Scroll Ad Blocking is enabled from dashboard.
		if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {

			wp_enqueue_script( 'amp-access-scroll', 'https://cdn.ampproject.org/v0/amp-access-scroll-0.1.js', false );

		} else {
			// Load scroll scripts for non-AMP pages.
			wp_enqueue_script(
				'pmc-scroll-scripts',
				sprintf( '%sassets/js/scroll-scripts.js', PMC_PARTNER_SCROLL_PLUGIN_URL ),
				[],
				PMC_PARTNER_SCROLL_VERSION,
				true
			);

			// Load styling only for non-AMP pages.
			wp_enqueue_style(
				'pmc-partner-scroll-css',
				sprintf( '%sassets/css/scroll-style.css', PMC_PARTNER_SCROLL_PLUGIN_URL ),
				[],
				PMC_PARTNER_SCROLL_VERSION
			);
		}
	}

	/**
	 * Helper function for determining if Scroll AD blocking is enabled from Dashoboard.
	 *
	 * @return bool
	 */
	public function is_scroll_enabled() {
		return ( 'yes' === strtolower( PMC_Cheezcap::get_instance()->get_option( self::SCROLL_CONTROL ) ) );
	}

	/**
	 * Helper function get scroll jwplayer id.
	 *
	 * @return bool
	 */
	public function scroll_jwplayer_id() {
		return ( PMC_Cheezcap::get_instance()->get_option( self::SCROLL_JWPLAYER_ID ) );
	}

}
