<?php

/**
 * This class handles the Ads.txt file
 * Ads.txt is a simple, flexible, and secure method for publishers and distributors
 * to declare who is authorized to sell their inventory, improving transparency for programmatic buyers.
 *
 * To Add this text in each theme yoou need use filter "pmc_ads_txt".
 * Ex:
 * add_filter( 'pmc_ads_txt', function( $ads_txt ) {
 *  return $ads_txt = [ '< SSP/Exchange Domain >, < SellerAccountID >, < PaymentsType >, < TAGID > ']
 *  // Tag Id can be optional
 * });
 *
 * @author Vinod Tella <vtella@pmc.com>
 * @since 2017-10-02
 * @version 2017-10-02 Vinod Tella PMCRS-694
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Txt {

	use Singleton;

	/**
	 * Calling required hooks
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'pmc_ads_txt_init' ) );
		add_action( 'parse_query', array( $this, 'pmc_ads_txt_load' ) );
	}

	/**
	 * Initialise Ads.txt rewrite rules.
	 */
	public function pmc_ads_txt_init() {
		global $wp_rewrite, $wp;

		//register rewrite rule for ads.txt request
		add_rewrite_rule( 'ads\.txt$', $wp_rewrite->index.'?ads_txt=1', 'top' );

		//add 'ads_txt' query variable to WP
		$wp->add_query_var('ads_txt');

	}

	/**
	 * Check if current page is ads.txt or not.
	 *
	 * @return bool
	 */
	public function is_ads_txt() {
		if ( '1' === get_query_var( 'ads_txt' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * load ads.txt content.
	 */
	public function pmc_ads_txt_load() {
		if( $this->is_ads_txt() ) {
			$this->pmc_ads_txt();
			die();
		}
	}

	/**
	 * Display ads.txt content
	 */
	public function pmc_ads_txt() {
		$ads_txt_content = apply_filters( 'pmc_ads_txt', ['#Ads.tx'] );
		$ads_txt_content = implode( PHP_EOL , $ads_txt_content );
		//serve correct headers
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo esc_attr( $ads_txt_content );
	}

}

