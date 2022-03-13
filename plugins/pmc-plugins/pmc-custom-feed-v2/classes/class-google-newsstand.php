<?php
/**
 * This class adds feed configuration for Google Newsstand Feeds.
 */

namespace PMC\Custom_Feed;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Handles Google News Stand related feed changes.
 */
class GoogleNewsStand {

	use Singleton;

	/**
	 * Class initialization routine.
	 *
	 * @codeCoverageIgnore
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

		// If the current feed is not for Google Newsstand then bailout.
		if ( 'feed-rss2.php' !== $template || ! isset( $feed_options['is-google-newsstand-gallery'] ) || true !== $feed_options['is-google-newsstand-gallery'] ) {
			return;
		}

		// Register filters to configure feed.
		add_filter( 'pmc_custom_feed_rss2_required_shortcodes', array( $this, 'filter_required_shortcodes' ), 10, 3 );

	}

	/**
	 * Whitelist required shortcodes.
	 *
	 * @param array       $required_shortcodes List of shortcodes tp whitelist.
	 * @param WP_POST     $post Current Post Object.
	 * @param array|mixed $feed_options Feed configuration settings.
	 *
	 * @return array
	 */
	public function filter_required_shortcodes( $required_shortcodes, $post, $feed_options ) {
		$required_shortcodes[] = 'caption';
		return array_unique( (array) $required_shortcodes );
	}

}

GoogleNewsStand::get_instance();

// EOF
