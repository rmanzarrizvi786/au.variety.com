<?php
/**
 * Class for admin section.
 *
 * @since 2018-04-10
 *
 * @package pmc-apple-news
 */

namespace PMC\Apple_News;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Apple_News\Content_Filter;

class Admin {

	use Singleton;

	/**
	 * Construct Mehod.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'apple_news_section_settings', array( $this, 'add_advanced_setting_option' ), 10, 2 );
		add_filter( 'apple_news_section_groups', array( $this, 'add_advanced_section_groups' ), 10, 2 );
		add_filter( 'apple_news_section_taxonomy', array( $this, 'get_news_section_taxonomy' ) );

		add_action( 'admin_init', [ $this, 'add_post_option' ] );

	}

	/**
	 * To add setting in advanced section in Apple news settings page.
	 *
	 * @filter apple_news_section_settings
	 *
	 * @param  array  $settings List of settings.
	 * @param  string $pages settings page slug.
	 *
	 * @return array List of settings.
	 */
	public function add_advanced_setting_option( $settings, $pages ) {

		if ( 'apple-news-options' !== $pages ) {
			return $settings;
		}

		if ( empty( $settings ) || ! is_array( $settings ) ) {
			$settings = array();
		}

		$vertical_taxonomy_slug = 'vertical';

		$taxonomies = array(
			'category',
			'post_tag',
		);

		if ( taxonomy_exists( $vertical_taxonomy_slug ) ) {
			$taxonomies[] = $vertical_taxonomy_slug;
		}

		$default_apple_news_amazon_ecommerce_tag = Content_Filter::get_instance()->get_default_amazon_ecommerce_affiliate_code();
		if ( empty( $default_apple_news_amazon_ecommerce_tag ) ) {
			$default_apple_news_amazon_ecommerce_tag = __( 'N/A', 'pmc-apple-news' );
		}

		$settings['section_taxonomy'] = array(
			'label'       => __( 'Section taxonomy', 'pmc-apple-news' ),
			'type'        => $taxonomies,
			'description' => __( 'Choose how you want your Apple News sections be organized by.', 'pmc-apple-news' ),
		);

		$settings['use_seo_title'] = array(
			'label'       => __( 'use SEO title?', 'pmc-apple-news' ),
			'type'        => array( 'no', 'yes' ),
			'description' => __( 'If set to yes, SEO title to be pulled and displayed on Apple News articles.', 'pmc-apple-news' ),
		);

		$settings['promo_module'] = array(
			'label'       => __( 'Enable Promo module in the article content?', 'pmc-apple-news' ),
			'type'        => array( 'no', 'yes' ),
			'description' => __( 'If set to yes, Promo module will be displayed in Apple News articles.', 'pmc-apple-news' ),
		);

		$settings['promo_module_utm'] = array(
			'label'       => __( 'Promo Module Tracking', 'pmc-apple-news' ),
			'type'        => 'string',
			'description' => __( 'Add UTM query parameter', 'pmc-apple-news' ),
		);

		// New Ecommerce Promo Settings.
		$settings['ecommerce_module'] = [
			'label'       => __( 'Activate Ecommerce Module', 'pmc-apple-news' ),
			'type'        => [ 'no', 'yes' ],
			'description' => __( 'If set to yes, this will use the ecommerce module over the promo module when possiable.', 'pmc-apple-news' ),
		];

		$settings['ecommerce_module_amazon_tag'] = [
			'label'       => __( 'Tag override for Amazon URLs', 'pmc-apple-news' ),
			'type'        => 'string',
			'description' => __( 'This will add the tag query arg to the end of Amazon URLs. Default value if empty: ', 'pmc-apple-news' ) . esc_html( $default_apple_news_amazon_ecommerce_tag ),
			'required'    => false,
		];

		$settings['ecommerce_module_title'] = [
			'label'       => __( 'Title override', 'pmc-apple-news' ),
			'type'        => 'string',
			'description' => __( 'Default value if empty: "Today\'s Top Deal"', 'pmc-apple-news' ),
			'required'    => false,
		];

		$settings['ecommerce_module_description'] = [
			'label'       => __( 'Description override', 'pmc-apple-news' ),
			'type'        => 'string',
			'description' => __( 'Default value if empty: (site title) may receive a commission."', 'pmc-apple-news' ),
			'required'    => false,
		];

		$settings['ecommerce_module_buy_button_text'] = [
			'label'       => __( 'Buy button text override', 'pmc-apple-news' ),
			'type'        => 'string',
			'description' => __( 'Default value if empty: "Buy Now"', 'pmc-apple-news' ),
			'required'    => false,
		];

		$settings['ecommerce_module_display_promo_1_twice'] = [
			'label'       => __( 'Display the first module twice', 'pmc-apple-news' ),
			'type'        => [ 'yes', 'no' ],
			'description' => __( 'If set to yes, the first Ecommerce module will display twice. If set to no, it will display 2 different Ecommerce modules or a normal promo module if nothing is found.', 'pmc-apple-news' ),
		];

		return $settings;
	}

	/**
	 * To add group in advanced section in Apple news settings page.
	 *
	 * @filter apple_news_section_groups
	 *
	 * @param  array  $groups List of group.
	 * @param  string $pages settings page slug.
	 *
	 * @return array List of group.
	 */
	public function add_advanced_section_groups( $groups, $pages ) {

		if ( 'apple-news-options' !== $pages ) {
			return $groups;
		}

		if ( empty( $groups ) || ! is_array( $groups ) ) {
			$groups = array();
		}

		/**
		 * Since, apple-news plugin don't pass argument for which section it's filter is applying.
		 * We can only detect using checking element for "Advanced Section"
		 *
		 * Reference : https://github.com/alleyinteractive/apple-news/blob/master/admin/settings/class-admin-apple-settings-section.php#L168
		 */
		if ( empty( $groups['alerts'] ) ) {
			return $groups;
		}

		$groups['pmc_settings'] = array(
			'label'    => __( 'PMC Settings', 'pmc-apple-news' ),
			'settings' => array( 'section_taxonomy', 'use_seo_title', 'promo_module', 'promo_module_utm' ),
		);

		$groups['pmc_settings_ecommerce_module'] = [
			'label'    => __( 'PMC Ecommerce Module', 'pmc-apple-news' ),
			'settings' => [
				'ecommerce_module',
				'ecommerce_module_amazon_tag',
				'ecommerce_module_title',
				'ecommerce_module_description',
				'ecommerce_module_buy_button_text',
				'ecommerce_module_display_promo_1_twice',
			],
		];

		return $groups;
	}

	/**
	 * To set vertical taxonomy as default taxonomy for Apple news section if exists.
	 *
	 * @param  string $taxonomy_name The taxonomy slug.
	 *
	 * @return string The taxonomy slug.
	 */
	public function get_news_section_taxonomy( $taxonomy_name ) {

		if ( empty( $taxonomy_name ) ) {
			return $taxonomy_name;
		}

		$settings = get_option( 'apple_news_settings', array() );

		if ( ! empty( $settings['section_taxonomy'] ) && taxonomy_exists( $settings['section_taxonomy'] ) ) {
			return $settings['section_taxonomy'];
		}

		return $taxonomy_name;
	}

	/**
	 * Ensure there is an 'Exclude Ecommerce Module' term is available in Post Options.
	 *
	 * @param null
	 *
	 * @return null
	 */
	public function add_post_option() {

		if ( class_exists( '\PMC\Post_Options\Taxonomy', false ) ) {
			\PMC\Post_Options\API::get_instance()->register_global_options(
				[
					'disable-ecommerce-module' => [
						'label'       => __( 'Disable Ecommerce Module', 'pmc-apple-news' ),
						'description' => __( 'When selected, the ecommerce module will not display on this post.', 'pmc-apple-news' ),
					],
				]
			);
		}

	}

}
