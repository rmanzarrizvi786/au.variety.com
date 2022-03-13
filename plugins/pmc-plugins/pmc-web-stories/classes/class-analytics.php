<?php
/**
 * Override settings and add new options to offical web stories plugin.
 *
 * @package pmc-web-stories
 */

namespace PMC\Web_Stories;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Google_Universal_Analytics;


/**
 * Class Analytics
 */
class Analytics {

	use Singleton;

	const FILTER_GA_ACCOUNT_ID = 'pmc_web_stories_ga_account_id';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup Hooks
	 */
	protected function _setup_hooks() {
		add_action( 'web_stories_print_analytics', [ $this, 'web_stories_analytics' ] );
		add_action( 'init', [ $this, 'action_init' ] );
	}

	public function action_init() {
		// Add default GA ID for web stories
		if ( class_exists( \Google\Web_Stories\Settings::class ) ) {
			add_filter( sprintf( 'option_%s', \Google\Web_Stories\Settings::SETTING_NAME_TRACKING_ID ), [ $this, 'filter_set_default_web_story_ga_account_id' ] );
			add_filter( sprintf( 'default_option_%s', \Google\Web_Stories\Settings::SETTING_NAME_TRACKING_ID ), [ $this, 'filter_set_default_web_story_ga_account_id' ] );
		}
	}

	/**
	 * Filter to return default value for web-stories plugin if not defined
	 * @param $value
	 * @return mixed
	 */
	public function filter_set_default_web_story_ga_account_id( $value ) {
		if ( empty( $value ) ) {
			$value = $this->get_ga_account_id();
		}
		return $value;
	}

	/**
	 * Prints the amp-analytics tag on the web-stories single post.
	 */
	public function web_stories_analytics() {

		$config = $this->get_analytics_configuration();

		?>
			<amp-analytics type="googleanalytics" id="ga">
				<script type="application/json">
				<?php echo wp_json_encode( $config ); ?>
				</script>
			</amp-analytics>

		<?php
	}

	/**
	 * Helper function to return ga_id
	 *
	 * @return mixed Google analytics ID.
	 */
	public function get_ga_account_id() {
		return apply_filters( self::FILTER_GA_ACCOUNT_ID, get_option( 'pmc_google_analytics_account' ) );
	}

	/**
	 * Gets the web stories analytics configuration
	 *
	 * @return array An array of analytics configuration.
	 */
	public function get_analytics_configuration() {

		$config = [
			'vars'           => [
				'account' => $this->get_ga_account_id(),
			],
			'extraUrlParams' => $this->get_ga_custom_dimensions(),
			'triggers'       => [
				'trackPageviewWithCustomData' => [
					'label'   => 'trackPageviewWithCustomData',
					'on'      => 'visible',
					'request' => 'pageview',
				],
			],
		];

		/**
		 * Filters the web stories google analytics configuration.
		 *
		 * @param $config array An array of google analytics configuration data.
		 */
		return apply_filters( 'pmc_web_stories_google_analytics_configuration', $config );
	}

	/**
	 * Method to get Google analytics custom dimensions
	 *
	 * @return array
	 */
	public function get_ga_custom_dimensions() {

		return PMC_Google_Universal_Analytics::get_instance()->get_mapped_dimensions();

	}

}
