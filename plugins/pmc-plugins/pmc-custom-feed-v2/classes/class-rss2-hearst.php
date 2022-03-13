<?php
/**
 * This class adds feed configuration for Hearst Feed ( feed-rss2-hearst.php ).
 */

namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Handles Hearst Feed ( feed-rss2-hearst.php ) related feed changes.
 */
class RSS2_Hearst {

	use Singleton;

	/**
	 * Class initialization routine.
	 */
	protected function __construct() {
		add_action( 'pmc_custom_feed_start', [ $this, 'action_pmc_custom_feed_start' ], 10, 3 );
	}

	/**
	 * Action hook fired before feed template start.
	 *
	 * @param string $feed         Custom feed post.
	 * @param array  $feed_options Custom feed options.
	 * @param string $template     Custom feed template.
	 */
	public function action_pmc_custom_feed_start( $feed = false, $feed_options = false, $template = '' ) {

		// If the current feed is not for RRS2 Hearst Feed then bailout.
		if ( 'feed-rss2-hearst.php' !== $template ) {
			return;
		}

		/**
		 * Register filters to configure feed.
		 */

		// Append line break at the end of the related links.
		add_filter( 'pmc_custom_feed_related_posts_surrounding_html', array( $this, 'filter_related_posts_surrounding_html' ) );

	}

	/**
	 * Append line break at the end.
	 *
	 * @param array $surrounding_html_tags Related links html.
	 *
	 * @return string
	 */
	public function filter_related_posts_surrounding_html( $surrounding_html_tags ) {

		// Append line break at the end of the related links.
		if ( ! empty( $surrounding_html_tags ) && is_array( $surrounding_html_tags ) && isset( $surrounding_html_tags['after'] ) ) {
			$surrounding_html_tags['after'] .= '<br>';
		}

		return $surrounding_html_tags;
	}

}

RSS2_Hearst::get_instance();

// EOF
