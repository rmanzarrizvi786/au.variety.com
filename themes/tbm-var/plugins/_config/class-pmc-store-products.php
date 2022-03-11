<?php
namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Store_Products {

	use Singleton;

	/**
	 * PMC_Store_Products constructor.
	 */
	protected function __construct() {

		define( 'PMC_SP_AMAZON_STORE_ID', 'variety0e8-20' );
		define( 'PMC_SP_AMAZON_API_ACCESS_KEY', 'AKIAIUSNZFZ6JKWWGIEA' );
		define( 'PMC_SP_AMAZON_API_ACCESS_SECRET', '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' );

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() : void {

		add_filter( 'pmc_store_products_amazon_utm_replacements', [ $this, 'filter_amazon_utm_replacements' ] );
		add_filter( 'pmc_store_products_widget_template', [ $this, 'get_widget_template_path' ] );

	}

	/**
	 * Custom affiliate IDs for specific UTM parameters
	 *
	 * @return array
	 */
	public function filter_amazon_utm_replacements() : array {

		return [
			[
				// Apple News
				'utm' => 'utm_campaign=variety-apple-news',
				'id'  => 'varietyapplenews-20',
			],
			[
				// Sharethrough
				'utm' => 'utm_campaign=variety-sharethrough',
				'id'  => 'vysharethrough-20',
			],
			[
				// Cube house ads
				'utm' => 'utm_campaign=variety-cube',
				'id'  => 'vycubeads-20',
			],
		];

	}

	/**
	 * Filter the template used for standalone use of the shortcode.
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function get_widget_template_path( string $template ) : string {

		return sprintf(
			'%s/template-parts/article/buy-now.php',
			untrailingslashit( CHILD_THEME_PATH )
		);

	}

}

// EOF
