<?php

namespace PMC\Subscription;

use \CheezCapTextOption;
use PMC\Global_Functions\Traits\Singleton;
use PMC\Structured_Data\Article_Data;

/**
 * This class is responsible for
 * - setup cheezcap setting
 */
class Plugin {
	use Singleton;

	const PAID_CONTENT_SELECTOR = '.pmc-paywall';

	protected function __construct() {
		Article_Data::get_instance()->activate_on_all_post_types(); // activate for all post type

		add_filter( 'pmc_cheezcap_groups', [ $this, 'filter_pmc_cheezcap_groups' ] );

		// We need the action init to run at priority 15 > Cheezcap init at 11 in theme.
		// Any cheezcap option dependencies in action_init must run after cheezcap register finished.
		add_action( 'init', [ $this, 'action_init' ], 15 );
		add_action( 'pmc_pre_wp_head', [ $this, 'action_pmc_pre_header' ] );
	}

	public function action_init() {
		if ( is_admin() ) {
			// All admin related action/filter should add here
			return;
		}

		// Make this late priority so all content is wrapped in div tags.
		add_filter( 'the_content', [ $this, 'adjust_paid_content' ], 19 );

		add_filter( Article_Data::FILTER_DATA, [ $this, 'filter_pmc_plugin_structured_data_article_data' ] );
	}

	/**
	 * Wrap paywall content in div tag with class of paid_content_selector.
	 *
	 * @param  string $content
	 *
	 * @return string
	 */
	public function adjust_paid_content( $content ) {
		if ( (bool) pmc_paywall_article_access_required() ) {
			$content = sprintf(
				'<div class="%s">%s</div>',
				esc_attr( ltrim( self::PAID_CONTENT_SELECTOR, '.' ) ),
				$content
			);
		}

		return $content;
	}

	/**
	 * Get filtered settings.
	 *
	 * @param array $metadata
	 * @return array
	 */
	public function filter_pmc_plugin_structured_data_article_data( $metadata = [] ) {
		$post_id         = get_queried_object_id();
		$is_article_free = ! (bool) pmc_paywall_article_access_required( $post_id );

		// VIP: this function returns a tiny set of data. We need to do some temporarily debug
		// @codeCoverageIgnoreStart
		$result_with_post_id = pmc_paywall_article_access_required( $post_id );
		$result_with_post_id = is_object( $result_with_post_id ) ? get_class( $result_with_post_id ) : $result_with_post_id;

		$result_without_post_id = pmc_paywall_article_access_required();
		$result_without_post_id = is_object( $result_without_post_id ) ? get_class( $result_without_post_id ) : $result_without_post_id;

		echo '<!-- DEBUG structured_data_article_data V1 -->'; // @codingStandardsIgnoreLine
		echo '<!-- $postId = ' . $post_id . ' -->'; // @codingStandardsIgnoreLine
		echo '<!-- pmc_paywall_article_access_required( $post_id ) = ' . print_r( wp_kses_post( $result_with_post_id ), true ) . ' -->'; // @codingStandardsIgnoreLine
		echo '<!-- pmc_paywall_article_access_required() = ' . print_r( wp_kses_post( $result_without_post_id ), true ) . ' -->'; // @codingStandardsIgnoreLine
		// @codeCoverageIgnoreEnd

		if ( ! $is_article_free ) {
			$metadata['hasPart'] = [
				'@type'               => 'WebPageElement',
				'isAccessibleForFree' => 'False',
				'cssSelector'         => self::PAID_CONTENT_SELECTOR,
			];
		}

		$metadata['isAccessibleForFree'] = $is_article_free ? 'True' : 'False';

		return $metadata;
	}

	/**
	 * Filter to add cheezcap group
	 * @param  array  $cheezcap_groups The cheezcap groups
	 * @return array                   The array with new cheezcap group
	 */
	public function filter_pmc_cheezcap_groups( $cheezcap_groups = [] ) {

		$cheezcap_options = [

			// Cheezcap option to enter CDS Self-Service URL
			new CheezCapTextOption(
				'CDS Self-Service URL',  // cheezecap label
				'Account link destination for logged in users via CDS',  // cheezcap description
				'pmc_subscription_cds_ss_url', // cheezcap id
				'', // default
				false, // use textarea
				false  // validation callback
			),

			// Cheezcap option to enter Zuora Self-Service URL
			new CheezCapTextOption(
				'Zuora Self-Service URL',  // cheezecap label
				'Account link destination for logged in users via Zuora/Salesforce',  // cheezcap description
				'pmc_subscription_sf_ss_url', // cheezcap id
				'', // default
				false, // use textarea
				false  // validation callback
			),

		];

		// Needed for compatibility with BGR_CheezCap
		if ( class_exists( 'BGR_CheezCapGroup' ) ) {
			$cheezcap_group_class = '\BGR_CheezCapGroup'; // @codeCoverageIgnore
		} else {
			$cheezcap_group_class = '\CheezCapGroup';
		}

		$cheezcap_groups[] = new $cheezcap_group_class( 'Subscriptions', 'pmc_subscription', $cheezcap_options );

		return $cheezcap_groups;
	}

	/**
	 * Helper function to return the CDS Self-Service url
	 * @return string
	 */
	public function cds_selfservice_url() {
		return apply_filters( 'pmc_subscription_cds_ss_url', \PMC_Cheezcap::get_instance()->get_option( 'pmc_subscription_cds_ss_url' ) );
	}

	/**
	 * Helper function to return the Zuora Self-Service url
	 * @return string
	 */
	public function sf_selfservice_url() {
		return apply_filters( 'pmc_subscription_sf_ss_url', \PMC_Cheezcap::get_instance()->get_option( 'pmc_subscription_sf_ss_url' ) );
	}

	/**
	 * Load headers scripts
	 *
	 * @uses PMC::render_template
	 * @return void
	 */
	public function action_pmc_pre_header() {
		\PMC::render_template( PMC_SUBSCRIPTION_ROOT . '/templates/uls-js-script.php', [], true );
	}
}
