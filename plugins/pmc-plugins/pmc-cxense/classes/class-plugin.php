<?php

namespace PMC\Cxense;

use PMC\Global_Functions\Traits\Singleton;
use \PMC_Cheezcap;
use \CheezCapGroup;
use \CheezCapTextOption;

/**
 * This class is responsible for all initial setup and contains all hooks needed for sending data to Cxense.
 * @TODO: Clean up `get_meta_tags()` and possibly move the logic to own class.
 * @TODO: Better way to load modules where needed
 * @package pmc-cxense
 */
class Plugin {
	use Singleton;

	const CHEEZCAP_LABEL = 'Cxense';
	const CHEEZCAP_ID    = 'pmc-cxense';
	const SITE_ID        = 'pmc_cxense_site_id';
	const PAYWALL_ID     = 'pmc_cxense_paywall_id';
	const API_USER_NAME  = 'pmc_cxense_user_name';
	const API_KEY        = 'pmc_cxense_api_key';

	/**
	 * Class initialization.  Sets variables we may need to use more than once.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup actions for adding what Cxense needs in <head> and AMP pages.
	 */
	public function _setup_hooks(): void {
		add_filter( 'pmc_cheezcap_groups', [ $this, 'filter_pmc_cheezcap_groups' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 1 );
		add_action( 'wp_head', [ $this, 'action_pmc_tags_head' ], 1 );
		add_action( 'amp_post_template_head', [ $this, 'action_pmc_tags_head' ] );
		add_action( 'pmc_amp_content_before_header', [ $this, 'action_pmc_amp_tags_body' ] );
		add_action( 'pmc_tags_global_urlhashchanged_template', [ $this, 'action_send_pv_on_url_change' ] );
		add_filter( 'js_do_concat', [ $this, 'exclude_scripts_js_concat' ], 10, 2 );
		add_action( 'post_updated', [ $this, 'action_post_update_cxense_notify' ], 10, 3 );
		add_action( 'widgets_init', [ $this, 'register_widget' ] );
	}

	/**
	 * Enqueue scripts on the page
	 */
	public function enqueue_assets(): void {
		// Don't need to send page views or have modules for admin pages and feeds
		if ( is_admin() ) {
			return;
		}
		wp_enqueue_script( 'pmc_cxense_js', sprintf( '%s/assets/js/pmc-cxense.js', untrailingslashit( PMC_CXENSE_URI ) ), [], '1.9' );
		wp_localize_script( 'pmc_cxense_js', 'pmc_cxense_data', $this->get_pmc_cxense_data() );
	}

	/**
	 * Filter to add cheezcap group
	 *
	 * @param array $cheezcap_groups
	 * @return array
	 */
	public function filter_pmc_cheezcap_groups( $cheezcap_groups = [] ): array {

		$fields = [
			'cxense_site_id'      => [
				'label'       => 'Cxense Site ID',
				'description' => '',
				'id'          => self::SITE_ID,
				'useTextArea' => false,
			],
			'cxense_paywall_id'   => [
				'label'       => 'Cxense Paywall ID',
				'description' => 'Cxense paywall id that would be associated with cx-paywall div',
				'id'          => self::PAYWALL_ID,
				'useTextArea' => false,
			],
			'cxense_api_username' => [
				'label'       => 'Cxense Api Username',
				'description' => '',
				'id'          => self::API_USER_NAME,
				'useTextArea' => false,
			],
			'cxense_api_key'      => [
				'label'       => 'Cxense Api Key',
				'description' => '',
				'id'          => self::API_KEY,
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

		$cheezcap_groups[] = new \CheezCapGroup( self::CHEEZCAP_LABEL, self::CHEEZCAP_ID, $cheezcap_options );

		return $cheezcap_groups;
	}

	/**
	 * Notifies cxense bot to get updated post values.
	 *
	 * @param $post_id
	 * @param $post_after
	 * @param $post_before
	 */
	public function action_post_update_cxense_notify( $post_id, $post_after, $post_before ): void {

		$user_name  = \PMC_Cheezcap::get_instance()->get_option( self::API_USER_NAME );
		$api_key    = \PMC_Cheezcap::get_instance()->get_option( self::API_KEY );
		$cxense_api = new \PMC\Cxense\Api( $user_name, $api_key );
		$post_link  = get_post_permalink( $post_id );

		$cxense_api->get_data( 'profile/content/push', [ 'url' => $post_link ] );
	}

	/**
	 *  Function to send pageview on global url change.
	 */
	public function action_send_pv_on_url_change(): void {
		\PMC::render_template( PMC_CXENSE_DIR . '/templates/url-change-pv.php', [], true );
	}

	/**
	 * Helper function to gather data for rendering on the cxense-js-script template.
	 *
	 * @return array
	 */
	public function get_pmc_cxense_data(): array {
		$paywall = new Paywall();

		return [
			'modules'           => new Cxense_Modules( (array) apply_filters( 'pmc_cxense_modules', [] ) ),
			'site_id'           => $this->get_site_id(),
			'custom_parameters' => new Custom_Parameters( (array) apply_filters( 'pmc_cxense_custom_parameters', Cxense_Common_Data::get_instance()->get_default_custom_parameters() ) ),
			'page_location'     => (string) apply_filters( 'pmc_cxense_page_location', '' ),
			'paywall_module'    => $paywall,
		];
	}

	/**
	 * Render the Cxense meta tags in `wp_head` or `amp_post_template_head`.
	 *
	 * @throws \Exception
	 */
	public function action_pmc_tags_head(): void {
		\PMC::render_template(
			PMC_CXENSE_DIR . '/templates/header.php',
			[
				'meta_tags'    => $this->get_meta_tags(),
				'allowed_html' => [
					'meta' => [
						'name'           => [],
						'content'        => [],
						'data-separator' => [],
					],
				],
			],
			true
		);
	}

	/**
	 * Render the Cxense AMP analytics tag in `pmc_amp_content_before_header`.
	 *
	 * @throws \Exception
	 */
	public function action_pmc_amp_tags_body(): void {
		\PMC::render_template(
			PMC_CXENSE_DIR . '/templates/amp-body.php',
			[
				'site_id'           => $this->get_site_id(),
				'custom_parameters' => new Custom_Parameters( (array) apply_filters( 'pmc_cxense_custom_parameters', Cxense_Common_Data::get_instance()->get_default_custom_parameters() ), 'cp_' ),
			],
			true
		);
	}

	/**
	 * Gets all meta tags for the Cxense bot as a string to render in the head of all pages.
	 * Themes can override these values by using the `pmc_cxense_meta_tags` filter.
	 * @return string
	 */
	public function get_meta_tags(): string {
		$meta_tags = [];
		$tags      = apply_filters( 'pmc_cxense_meta_tags', Cxense_Common_Data::get_instance()->get_default_meta_tags() );

		if ( ! empty( $tags ) && is_array( $tags ) ) {
			foreach ( $tags as $name => $content ) {
				if ( is_array( $content ) ) {
					$meta_tags[] = sprintf( '<meta name="cXenseParse:%1$s" content="%2$s" data-separator="," />', esc_attr( $name ), esc_attr( implode( ',', $content ) ) );
				} else {
					$meta_tags[] = sprintf( '<meta name="cXenseParse:%1$s" content="%2$s" />', esc_attr( $name ), esc_attr( $content ) );
				}
			}

			return implode( PHP_EOL, $meta_tags );
		}

		return '';
	}

	/**
	 * Returns the site id.
	 *
	 * Themes should add site id for each brand using the below filter.
	 * If site id is empty, the javascript will prevent any data sending to Cxense,
	 * so no validation is being done at this point.
	 *
	 * @return string
	 */
	public function get_site_id(): string {
		$site_id = \PMC_Cheezcap::get_instance()->get_option( self::SITE_ID );
		return apply_filters( 'pmc_cxense_site_id', $site_id );
	}

	/**
	 * Do not concatenate cxense js
	 */
	public function exclude_scripts_js_concat( $do_concat, $handle ) {
		if ( 'pmc_cxense_js' === $handle ) {
			return false;
		}

		return $do_concat;
	}

	public function register_widget() {

		register_widget( 'PMC\Cxense\Widget' );
	}
}
