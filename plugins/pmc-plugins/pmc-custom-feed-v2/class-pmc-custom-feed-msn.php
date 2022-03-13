<?php

/**
 * Customizations for the MSN feed template
 *
 * This class was initially duplicated from
 * class-pmc-custom-feed-ms.php which handles
 * overrides for the feed-rss2-ext.php template when
 * the 'MS Custom Feed' option is selected.
 *
 * That template was being used to serve MSN feeds
 * and the class adjusted the output to try and meet
 * MSN's needs.
 *
 * However, we needed a dedicated MSN template that
 * had many of those initial overrides baked in.
 * Rather than adding more logic to that template we
 * decided to start fresh and make feed-msn.php and this
 * class for the logic/programmatic pieces.
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_MSN {

	use Singleton;

	public $feed_name = '';
	public $feed_options = array();
	public $template_filename = '';
	public $post_content_embed_links = array();

	/**
	 * Singleton Initialization
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
		 * Actions
		 */
		add_action( 'pmc_custom_feed_start', [ $this, 'action_pmc_custom_feed_start' ], 10, 3 );
		add_action( 'pmc_custom_feed_content', [ $this, 'pmc_add_styling_for_msn_buy_button' ], 11, 4 );
	}

	/**
	 * WordPres hooks fired at the start of the feed
	 *
	 * @param string $feed_name    The current feed name
	 * @param array  $feed_options The current feed custom options
	 * @param string $template_filename     The current feed template filename
	 */
	public function action_pmc_custom_feed_start( $feed_name = '', $feed_options = array(), $template_filename = '' ) {

		// This class is only meant for the feed-msn template
		if ( 'feed-msn.php' !== $template_filename ) {
			return;
		}

		// Store a reference to the feed information
		// so we can use them in our hook callbacks below
		$this->feed_name = $feed_name;
		$this->feed_options = $feed_options;
		$this->template_filename = $template_filename;

		// Do not allow html tags in the feed/post titles
		// and in the excerpt
		add_filter( 'pmc_custom_feed_title', 'strip_tags' );
		add_filter( 'pmc_custom_feed_post_title', 'strip_tags' );
		add_filter( 'the_excerpt_rss', 'strip_tags' );

		// Don't show 'Read More' after the excerpt
		add_filter( 'excerpt_more', '__return_empty_string', 11 );

	}

	/**
	 * Add styling for Buy Button in MSN Feeds if 'msn-feeds-button-styling' custom feed option is checked.
	 *
	 * @param string   $content      post content for feed
	 * @param \string  $feed         current feed name
	 * @param \WP_Post $post         post object being process
	 * @param array    $feed_options array of option for current feed
	 *
	 * @return string
	 */
	public function pmc_add_styling_for_msn_buy_button( $content = '', $feed = '', $post, $feed_options = [] ): string {

		if ( ! empty( $feed_options['msn-feeds-button-styling'] ) ) {
					
			$classes = apply_filters( 'pmc_custom_feed_buy_now_button_css_classname', [ 'pmc-buy-now-button' ] );
			
			if ( ! empty( $classes ) && is_array( $classes ) ) {

				$regex   = '/(class\s?=\s?"[^"]*?(' . implode( '|', $classes ) . ')[^"]*)"/m'; // https://regex101.com/r/DqOY57/1
				$content = preg_replace_callback(
					$regex,
					function ( $matches ) {
						return $matches[1] . ' buynowbtn"';
					},
					$content
				);
			}
		}

		return $content;

	}
}

PMC_Custom_Feed_MSN::get_instance();


// EOF
