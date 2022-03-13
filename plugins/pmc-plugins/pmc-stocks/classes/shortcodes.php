<?php
/**
 * Class contains PMC Stocks Shortcodes related functions.
 *
 * @since 2016-07-18 - Mike Auteri - PPT-6906
 * @version 2016-07-18 - Mike Auteri - PPT-6906
 */
namespace PMC\Stocks;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Shortcodes {

	use Singleton;

	protected function __construct() {
		add_shortcode( 'pmc_stocks_graph', array( $this, 'stocks_graph' ) );
		add_shortcode( 'pmc_stocks_table', array( $this, 'stocks_table' ) );
	}

	/**
	 * Shortcode function from PMC Stocks Graph.
	 *
	 * @see pmc_stocks_graph
	 * @param $atts
	 * @return string
	 */
	public function stocks_graph( $atts ) {
		$api = Api::get_instance();

		$data = $api->stock_index_data( '3 months ago' );
		$summary = $api->stock_summary_data();

		if ( empty( $data ) || ! is_array ( $data ) ) {
			return '<!-- Error, no data was returned to render graph -->';
		}

		Plugin::get_instance()->enqueue();

		return PMC::render_template( PMC_STOCKS_ROOT . '/assets/templates/graph.php', array(
			'data'      => $data,
			'summary'   => $summary,
			'loader'    => plugins_url( '../assets/images/loader.svg', __FILE__ ),
		) );
	}

	/**
	 * Shortcode function from PMC Stocks Table.
	 *
	 * @see pmc_stocks_table
	 * @param $atts
	 * @return string
	 */
	public function stocks_table( $atts ) {
		$api = Api::get_instance();

		$data = $api->stock_data();
		$regions = get_terms( array(
			'taxonomy' => 'pmc-stock-region',
			'hide_empty' => false,
		) );
		$regions_list = array( 'all regions' );

		foreach ( $regions as $region ) {
			if ( empty( $region->name ) ) {
				continue;
			}
			$regions_list[] = $region->name;
		}

		$categories = get_terms( array(
			'taxonomy' => 'pmc-stock-category',
			'hide_empty' => false,
		) );
		$categories_list = array( 'all categories' );

		foreach ( $categories as $category ) {
			if ( empty( $category->name ) ) {
				continue;
			}
			$categories_list[] = $category->name;
		}

		Plugin::get_instance()->enqueue();

		return PMC::render_template( PMC_STOCKS_ROOT . '/assets/templates/table.php', array(
			'data'       => $data,
			'regions'    => $regions_list,
			'categories' => $categories_list,
		) );
	}
}

// EOF
