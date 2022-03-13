<?php

namespace PMC\Todays_Top_deal;

use PMC\EComm\Tracking;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Todays_Top_Deal\Admin;

class Shortcode {

	use Singleton;

	/**
	 * Identifier for the shortcode
	 *
	 * @var string
	 */
	const SHORTCODE_TAG = 'todays-top-deal';

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

		add_shortcode( self::SHORTCODE_TAG, [ $this, 'todays_top_deal_shortcode_output' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );

		add_filter( 'pmc-google-amp-styles', [ $this, 'filter_amp_style' ], 12 );
		add_filter( 'pmc_inject_content_paragraphs', [ $this, 'auto_inject_ecommerce_module' ] );

	}

	/**
	 * Outputs shortcode template if conditions are met.
	 *
	 * @return void
	 */
	public function register_scripts() : void {

		global $post;

		if ( $this->should_display_todays_top_deal_shortcode( $post->ID ) ) {
			wp_enqueue_style( 'pmc-todays-top-deal-css', PMC_TODAYS_TOP_DEAL_PLUGIN_URL . 'assets/css/todays-top-deal.css', [], PMC_TODAYS_TOP_DEAL_VERSION );
		}

	}

	/**
	 * To add style for related link module for AMP pages.
	 *
	 * @param string $style AMP page style
	 *
	 * @return string
	 */
	public function filter_amp_style( $style ) {

		global $post;

		if ( $this->should_display_todays_top_deal_shortcode( $post->ID ) ) {
			$template = sprintf( '%s/templates/amp-style.php', untrailingslashit( PMC_TODAYS_TOP_DEAL_PLUGIN_DIR ) );

			$style .= \PMC::render_template( $template );
		}

		return $style;

	}

	/**
	 * Outputs shortcode template if conditions are met.
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function todays_top_deal_shortcode_output() : string {

		global $post;

		if ( ! $this->should_display_todays_top_deal_shortcode( $post->ID ) ) {
			return '';
		}

		if ( ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) || apply_filters( 'pmc_todays_top_deal_force_amp', false ) ) {
			$ecommerce_variants_amp = $this->get_todays_top_deal_variants( true );

			if ( empty( $ecommerce_variants_amp ) ) {
				return '';
			}

			$amp_ecommerce_variant = $ecommerce_variants_amp[0];

			return \PMC::render_template(
				sprintf( '%s/templates/shortcode.php', untrailingslashit( PMC_TODAYS_TOP_DEAL_PLUGIN_DIR ) ),
				[
					'ecommerce_title'  => Admin::get_instance()->get_ecommerce_module_title(),
					'title'            => $amp_ecommerce_variant['title'],
					'description'      => Admin::get_instance()->get_ecommerce_module_description(),
					'link'             => $amp_ecommerce_variant['link'],
					'buy_button_text'  => Admin::get_instance()->get_ecommerce_module_buy_button_text(),
					'image_url'        => $amp_ecommerce_variant['image_url'],
					'image_height'     => 0,
					'image_width'      => 0,
					'price'            => $amp_ecommerce_variant['price'],
					'coupon_code'      => $amp_ecommerce_variant['coupon_code'],
					'discount_amount'  => $amp_ecommerce_variant['discount_amount'],
					'discount_percent' => $amp_ecommerce_variant['discount_percent'],
					'original_price'   => $amp_ecommerce_variant['original_price'],
					'is_amazon'        => $amp_ecommerce_variant['is_amazon'],
				],
				false
			);
		}

		$ecommerce_variants = $this->get_todays_top_deal_variants();

		if ( empty( $ecommerce_variants ) ) {
			return '';
		}

		return \PMC::render_template(
			sprintf( '%s/templates/shortcode.php', untrailingslashit( PMC_TODAYS_TOP_DEAL_PLUGIN_DIR ) ),
			[
				'ecommerce_title'  => Admin::get_instance()->get_ecommerce_module_title(),
				'title'            => '%title%',
				'description'      => Admin::get_instance()->get_ecommerce_module_description(),
				'link'             => '#',
				'buy_button_text'  => Admin::get_instance()->get_ecommerce_module_buy_button_text(),
				'image_url'        => '#',
				'image_height'     => 0,
				'image_width'      => 0,
				'price'            => '%price%',
				'coupon_code'      => '%coupon_code%',
				'discount_amount'  => '%discount_amount%',
				'discount_percent' => '%discount_percent%',
				'original_price'   => '%original_price%',
				'is_amazon'        => true,
				'variants'         => $ecommerce_variants,
			],
			false
		);

	}

	/**
	 * Adds in Today's Top Deal shortcode to article automatticly if conditions are met.
	 *
	 * @param array $paragraphs
	 *
	 * @return array
	 */
	public function auto_inject_ecommerce_module( $paragraphs ) : array {

		global $post;

		if (
			! $this->should_display_todays_top_deal_shortcode( $post->ID ) ||
			has_shortcode( $post->post_content, 'todays-top-deal' ) // Don't inject todays top deals if manually placed in article.
		) {
			return $paragraphs;
		}

		$paragraphs[2][] = do_shortcode( '[todays-top-deal]' );

		return $paragraphs;

	}

	/**
	 * Determines if Today's Top Deal shortcode should be displayed.
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function should_display_todays_top_deal_shortcode( $post_id = 0 ) : bool {

		if (
			empty( $post_id ) ||
			! function_exists( 'is_apple_news_rendering_content' )
		) {
			return false;
		}

		// Only display on WP articles (exclude 3rd parties: feeds, amp, fb, apple news).
		if (
			Admin::get_instance()->ecommerce_module_enabled() &&
			! \PMC\Post_Options\API::get_instance()->post( $post_id )->has_option( 'disable-todays-top-deal' ) &&
			is_single() &&
			! is_feed() &&
			! is_apple_news_rendering_content()
		) {
			return true;
		}

		return false;

	}

	/**
	 * Get's array of Today's Top Deal products (up to 10 itmes) set in carousel.
	 * Structures data to be ready to be added to JS variable to display random product on frontend.
	 *
	 * @param bool $is_amp
	 *
	 * @return array
	 */
	public function get_todays_top_deal_variants( $is_amp = false ) : array {

		$carousel_options = [
			'flush_cache'          => true,
			'add_filler'           => false,
			'add_filler_all_posts' => false,
		];

		if ( ! $is_amp ) {
			$products = pmc_render_carousel( 'pmc_carousel_modules', 'todays-top-deal', 10, '', $carousel_options );
		} else {
			$products = pmc_render_carousel( 'pmc_carousel_modules', 'todays-top-deal-amp', 1, '', $carousel_options );
		}

		if ( empty( $products ) ) {
			return [];
		}

		$variants = [];

		foreach ( $products as $product ) {
			$product_url = ! empty( $product['url'] ) ? $product['url'] : '';

			$variant = [
				'title'            => ! empty( $product['title'] ) ? $product['title'] : '',
				'image_url'        => ! empty( $product['image'] ) ? $product['image'] : '',
				'image_width'      => '',
				'image_height'     => '',
				'price'            => '',
				'original_price'   => '',
				'discount_amount'  => '',
				'discount_percent' => '',
				'coupon_code'      => '',
			];

			$variant = $this->update_todays_top_deals_with_amazon_api_data( $product_url, $variant );

			$promo_post_id = ! empty( $product['parent_ID'] ) ? (int) $product['parent_ID'] : 0;
			if ( ! empty( $promo_post_id ) ) {
				$override_price = get_post_meta( $promo_post_id, '_pmc_carousel_override_price', true );
				if ( ! empty( $override_price ) && $variant['price'] !== $override_price ) {
					$variant['price'] = $override_price;

					$variant['original_price']   = '';
					$variant['discount_amount']  = '';
					$variant['discount_percent'] = '';
				}

				$override_coupon = get_post_meta( $promo_post_id, '_pmc_carousel_override_coupon', true );
				if ( ! empty( $override_coupon ) ) {
					$variant['coupon_code'] = $override_coupon;
				}
			}

			if ( empty( $variant['image_width'] ) || empty( $variant['image_height'] ) ) {
				$variant['image_width']  = 160;
				$variant['image_height'] = 160;
			}

			$variant['link'] = apply_filters( 'pmc_todays_top_deal_product_url', $product_url );

			if ( empty( $variant['link'] ) || empty( $variant['title'] ) ) {
				continue;
			}

			$variant['link'] = Tracking::get_instance()->track( $variant['link'] );

			$variants[] = $variant;
		}

		return $variants;

	}

	/**
	 * Takes in product and updates with Amazon API data if found.
	 *
	 * @param array $amazon_url
	 * @param array $ecommerce_data
	 *
	 * @return array
	 */
	public function update_todays_top_deals_with_amazon_api_data( string $amazon_url = '', array $ecommerce_data = [] ) : array {

		if (
			! class_exists( '\PMC\Store_Products\Product' ) ||
			! class_exists( '\PMC\Store_Products\Shortcode' ) ||
			empty( $amazon_url )
		) {
			return $ecommerce_data;
		}

		$product = apply_filters( 'pmc_carousel_todays_top_deal_product', [] );
		if ( empty( $product ) ) {
			$asin = \PMC\Store_Products\Shortcode::get_asin_from_amazon_url( $amazon_url );
			if ( empty( $asin ) ) {
				return $ecommerce_data;
			}

			$product = \PMC\Store_Products\Product::create_from_asin( $asin );
			if ( empty( $product ) || ! is_object( $product ) ) {
				return $ecommerce_data;
			}
		}

		$product = (array) $product;

		foreach ( $ecommerce_data as $key => $value ) {
			if ( empty( $value ) && ! empty( $product[ $key ] ) ) {
				$ecommerce_data[ $key ] = (string) $product[ $key ];
			}
		}

		$ecommerce_data['is_amazon'] = true;

		return $ecommerce_data;

	}

}

// EOF
