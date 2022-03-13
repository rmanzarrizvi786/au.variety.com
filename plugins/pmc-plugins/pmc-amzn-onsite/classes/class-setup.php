<?php

namespace PMC\Amzn_Onsite;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Cheezcap;


class Setup {

	use Singleton;

	/**
	 * Setup constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() : void {

		// Append product information to post_content with high priority before any filter/modification done to post_content.
		add_filter( 'the_content', [ $this, 'append_products_to_article' ], 1 );
		add_filter( 'pmc_custom_feed_amazon_deals_products', [ $this, 'get_products_for_feed_by_post_id' ], 10, 2 );
		add_action( 'wp_footer', [ $this, 'load_amazon_onetag_script_tag' ] );
		add_filter( 'pmc_custom_feed_content', [ $this, 'append_products_to_feed_content' ], 10, 4 );

	}


	/**
	 * Append amazon onsite products at the end of the feed content, SADE-519.
	 *
	 * @param string   $content         post content for feed
	 * @param \string  $feed            current feed name
	 * @param \WP_Post $post            post object being process
	 * @param array    $feed_options    array of option for current feed
	 *
	 * @return string
	 */
	public function append_products_to_feed_content( $content, $feed, $post, $feed_options ) : string {

		// Filter to prevent products from being rendered in post content in feed. Defaults to true.
		if (
			! is_a( $post, '\WP_Post' )
			|| ! apply_filters( 'pmc_amzn_onsite_add_products_to_feed', true )
		) {
			return $content;
		}

		$products = $this->get_products_by_post_id( $post->ID );

		if ( empty( $products ) || ! is_array( $products ) ) {
			return $content;
		}

		$template_part = 'feed';

		// Handle feed-amazon-deals template separately as it requires special syntax. Currently relies on pmc-store-product shortcode.
		if (
			is_array( $feed_options )
			&& ! empty( $feed_options['template'] )
			&& 'feed-amazon-deals' === $feed_options['template']
			&& shortcode_exists( 'pmc-store-product' )
		) {
			$template_part = 'amazon-deals-feed';
		}

		$template = sprintf(
			'%s/templates/product-%s.php',
			untrailingslashit( AMZN_ONSITE_PLUGIN_DIR ),
			$template_part
		);

		$product_markup = $this->get_product_markup( $products, $template );

		$disclaimer_copy            = '';
		$disclaimer_display_options = \PMC_Cheezcap::get_instance()->get_option( 'pmc-amzn-onsite-disclaimer-display' );

		if (
			is_array( $disclaimer_display_options ) &&
			( in_array( 'feed', (array) $disclaimer_display_options, true ) )
		) {
			$disclaimer_copy = \PMC_Cheezcap::get_instance()->get_option( 'pmc-amzn-onsite-disclaimer-copy' );
		}

		$disclaimer_copy = ! empty( $disclaimer_copy ) ? '<p><em>' . $disclaimer_copy . '</em></p>' : '';

		// $product_markup is escaped in template and includes data-* attributes
		// that are stripped out on VIP when using wp_kses_post.
		return $content . $disclaimer_copy . implode( '', $product_markup );
	}

	/**
	 * Append Amazon products to content from `Amazon products Meta box` if it's an article.
	 *
	 * @param string $content
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function append_products_to_article( $content ) : string {

		$content = (string) $content;

		// Temporary filter to opt-in to plugin appending content to post.
		if ( false === apply_filters( 'pmc_amzn_onsite_add_products', false ) ) {
			return $content;
		}

		$post_types = apply_filters( 'amzn_onsite_post_types', [ 'post' ] );

		if ( is_feed() || ! is_singular( $post_types ) ) {
			return $content;
		}

		$post             = get_post();
		$product_data     = (array) $this->get_products_by_post_id( $post->ID );
		$default_template = sprintf(
			'%s/templates/product-article.php',
			untrailingslashit( AMZN_ONSITE_PLUGIN_DIR )
		);
		$template         = apply_filters( 'pmc_amzn_onsite_product_article_template', $default_template );
		$product_markup   = $this->get_product_markup( $product_data, $template );

		if ( empty( $product_markup ) ) {
			return $content;
		}

		$product_markup = implode( '', $product_markup );
		$product_markup = apply_filters( 'pmc_amzn_onsite_after_products', $product_markup );

		$disclaimer_copy            = '';
		$disclaimer_display_options = \PMC_Cheezcap::get_instance()->get_option( 'pmc-amzn-onsite-disclaimer-display' );

		if (
			is_array( $disclaimer_display_options ) &&
			( in_array( 'site', (array) $disclaimer_display_options, true ) )
		) {
			$disclaimer_copy = \PMC_Cheezcap::get_instance()->get_option( 'pmc-amzn-onsite-disclaimer-copy' );
		}

		$disclaimer_copy = ! empty( $disclaimer_copy ) ? '<p><em>' . $disclaimer_copy . '</em></p>' : '';

		// $product_markup is escaped in template and includes data-* attributes
		// that are stripped out on VIP when using wp_kses_post.
		return $content . $disclaimer_copy . $product_markup;

	}

	public function get_product_markup( $product_data, $template ) {

		if (
			empty( $product_data )
			|| empty( $template )
			|| ! is_array( $product_data )
			|| ! \PMC::is_file_path_valid( $template )
		) {
			return [];
		}

		$product_markup = [];

		foreach ( $product_data as $count => $product ) {

			$product['display_title'] = '';
			$product['count']         = $count + 1;

			if ( ! empty( $product['title'] ) ) {
				$product['display_title'] = sprintf(
					'<h2>%d. %s</h2>',
					intval( $product['count'] ),
					esc_html( $product['title'] )
				);
			}

			if ( false !== strpos( $template, 'product-feed' ) ) {
				$product['description'] = apply_filters( 'the_content', \PMC::strip_shortcodes( $product['description'] ) );
			}

			$product_markup[] = \PMC::render_template(
				$template,
				[
					'product' => $product,
				]
			);

		}

		return $product_markup;
	}

	/**
	 * Gets the Amazon products for a given post ID.
	 *
	 * @param int   $post_id  Current post ID in feed.
	 *
	 * @return array Amazon Products array.
	 */
	public function get_products_by_post_id( int $post_id ) : array {

		$products = [];

		if ( 0 >= intval( $post_id ) ) {
			return $products;
		}

		$product_data  = get_post_meta( $post_id, '_amzn_product_information', true );
		$hide_products = (
			! is_feed()
			&& 1 === intval( get_post_meta( $post_id, 'hide_products_from_post', true ) )
		);

		if ( empty( $product_data ) || ! empty( $hide_products ) ) {
			return $products;
		}

		foreach ( $product_data as $product ) {

			if ( empty( $product ) || ! is_array( $product ) ) {
				continue;
			}

			$product['product_link'] = $this->_get_product_link( $product );

			if ( empty( $product['product_link'] ) || false === strpos( $product['product_link'], 'amazon.com' ) ) {
				continue;
			}

			$product['summary'] = ( ! empty( $product['summary'] ) ) ? $product['summary'] : $product['description'];

			$products[] = wp_parse_args(
				$product,
				[
					'product_link'      => '',
					'product_id'        => '',
					'product_price'     => '',
					'description'       => '',
					'summary'           => '',
					'product_awards'    => '',
					'title'             => '',
					'alternative_title' => '',
					'rank'              => '',
				]
			);

		}

		return $products;

	}

	/**
	 * Get products from post ID to use in feed.
	 *
	 * @param array $products
	 * @param int   $post_id
	 *
	 * @return array
	 */
	public function get_products_for_feed_by_post_id( array $products, int $post_id ) : array {

		// Temporary filter to opt-in to add products to feed from plugin.
		if ( false === apply_filters( 'pmc_amzn_onsite_add_products', false ) ) {
			return $products;
		}

		$products = $this->get_products_by_post_id( $post_id );

		foreach ( $products as $key => $product ) {

			$title = '';
			$rank  = ( ! empty( $product['rank'] ) ) ? $product['rank'] : '';
			$award = ( ! empty( $product['product_awards'] ) ) ? $product['product_awards'] : '';

			if ( ! empty( $product['alternative_title'] ) ) {
				$title = $product['alternative_title'];
			} elseif ( ! empty( $product['title'] ) ) {
				$title = $product['title'];
			}

			$products[ $key ]['url']      = $product['product_link'];
			$products[ $key ]['headline'] = $title;
			$products[ $key ]['summary']  = $product['summary'];
			$products[ $key ]['rank']     = $rank;
			$products[ $key ]['award']    = $award;

		}

		return $products;

	}

	/**
	 * Gets Amazon product link.
	 *
	 * @param array $product
	 *
	 * @return string
	 */
	protected function _get_product_link( array $product ) : string {

		$link = '';

		if (
			! empty( $product['product_link'] )
			&& false !== filter_var( $product['product_link'], FILTER_VALIDATE_URL )
			&& false !== strpos( $product['product_link'], 'amazon.com' )
		) {
			$url_parts = wp_parse_url( $product['product_link'] );
			$link      = sprintf( '%s://%s%s', $url_parts['scheme'], $url_parts['host'], $url_parts['path'] );
		} elseif ( ! empty( $product['product_id'] ) ) {
			$link = sprintf( 'https://amazon.com/dp/%s', $product['product_id'] );
		}

		if ( ! empty( $link ) ) {
			$link = trailingslashit( $link );
		}

		return $link;

	}

	/**
	 * Add Amazon OneTag script tag in footer
	 */
	public function load_amazon_onetag_script_tag() {

		\PMC::render_template( AMZN_ONSITE_PLUGIN_DIR . '/templates/amazon-onetag-scripts.php', [], true );

	}

	/**
	 * Get Amazon onsite content
	 * @param int $post_id
	 * @param string $format
	 *
	 * @return string Amazon onsite products mark up string.
	 * @throws \Exception
	 */
	public function get_amazon_products( $post_id = 0, $format = '' ) : string {
		$products = '';
		if ( ! empty( $post_id ) || 0 < intval( $post_id ) ) {

			$product_data     = (array) $this->get_products_by_post_id( $post_id );
			$product_markup   = [];
			$context          = ( ! is_feed() ) ? 'article' : 'feed';
			$default_template = sprintf(
				'%s/templates/product-%s.php',
				untrailingslashit( AMZN_ONSITE_PLUGIN_DIR ),
				$context
			);

			$template = apply_filters( 'pmc_amzn_onsite_product_article_template', $default_template );

			if ( \PMC::is_file_path_valid( $template ) ) {

				foreach ( $product_data as $count => $product ) {

					$product['display_title'] = '';
					$product['count']         = $count + 1;

					if ( ! empty( $product['title'] ) ) {
						$product['display_title'] = sprintf(
							'<h2>%s</h2>',
							esc_html( $product['title'] )
						);
					}

					$product_markup[] = \PMC::render_template(
						$template,
						[
							'product' => $product,
							'format'  => $format,
						]
					);

				}

				$products = implode( '', $product_markup );

			}

		}
		return $products;
	}

}
