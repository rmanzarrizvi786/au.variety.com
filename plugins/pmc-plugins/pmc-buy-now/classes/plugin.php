<?php
/**
 * Syntax usage in template to add tracking info to buy-now button:
 *
 * 1. Auto track click event; all click is auto tracked via this method
 *      <a href="..." <?php do_action( 'pmc_do_render_buy_now_ga_tracking_attr', $variables, $defaults ); ?>>...</a>
 * 2. Add ga-data attribute to the element. Doesn't do auto track; track must be custom implemented
 *      <a href="..." <?php do_action( 'pmc_do_render_buy_now_ga_data_attr', $variables, $defaults ); ?>>...</a>
 */

namespace PMC\Buy_Now;

use PMC\Global_Functions\Traits\Singleton;

class Plugin {
	use Singleton;

	protected function __construct() {
		$this->_setup_hooks();
		$this->_init_plugins();
	}

	/**
	 * Initialize the plugin by instantiate all required singleton class objects
	 */
	protected function _init_plugins() {
		Admin_UI::get_instance();
		Frontend::get_instance();
	}

	/**
	 * Setup all necessary wp action/filter hooks
	 */
	protected function _setup_hooks() {
		add_action( 'pmc_do_render_buy_now_ga_tracking_attr', [ $this, 'render_buy_now_ga_tracking_attr' ], 10, 2 );
		add_action( 'pmc_do_render_buy_now_ga_data_attr', [ $this, 'render_buy_now_ga_data_attr' ], 10, 2 );
		add_filter( 'pmc_get_buy_now_ga_data', [ $this, 'get_ga_data' ], 10, 2 );
	}

	/**
	 * Do not call this method directly, use do_action( 'pmc_do_render_buy_now_ga_tracking_attr', $ga_data, $variables );
	 * @param $ga_data
	 * @param array $variables
	 * @return string
	 */
	public function render_buy_now_ga_tracking_attr( $variables, $defaults = [] ) : void {
		$ga_data = $this->get_ga_data( (array) $variables, (array) $defaults );
		do_action( 'pmc_do_render_custom_ga_tracking_attr', $ga_data );
	}

	/**
	 * Do not call this method directly, use do_action( 'pmc_do_render_buy_now_ga_data_attr', $ga_data, $variables );
	 * @param $ga_data
	 * @param array $variables
	 * @return string
	 */
	public function render_buy_now_ga_data_attr( $variables, $defaults = [] ) : void {
		$ga_data = $this->get_ga_data( (array) $variables, (array) $defaults );
		do_action( 'pmc_do_render_custom_ga_data_attr', $ga_data );
	}


	/**
	 * Helper function to generate the ga_data used for buy now button tracking
	 *
	 * @param array $args     This array usually coming from the buy now button template $variablese and/or shortcode $atts
	 * @param array $defaults Override any default set by the helper function
	 * @return array
	 * [
	 *   'category' => '',        // event category
	 *   'details'  => true,      // auto extract the <a> link text content for event label
	 *   'label'    => 'buy-now', // The event label
	 *   'url'      => true,      // auto extract the link url for event label
	 *    'product' => [
	 *       'category' => '',         // product's category
	 *       'name'     => '',         // product's title
	 *       'price'    => '',         // product's price
	 *       'url'      => '%=href=%', // auto extra the product link
	 *    ],
	 * ]
	 */
	public function get_ga_data( array $args, array $defaults = [] ) : array {
		// @TODO: When buy now button is standardize, these ga tracking should be aligned
		// @see https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#product-data
		// @ref https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#enhanced-ecomm
		$ga_data = [
			'category' => 'article-page',
			'details'  => true,
			'label'    => 'buy-now',
			'url'      => true,
			'product'  => [
				'url' => '%=href=%',
			],
		];

		$ga_data = $this->_override_data( $ga_data, $defaults );

		if ( ! empty( $args['text'] ) ) {
			$ga_data['details']         = $args['text'];
			$ga_data['product']['name'] = $args['text'];
		}
		if ( ! empty( $args['title'] ) ) {
			$ga_data['product']['name'] = $args['title'];
		}
		if ( ! empty( $args['price'] ) ) {
			$ga_data['product']['price'] = trim( str_replace( '$', '', $args['price'] ) );
		}
		if ( ! empty( $args['category'] ) ) {
			$ga_data['product']['category'] = $args['category'];
		}

		if ( ! empty( $args['product'] ) ) {
			$product         = (array) $args['product'];
			$from_to_mapping = [
				'title'    => 'name',
				'id'       => 'id',
				'price'    => 'price',
				'category' => 'category',
			];
			foreach ( $from_to_mapping as $from => $to ) {
				if ( ! empty( $product[ $from ] ) ) {
					$ga_data['product'][ $to ] = $product[ $from ];
				}
			}
		}

		if ( ! empty( $args['ga_tracking'] ) && is_array( $args['ga_tracking'] ) ) {
			$ga_data = $this->_override_data( $ga_data, $args['ga_tracking'] );
		} elseif ( ! empty( $args['ga_data'] ) && is_array( $args['ga_data'] ) ) {
			$ga_data = $this->_override_data( $ga_data, $args['ga_data'] );
		}

		return $ga_data;
	}

	/**
	 * Helper function to apply $override to $data recursively
	 * @param array $data
	 * @param array $override
	 * @return array
	 */
	private function _override_data( array $data, array $override ) : array {
		foreach ( $override as $key => $value ) {
			if ( ! empty( $value ) ) {
				if ( is_array( $value ) ) {
					$tmp = isset( $data[ $key ] ) ? $data[ $key ] : [];
					$tmp = $this->_override_data( $tmp, $value );
					if ( ! empty( $tmp ) ) {
						$data[ $key ] = $tmp;
					}
				} else {
					$data[ $key ] = $value;
				}
			}
		}
		return $data;
	}

}
