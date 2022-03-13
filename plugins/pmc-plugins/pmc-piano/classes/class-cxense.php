<?php


namespace PMC\Piano;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Cheezcap;

/**
 * This class is responsible Cxense integration with Piano.
 *
 * Class Cxense
 * @package PMC\Piano
 */
class Cxense {

	use Singleton;

	public function __construct() {
		$this->_setup_hooks();
	}

	public function _setup_hooks(): void {
		add_action( 'wp_head', [ $this, 'action_pmc_tags_head' ], 1 );
		add_action( 'amp_post_template_head', [ $this, 'action_pmc_tags_head' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 1 );
		add_action( 'pmc_amp_content_before_header', [ $this, 'action_pmc_amp_tags_body' ] );
	}

	/**
	 * Enqueue scripts on the page
	 */
	public function enqueue_assets(): void {
		// Don't need to send page views or have modules for admin pages and feeds
		if ( is_admin() ) {
			return;
		}

		$cxense_data = [
			'siteId'           => PMC_Cheezcap::get_instance()->get_option( Plugin::CXENSE_SITE_ID ),
			'persistedQueryId' => PMC_Cheezcap::get_instance()->get_option( Plugin::PERSISTANCE_QUERY_ID ),
			'applicationId'    => PMC_Cheezcap::get_instance()->get_option( Plugin::PIANO_APP_ID ),
			'compatMode'       => PMC_Cheezcap::get_instance()->get_option( Plugin::CXENSE_COMPAT_MODE ),
		];

		wp_enqueue_script( 'pmc_cxense_js', sprintf( '%s/assets/build/js/pmc-cxense.js', untrailingslashit( PMC_PIANO_URI ) ), [], '1.2' );
		wp_localize_script( 'pmc_cxense_js', 'pmcCxenseData', $cxense_data );
	}

	/**
	 * Render the Cxense meta tags in `wp_head` or `amp_post_template_head`.
	 *
	 * @throws \Exception
	 */
	public function action_pmc_tags_head(): void {
		\PMC::render_template(
			PMC_PIANO_ROOT . '/templates/cxense-header.php',
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
	 * Gets all meta tags for the Cxense bot as a string to render in the head of all pages.
	 * Themes can override these values by using the `pmc_cxense_meta_tags` filter.
	 * @return string
	 */
	public function get_meta_tags(): string {
		$meta_tags = [];
		$tags      = apply_filters( 'pmc_cxense_meta_tags', Common_Data::get_instance()->get_default_meta_tags() );

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
	 * Render the Cxense AMP analytics tag in `pmc_amp_content_before_header`.
	 *
	 * @throws \Exception
	 */
	public function action_pmc_amp_tags_body(): void {
		\PMC::render_template(
			PMC_PIANO_ROOT . '/templates/amp/amp-cxense-tracking.php',
			[
				'site_id'           => PMC_Cheezcap::get_instance()->get_option( Plugin::CXENSE_SITE_ID ),
				'custom_parameters' => apply_filters( 'pmc_cxense_custom_parameters', Common_Data::get_instance()->get_default_custom_parameters() ),
			],
			true
		);
	}

}
