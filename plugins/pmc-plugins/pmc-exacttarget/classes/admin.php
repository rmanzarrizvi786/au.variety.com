<?php

namespace PMC\Exacttarget;

use PMC\Global_Functions\Traits\Singleton;

class Admin {

	use Singleton;

	/**
	 * Set up hooks
	 *
	 * @since 2019-05-27
	 * @version 2019-05-27 MJ Zorick PMCP-1290
	 *
	 * @return void
	 */
	protected function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'action_admin_enqueue_scripts' ], 12 );
		add_action( 'admin_menu', [ $this, 'action_admin_menu' ] );

		add_filter( 'pmc_global_cheezcap_options', [ $this, 'filter_pmc_global_cheezcap_options' ] );
	}

	/**
	 * Initiates Exacttarget Admin menu
	 *
	 * @param string $page post type
	 *
	 * @return void
	 */
	function action_admin_menu() : void {

		if ( \Exact_Target::is_active() ) {
			add_submenu_page(
				'exacttarget_setup',
				__( 'Recurring Newsletters', 'pmc-exacttarget-v2' ),
				__( 'Recurring Newsletters', 'pmc-exacttarget-v2' ),
				'publish_posts',
				'exacttarget_recurring_newsletters',
				function() {
					require PMC_EXACTTARGET_PATH . '/recurring-newsletters.php';
				}
			);
			add_submenu_page(
				'exacttarget_recurring_newsletters',
				__( 'Recurring Newsletter', 'pmc-exacttarget-v2' ),
				__( 'Recurring Newsletter', 'pmc-exacttarget-v2' ),
				'publish_posts',
				'exacttarget_recurring_newsletter',
				function() {
					require PMC_EXACTTARGET_PATH . '/recurring-newsletter.php';
				}
			);
			add_submenu_page(
				'exacttarget_setup',
				__( 'Breaking News Alerts', 'pmc-exacttarget-v2' ),
				__( 'Breaking News Alerts', 'pmc-exacttarget-v2' ),
				'publish_posts',
				'exacttarget_fast_newsletters',
				function() {
					require PMC_EXACTTARGET_PATH . '/fast-newsletters.php';
				}
			);
			add_submenu_page(
				'exacttarget_fast_newsletters',
				__( 'Breaking News Alert', 'pmc-exacttarget-v2' ),
				__( 'Breaking News Alert', 'pmc-exacttarget-v2' ),
				'publish_posts',
				'exacttarget_fast_newsletter',
				function() {
					require PMC_EXACTTARGET_PATH . '/fast-newsletter.php';
				}
			);
			add_submenu_page(
				'exacttarget_setup',
				__( 'Newsletter Settings', 'pmc-exacttarget-v2' ),
				__( 'Newsletter Settings', 'pmc-exacttarget-v2' ),
				'publish_posts',
				'exacttarget_newsletter_settings',
				function() {
					require PMC_EXACTTARGET_PATH . '/newsletter-settings.php';
				}
			);
			add_submenu_page(
				'exacttarget_setup',
				__( 'Newsletter Statuses', 'pmc-exacttarget-v2' ),
				__( 'Newsletter Statuses', 'pmc-exacttarget-v2' ),
				'publish_posts',
				'exacttarget_newsletter_statuses',
				function() {
					require PMC_EXACTTARGET_PATH . '/newsletter-statuses.php';
				}
			);

		}
	}

	/**
	 * Added Exacttarget Cheezcap Options
	 *
	 * @param array $cheezcap_options List of cheezcap options.
	 *
	 * @return array $cheezcap_options
	 */
	public function filter_pmc_global_cheezcap_options( array $cheezcap_options = [] ) : array {

		$cheezcap_options[] = new \CheezCapDropdownOption(
			__( 'Custom subject for Breaking news newsletter', 'pmc-exacttarget-v2' ),
			__( 'Turn on custom subject for Breaking news newsletter', 'pmc-exacttarget-v2' ),
			'pmc_exacttarget_breaking_news_subject',
			[ 'no', 'yes' ],
			0, // 1sts option => no by default
			[ 'No', 'Yes' ]
		);

		return $cheezcap_options;
	}

	/**
	 * Enqueue JS and CSS
	 *
	 * @param string $page admin hook name
	 *
	 * @return void
	 */
	public function action_admin_enqueue_scripts( $page ) : void {

		// load for any exacttarget page
		if ( 'post.php' !== $page && 'post-new.php' !== $page && 0 === strpos( $page, 'exacttarget_' ) ) {
			// jquery dependencies
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'thickbox' ); // TODO - is thickbox included in jquery or is it still needed here?

			// TODO - is jquery.jeditable.js necessary?
			wp_enqueue_script( 'jquery_jeditable_js', PMC_EXACTTARGET_PLUGIN_URL . '/js/jquery.jeditable.js', [ 'jquery' ], PMC_EXACTTARGET_VERSION );
			wp_enqueue_script(
				'mmc_nws_inline_edit_js',
				PMC_EXACTTARGET_PLUGIN_URL . '/js/mmc_newsletter_inline_edit.js',
				[
					'jquery',
					'jquery_jeditable_js',
					'mmc_newsletter_js',
				],
				PMC_EXACTTARGET_VERSION
			);
			wp_enqueue_script(
				'mmc_newsletter_js',
				PMC_EXACTTARGET_PLUGIN_URL . '/js/mmc_newsletter.js',
				[
					'jquery',
					'media-upload',
					'thickbox',
				],
				PMC_EXACTTARGET_VERSION
			);
			wp_enqueue_script(
				'mmc_upload',
				PMC_EXACTTARGET_PLUGIN_URL . '/js/custom_image_uploader.js',
				[
					'jquery',
					'media-upload',
					'thickbox',
				],
				PMC_EXACTTARGET_VERSION
			);
			wp_enqueue_script( 'mmcnws_shared_js', PMC_EXACTTARGET_PLUGIN_URL . '/js/shared_functions.js', [ 'jquery' ], PMC_EXACTTARGET_VERSION );
			wp_enqueue_script( 'sailthru-tag-autocomplete-js', PMC_EXACTTARGET_PLUGIN_URL . '/js/sailthru-tag-autocomplete.js', array( 'jquery-ui-autocomplete' ) );
			wp_localize_script(
				'sailthru-tag-autocomplete-js',
				'sailthru_admin_t_ac',
				[
					'nonce' => wp_create_nonce( 'sailthru_t_ac_nonce' ),
				]
			);
			// CSS
			wp_enqueue_style( 'sailthru-tag-autocomplete-css', PMC_EXACTTARGET_PLUGIN_URL . '/css/sailthru-tag-autocomplete.css', array( 'sailthru-jquery-ui-theme-smoothness' ) );
			wp_enqueue_style( 'MMC_newsletter_admin_css', PMC_EXACTTARGET_PLUGIN_URL . '/css/mmc_newsletter.css', false, PMC_EXACTTARGET_VERSION, 'all' );
			wp_enqueue_style( 'MMC_newsletter_postModule_css', PMC_EXACTTARGET_PLUGIN_URL . '/css/mmc_newsletter_postModule.css', false, PMC_EXACTTARGET_VERSION, 'all' );
			wp_enqueue_style( 'thickbox' );  // TODO - is thickbox styles needed here?
			//load jquery-ui css from Google CDN, WordPress doesn't have it bundled
			wp_enqueue_style( 'sailthru-jquery-ui-theme-smoothness', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.min.css', [] );
		}

		// load for any edit post or new post
		if ( 'post.php' === $page || 'post-new.php' === $page ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'load_newsletter_info', PMC_EXACTTARGET_PLUGIN_URL . '/js/load_newsletter_info.js', [ 'jquery' ], PMC_EXACTTARGET_VERSION );
			wp_enqueue_script( 'exacttarget_preventDoubleClick', PMC_EXACTTARGET_PLUGIN_URL . '/js/preventDoubleClick.js', [ 'jquery' ], PMC_EXACTTARGET_VERSION );
			wp_enqueue_script( 'mmcnws_shared_js', PMC_EXACTTARGET_PLUGIN_URL . '/js/shared_functions.js', [ 'jquery' ], PMC_EXACTTARGET_VERSION );

			// CSS
			wp_enqueue_style( 'MMC_newsletter_postModule_css', PMC_EXACTTARGET_PLUGIN_URL . '/css/mmc_newsletter_postModule.css', false, PMC_EXACTTARGET_VERSION, 'all' );

		}
	}

}

//EOF
