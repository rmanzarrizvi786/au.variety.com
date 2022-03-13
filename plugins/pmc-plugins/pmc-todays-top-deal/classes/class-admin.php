<?php

namespace PMC\Todays_Top_Deal;

use \PMC\Global_Functions\Traits\Singleton;
use \CheezCapDropdownOption;
use \CheezCapTextOption;
use \CheezCapGroup;
use \PMC_Cheezcap;

class Admin {

	use Singleton;

	/**
	 * Constants for option names.
	 */
	const DEFAULT_TITLE          = 'Today\'s Top Deal';
	const DEFAULT_TITLE_AMP      = 'Today\'s Top Deal (AMP)';
	const OPTION_DISPLAY         = 'pmc_disable_todays_top_deal_module';
	const OPTION_TITLE           = 'pmc_todays_top_deal_module_title';
	const OPTION_DESCRIPTION     = 'pmc_todays_top_deal_module_description';
	const OPTION_BUY_BUTTON_TEXT = 'pmc_todays_top_deal_module_buy_button_text';

	/**
	 * __construct function of class.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Set up actions and filters.
	 */
	protected function _setup_hooks() {

		add_action( 'admin_init', [ $this, 'create_pmc_carousel_term' ] );
		add_action( 'admin_init', [ $this, 'add_post_option' ] );

		add_filter( 'pmc_cheezcap_groups', [ $this, 'filter_pmc_cheezcap_groups' ], 20 );

	}

	/**
	 * search and add, if needed, term for pmc_carousel
	 *
	 * @return void
	 */
	public function create_pmc_carousel_term() : void {

		$term_exists = wpcom_vip_term_exists( self::DEFAULT_TITLE, 'pmc_carousel_modules' );

		if ( null === $term_exists ) {
			wp_insert_term( self::DEFAULT_TITLE, 'pmc_carousel_modules' );
		}

		$amp_term_exists = wpcom_vip_term_exists( self::DEFAULT_TITLE_AMP, 'pmc_carousel_modules' );

		if ( null === $amp_term_exists ) {
			wp_insert_term( self::DEFAULT_TITLE_AMP, 'pmc_carousel_modules' );
		}

	}

	/**
	 * Ensure there is an "Disable Today's Top Deal" term is available in Post Options.
	 *
	 * @param null
	 *
	 * @return null
	 */
	public function add_post_option() {

		if ( class_exists( '\PMC\Post_Options\Taxonomy', false ) ) {
			\PMC\Post_Options\API::get_instance()->register_global_options(
				[
					'disable-todays-top-deal' => [
						'label'       => __( 'Disable Today\'s Top Deal', 'pmc-todays-top-deal' ),
						'description' => __( 'When selected, Today\'s Top Deal will be displayed on this post.', 'pmc-todays-top-deal' ),
					],
				]
			);
		}

	}

	/**
	 * Add CheezCap options for ecommerce module.
	 *
	 * @param array $cheezcap_options CheezCap options.
	 *
	 * @return array $cheezcap_options CheezCap options.
	 */
	public function filter_pmc_cheezcap_groups( $cheezcap_groups = [] ) : array {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = [];
		}

		$cheezcap_options = [
			new CheezCapDropdownOption(
				esc_html__( 'Enable Today\'s Top Deal Module', 'pmc-todays-top-deal' ),
				esc_html__( 'If enabled, this will display a random product after 2 paragraphs on every article. Make sure to add products to the ecommerce module via the Carousels. ', 'pmc-todays-top-deal' ),
				self::OPTION_DISPLAY,
				[ 'disabled', 'enabled' ],
				0, // Default option => Disabled.
				[ esc_attr__( 'Disabled', 'pmc-todays-top-deal' ), esc_attr__( 'Enabled', 'pmc-todays-top-deal' ) ]
			),
			new CheezCapTextOption(
				__( 'Title override', 'pmc-todays-top-deal' ),
				__( 'Default: Today\'s Top Deal', 'pmc-todays-top-deal' ),
				self::OPTION_TITLE,
				''
			),
			new CheezCapTextOption(
				__( 'Description override', 'pmc-todays-top-deal' ),
				__( 'Default: ', 'pmc-todays-top-deal' ) . get_bloginfo( 'name' ) . __( ' may receive a commission.', 'pmc-todays-top-deal' ),
				self::OPTION_DESCRIPTION,
				''
			),
			new CheezCapTextOption(
				__( 'Buy button text override', 'pmc-todays-top-deal' ),
				__( 'Default: Buy Now', 'pmc-todays-top-deal' ),
				self::OPTION_BUY_BUTTON_TEXT,
				''
			),
		];

		$cheezcap_options  = apply_filters( 'pmc_amazon_lookup_cheezcap_options', $cheezcap_options );
		$cheezcap_groups[] = new CheezCapGroup( self::DEFAULT_TITLE, 'pmc_todays_top_deal_cheezcap', $cheezcap_options );

		return $cheezcap_groups;

	}

	/**
	 * Check if ecommerce module is enabled.
	 *
	 * @return bool
	 */
	public function ecommerce_module_enabled() {

		if ( 'enabled' === PMC_Cheezcap::get_instance()->get_option( self::OPTION_DISPLAY ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if ecommerce module is enabled.
	 *
	 * @return string
	 */
	public function get_ecommerce_module_title() : string {

		$custom_title = PMC_Cheezcap::get_instance()->get_option( self::OPTION_TITLE );
		if ( ! empty( $custom_title ) ) {
			return (string) $custom_title;
		}

		return __( 'Today\'s Top Deal', 'pmc-todays-top-deal' );

	}

	/**
	 * Check if ecommerce module is enabled.
	 *
	 * @return string
	 */
	public function get_ecommerce_module_description() : string {

		$custom_description = PMC_Cheezcap::get_instance()->get_option( self::OPTION_DESCRIPTION );
		if ( ! empty( $custom_description ) ) {
			return $custom_description;
		}

		return get_bloginfo( 'name' ) . __( ' may receive a commission.', 'pmc-todays-top-deal' );

	}

	/**
	 * Check if ecommerce module is enabled.
	 *
	 * @return string
	 */
	public function get_ecommerce_module_buy_button_text() : string {

		$custom_button_text = PMC_Cheezcap::get_instance()->get_option( self::OPTION_BUY_BUTTON_TEXT );
		if ( ! empty( $custom_button_text ) ) {
			return (string) $custom_button_text;
		}

		return __( 'Buy Now', 'pmc-todays-top-deal' );

	}

}

// EOF
