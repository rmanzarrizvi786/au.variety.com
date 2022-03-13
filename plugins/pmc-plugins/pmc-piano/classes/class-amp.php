<?php

namespace PMC\Piano;

use PMC;
use PMC\Global_Functions\Traits\Singleton;

/**
 * This class is responsible for Piano AMP integration.
 *
 * Class Amp
 * @package PMC\Piano
 */
class Amp {

	use Singleton;

	public const AMP_LOGIN_ENDPOINT = 'pmc-amp-login';

	public const AMP_LOGOUT_ENDPOINT = 'pmc-amp-logout';

	public const AMP_SUBSCRIPTION_ENDPOINT = 'subscribe';

	public function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks(): void {
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );                                          // Register endpoints.
		add_filter( 'request', [ $this, 'filter_set_piano_request' ] );                               // Setting endpoints.
		add_filter( 'amp_post_template_data', [ $this, 'filter_add_amp_component_scripts' ] );        // Add amp-subscription script to head.
		add_filter( 'pmc_post_amp_content', [ $this, 'filter_wrap_amp_content' ] );                   // Update markup for amp pages.
		add_action( 'amp_post_template_head', [ $this, 'action_add_amp_subscription_config' ] );      // Add subscription config json to head.
		add_action( 'template_include', [ $this, 'action_add_amp_login_template' ] );                 // Add login template for Piano amp integration.
		add_action( 'wp_footer', [ $this, 'action_add_piano_amp_logout_script' ] );                   // Add piano logout script.
		add_filter( 'wp_nav_menu_items', [ $this, 'filter_add_amp_menu_item' ], 10, 2 );              // Adding custom elements to amp sidebar.
	}

	/**
	 * Register endpoints for piano amp request.
	 */
	public function add_rewrite_rules() {
		add_rewrite_endpoint( self::AMP_LOGIN_ENDPOINT, EP_PERMALINK );
		add_rewrite_endpoint( self::AMP_LOGOUT_ENDPOINT, EP_PERMALINK );
	}

	/**
	 * Sets amp login endpoint forcefully true.
	 *
	 * - Any url having ?<endpoint> should be valid.
	 * - By default value for <endpoint> param would be empty unless explicitly defined any value.
	 *
	 * Eg:
	 *  - <site>?pmc-amp-login=1
	 *  - <site>?pmc-amp-login=true
	 *  - <site>?pmc-amp-login
	 *
	 * - Following function would set value to true if <endpoint> query param is set in url.
	 *
	 * @param array $query_var
	 * @return array
	 */
	public function filter_set_piano_request( $query_var = [] ) : array {

		if ( isset( $query_var[ self::AMP_LOGIN_ENDPOINT ] ) ) {
			$query_var[ self::AMP_LOGIN_ENDPOINT ] = true;
		}

		if ( isset( $query_var[ self::AMP_LOGOUT_ENDPOINT ] ) ) {
			$query_var[ self::AMP_LOGOUT_ENDPOINT ] = true;
		}

		return $query_var;

	}

	/**
	 * Return content wrapped in <section subscriptions-section="content"> tag.
	 *
	 * This funnction wraps content around `<section subscriptions-section="content">` tag.
	 * AMP runtime uses this wrapper element to show/hide premium sections based on the authorization/entitlements response.
	 *
	 * Refrence doc
	 * - https://amp.dev/documentation/components/amp-subscriptions/#subscriptions-section
	 *
	 * @param string $content
	 * @return string
	 */
	public function filter_wrap_amp_content( $content = '' ) : string {

		$content = sprintf( '<section subscriptions-section="content">%s</section>', $content );

		return $content;
	}

	/**
	 * This function adds piano amp logout script to all the regular pages.
	 *
	 * @throws \Exception
	 */
	public function action_add_piano_amp_logout_script() {

		if ( get_query_var( self::AMP_LOGOUT_ENDPOINT ) ) {
			\PMC::render_template(
				PMC_PIANO_ROOT . '/templates/amp/amp-piano-logout.php',
				[],
				true
			);
		}
	}

	/**
	 * Returns amp login template on hitting amp-login endpoint.
	 *
	 * @param $template
	 * @return mixed|string
	 */
	function action_add_amp_login_template( $template ) {

		if ( get_query_var( self::AMP_LOGIN_ENDPOINT ) ) {
			return PMC_PIANO_ROOT . '/templates/amp/amp-piano-login.php';
		}

		return $template;

	}

	/**
	 * Return Piano AMP authorization URL.
	 *
	 * AMP subscriptions component uses this url to send data to piano.
	 * Data contains application id, tags, custom parameter, author details and amp specific string literals.
	 *
	 * Reference doc
	 * - https://amp.dev/documentation/components/amp-subscriptions/#authorization-endpoint
	 *
	 * @return string
	 */
	public function get_authorization_url(): string {

		$application_id = apply_filters( 'piano_application_id', '' );

		if ( empty( $application_id ) ) {
			return '';
		}

		$amp_subscription_url = 'https://sandbox.tinypass.com/xbuilder/experience/executeAmpSubscriptions';

		// Change amp subscription url for production application.
		if ( PMC::is_production() ) {
			$amp_subscription_url = 'https://experience.tinypass.com/xbuilder/experience/executeAmpSubscriptions';
		}

		/**
		 * Default parameters required for amp subscription module.
		 * Refrence url:
		 * - https://docs.piano.io/amp-experiences/#AMPinitializesub
		 * - https://amp.dev/documentation/components/amp-subscriptions/#configuration
		 * - https://amp.dev/documentation/components/amp-subscriptions/#url-variables
		 */
		$amp_url_params = [
			'protocol_version' => 1,
			'aid'              => $application_id,     // Piano application id.
			'reader_id'        => 'READER_ID',
			'url'              => 'SOURCE_URL',
			'referer'          => 'DOCUMENT_REFERRER',
			'_'                => 'RANDOM',
		];

		/**
		 * Set custom variable as parameter for authorization url.
		 */
		$piano_data   = Plugin::get_instance()->get_pmc_piano_data();
		$piano_params = [
			'content_author'   => implode( ', ', $piano_data['author'] ),
			'tags'             => implode( ', ', $piano_data['tags'] ),
			'custom_variables' => wp_json_encode( $piano_data['customVariables'] ),
		];

		$amp_url_params = array_merge( $amp_url_params, $piano_params );

		return $amp_subscription_url . '?' . http_build_query( $amp_url_params, '', '&' );
	}

	/**
	 * Adds AMP subscriptions component.
	 * https://amp.dev/documentation/components/amp-subscriptions/
	 *
	 * @param array $data
	 * @return array
	 */
	public function filter_add_amp_component_scripts( $data = [] ): array {

		$data['amp_component_scripts']['amp-subscriptions'] = 'https://cdn.ampproject.org/v0/amp-subscriptions-0.1.js';

		return $data;
	}

	/**
	 * Renders Subscription component config.
	 *
	 * @throws \Exception
	 */
	public function action_add_amp_subscription_config(): void {

		\PMC::render_template(
			PMC_PIANO_ROOT . '/templates/amp/amp-subscription-config.php',
			[
				'authorization_url' => $this->get_authorization_url(),
				'login_url'         => get_site_url() . '?' . self::AMP_LOGIN_ENDPOINT,
				'subscription_url'  => get_site_url() . '/' . self::AMP_SUBSCRIPTION_ENDPOINT,
				'logout_url'        => get_site_url() . '?' . self::AMP_LOGOUT_ENDPOINT,
			],
			true
		);
	}

	/**
	 * Adds extra element to amp sidebar.
	 *
	 * Reference doc
	 *  - https://amp.dev/documentation/components/amp-subscriptions/#subscriptions-display
	 *
	 * @param $items
	 * @param $args
	 * @return mixed|string
	 */
	public function filter_add_amp_menu_item( $items, $args ) {

		if ( is_single() && 'amp_side_menu' === $args->theme_location ) {
			$items .= '<li subscriptions-display="data.loggedIn" subscriptions-action="logout" subscriptions-service="local"><a>Logout</a></li>';
		}

		return $items;
	}

}
