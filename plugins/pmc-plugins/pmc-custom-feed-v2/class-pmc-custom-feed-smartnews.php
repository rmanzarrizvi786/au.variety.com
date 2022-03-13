<?php
/**
 * Class containing Smartnews feed related template tags
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2015-07-30
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Smartnews {

	use Singleton;

	/**
	 * @var array Array containing current feed's options
	 */
	protected $_feed_options = array();

	/**
	 * Class initialization routine
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * This function sets up hook listeners
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		add_action( 'pmc_custom_feed_item', array( $this, 'on_action_pmc_custom_feed_item' ) );
		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
	}

	/**
	 * Action hook before feed template start
	 * Added filter render_featured_or_first_image_in_post_params and
	 * pmc_custom_feed_render_first_image_in_gallery to NOT render
	 * featured image node <image> and <img /> in item and content:encoded respectively
	 *
	 * @since   2015-10-09
	 * @version 2015-10-09 Archana Mandhare PMCVIP-321
	 *
	 * @return void
	 */
	public function action_pmc_custom_feed_start( $feed = false, $feed_options = false, $template = '' ) {

		// assuming 'smartnews-related-links' is specific to SmartNews feed
		if ( ! $this->_feed_has_option( 'smartnews-related-links' ) ) {
			return;
		}

		add_filter( 'render_featured_or_first_image_in_post_params', '__return_false' );
		add_filter( 'pmc_custom_feed_render_first_image_in_gallery', '__return_false' );
	}

	/**
	 * This function loads current feed's options if not already loaded
	 *
	 * @return void
	 */
	protected function _load_feed_options() {
		if ( empty( $this->_feed_options ) ) {
			$this->_feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
		}
	}

	/**
	 * Helper function to check if an option is enabled for current feed or not
	 *
	 * @param string $option_name
	 *
	 * @return boolean Returns TRUE if option is enabled else FALSE
	 */
	protected function _feed_has_option( $option_name ) {
		$this->_load_feed_options();

		if ( empty( $option_name ) || ! isset( $this->_feed_options[ $option_name ] ) || $this->_feed_options[ $option_name ] !== true ) {
			return false;
		}

		return true;
	}

	/**
	 * This function accepts a post objest and renders the Smartnews related link nodes
	 * if the 'Smartnews: Related Links' feed option is selected for the current feed.
	 *
	 * @param WP_Post $post
	 * @param integer $count Number of related links to render
	 *
	 * @return void
	 */
	public function render_related_links( $post, $count = 3 ) {

		if ( empty( $post->ID ) || ! $this->_feed_has_option( 'smartnews-related-links' ) ) {
			return;
		}

		$related_links = [];

		if ( class_exists( 'PMC\Automated_Related_Links\Plugin' ) ) {
			$instance      = \PMC\Automated_Related_Links\Plugin::get_instance();
			$related_links = $instance->get_related_links( $post->ID );
		}

		if ( ( empty( $related_links ) || ! is_array( $related_links ) ) && function_exists( 'pmc_related_articles' ) ) {
			$related_posts = pmc_related_articles( $post->ID );
			$related_posts = ( ! empty( $related_posts ) && is_array( $related_posts ) ) ? $related_posts : [];

			foreach ( $related_posts as $item ) {
				$related_links[] = [
					'id'        => $item->post_id,
					'title'     => $item->title,
					'url'       => get_permalink( $item->post_id ),
					'image_src' => $item->image_src,
					'automated' => true,
				];
			}

		}

		$count = ( intval( $count ) < 1 ) ? 3 : intval( $count );

		if ( empty( $related_links ) || ! is_array( $related_links ) ) {
			return;
		}

		$related_links = array_slice( $related_links, 0, $count );

		foreach ( $related_links as $item ) {
			$image_src = ( ! empty( $item['image_src'] ) ) ? $item['image_src'] : '';

			if ( empty( $item['image_src'] ) ) {
				$image_src = get_the_post_thumbnail_url( $item['id'], 'related-articles' );
			}

			printf(
				'<snf:relatedLink title="%s" link="%s" thumbnail="%s" />',
				esc_attr( $item['title'] ),
				esc_url_raw( PMC_Custom_Feed_Helper::pmc_feed_add_query_string( $item['url'] ) ),
				esc_url_raw( PMC_Custom_Feed_Helper::esc_xml( $image_src ) )
			);
		}
	}

	/**
	 * Called on 'pmc_custom_feed_item' hook this function outputs Smartnews
	 * related links for current post
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function on_action_pmc_custom_feed_item( $post ) {
		$this->render_related_links( $post );
	}

}    //end of class


//initialize class
PMC_Custom_Feed_Smartnews::get_instance();


//EOF
