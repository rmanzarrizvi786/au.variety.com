<?php
/**
 * Config file for PMC Apple News plugin.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2017-12-06
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Apple_News {

	use Singleton;

	const RELATED_LINK_SHORT_CODE = 'pmc-related-link';

	/**
	 * Construct Method.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Add action and filters hooks.
	 */
	protected function _setup_hooks() {

		/**
		 * Filters.
		 */
		add_filter( 'apple_news_exporter_content_pre', array( $this, 'exporter_content_pre' ) );
		add_filter( 'apple_news_exporter_content', array( $this, 'exporter_content' ) );
		add_filter( 'pmc_apple_news_promo_query_args', [ $this, 'update_apple_news_promo_query_args' ] );
		add_filter( 'pmc_apple_news_promo_heading', [ $this, 'filter_apple_news_promo_heading' ] );
		add_filter( 'pmc_apple_news_enable_buy_button', '__return_true' );
		add_filter( 'pmc_apple_news_amazon_affiliate_code', [ $this, 'apple_news_amazon_affiliate_code' ] );
		add_filter( 'pmc_apple_news_amazon_ecommerce_affiliate_code', [ $this, 'apple_news_amazon_ecommerce_affiliate_code' ] );
		add_filter( 'pmc_apple_news_buy_button_component_style', [ $this, 'apple_news_buy_button_component_style' ] );
		add_filter( 'pmc_apple_news_buy_button_text_style', [ $this, 'apple_news_buy_button_text_style' ] );

	}

	/**
	 * This does not do anything to post content.
	 * But, enable related link shortcode which is disable in
	 * \Variety\Plugins\Config\PMC_Related_Link::register_shortcodes();
	 *
	 * @param  string $content Post Content.
	 *
	 * @return string Post Content.
	 */
	public function exporter_content_pre( $content = '' ) {

		remove_shortcode( self::RELATED_LINK_SHORT_CODE );

		$related_plugin = \PMC_Related_Link::get_instance();

		add_shortcode( self::RELATED_LINK_SHORT_CODE, array( $related_plugin, 'shortcode_to_html' ) );

		return $content;
	}

	/**
	 * This does not do anything to post content.
	 * but call again \Variety\Plugins\Config\PMC_Related_Link::register_shortcodes();
	 * So, default functionality can work without interruption.
	 *
	 * @param  string $content Post Content.
	 *
	 * @return string Post Content.
	 */
	public function exporter_content( $content = '' ) {

		PMC_Related_Link::get_instance()->register_shortcodes();

		return $content;
	}

	/** Update apple new query args
	 * @param array $args
	 *
	 * @return array
	 */
	public function update_apple_news_promo_query_args( $args = [] ) {
		$args['tax_query'] = [ // phpcs:ignore -- slow query ok.
			[
				'taxonomy' => 'vertical',
				'field'    => 'slug',
				'terms'    => 'shopping',
			],
		];

		return $args;
	}

	/**
	 * @param string $heading
	 *
	 * @return string
	 */
	public function filter_apple_news_promo_heading( $heading = '' ) {
		$heading = 'MORE FROM VARIETY';
		return $heading;
	}

	/**
	 * @param string $affiliate_code affiliate code ID
	 *
	 * @return string affiliate code ID for Apple News
	 */
	public function apple_news_amazon_affiliate_code( $affiliate_code = '' ): string {
		return 'varietyapplenews-20';
	}

	/**
	 * @param string $affiliate_code affiliate code ID
	 *
	 * @return string affiliate code ID for Apple News Ecommerce Module
	 */
	public function apple_news_amazon_ecommerce_affiliate_code( $affiliate_code = '' ): string {
		return 'vydealswidget-20';
	}

	/**
	 * @param array $style default button component style
	 *
	 * @return array modifiied component style
	 */
	public function apple_news_buy_button_component_style( $style ): array {

		return [
			'backgroundColor' => '#abdddc',
			'mask'            => [
				'type'   => 'corners',
				'radius' => 25,
			],
		];

	}

	/**
	 * @param array $style default button text style
	 *
	 * @return array modifiied text style
	 */
	public function apple_news_buy_button_text_style( $style ): array {

		return [
			'textColor'  => '#060808',
			'fontWeight' => 'bold',
			'fontSize'   => 20,
		];

	}

}
