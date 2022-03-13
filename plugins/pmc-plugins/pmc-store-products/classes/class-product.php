<?php
// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
// phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCase
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
// phpcs:disable WordPress.VIP.RestrictedFunctions.wp_remote_get_wp_remote_get
// phpcs:disable WordPressVIPMinimum.Performance.LowExpiryCacheTime.LowCacheTime


namespace PMC\Store_Products;

use PMC;
use PMC\EComm\Tracking;

/**
 * Extended details about a product.
 */
class Product {

	/**
	 * Length of time to cache a product.
	 *
	 * @var int
	 */
	const CACHE_EXPIRY = 6 * HOUR_IN_SECONDS; // 6 hours.

	/**
	 * Length of time to cache a product fallback.
	 *
	 * @var int
	 */
	const FAILBACK_CACHE_EXPIRY = 24 * HOUR_IN_SECONDS; // 24 hours.

	/**
	 * Length of time to cache the fallback value in place of a product
	 * to handle a failed request.
	 *
	 * @var int
	 */
	const EMPTY_CACHE_EXPIRY = 3 * MINUTE_IN_SECONDS; // 5 minutes.

	/**
	 * Product ID or SKU, as identified by its source.
	 *
	 * Required.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Title for the object.
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * Description for the object.
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * Retailer for the object.
	 *
	 * @var string
	 */
	public $retailer = null;

	/**
	 * Product URL, as defined by its source.
	 *
	 * @var string
	 */
	public $url = null;

	/**
	 * Product Image URL, as defined by its source.
	 *
	 * @var string
	 */
	public $image_url = null;

	/**
	 * Manufacturer, as identified by its source.
	 *
	 * @var string
	 */
	public $manufacturer = null;

	/**
	 * Category, as identified by its source.
	 *
	 * @var string
	 */
	public $category = null;

	/**
	 * Variant, as identified by its source.
	 *
	 * @var string
	 */
	public $variant = null;

	/**
	 * Price, as identified by its source.
	 *
	 * @var string
	 */
	public $price = null;

	/**
	 * Original Price, as identified by its source.
	 *
	 * @var string
	 */
	public $original_price = null;

	/**
	 * Discount Amount, as identified by its source.
	 *
	 * @var string
	 */
	public $discount_amount = null;

	/**
	 * Discount Percent, as identified by its source.
	 *
	 * @var string
	 */
	public $discount_percent = null;

	/**
	 * Award, as identified by its source.
	 *
	 * @var string
	 */
	public $award = null;

	/**
	 * Rating, as identified by its source.
	 *
	 * @var string
	 */
	public $rating = null;

	/**
	 * Context in which the product is displayed.
	 *
	 * Must be set by the calling controller as it's display-specific.
	 *
	 * @var string
	 */
	public $display_context = null;

	/**
	 * Position for the product's display context.
	 *
	 * Must be set by the calling controller as it's display-specific.
	 *
	 * @var string
	 */
	public $position = null;

	/**
	 * GUID for the product, that hopefully identifies its unique position on page.
	 *
	 * Must be set by the calling controller as it's display-specific.
	 *
	 * @var string
	 */
	public $guid = null;

	/**
	 * Can never be instantiated on its own
	 */
	private function __construct() {
		// no-op
	}

	/**
	 * Create a new product object from its ASIN based on Amazon API response.
	 *
	 * @param string $asin Amazon product ID for the product.
	 * @param string $tracking_id Tag parameter from URL.
	 * @return Product|false
	 */
	public static function create_from_asin( $asin, string $tracking_id = '' ) {

		global $post;

		if ( empty( $asin ) ) {
			return false;
		}

		$item = self::_get_amazon_item_from_asin( $asin, 'Large', $tracking_id );

		if ( empty( $item ) ) {
			return false;
		}

		$product_description = '';

		if ( is_a( $post, 'WP_Post' ) ) {
			$post_products = get_post_meta( $post->ID, '_amzn_product_information', true );

			foreach ( $post_products ?: [] as $post_product ) {
				// @codeCoverageIgnoreStart
				if ( $asin === $post_product['product_id'] ) {
					$product_description = $post_product['description'];
				}
				// @codeCoverageIgnoreEnd
			}
		}

		$product                   = new Product;
		$product->id               = $asin;
		$product->retailer         = 'amazon';
		$product->description      = $product_description;
		$product->title            = '';
		$product->manufacturer     = '';
		$product->url              = '';
		$product->image_url        = '';
		$product->price            = '';
		$product->original_price   = '';
		$product->discount_amount  = '';
		$product->discount_percent = '';
		$product->category         = '';
		$item                      = current( $item );

		if ( ! empty( $item->ItemInfo->Title->DisplayValue ) ) {
			$product->title = sanitize_text_field( $item->ItemInfo->Title->DisplayValue );
		}

		if ( ! empty( $item->ItemInfo->ByLineInfo->Manufacturer->DisplayValue ) ) {
			$product->manufacturer = sanitize_text_field( $item->ItemInfo->ByLineInfo->Manufacturer->DisplayValue );
		}

		if ( ! empty( $item->DetailPageURL ) ) {
			$product->url = sanitize_text_field( $item->DetailPageURL );
		}

		if ( ! empty( $item->Images->Primary->Large->URL ) ) {
			$product->image_url = sanitize_text_field( $item->Images->Primary->Large->URL );
		}

		if ( ! empty( $item->Offers->Listings ) ) {
			foreach ( $item->Offers->Listings as $listing ) {
				if ( 'new' === strtolower( $listing->Condition->Value ) ) {

					if ( ! empty( $listing->Price->Amount ) ) {
						$price          = number_format( (float) $listing->Price->Amount, 2 );
						$product->price = sanitize_text_field( '$' . $price );
					}

					if ( ! empty( $listing->Price->Savings->Amount ) ) {
						$product->discount_amount = sanitize_text_field( '$' . (string) $listing->Price->Savings->Amount );
					}

					if ( ! empty( $listing->Price->Savings->Percentage ) ) {
						$product->discount_percent = sanitize_text_field( (string) $listing->Price->Savings->Percentage . '%' );
					}

					if ( ! empty( $listing->Price->Amount ) && ! empty( $listing->Price->Savings->Amount ) ) {
						$original_price          = (float) $listing->Price->Amount + (float) $listing->Price->Savings->Amount;
						$original_price          = number_format( (float) $original_price, 2 );
						$product->original_price = sanitize_text_field( '$' . $original_price );
					}

					break;
				}
			}
		}

		if ( ! empty( $item->BrowseNodeInfo->BrowseNodes[0]->DisplayName ) ) {
			$product->category = sanitize_text_field( $item->BrowseNodeInfo->BrowseNodes[0]->DisplayName );
		}

		if ( ! empty( $item->ParentASIN ) ) {
			$variant_items = static::_get_amazon_item_from_asin( $item->ParentASIN, 'Variations' );
			$variant       = sanitize_text_field( $item->ParentASIN );

			if ( is_array( $variant_items ) ) {
				$variants = [];

				foreach ( $variant_items as $variant_item ) {
					if ( ! $product->price && ! empty( $variant_item->Offers->Listings ) ) {
						foreach ( $variant_item->Offers->Listings as $listing ) {
							if ( 'new' === strtolower( $listing->Condition->Value ) ) {

								if ( ! empty( $listing->Price->Amount ) ) {
									$price          = number_format( (float) $listing->Price->Amount, 2 );
									$product->price = sanitize_text_field( '$' . $price );
								}

								break;
							}
						}
					}
					$variants[] = sanitize_text_field( $variant_item->ASIN );
				}
				$variant .= ';' . implode( ',', $variants );
			}

			$product->variant = $variant;
		}

		if ( ! empty( $product->url ) ) {
			$product->url = Tracking::get_instance()->track( $product->url );
		}

		return $product;

	}

	/**
	 * Get Amazon product data based on its ASIN.
	 *
	 * @param string $asin           Amazon product ID for the project.
	 * @param string $response_group Which blob of data to return.
	 * @param string $tracking_id    Tag parameter from URL.
	 * @return object|false
	 */
	protected static function _get_amazon_item_from_asin( $asin, $response_group = 'Large', string $tracking_id = '' ) {

		// ::create_from_asin() only ever passes 'Large', 'Variations' into here.
		// @codeCoverageIgnoreStart
		if ( ! in_array( $response_group, array( 'Large', 'Variations' ), true ) ) {
			$response_group = 'Large';
		}
		// @codeCoverageIgnoreEnd

		$cache_keys = self::generate_cache_keys( $asin, $tracking_id, $response_group );

		$cache_value = wp_cache_get( $cache_keys['primary'] );

		// If the primary cache doesn't exist, then $cache_value===false.
		// However, if the most recent API request failed, then $cache_value==='';
		if ( false !== $cache_value ) {
			// If the primary cache was '', then attempt to use failback value.
			if ( empty( $cache_value ) ) {
				$cache_value = wp_cache_get( $cache_keys['failback'] );
				// No sense in trying to extract
				// if the value is empty.
				if ( empty( $cache_value ) ) {
					return false;
				}
			}

			return static::_api_5_get_amazon_item_from_response( $cache_value );
		}

		return static::_api_5_get_amazon_item( $asin, $tracking_id, $response_group, $cache_keys['primary'], $cache_keys['failback'] );

	}

	/**
	 * Generate cache keys in a standardized manner.
	 *
	 * @param string $asin Product ASIN ID.
	 * @param ?string $tracking_id Product tracking ID.
	 * @param ?string $response_group Product response group.
	 * @return array
	 */
	public static function generate_cache_keys( string $asin, ?string $tracking_id = '', ?string $response_group = 'Large' ): array {
		$primary_cache_key  = 'pmc_sp_amazon_product_' . $asin . '_' . $tracking_id . '_' . $response_group;
		$failback_cache_key = $primary_cache_key . '_failback';

		// append for API 5.0
		$primary_cache_key  .= '_5';
		$failback_cache_key .= '_5';

		return [
			'primary'  => $primary_cache_key,
			'failback' => $failback_cache_key,
		];
	}

	/**
	 * Get the Amazon item object from its response.
	 *
	 * @param $asin
	 * @param $tracking_id
	 * @param $response_group
	 * @param $primary_cache_key
	 * @param $failback_cache_key
	 *
	 * @return bool
	 */
	protected static function _api_5_get_amazon_item( $asin, $tracking_id, $response_group, $primary_cache_key, $failback_cache_key ) {

		$associate_tag = ( ! empty( $tracking_id ) ) ? $tracking_id : pmc_sp_get_amazon_store_id();

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

		$operator = ( 'Variations' === $response_group ) ? 'GetVariations' : 'GetItems';
		$api      = new Amazon_Api( [ $asin ], $associate_tag, $resources, $operator );

		$args = [
			'headers' => $api->get_headers(),
			'body'    => $api->get_payload(),
		];

		$response      = wp_safe_remote_post( $api->url, $args );
		$response_body = '';

		if ( ! is_wp_error( $response ) ) {
			$response_body = wp_remote_retrieve_body( $response );

			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$cache_expiry = self::CACHE_EXPIRY;
				wp_cache_set( $failback_cache_key, $response_body, '', self::FAILBACK_CACHE_EXPIRY );
			} else {
				// Empty response body will cause failback value to be used.
				$response_body = '';
				$cache_expiry  = self::EMPTY_CACHE_EXPIRY;
			}
			wp_cache_set( $primary_cache_key, $response_body, '', $cache_expiry );
			// Use the failback value if the response was errored.
			if ( empty( $response_body ) ) {
				$response_body = wp_cache_get( $failback_cache_key );
			}
		}

		return self::_api_5_get_amazon_item_from_response( $response_body );

	}

	/**
	 * Get the Amazon item object from its response body.
	 *
	 * @param $response_body
	 *
	 * @return bool
	 */
	protected static function _api_5_get_amazon_item_from_response( $response_body ) {

		$items = false;

		$endpoints = [
			'ItemsResult',
			'VariationsResult',
		];

		if ( ! empty( $response_body ) ) {
			foreach ( $endpoints as $endpoint ) {
				if ( false !== strpos( $response_body, $endpoint ) ) {
					$results = json_decode( $response_body );

					if ( is_array( $results->$endpoint->Items ) ) {
						$items = $results->$endpoint->Items;
					}

				}
			}

		}

		return $items;

	}

}
