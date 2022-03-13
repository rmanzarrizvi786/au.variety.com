<?php
// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCase
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
// phpcs:disable WordPressVIPMinimum.Performance.LowExpiryCacheTime.LowCacheTime

namespace PMC\Store_Products;

use PMC;
use PMC\Buy_Now\Frontend;
use PMC\EComm\Tracking;
use PMC\Global_Functions\Traits\Singleton;

class Shortcode {

	use Singleton;

	/**
	 * Identifier for the shortcode.
	 *
	 * This shortcode has been deprecated and we now use
	 * the buy-now shortcode for this functionality.
	 *
	 * @deprecated
	 * @var string
	 */
	const SHORTCODE_TAG = 'pmc-store-product';

	/**
	 * Identifier for the meta key corresponding with a saved list of
	 * amazon product references in the post content.
	 *
	 * @var string
	 */
	const PRODUCT_LIST_META_KEY = 'amazon_product_references';

	/**
	 * Path to plugin assets.
	 *
	 * @var string
	 */
	private $_assets_path;

	/**
	 * Default attributes for the shortcode
	 */
	public static $default_atts = [
		'url'         => '',
		'link'        => '',
		'asin'        => '',
		'title'       => '',
		'price'       => '',
		'rating'      => '',
		'award'       => '',
		'summary'     => '',
		'button_type' => 'amazon',  // Store production always be amazon button type, added here to allow reuse filter pmc_buy_now_data
	];

	/**
	 * Products observed during the pageload.
	 *
	 * @var array
	 */
	private $_observed_products = [];

	/**
	 * Shortcodes observed during the pageload.
	 *
	 * @var array
	 */
	private $_observed_shortcodes = [];

	/**
	 * URL for plugin assets.
	 *
	 * @var string
	 */
	private $_assets_url;

	/**
	 * Path to plugin templates.
	 *
	 * @var string
	 */
	private $_templates_path;

	/**
	 * Instantiates the class.
	 *
	 */
	public function __construct() {

		$this->_assets_path    = dirname( __DIR__ ) . '/assets';
		$this->_assets_url     = rtrim( plugins_url( 'assets', $this->_assets_path ), '/' );
		$this->_templates_path = dirname( __DIR__ ) . '/templates';

		add_shortcode( self::SHORTCODE_TAG, array( $this, 'render_callback' ) );
		add_action( 'init', array( $this, 'action_init' ) );
		add_action( 'pmc_google_analytics_pre_send_js', array( $this, 'action_pmc_google_analytics_pre_send_js' ) );
		add_filter( 'pmc_ga_event_tracking', array( $this, 'filter_pmc_ga_event_tracking' ) );
		add_filter( 'pmc_google_amp_ga_event_tracking', array( $this, 'filter_pmc_google_amp_ga_event_tracking' ) );
		add_filter( 'pmc_strip_shortcode', array( $this, 'filter_pmc_strip_shortcode' ), 10, 3 );
		add_filter( 'pmc_buy_now_button_types', [ $this, 'amazon_buy_now_button_type' ] );
		add_filter( 'pmc_buy_now_options', [ $this, 'amazon_buy_now_options' ] );
		// Priority 11 required to override other themes using this hook that may not be Amazon.
		add_action( 'wp', [ $this, 'amazon_prefetch_products' ], 10 );
		add_action( 'save_post', [ $this, 'amazon_get_products_to_prefetch' ], 10, 2 );

		// We need this filter to run at higher priority to allow amazon product lookup
		add_filter( 'pmc_buy_now_data', [ $this, 'filter_pmc_buy_now_data' ], 5 );
	}

	/**
	 * Render the shortcode.
	 *
	 * If the shortcode includes $content, then simply
	 * wrap with a link. Otherwise, render the product template.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode inner content.
	 * @return string
	 */
	public function render_callback( $atts, $content ) {
		$atts = shortcode_atts( static::$default_atts, $atts );

		$buy_now_data = [
			'data'         => array_merge( $atts, [ 'content' => $content ] ),
			'amp_template' => sprintf( '%s/templates/shortcode-amp.php', untrailingslashit( PMC_BUY_NOW_PLUGIN_DIR ) ),
			'template'     => sprintf( '%s/templates/shortcode.php', untrailingslashit( PMC_BUY_NOW_PLUGIN_DIR ) ),
		];

		$buy_now_data = apply_filters( 'pmc_buy_now_data', $buy_now_data );

		$template_html = '';
		if ( ! empty( $buy_now_data['template_html'] ) ) {
			$template_html = $buy_now_data['template_html'];
		} else {
			if ( \PMC::is_amp() ) {
				$template_file = $buy_now_data['amp_template'];
			} else {
				$template_file = $buy_now_data['template'];
			}
			if ( ! empty( $buy_now_data['data']['link'] ) && ! empty( $template_file ) ) {
				$template_html = \PMC::render_template(
					$template_file,
					$buy_now_data['data'],
					false
				);
			}
		}

		return $template_html;
	}

	public function filter_pmc_buy_now_data( array $buy_now_data ) {

		$data        = $buy_now_data['data'] ?? [];
		$button_type = $data['button_type'] ?? '';

		if ( 'amazon' !== $button_type ) {
			return $buy_now_data;
		}

		$content = $data['content'] ?? '';

		$product = static::_get_product_from_shortcode_atts( $data );

		if ( ! $product ) {
			if ( $data['url'] ) {
				$data['url']                   = Tracking::get_instance()->track( $data['url'] );
				$buy_now_data['template_html'] = '<a href="' . esc_url( $data['url'] ) . '">' . esc_html( $content ) . '</a>';
			} else {
				$buy_now_data['template_html'] = $content;
			}
			return $buy_now_data;
		}

		$action_wp_footer = [ $this, 'action_wp_footer' ];

		if ( ! has_action( 'wp_footer', $action_wp_footer ) ) {
			add_action( 'wp_footer', $action_wp_footer );
		}

		$product->guid = static::_get_product_guid_from_atts_content( $data, $content );

		/**
		 * Permit modification of the product object prior to display
		 *
		 * @param object $product Project to display.
		 */
		$product = apply_filters( 'pmc_store_products_displayed_product', $product );

		$template_vars = [
			'product'    => $product,
			'title'      => ( ! empty( $data['title'] ) ) ? $data['title'] : $product->title,
			'text'       => ( ! empty( $data['text'] ) ) ? $data['title'] : $product->title,
			'url'        => ( ! empty( $data['url'] ) ) ? $data['url'] : $product->url,
			'link'       => ( ! empty( $data['link'] ) ) ? $data['link'] : $product->url,
			'price'      => ( ! empty( $data['price'] ) ) ? $data['price'] : $product->price,
			'orig_price' => ( ! empty( $data['orig_price'] ) ) ? $data['orig_price'] : $product->original_price,
			'rating'     => ( ! empty( $data['rating'] ) ) ? $data['rating'] : $product->rating,
			'award'      => ( ! empty( $data['award'] ) ) ? $data['award'] : $product->award,
			'summary'    => ( ! empty( $data['summary'] ) ) ? $data['summary'] : $product->summary,
		];

		$template_vars['percentage'] = Frontend::get_instance()->calculate_percentage( $template_vars['price'], $template_vars['orig_price'] );
		$buy_now_data['data']        = array_merge( $buy_now_data['data'], $template_vars );

		if ( ! empty( $content ) ) {
			/**
			 * Permit override of the link template.
			 *
			 * @param string $template Path to the link template.
			 */
			$template                     = apply_filters( 'pmc_store_products_link_template', $this->_templates_path . '/link.php' );
			$buy_now_data['data']['text'] = $content;
		} elseif ( is_feed() ) {
			$template = $this->_templates_path . '/feed.php';

			// phpcs:ignore PmcWpVip.Functions.ClassExists.NoClassAutoloading
			if ( class_exists( '\PMC_Custom_Feed', true ) ) {
				$feed_template = \PMC_Custom_Feed::get_instance()->get_feed_config( 'template' );

				if ( 'feed-amazon-deals' === $feed_template ) {
					$template = sprintf( '%s/amazon-deals-feed.php', untrailingslashit( $this->_templates_path ) );
				}
			}

			/**
			 * Permit override of the feed template.
			 *
			 * @param string $template Path to the feed template.
			 */
			$template = apply_filters( 'pmc_store_products_feed_template', $template );
		} else {
			/**
			 * Permit override of the widget template.
			 *
			 * @param string $template Path to the widget template.
			 */
			$template = apply_filters( 'pmc_store_products_widget_template', $this->_templates_path . '/widget.php' );
		}

		$buy_now_data['template'] = $template;
		return $buy_now_data;
	}

	/**
	 * Perform whatever actions are necessary on init.
	 *
	 */
	public function action_init() {
		static::extend_post_kses();
	}

	/**
	 * Extend kses to allow our data attribute.
	 */
	public static function extend_post_kses() {
		global $allowedposttags;

		$tags           = array( 'a' );
		$new_attributes = array( 'data-pmc-sp-product' => array() );

		foreach ( $tags as $tag ) {
			if ( isset( $allowedposttags[ $tag ] ) && is_array( $allowedposttags[ $tag ] ) ) {
				$allowedposttags[ $tag ] = array_merge( $allowedposttags[ $tag ], $new_attributes );
			}
		}
	}

	/**
	 * Enable GA ECommerce API on article pages with the shortcode.
	 *
	 * Note: Only processes the post_content for the main queried object.
	 *
	 */
	public function action_pmc_google_analytics_pre_send_js() {

		if ( empty( $this->_observed_products ) ) {
			return;
		}
		$template = $this->_templates_path . '/ga-head.php';
		PMC::render_template(
			$template,
			array(
				'products' => $this->_observed_products,
			),
			true
		);
	}

	/**
	 * Filter PMC GA event tracking to track our own clicks
	 *
	 * @param array $events Any existing click events to be tracked.
	 * @return array
	 */
	public function filter_pmc_ga_event_tracking( $events ) {
		if ( ! is_singular() ) {
			return $events;
		}
		$events = array_merge( $events, $this->get_ga_events_for_view() );
		return $events;
	}

	/**
	 * Track click events on AMP templates.
	 *
	 * @param array $triggers Existing AMP triggers.
	 * @return array
	 */
	public function filter_pmc_google_amp_ga_event_tracking( $triggers ) {
		foreach ( $this->get_ga_events_for_view() as $event ) {
			$triggers[] = array(
				'on'       => 'click',
				'category' => $event['category'],
				'label'    => $event['label'],
				'selector' => $event['selector'],
			);
		}
		return $triggers;
	}

	/**
	 * Get events for shortcodes displayed on this view.
	 *
	 * @return array
	 */
	public function get_ga_events_for_view() {
		$events                     = [];
		$this->_observed_products   = [];
		$this->_observed_shortcodes = [];
		/**
		 * Allow other code to include content to inspect for shortcodes displayed.
		 *
		 * @param string $content Existing post content.
		 */
		$content = (string) apply_filters( 'pmc_store_products_displayed_content', get_queried_object()->post_content );

		foreach ( static::_get_existing_shortcodes( $content ) as $i => $shortcode ) {
			$product = static::_get_product_from_shortcode_atts( $shortcode['atts'] );
			if ( $product ) {
				$product->display_context     = 'article';
				$product->position            = $i + 1;
				$product->guid                = static::_get_product_guid_from_atts_content( $shortcode['atts'], $shortcode['content'] );
				$this->_observed_products[]   = $product;
				$this->_observed_shortcodes[] = $shortcode;
			}
		}

		$revenue = '0';
		if ( class_exists( 'PMC_Cheezcap' ) ) {
			$pmc_cheezcap = \PMC_Cheezcap::get_instance();
			$revenue      = $pmc_cheezcap->get_option( 'pmc_store_average_revenue' );
		}

		/**
		 * Permit average revenue to be overridden, particularly if the options
		 * implementation is in the theme.
		 *
		 * Value *must* be a string if set.
		 *
		 * @param string $revenue Average revenue to be reported.
		 */
		$revenue = apply_filters( 'pmc_store_products_average_revenue', $revenue );

		foreach ( $this->_observed_products as $i => $product ) {
			$shortcode = $this->_observed_shortcodes[ $i ];
			$category  = '';
			$label     = '';
			if ( ! empty( $shortcode['content'] ) ) {
				// Shortcode wrapping an image.
				if ( false !== stripos( $shortcode['content'], '<img ' ) ) {
					preg_match( '#alt=[\'\"]([^\'\"]+)[\'\"]#', $shortcode['content'], $alt_matches );
					$category = 'Amazon Image Click';
					$label    = ! empty( $alt_matches[1] ) ? $alt_matches[1] : $product->title;
				} else {
					$category = 'Amazon Inline Text';
					$label    = $shortcode['content'];
				}
			} else {
				$category = 'Amazon Buy Button';
				$label    = ( ! empty( $shortcode['atts']['title'] ) ) ? $shortcode['atts']['title'] : $product->title;
			}
			$product_data = [
				'id'       => $product->id,
				'name'     => $product->title,
				'brand'    => $product->manufacturer,
				'category' => $product->category,
				'variant'  => $product->variant,
				'position' => $product->position,
				'summary'  => $product->summary,
				'rating'   => $product->rating,
				'award'    => $product->award,
				'quantity' => '1',
				'price'    => str_replace( '$', '', $product->price ),
			];
			$selector     = '[data-pmc-sp-product="' . esc_attr( $product->guid ) . '"]';
			array_push(
				$events,
				[
					'action'     => 'click',
					'category'   => $category,
					'label'      => $label,
					'selector'   => $selector,
					'url'        => true,
					'pre_events' => [
						[ 'ec:addProduct', $product_data, 1 ],
						[ 'ec:setAction', 'click', [ 'list' => $product->display_context ] ],
					],
				],
				[
					'action'     => 'click',
					'category'   => $category . ' - Checkout',
					'label'      => $label,
					'selector'   => $selector,
					'url'        => true,
					'pre_events' => [
						[ 'ec:addProduct', $product_data, 1 ],
						[ 'ec:setAction', 'checkout' ],
					],
				]
			);

			// Use $revenue as product price for Purchase stage per
			// reporting requirements.
			$product_data['price'] = $revenue;

			array_push(
				$events,
				[
					'action'     => 'click',
					'category'   => $category . ' - Purchase',
					'label'      => $label,
					'selector'   => $selector,
					'url'        => true,
					'pre_events' => [
						[ 'ec:addProduct', $product_data, 1 ],
						[
							'ec:setAction',
							'purchase',
							[
								'id'      => 'T' . wp_rand(),
								'revenue' => $revenue,
							],
						],
					],
				]
			);

		}
		return $events;
	}

	/**
	 * Don't strip our shortcode from PMC custom feeds.
	 *
	 * @param string $content        Transformed content.
	 * @param string $shortcode      Shortcode to be stripped.
	 * @param string $origin_content Original content.
	 * @return string
	 */
	public function filter_pmc_strip_shortcode( $content, $shortcode, $origin_content ) {

		if ( static::_is_valid_shortcode( $shortcode ) ) {
			return $origin_content;
		}

		return $content;

	}

	/**
	 * If there are UTM-based affiliate ID replacements, perform them on the
	 * frontend.
	 */
	public function action_wp_footer() {

		/**
		 * Permit theme to define any Amazon UTM-based affiliate ID replacements
		 *
		 * @param array $amazon_utm_replacements UTM-based affiliate ID replacements.
		 */
		$amazon_utm_replacements = apply_filters( 'pmc_store_products_amazon_utm_replacements', [] );

		if ( empty( $amazon_utm_replacements ) ) {
			return;
		}

		PMC::render_template(
			$this->_templates_path . '/utm-replacements.php',
			[
				'amazon_utm_replacements' => $amazon_utm_replacements,
			],
			true
		);

	}
	/**
	 * Get existing shortcodes
	 *
	 * @param string  $content       Content to parse for a shortcode.
	 * @param string  $shortcode_tag Shortcode tag to look for.
	 *
	 * @return bool|array
	 */
	protected static function _get_existing_shortcodes( string $content ) {

		if ( ! static::_has_valid_shortcode( $content ) ) {
			return [];
		}

		$backup_tags = $GLOBALS['shortcode_tags'];

		remove_all_shortcodes();

		add_shortcode( self::SHORTCODE_TAG, '__return_false' );
		add_shortcode( \PMC\Buy_Now\Frontend::SHORTCODE_TAG, '__return_false' );

		preg_match_all( '/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return false;
		}

		$existing = [];

		foreach ( $matches as $shortcode ) {
			if ( static::_is_valid_shortcode( $shortcode[2] ) ) {
				$atts       = shortcode_parse_atts( $shortcode[3] );
				$atts       = shortcode_atts( static::$default_atts, $atts );
				$existing[] = array(
					'full'    => $shortcode[0],
					'atts'    => $atts,
					'content' => $shortcode[5],
				);
			}
		}

		$GLOBALS['shortcode_tags'] = $backup_tags;

		return $existing;

	}

	/**
	 * Get any custom Tracking ID from an Amazon URL.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function get_tracking_id_from_amazon_url( string $url ) : string {

		$tracking_id = '';

		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( ! in_array(
			(string) $host,
			(array) [
				'amazon.com',
				'www.amazon.com',
				'amzn.to',
			],
			true
		) ) {
			return $tracking_id;
		}

		// Short URL needs to be resolved to its full URL
		if ( 'amzn.to' === $host ) {
			$url = static::_get_full_url_from_shortlink( $url );

			if ( empty( $url ) ) {
				return $tracking_id;
			}
		}

		$parts = wp_parse_url( htmlspecialchars_decode( $url ) );

		parse_str( $parts['query'], $query );

		if ( ! empty( $query['tag'] ) ) {
			$tracking_id = sanitize_key( $query['tag'] );
		}

		return $tracking_id;

	}

	/**
	 * Gets the ASIN from an Amazon URL
	 *
	 * @param string $url Something that may be an Amazon URL.
	 * @return string|false
	 */
	public static function get_asin_from_amazon_url( $url ) {
		// Don't transform non-Amazon links
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! in_array(
			$host,
			array(
				'amazon.com',
				'www.amazon.com',
				'amzn.to',
			),
			true
		) ) {
			return false;
		}
		// Short URL needs to be resolved to its full URL
		if ( 'amzn.to' === $host ) {
			$url = static::_get_full_url_from_shortlink( $url );
			if ( ! $url ) {
				return false;
			}
		}
		// Assume the ASIN is the last part of the Amazon URL
		$path = trim( wp_parse_url( $url, PHP_URL_PATH ), '/' );
		$path = explode( '/', $path );
		$asin = array_pop( $path );
		// Some URLs end with 'ref=', which isn't the ASIN
		if ( 0 === stripos( $asin, 'ref=' ) ) {
			$asin = array_pop( $path );
		}

		// ASINs should always be 10 character alphanumeric strings
		// https://www.amazon.com/gp/seller/asin-upc-isbn-info.html
		if ( 10 !== strlen( $asin ) ) {
			return false;
		}
		return $asin;
	}

	/**
	 * Gets a product object based on shortcode $atts.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return Product|false
	 */
	protected static function _get_product_from_shortcode_atts( $atts ) {

		if ( ! empty( $atts['asin'] ) ) {
			return Product::create_from_asin( $atts['asin'] );
		}

		if ( empty( $atts['url'] ) && ! empty( $atts['link'] ) ) {
			$atts['url'] = $atts['link'];
		}

		if ( ! empty( $atts['url'] ) ) {
			$asin        = static::get_asin_from_amazon_url( $atts['url'] );
			$tracking_id = static::get_tracking_id_from_amazon_url( (string) $atts['url'] );

			if ( $asin ) {
				return Product::create_from_asin( $asin, $tracking_id );
			}
		}

		return false;
	}

	/**
	 * Generate a product guid from shortcode attributes and inner content.
	 *
	 * Even when a product is displayed multiple times on a page, it's hopefully
	 * displayed in a couple different ways, so this ensures a unique identifier.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode inner content.
	 * @return string
	 */
	protected static function _get_product_guid_from_atts_content( $atts, $content ) {
		return md5(
			wp_json_encode(
				array(
					$atts,
					// Texturized, to match what's passed through to the shortcode.
					wptexturize( $content ),
				)
			)
		);
	}

	/**
	 * Resolve a shortlink to its full URL by making a HEAD request
	 * and pulling the location from the header.
	 *
	 * @param string $url Original shortlink.
	 * @return string|false
	 */
	protected static function _get_full_url_from_shortlink( $url ) {
		$cache_key   = 'pmc_sp_shortlink_' . md5( $url );
		$cache_value = wp_cache_get( $cache_key );
		if ( false !== $cache_value ) {
			return $cache_value;
		}
		$resolved_url = '';
		$response     = wp_remote_head( $url, array( 'timeout' => 1 ) );
		if ( ! is_wp_error( $response )
			&& 301 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( ! empty( $headers['location'] ) ) {
				$resolved_url = $headers['location'];
			}
		}
		wp_cache_set( $cache_key, $resolved_url, 48 * HOUR_IN_SECONDS );
		return $resolved_url;
	}

	/**
	 * Sets the Amazon option in Buy Now dropdown as well setting fields available.
	 *
	 * @param array $buttons
	 *
	 * @return array
	 */
	public function amazon_buy_now_button_type( array $button_types ) : array {

		$button_types['amazon'] = [
			'label'  => __( 'Amazon', 'pmc-store-products' ),
			'fields' => [
				'asin',
				'title',
				'url',
				'link',
				'price',
				'rating',
				'summary',
				'award',
			],
		];

		return $button_types;

	}

	/**
	 * Available fields for Amazon Buy Now shortcode.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function amazon_buy_now_options( array $options ) : array {

		$defaults = [
			[
				'title'       => __( 'Title', 'pmc-store-products' ),
				'name'        => 'title',
				'type'        => 'text',
				'placeholder' => __( 'Product Title', 'pmc-store-products' ),
			],
			[
				'title'       => __( 'URL', 'pmc-store-products' ),
				'name'        => 'url',
				'type'        => 'text',
				'placeholder' => __( 'https://', 'pmc-store-products' ),
			],
			[
				'title' => __( 'ASIN', 'pmc-store-products' ),
				'name'  => 'asin',
				'type'  => 'text',
			],
			[
				'title' => __( 'Rating', 'pmc-store-products' ),
				'name'  => 'rating',
				'type'  => 'text',
			],
			[
				'title' => __( 'Summary', 'pmc-store-products' ),
				'name'  => 'summary',
				'type'  => 'text',
			],
			[
				'title' => __( 'Award', 'pmc-store-products' ),
				'name'  => 'award',
				'type'  => 'text',
			],
		];

		return array_merge( $options, $defaults );

	}

	/**
	 * Set custom template for Amazon button.
	 *
	 * @param string $default_template
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function amazon_buy_now_template( string $default_template, array $atts, string $content = '' ) : string {

		if ( 'amazon' === $atts['button_type'] ) {
			return $this->render_callback( $atts, $content );
		}

		return $default_template;

	}

	/**
	 * Checks if content has a valid buy-now or pmc-store-product shortcode.
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	private static function _has_valid_shortcode( string $content ) : bool {

		if (
			has_shortcode( $content, self::SHORTCODE_TAG )
			|| has_shortcode( $content, \PMC\Buy_Now\Frontend::SHORTCODE_TAG )
		) {
			return true;
		}

		return false;

	}

	/**
	 * Checks if a shortcode is a buy-now or pmc-store-product shortcode.
	 *
	 * @param string $shortcode
	 *
	 * @return bool
	 */
	private static function _is_valid_shortcode( string $shortcode ) : bool {

		if (
			self::SHORTCODE_TAG === $shortcode
			|| \PMC\Buy_Now\Frontend::SHORTCODE_TAG === $shortcode
		) {
			return true;
		}

		return false;

	}

	/**
	 * Store and return a list of Amazon ASIN ID's on the page.
	 *
	 * @param $post_id int Post ID.
	 *
	 * @return ?array
	 */
	public function amazon_get_products_to_prefetch( int $post_id ): ?array {
		$post = get_post( $post_id );

		if ( empty( $post->post_content ) ) {
			// Reset the meta value if empty.
			update_post_meta( $post->ID, self::PRODUCT_LIST_META_KEY, [] );

			return [];
		}

		// Fetch all the existing shortcodes.
		$shortcodes = static::_get_existing_shortcodes( $post->post_content );

		// Grab the ASIN and tracking_id for each item on the list.
		$asin = array_map(
			function ( $shortcode ) {
				$atts = $shortcode['atts'];

				if ( ! empty( $atts['asin'] ) ) {
					return [
						'asin'        => $atts['asin'],
						'tracking_id' => '',
					];
				}

				if ( empty( $atts['url'] ) && ! empty( $atts['link'] ) ) {
					$atts['url'] = $atts['link'];
				}

				if ( ! empty( $atts['url'] ) ) {
					$asin = static::get_asin_from_amazon_url( $atts['url'] );

					if ( $asin ) {
						$tracking_id = static::get_tracking_id_from_amazon_url( (string) $atts['url'] );

						return [
							'asin'        => $asin,
							'tracking_id' => $tracking_id,
						];
					}
				}

				return [];
			},
			(array) $shortcodes
		);

		// Reduce to an array keyed on ASIN
		$reduced = array_reduce(
			$asin,
			function( $carry, $item ) {
				if ( isset( $item['asin'] ) && isset( $item['tracking_id'] ) ) {
					$carry[ $item['asin'] ] = $item;
				}

				return $carry;
			},
			[]
		);

		// Store this list as post meta for future reference.
		update_post_meta( $post->ID, self::PRODUCT_LIST_META_KEY, $reduced );

		// Return the new meta value.
		return $reduced;
	}

	/**
	 * Fetch and cache each uncached Amazon item on the page.
	 *
	 * @return bool
	 */
	public function amazon_prefetch_products(): bool {
		// If not a frontend single post, skip.
		if ( is_admin() || ! is_singular() ) {
			return false;
		}

		// Fetch list of ASIN ID's, or compute it now.
		$asin = $this->_sanitize_meta( get_post_meta( get_the_ID(), self::PRODUCT_LIST_META_KEY, true ) );
		if ( empty( $asin ) ) {
			$asin = $this->amazon_get_products_to_prefetch( get_the_ID() );
		}

		// If there are still no ASIN ID's, stop.
		if ( empty( $asin ) ) {
			return false;
		}

		// Filter out previously cached items.
		$filtered = array_filter(
			$asin,
			function( $arr ) {
				$cache_keys = Product::generate_cache_keys( $arr['asin'], $arr['tracking_id'] );

				return empty( wp_cache_get( $cache_keys['primary'] ) );
			}
		);

		// Process up to 10 ID's at a time.
		foreach ( array_chunk( $filtered, 10, true ) as $arr ) {
			static::_amazon_fetch_and_cache_products( $arr );
		}

		// True if successful.
		return true;
	}

	/**
	 * @param array|string|bool $arr
	 *
	 * @return void
	 */
	private static function _sanitize_meta( $arr ): array {
		if ( empty( $arr ) || 'array' !== gettype( $arr ) ) {
			return [];
		}

		return array_filter(
			(array) $arr,
			function( $var ) {
				return isset( $var['asin'] ) && isset( $var['tracking_id'] );
			}
		);
	}

	/**
	 * Fetch and cache a list of up to 10 product ASIN ID's.
	 *
	 * @param $arr array An array of ASIN and Tracking ID's.
	 */
	private static function _amazon_fetch_and_cache_products( array $arr ) {
		// Start building the API parameters.
		$associate_tag = pmc_sp_get_amazon_store_id();

		$resources = [
			'Images.Primary.Large',
			'ItemInfo.Title',
			'ItemInfo.ProductInfo',
			'ItemInfo.Features',
			'ItemInfo.ByLineInfo',
			'Offers.Listings.Condition',
			'Offers.Listings.Price',
			'Offers.Listings.Promotions',
			'BrowseNodeInfo.BrowseNodes',
			'ParentASIN',
		];

		$operator = 'GetItems';

		// Pass the list of ASIN ID's to the existing API constructor.
		$api = new Amazon_Api( array_keys( (array) $arr ), $associate_tag, $resources, $operator );

		$args = [
			'headers' => $api->get_headers(),
			'body'    => $api->get_payload(),
		];

		$response = wp_safe_remote_post( $api->url, $args );

		if ( ! is_wp_error( $response ) ) {
			$response_body = wp_remote_retrieve_body( $response );

			// If the response is ok, cache each item.
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				foreach ( json_decode( $response_body )->ItemsResult->Items as $item ) {
					// phpcs:ignore
					$tracking_id = $arr[$item->ASIN]['tracking_id'];

					$cache_keys = Product::generate_cache_keys( $item->ASIN, $tracking_id );

					$cache_value = (object) array(
						'ItemsResult' => (object) array(
							'Items' => array(
								$item,
							),
						),
					);

					wp_cache_set( $cache_keys['primary'], wp_json_encode( $cache_value ), '', Product::CACHE_EXPIRY );
				}
			}
		}
	}
}
