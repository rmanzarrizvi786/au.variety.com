<?php
use \PMC\Global_Functions\Traits\Singleton;

/**
 * Customizations for the Yahoo feed template
 */
class PMC_Custom_Feed_Yahoo {

	use Singleton;

	/**
	 * Singleton Initialization
	 *
	 * @codeCoverageIgnore
	 */
	protected function _init() {

		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
		add_action( 'pmc_custom_feed_end', array( $this, 'action_pmc_custom_feed_end' ), 10, 3 );

		// Filter to add the tax query for 'Exclude from Yahoo' Post Option.
		add_filter( 'pmc_custom_feed_config', array( $this, 'pmc_prevent_syndication_to_yahoo' ), 10, 2 );
	}

	/**
	 * WordPress hooks fired at the start of the feed
	 *
	 * @param string $feed_name The current feed name.
	 * @param array  $feed_options The current feed custom options.
	 * @param string $template_filename The current feed template filename.
	 */
	public function action_pmc_custom_feed_start( $feed_name = '', $feed_options = array(), $template_filename = '' ) {

		// This class is only meant for the feed-yahoo template.
		if ( 'feed-yahoo.php' !== $template_filename ) {
			return;
		}

		// Show WP default markup for image caption shortcode.
		add_filter( 'img_caption_shortcode', '__return_empty_string', 12 );

		add_filter( 'pmc_custom_feed_allow_shortcodes', [ $this, 'filter_allow_shortocodes' ] );
	}


	/**
	 * Fiter shortcode allow list
	 *
	 * @param array $arr Array of allow shortcodes for yahoo feed content.
	 *
	 * @return array
	 */
	public function filter_allow_shortocodes( $arr ) {

		$arr[] = 'caption';
		$arr[] = 'wp_caption';

		return $arr;
	}

	/**
	 * WordPress hooks fired at the end of the feed
	 *
	 * @param string $feed_name The current feed name.
	 * @param array  $feed_options The current feed custom options.
	 * @param string $template_filename The current feed template filename.
	 */
	public function action_pmc_custom_feed_end( $feed_name = '', $feed_options = array(), $template_filename = '' ) {

		// This class is only meant for the feed-yahoo template.
		if ( 'feed-yahoo.php' !== $template_filename ) {
			return;
		}

		// Remove filter added for feed to show WP default markup for image caption shortcode.
		remove_filter( 'img_caption_shortcode', '__return_empty_string', 12 );

		remove_filter( 'pmc_custom_feed_allow_shortcodes', [ $this, 'filter_allow_shortocodes' ] );
	}

	/**
	 * This will prevent the posts from syndicating to yahoo
	 * if they have the 'Exclude from Yahoo option' Checkbox Ticked.
	 * */
	public function pmc_prevent_syndication_to_yahoo( $options, $key ) {

		// If the $key is not 'taxonomy', return the $options.
		if ( 'taxonomy' !== $key ) {

			return $options;
		}

		// Get the current template.
		$template = get_post_meta( get_queried_object_id(), '_pmc_custom_feed_template', true );

		// If the template is not 'feed-yahoo', return the $options.
		if ( 'feed-yahoo' !== $template ) {

			return $options;
		}

		// Set the value for return
		$updated_options = '_post-options:exclude-from-yahoo:NOT IN';

		// If the user has entered data in Taxonomy field in PMC Feeds from the wp-admin, it will be concatinated along with that.
		if ( ! empty( $options ) ) {

			// Usage: tax_query1|tax_query2.
			// There should be no space in the | operator.
			// Reference of usage: https://confluence.pmcdev.io/display/PMCKB/PMC+Feeds+Tool+-+Configuration+Guide#PMCFeedsToolConfigurationGuide-TaxonomyExamples
			$updated_options = $options . '|' . $updated_options;
		}

		// If the Taxonomy field is empty.
		return $updated_options;

	}
}

PMC_Custom_Feed_Yahoo::get_instance();
