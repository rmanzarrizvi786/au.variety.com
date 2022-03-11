<?php
/**
 * Configuration for pmc-google-universal-analytics plugin.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2017-09-19
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use Variety\Inc\Article;
use \Variety\Plugins\Variety_VIP\Content;
use \PMC\Global_Functions\Traits\Singleton;

class PMC_Google_Universal_Analytics {

	use Singleton;

	/**
	 * Construct Method.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 *
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'pmc_google_analytics_cross_domains', [ $this, 'add_cross_domains_for_ga_tracking' ] );

		add_action( 'pmc_google_analytics_custom_dimensions_js', [ $this, 'ga_custom_dimensions_js' ], 10, 2 );

	}



	/**
	 * Filter the list of GA cross domains.
	 *
	 * When populated with domains, a user's GA session will extend through configured domains.
	 * e.g. we add 'pubservice.com' to the list on Variety so that a user session will continue
	 * when they leave variety.com to pubservice.com to purchase a subscription. Likewise, when the GA pageview
	 * is sent on ESP (via Mezzobit - see below), we include 'variety.com' to the list of cross domains.
	 *
	 * @see https://support.google.com/analytics/answer/1034342?hl=en
	 *
	 * @param array An array of domains to include in cross domain tracking.
	 *
	 * @return array
	 */
	public function add_cross_domains_for_ga_tracking( $cross_domains = [] ) {

		$cross_domains[] = 'pubservice.com'; // ESP Domain
		$cross_domains[] = 'doubleclick.net'; // DFP Redirect URLs in Ads

		return $cross_domains;
	}

	/**
	 *
	 *
	 * Add custom dimensions to the GA/UA javascript.
	 *
	 * @param array $dimensions    List of custom dimensions
	 * @param array $dimension_map Indexes of custom dimensions
	 *
	 * @uses filter::pmc_google_analytics_custom_dimensions_js
	 * @see  PMC_Google_Universal_Analytics
	 *
	 */
	public function ga_custom_dimensions_js( $dimensions, $dimension_map ) {

		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_queried_object_id();

		if ( ! Content::is_vip_page() ) {
			$dim_is_vip_content = 'dimension' . $dimension_map['post-options'];
			$variety_post_vip   = Article::get_instance()->is_article_vip( $post_id );
			$value              = 'no';
			if ( true === $variety_post_vip ) {
				$value = 'yes';
			}
			?>
			dim[decodeURIComponent( '<?php echo rawurlencode( $dim_is_vip_content ); ?>' ) ] = '<?php echo esc_js( $value ); ?>';
			<?php
		} else {
			$vip_vertical = wp_get_post_terms( $post_id, Content::VIP_CATEGORY_TAXONOMY );

			$vip_vertical_text = '';
			if ( ! is_wp_error( $vip_vertical ) ) {
				$vip_vertical      = wp_list_pluck( $vip_vertical, 'slug' );
				$vip_vertical_text = implode( '|', $vip_vertical );
			}

			$vip_tag      = wp_get_post_terms( $post_id, Content::VIP_TAG_TAXONOMY );
			$vip_tag_text = '';
			if ( ! is_wp_error( $vip_tag ) ) {
				$vip_tag      = wp_list_pluck( $vip_tag, 'slug' );
				$vip_tag_text = implode( '|', $vip_tag );
			}

			$dim_vip_category_term = 'dimension' . $dimension_map['vertical'];
			$dim_vip_tag_term      = 'dimension' . $dimension_map['category'];

			?>

			dim[decodeURIComponent( '<?php echo rawurlencode( $dim_vip_category_term ); ?>' ) ] = '<?php echo esc_js( $vip_vertical_text ); ?>';
			dim[decodeURIComponent( '<?php echo rawurlencode( $dim_vip_tag_term ); ?>' ) ]  = '<?php echo esc_js( $vip_tag_text ); ?>';


			<?php
		}

	}

}
