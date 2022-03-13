<?php

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Krux_Tag {

	use Singleton;

	protected function __construct() {
		// this filter need to add before init fire so cheezcap can see the filter during init.
		add_filter( 'pmc_global_cheezcap_options', array( $this, "filter_pmc_global_cheezcap_options" ) );
		add_action( 'wp', array( $this, 'action_wp' ) );
		add_action( 'wp_head', array( $this, 'action_wp_head' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_krux_pixel_js' ) );

	}

	public function action_wp() {
		// Only load the Krux tag for US visitors
		if( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV && pmc_geo_get_user_location() != 'us' ) {
			return false;
		}

		if( defined( 'PMC_IS_VIP_GO_SITE' ) && true === PMC_IS_VIP_GO_SITE && pmc_geo_get_user_location() != 'us' ) {
			return false;
		}

		$blocked_post_types = [ 'gallery', 'attachment' ];
		$post_type = get_post_type();

		// Don't load Krux for default wp gallery or attachment pages
		if ( is_single() && in_array( $post_type, (array) $blocked_post_types, true ) ) {
			return false;
		}

		add_action( 'pmc_tags_head', array( $this, 'action_pmc_tags_head' ) );
		add_action('pmc-tags-footer', array( $this, 'action_pmc_tags_footer' ) );
	}

	/**
	 * Get the allowed data attributes (array keys) for Krux.
	 * Each time a new data attribute is added, you need to update this function.
	 * Global attributes should be added to the array here.
	 * LOB/theme specific attributes should be added to the theme using the filter.
	 *
	 * @since 2015-07-03 Corey Gilmore PPT-5136
	 *
	 * @version 2015-07-03 Corey Gilmore Initial version - PPT-5136
	 *
	 * @return array List of key names from PMC_Page_Meta to extract.
	 */
	protected static function get_allowed_data_attribute_keys() {
		$allowed_data_attributes = array(
			'author',
			'category',
			'env',
			'lob',
			'logged-in',
			'page-type',
			'primary-category',
			'primary-vertical',
			'subscriber-type',
			'tag',
			'vertical',
		);

		/**
		 * Filter the list of allowed data attributes for Krux.
		 *
		 * @since 2015-07-03 Corey Gilmore
		 *
		 * @version 2015-07-03 Corey Gilmore Initial version - PPT-5136
		 *
		 * @param array $allowed_data_attributes List of allowed attribute names (array keys) for Krux
		 */
		$allowed_data_attributes = apply_filters( 'pmc_krux_allowed_data_attributes', $allowed_data_attributes );

		return $allowed_data_attributes;

	}

	public function action_wp_head() {
		$meta = PMC_Page_Meta::get_page_meta();

		// Get the filtered list of allowed data attributes
		$allowed_data_attributes = self::get_allowed_data_attribute_keys();

		// Remove any keys that aren't listed in $allowed_data_attributes
		$meta = array_intersect_key( $meta, array_flip( $allowed_data_attributes ) );

		PMC_Scripts::add_script( 'pmc_krux', $meta, 'wp_head', 11 );
	}


	/*
	 * @ticket PPT-4256 Archana Mandhare
	 * @since 2015-04-17 Load Krux pixel for every 5 gallery images
	 * @version 2015/06/25 Javier Martinez PPT-4965 Converted this function into filter
 	 *
	 */
	public function get_krux_gallery_tracking_pixel() {

		if ( 'pmc-gallery' === get_post_type() ) {

			$gallerypost = get_post();

			$title = "&title=" . urlencode( get_the_title( $gallerypost->ID ) );

			$meta = PMC_Page_Meta::get_page_meta();

			$tags_url = "";
			if ( ! empty( $meta['tag'] ) ) {
				foreach ( $meta['tag'] as $tag ) {
					$tags_url .= "&tag=" . urlencode( $tag );
				}
			}

			$verticals_url = "";
			if ( ! empty( $meta['vertical'] ) ) {
				foreach ( $meta['vertical'] as $vertical ) {
					$verticals_url .= "&vertical=" . urlencode( $vertical );
				}
			}

			$categories_url = "";
			if ( ! empty( $meta['category'] ) ) {
				foreach ( $meta['category'] as $category ) {
					$categories_url .= "&category=" . urlencode( $category );
				}
			}

			// create a URL of all of the above data and send it to js
			// Concatenate URL instead of using add_query_arg() to allow duplicate params
			// See PPT-4965 and PPT-4256
			$krux_base_url = 'https://beacon.krxd.net/event.gif?event_id=JsCMkhiz&event_type=cact&pub_id=c500aa57-d425-43d5-867c-ffa47fd2e0dd';
			$krux_tracking_url = $krux_base_url . $title . $tags_url . $verticals_url . $categories_url;

			return $krux_tracking_url;

		} else {
			return false;
		}
	}

	public function enqueue_krux_pixel_js() {
		// get krux config id from cheezcap setting
		$krux_config_id = PMC_Cheezcap::get_instance()->get_option( 'pmc_krux_tag_config_id' );

		// only render template if krux config id is given
		if ( !empty( $krux_config_id ) ) {

			$krux_event_pixels = array(
				'gallery_slide_view' => esc_url_raw( $this->get_krux_gallery_tracking_pixel() ),
			);

			$krux_event_pixels = apply_filters( 'pmc_krux_event_tracking_pixels', $krux_event_pixels );

			if( !empty( $krux_event_pixels ) ) {

				wp_register_script( 'pmc-krux-pixel-js', plugins_url( 'js/krux-pixel.js', __FILE__ ) , array( 'jquery', 'pmc-hooks' ), null, true );

				wp_localize_script( 'pmc-krux-pixel-js', 'krux_event_pixels', $krux_event_pixels );
				wp_enqueue_script( 'pmc-krux-pixel-js' );
			}

		}
	}


	public function action_pmc_tags_head() {
		// get krux config id from cheezcap setting
		$krux_config_id = PMC_Cheezcap::get_instance()->get_option( 'pmc_krux_tag_config_id' );
		// only render template if krux config id is given
		if ( !empty( $krux_config_id ) ) {
			require_once( __DIR__ . '/templates/control-tag-head.php' );
		}
	}

	public function action_pmc_tags_footer() {
		// get krux config id from cheezcap setting
		$krux_config_id = PMC_Cheezcap::get_instance()->get_option( 'pmc_krux_tag_config_id' );
		// only render template if krux config id is given
		if ( !empty( $krux_config_id ) ) {
			require_once( __DIR__ . '/templates/control-tag-footer.php' );
		}
	}

	// add cheezcap options to enter cdn hosts
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {

		if ( empty( $cheezcap_options ) || ! is_array( $cheezcap_options ) ) {
			$cheezcap_options = array();
		}

		$cheezcap_options[] = new CheezCapTextOption(
					'Krux tag configuration id',
					'Enter Krux tag configuration id to activate Krug tags for all pages',
					'pmc_krux_tag_config_id',
					'' // default value
					);

		return $cheezcap_options;

	}

}

if( apply_filters( 'pmc_krux_enabled', true ) ) {
	PMC_Krux_Tag::get_instance();
}

// EOF
