<?php
/**
 * Class to add Amazon product UI for post types on which it has been enabled
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-03-18
 */

// NOTE: DO not change this namespace as it is referenced directly from theme :(
namespace PMC\Custom_Feed_V2\Components;


use PMC;
use PMC\EComm\Tracking;
use PMC\Global_Functions\Traits\Singleton;

class Amazon_UI {

	use Singleton;

	const ID = 'pmc-cfv2-amzn-ui';

	const META_PRODUCTS_NAME    = '_amzn_product_information';
	const META_ALT_HEADING_NAME = '_alternative-amazon-heading';
	const META_IMG_CREDIT_NAME  = '_image_credit';

	const UI_ALT_HEADING_NAME = 'alternative-amazon-heading';

	const NONCE_ACTION = 'alternative_heading_save';
	const NONCE_NAME   = 'amazon_title_heading_nonce';

	/**
	 * class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to setup listeners on WP hooks
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_stuff' ] );

		/*
		 * Filters
		 */
		add_filter( 'pmc_custom_feed_amazon_deals_products', [ $this, 'get_products_from_post' ], 10, 2 );
		add_filter( 'pmc_custom_feed_amazon_deals_heroimagecaption', [ $this, 'get_image_credit' ], 10, 2 );
		add_filter( 'pmc_custom_feed_amazon_deals_introtext', '__return_empty_string' );
		add_filter( 'the_title_rss', [ $this, 'maybe_get_alternate_heading' ] );

	}

	/**
	 * Enqueue assets in wp-admin
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function enqueue_admin_stuff( $hook ) : void {

		if (
			( 'post-new.php' === $hook || 'post.php' === $hook )
			&& get_post_type() === 'post'
		) {
			wp_enqueue_script(
				sprintf( '%s-admin-post-js', self::ID ),
				sprintf(
					'%s/assets/js/admin/post.js',
					untrailingslashit( PMC_CUSTOM_FEED_V2_URL )
				),
				[
					'jquery',
					'underscore',
				],
				false
			);
		}

	}

	/**
	 * Method to get Amazon product URL
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function get_product_link( array $item = [] ) : string {

		$product_url = '';

		if ( ! empty( $item['product_link'] ) ) {

			$url_parts   = wp_parse_url( $item['product_link'] );
			$product_url = sprintf(
				'%s://%s/%s',
				$url_parts['scheme'],
				trim( $url_parts['host'], '/' ),
				trim( $url_parts['path'], '/' )
			);

		} elseif ( ! empty( $item['product_id'] ) ) {

			$product_url = sprintf(
				'https://amazon.com/dp/%s',
				$item['product_id']
			);

		}

		if ( ! empty( $product_url ) ) {
			$product_url = trailingslashit( $product_url );
		}

		return Tracking::get_instance()->track( $product_url );

	}

	/**
	 * Gets the Amazon products for a given post.
	 *
	 * @param array   $products Amazon Products
	 * @param int     $post_id  Current post ID in feed
	 * @param boolean $is_feed Prepare content for usage in a feed
	 *
	 * @return array Amazon Products array
	 */
	public function get_products_from_post( $products, $post_id, bool $is_feed = true ) : array {

		if ( empty( $products ) || ! is_array( $products ) ) {
			$products = [];
		}

		if ( intval( $post_id ) < 1 ) {
			return $products;
		}

		$products_data = get_post_meta( $post_id, self::META_PRODUCTS_NAME, true );

		if ( empty( $products_data ) ) {
			return $products;
		}

		foreach ( $products_data as $product ) {

			if ( empty( $product ) || ! is_array( $product ) ) {
				continue;
			}

			$product_url = $this->get_product_link( $product );

			if ( empty( $product_url ) || false === strpos( $product_url, 'amazon.com' ) ) {
				continue;
			}

			$description = ( ! empty( $product['description'] ) ) ? $product['description'] : '';

			if ( $is_feed ) {
				$description = $this->_get_product_description_for_feed( $description );
			}

			$title = ( ! empty( $product['title'] ) ) ? $product['title'] : '';
			$title = ( is_feed() && ! empty( $product['alternative_title'] ) ) ? $product['alternative_title'] : $title;

			$products[] = [
				'url'                 => $product_url,
				'headline'            => $title,
				'summary'             => ( ! empty( $product['summary'] ) ) ? do_shortcode( $product['summary'] ) : '',
				'product_description' => $description,
				'price'               => ( ! empty( $product['product_price'] ) ) ? $product['product_price'] : '',
				'rank'                => ( ! empty( $product['rank'] ) ) ? $product['rank'] : '',
				'award'               => ( ! empty( $product['product_awards'] ) ) ? $product['product_awards'] : '',
			];

		}

		return $products;

	}

	/**
	 * Returns image credit instead of image caption
	 *
	 * @param string $caption Image caption
	 * @param array  $image   Image details array
	 *
	 * @return string Returns image credit instead of image caption
	 */
	public function get_image_credit( string $caption, array $image ) : string {

		if ( ! empty( $image['image_id'] ) ) {
			$image_credit = get_post_meta( $image['image_id'], self::META_IMG_CREDIT_NAME, true );
		}

		$caption = ( ! empty( $image_credit ) ) ? $image_credit : '';

		return $caption;

	}

	/**
	 * Filter to use amazon associates feed Alternate Headline if post_title contains 'amazon' keyword.
	 *
	 * @param $post_title string post title for feed.
	 *
	 * @return string returns post title.
	 */
	public function maybe_get_alternate_heading( string $post_title ) : string {

		if ( ! is_feed() ) {
			return $post_title;
		}

		$feed_options = \PMC_Custom_Feed::get_instance()->get_feed_config();

		if ( empty( $feed_options['template'] ) || 'feed-amazon-deals' !== $feed_options['template'] ) {
			return $post_title;
		}

		$post = get_post();

		if ( ! empty( $post ) ) {

			$alternate_post_title = get_post_meta( $post->ID, self::META_ALT_HEADING_NAME, true );

			if ( ! empty( $alternate_post_title ) ) {
				$post_title = $alternate_post_title;
			}

		}

		return $post_title;

	}

	/**
	 * Remove images, shortcodes, and empty paragraph tags for product description feeds.
	 *
	 * @param string $product_description
	 *
	 * @return string
	 */
	protected function _get_product_description_for_feed( string $product_description ) : string {

		$allowed_html = wp_kses_allowed_html( 'post' );

		unset( $allowed_html['img'] );

		$product_description       = wp_kses( $product_description, $allowed_html );
		$product_description       = strip_shortcodes( $product_description );
		$product_description       = force_balance_tags( $product_description );
		$product_description_array = explode( '<p>', $product_description );
		$product_description_array = array_filter( $product_description_array );

		foreach ( $product_description_array as $key => $value ) {
			if (
				'</p>' === trim( $value )
			) {
				unset( $product_description_array[ $key ] );
			}
		}

		$product_description_array = array_values( $product_description_array );
		$product_description       = '<p>' . implode( '<p>', $product_description_array );

		unset( $product_description_array );

		return force_balance_tags( $product_description );

	}

}    //end class

//EOF
