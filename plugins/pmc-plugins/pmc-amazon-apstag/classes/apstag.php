<?php

namespace PMC\Amazon_Apstag;

use \PMC_Cheezcap;
use \PMC\Global_Functions\Traits\Singleton;

/**
 * Integrating Amazon Apstag header bidding
 *
 */
class Apstag {

	use Singleton;

	const PUB_ID = 3157;

	/**
	 * Class instantiation.
	 * Hook into WordPress.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		// Only proceed if we're not in the admin
		if ( is_admin() ) {
			return;
		}
		add_action( 'pmc_tags_head', array( $this, 'action_pmc_tags_head' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}

	/**
	 * Rendering Apstag js config on top of the page.
	 */
	public function action_pmc_tags_head() {

		if ( 'enabled' !== get_option( 'cap_pmc_amazon_apstag_enable' ) ) {
			return;
		}

		$header_tag_template = PMC_AMAZON_APSTAG_DIR . '/templates/header-tags.php';
		echo \PMC::render_template( $header_tag_template, array(
				'pub_id' => (int) self::PUB_ID,
			)
		);

	}

	/**
	 * Enqueue Apstag JS script
	 * which handles fetching bids and setting targeting for each ad slots
	 */
	public function enqueue_scripts() {

		if ( 'enabled' !== get_option( 'cap_pmc_amazon_apstag_enable' ) ) {
			return;
		}

		$js_ext = ( \PMC::is_production() ) ? '.min.js' : '.js';

		$script_url = sprintf( '%sassets/js/amazon-apstag%s', PMC_AMAZON_APSTAG_URL, $js_ext );

		//Amazon Apstag needs to be enqueued in header.
		$apstag_data['is_enabled'] = PMC_Cheezcap::get_instance()->get_option( 'pmc_amazon_apstag_enable' );
		$apstag_data['is_gallery'] = PMC_Cheezcap::get_instance()->get_option( 'pmc_amazon_apstag_for_gallery' );
		$apstag_data['is_video']   = PMC_Cheezcap::get_instance()->get_option( 'pmc_amazon_apstag_for_video' );
		wp_register_script( 'pmc-amazon-apstag-js', $script_url, array( 'jquery' ), '1.6', false );
		wp_localize_script( 'pmc-amazon-apstag-js', 'pmc_apstag', $apstag_data );
		wp_enqueue_script( 'pmc-amazon-apstag-js' );

	}
}
