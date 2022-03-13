<?php

namespace PMC\Piano;

use CheezCapDropdownOption;
use ErrorException;
use PMC;
use PMC\Frontend_Components\Badges\Sponsored_Content;
use PMC\Global_Functions\Evergreen_Content;
use PMC\Global_Functions\Traits\Singleton;
use PMC\Post_Options\API as PostOptionsAPI;
use PMC\Piano\Licensee_Endpoint;
use PMC_Google_Universal_Analytics;
use PMC_Page_Meta;
use CheezCapTextOption;

class Plugin {

	use Singleton;

	public const FILTER_PIANO_CUSTOM_VARIABLES = 'pmc_piano_custom_variables';

	public const FILTER_PIANO_TAG = 'pmc_piano_tag';

	public const FILTER_PIANO_AUTHOR = 'pmc_piano_author';

	public const FILTER_PIANO_PIXEL_CONFIG = 'pmc_piano_pixel_data';

	public const CHEEZCAP_ID = 'pmc-piano';

	public const CHEEZCAP_LABEL = 'Piano';

	public const PIANO_APP_ID = 'piano_app_id';

	public const CXENSE_SITE_ID = 'piano_cxense_site_id';

	public const PERSISTANCE_QUERY_ID = 'piano_persistance_query_id';

	public const PIANO_API_TOKEN = 'piano_api_token';

	public const PIANO_ENVIRONMENT_URL = 'piano_environment_url';

	public const CXENSE_COMPAT_MODE = 'piano_cxense_compat_mode';

	/**
	 * Class initialization.
	 */
	protected function __construct() {
		$this->_setup_hooks();
		$this->_register_endpoints();
	}

	/**
	 * Setup actions for adding what Piano needs.
	 */
	public function _setup_hooks(): void {
		add_action( 'wp_head', [ $this, 'js_piano_customizations' ], 1 );
		add_filter( 'pmc_cheezcap_groups', [ $this, 'filter_pmc_cheezcap_groups' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 2 );
		add_filter( 'script_loader_tag', [ $this, 'filter_script_loader_tag' ], 10, 2 );
		add_filter( 'js_do_concat', [ $this, 'filter_wpcom_js_do_concat' ], 10, 2 );
	}

	/**
	 * Register REST endpoints.
	 */
	public function _register_endpoints(): void {
		// Initialize licensee endpoint
		PMC\Global_Functions\WP_REST_API\Manager::get_instance()->register_endpoint(
			Licensee_Endpoint::class
		);
	}

	/**
	 * Filter to add cheezcap group option.
	 *
	 * @param array $cheezcap_groups
	 *
	 * @return array
	 */
	public function filter_pmc_cheezcap_groups( $cheezcap_groups = [] ): array {

		// Cheezcap text options.
		$fields = [
			'piano_app_id'          => [
				'label'       => 'Application ID',
				'description' => '',
				'id'          => self::PIANO_APP_ID,
				'useTextArea' => false,
			],
			'cxense_site_id'        => [
				'label'       => 'Cxense Site ID',
				'description' => '',
				'id'          => self::CXENSE_SITE_ID,
				'useTextArea' => false,
			],
			'persistance_query_id'  => [
				'label'       => 'Persistance Query ID',
				'description' => '',
				'id'          => self::PERSISTANCE_QUERY_ID,
				'useTextArea' => false,
			],
			'piano_api_token'       => [
				'label'       => 'Piano Publisher API Token',
				'description' => '',
				'id'          => self::PIANO_API_TOKEN,
				'useTextArea' => false,
			],
			'piano_environment_url' => [
				'label'       => 'URL for the Piano Publisher API for this environment',
				'description' => '',
				'id'          => self::PIANO_ENVIRONMENT_URL,
				'useTextArea' => false,
			],
		];

		$cheezcap_options = [];

		foreach ( $fields as $field ) {
			$cheezcap_options[] = new CheezCapTextOption(
				$field['label'],
				$field['description'],
				$field['id'],
				'',
				$field['useTextArea']
			);
		}

		$cheezcap_options[] = new CheezCapDropdownOption(
			'Enable compatibility mode for Cxense',
			'Option to enable/disable compatibility mode for Cxense script',
			self::CXENSE_COMPAT_MODE,
			[ 'disable', 'enable' ],
			0, // default option index set to disabled
			[ 'Disable', 'Enable' ]
		);

		$cheezcap_groups[] = new \CheezCapGroup( self::CHEEZCAP_LABEL, self::CHEEZCAP_ID, $cheezcap_options );

		return $cheezcap_groups;
	}

	/**
	 * Return Piano script URL.
	 *
	 * @return string
	 */
	public function get_script_url(): string {
		$application_id = apply_filters( 'piano_application_id', '' );

		if ( empty( $application_id ) ) {
			return '';
		}

		if ( PMC::is_production() ) {
			return 'https://experience.tinypass.com/xbuilder/experience/load?aid=' . $application_id;
		}

		return 'https://sandbox.tinypass.com/xbuilder/experience/load?aid=' . $application_id;
	}

	/**
	 * Enqueue scripts on the page
	 */
	public function enqueue_assets(): void {
		// Don't need to send page views or have modules for admin pages and feeds
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_script( 'pmc_piano_tinypass_js', $this->get_script_url(), [], PMC_PIANO_VERSION );
		wp_enqueue_script( 'pmc_piano_js', sprintf( '%s/assets/build/js/pmc-piano.js', untrailingslashit( PMC_PIANO_URI ) ), [ 'pmc_piano_tinypass_js' ], PMC_PIANO_VERSION );
		wp_localize_script( 'pmc_piano_js', 'pmcPianoData', $this->get_pmc_piano_data() );

		wp_enqueue_script( 'pmc_piano_js_pixels', sprintf( '%s/assets/build/js/pmc-pixels.js', untrailingslashit( PMC_PIANO_URI ) ), [], PMC_PIANO_VERSION );
	}

	public function js_piano_customizations() {
		$piano_author = apply_filters( static::FILTER_PIANO_AUTHOR, $this->get_author() );
		$piano_author = is_array( $piano_author ) ? implode( ',', $piano_author ) : $piano_author;

		PMC::render_template(
			PMC_PIANO_ROOT . '/templates/piano-js-init.php',
			[
				'piano_custom_variables' => apply_filters( static::FILTER_PIANO_CUSTOM_VARIABLES, $this->get_custom_variables() ),
				'piano_tags'             => apply_filters( static::FILTER_PIANO_TAG, $this->get_tags() ),
				'piano_author'           => $piano_author,
			],
			true
		);
	}

	/**
	 * Filter function to add GDPR privacy cookie blocking.
	 *
	 * @param string $tag Enqueued script tag.
	 * @param string $handle Enqueued script handle.
	 *
	 * @return string
	 */
	public function filter_script_loader_tag( $tag, $handle ) {
		$blocker_atts = [
			'type'  => 'text/javascript',
			'class' => '',
		];

		if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
			$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
		}

		if ( 'pmc_piano_js_pixels' === $handle ) {
			if ( false === strpos( $tag, 'type' ) ) {
				return str_replace( '<script', sprintf( '<script type=\'%s\' class=\'%s\'', esc_attr( $blocker_atts['type'] ), esc_attr( $blocker_atts['class'] ) ), $tag );
			}

			return str_replace( '<script type=\'text/javascript\'', sprintf( '<script type=\'%s\' class=\'%s\'', esc_attr( $blocker_atts['type'] ), esc_attr( $blocker_atts['class'] ) ), $tag );
		}

		return $tag;
	}

	/**
	 * Filter for whether or not to concat js files
	 *
	 * @param bool $do_concat
	 * @param string $handle
	 *
	 * @return bool
	 */
	public function filter_wpcom_js_do_concat( $do_concat, $handle ): bool {
		// Tracking JS respects cookie blocking implementation, should not concatenate.
		$do_concat = ( 'pmc_piano_js_pixels' === $handle ) ? false : $do_concat;

		return $do_concat;
	}

	/**
	 * Helper function to gather data for rendering on the piano-js-script template.
	 *
	 * @return array
	 * @throws ErrorException
	 */
	public function get_pmc_piano_data(): array {
		return [
			'scriptUrl'       => $this->get_script_url(),
			'isProd'          => \PMC::is_production(),
			'canDebug'        => ! \PMC::is_production() || current_user_can( 'manage_options' ),
			'customVariables' => apply_filters( static::FILTER_PIANO_CUSTOM_VARIABLES, $this->get_custom_variables() ),
			'tags'            => apply_filters( static::FILTER_PIANO_TAG, $this->get_tags() ),
			'author'          => apply_filters( static::FILTER_PIANO_AUTHOR, $this->get_author() ),
			'trackingPixels'  => apply_filters( static::FILTER_PIANO_PIXEL_CONFIG, $this->get_pmc_piano_pixel_config() ),
		];
	}

	public function get_pmc_piano_pixel_config(): Pixel_Config {
		return new Pixel_Config();
	}

	/**
	 * Get content tags that we can use to segment within Piano.
	 *
	 * @see https://docs.piano.io/content-tracking/#custags
	 *
	 * @return mixed|string
	 */
	private function get_tags() {
		$result = PMC_Page_Meta::get_page_meta()['tag'] ?? [];

		if ( empty( $result ) ) {
			return [];
		}

		$result = is_array( $result ) ? $result : [ $result ];

		return array_filter( $result );
	}

	/**
	 * Custom variables passed from the frontend to Piano.
	 *
	 * Note: Variables returned will be sent to Piano with `tp.push(['setCustomVariable']`
	 *
	 * @see https://docs.piano.io/content-tracking/#cusvar
	 *
	 * @return array
	 * @throws ErrorException
	 */
	private function get_custom_variables(): array {

		/** @var array $pmc_meta */
		$pmc_meta = PMC_Page_Meta::get_page_meta();

		$parameters = [
			'pmc-page_type'        => $pmc_meta['page-type'],
			'pmc-primary-category' => $pmc_meta['primary-category'],
			'pmc-category'         => $pmc_meta['category'],
			'pmc-primary-vertical' => $pmc_meta['primary-vertical'],
			'pmc-vertical'         => $pmc_meta['vertical'],
			'pmc-wp-logged-in'     => is_user_logged_in() ? 'yes' : 'no',
		];

		if ( is_singular() ) {
			$parameters = array_merge( $parameters, $this->get_singular_meta() );
		}

		return $parameters;
	}

	/**
	 * Gets meta tags needed for singular pages.
	 *
	 * @return array
	 * @throws ErrorException
	 */
	private function get_singular_meta(): array {
		$article       = get_post();
		$ga_dimensions = PMC_Google_Universal_Analytics::get_instance()->get_mapped_dimensions();

		return [
			'pmc-page-subtype'    => $ga_dimensions['dimension2'] ?? '',
			'pmc-post_type'       => $article->post_type,
			'pmc-is_free'         => ( ( PostOptionsAPI::get_instance()->post( get_the_ID() )->has_option( Paid_Content::FREE_CONTENT_OPTION ) ) ? 'yes' : 'no' ),
			'pmc-always-paywall'  => ( ( PostOptionsAPI::get_instance()->post( get_the_ID() )->has_option( Paid_Content::ALWAYS_PAYWALL_CONTENT_OPTION ) ) ? 'yes' : 'no' ),
			'pmc-branded-content' => ( ( PostOptionsAPI::get_instance()->post( get_the_ID() )->has_option( Sponsored_Content::SLUG ) ) ? 'yes' : 'no' ),
			'pmc-evergreen'       => ( ( PostOptionsAPI::get_instance()->post( get_the_ID() )->has_option( Evergreen_Content::SLUG ) ) ? 'yes' : 'no' ),
		];
	}

	private function get_author() {
		return PMC_Page_Meta::get_page_meta()['author'] ?? null;
	}
}
