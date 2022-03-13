<?php

namespace PMC\Google_Content_Experiments;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Experiment_Loader
 * @package PMC\Google_Content_Experiments
 *
 * Handle the output of scripts/enqueues to run an experiment.
 */
class Loader {

	use Singleton;

	/**
	 * Store a copy of the experiment being output.
	 *
	 * @var array
	 */
	public $current_experiment = array();

	/**
	 * Hook into WordPress to output any configured experiments.
	 *
	 * @param null
	 *
	 * @return bool|null
	 */
	protected function __construct() {
		if ( ! is_admin() ) {
			add_action( 'wp', array( $this, 'action_wp' ) );
		}
	}

	/**
	 * Callback for WordPress' 'wp' action hook.
	 *
	 * @return bool
	 */
	public function action_wp() {

		// This runs on the 'wp' hook because it's the earliest hook available after
		// $wp_query has been setup, and therefor we can properly execute
		// experiment conditions, e.g. is_singular( 'pmc-gallery' ), etc.
		$this->current_experiment = $this->get_current_experiment();

		// Output an experiment if one is enabled/allowed in this view.
		if ( ! empty( $this->current_experiment ) && is_array( $this->current_experiment ) ) {

			// Send the experiment data to GA along with the page's
			// GA pageview event.
			add_action( 'pmc_google_analytics_pre_send_js', array( $this, 'send_experiment_data_to_ga_v2' ), 10, 3 );

			// Enqueue the experiment JavaScript
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_experiment_javascript' ) );

			// Add the experiment and it's variation to the body class list
			// For ease with styling/targeting experiment elements.
			add_filter( 'body_class', array( $this, 'filter_body_class' ), 10, 2 );
		}

		// Ensure this hook only fires once
		remove_action( 'wp', array( $this, 'action_wp' ) );
	}

	/**
	 * Filters the list of CSS body classes for the current post or page.
	 *
	 * @param array $classes An array of body classes.
	 * @param array $class   An array of additional classes added to the body.
	 *
	 * @return array An array of modified body classes.
	 */
	public function filter_body_class( $classes, $class ) {

		$classes[] = 'content-experiment-' . $this->current_experiment['experiment_id'];
		$classes[] = 'content-experiment-variation-' . $this->current_experiment['experiment_variation'];

		return $classes;
	}

	/**
	 * Get the experiment which can output on the current page.
	 *
	 * @param null
	 *
	 * @return bool|array False on failure. An array of experiment data on success.
	 *                    [
	 *                        'experiment_id'        => 'wYdqtPuLT-yAaUqoYXJUnA'
	 *                        'experiment_name'      => 'PPT-7079 - Sitcky Nav Experiment',
	 *                        'experiment_js_src'    => 'http://some.file.js',
	 *                        'experiment_condition' => true,
	 *                        'experiment_variation' => 3
	 *                    ]
	 */
	public function get_current_experiment() {

		// Get an array of all the currently-registered experiments.
		$registered_experiments = pmc_get_registered_content_experiments();
		if ( empty( $registered_experiments ) || ! is_array( $registered_experiments ) ) {
			return false;
		}

		// Get an array of the currently-enabled experiments
		// (enabled via CheezCap)
		$enabled_experiments = pmc_get_enabled_content_experiments();
		if ( empty( $enabled_experiments ) || ! is_array( $enabled_experiments ) ) {
			return false;
		}

		$experiment_to_run = false;

		// Loop through the registered experiments, see if they're enabled,
		// check them for valid data, and return the last experiment which
		// validates all the checks/experiment conditions.
		foreach( $registered_experiments as $experiment_id => $experiment_data ) {
			if ( in_array( $experiment_id, $enabled_experiments ) ) {

				// If an experiment does not have a condition it will output on each page load
				// Passing true or false in the 'experiment_condition' allows for targeting
				// specific pages. You can use assertion checks like is_singular( 'pmc-gallery' )
				// Example: 'experiment_condition' => is_archive() && PMC::is_mobile()
				//
				// Purposefully using isset() here because the value may be false (which is OK)
				// If the experiment condition is false do not output the experiment on the current page
				if ( isset( $experiment_data['experiment_condition'] ) && false === $experiment_data['experiment_condition'] ) {
					continue;
				}

				// Only one experiment can output at a time/per page.
				// If multiple experiments are properly configured/get through
				// the above, the last experiment will be used/run on the current page.
				$experiment_to_run = $experiment_data;

				// Add the experiment id and type onto the array we'll return.
				$experiment_to_run['experiment_id'] = $experiment_id;
			}
		}

		if ( empty( $experiment_to_run ) || ! is_array( $experiment_to_run ) ) {
			return false;
		}

		// Get the variation for the current experiment. Our JavaScript
		// will store this variation in a cookie for reuse later on.
		// We set the cookie with JS because this PHP is executed too
		// late to send additional headers.
		$api = API::get_instance();
		$api->set_id( $experiment_to_run['experiment_id'] );
		$experiment_to_run['experiment_variation'] = $api->get_variation( $experiment_to_run['experiment_total_variants'] );

		if ( ! isset( $experiment_to_run['experiment_variation'] ) || false === $experiment_to_run['experiment_variation'] ) {

			/**
			 * If a registered experiment is not 'Running' in GA the experiment
			 * will not be run here and false is returned. This can make it difficult
			 * to build/debug experiments which are not setup in GA yet.
			 * The following filter was added so this function can be tested with
			 * phpunit and a dummy experiment. Ideally, once api.php has been setup
			 * to use the Analytics Management API we can use it to create/remove
			 * the dummy experiment.
			 *
			 * @param bool  $fail_if_experiment_not_in_ga Defaults to true.
			 * @param array $experiment_to_run            Experiment-to-run's data.
			 */
			if ( apply_filters( 'pmc_google_content_experiment_not_running_in_ga', true, $experiment_to_run ) ) {
				return false;
			}
		}

		// Ensure our variation is reliably an integer
		$experiment_to_run['experiment_variation'] = intval( $experiment_to_run['experiment_variation'] );

		return $experiment_to_run;
	}

	/**
	 * Output ga( 'set' ) calls for the chosen variation and exp id.
	 *
	 * @param array  $dimensions                    The current GA dimmensions.
	 * @param array  $dimension_map                 The current mapping of GA dimmensions to their named values.
	 * @param string $ga_dimmensions_JS_object_name The name of the JS object which contains the GA dimmensions
	 *
	 * @param null
	 *
	 * @return bool True on success, false on failure.
	 */
	public function send_experiment_data_to_ga( $dimensions = array(), $dimension_map = array(), $ga_dimmensions_object = '' ) {
		if ( empty( $this->current_experiment ) || ! is_array( $this->current_experiment ) ) {
			return false;
		}

		?>
		ga( 'set', 'expId', <?php echo wp_json_encode( $this->current_experiment['experiment_id'] ); ?> );
		ga( 'set', 'expVar', <?php echo wp_json_encode( $this->current_experiment['experiment_variation'] ); ?> );
		<?php

		return true;
	}

	/**
	 * Output ga( 'set' ) calls for the chosen variation and exp id.
	 *
	 * @param array  $dimensions                    The current GA dimmensions.
	 * @param array  $dimension_map                 The current mapping of GA dimmensions to their named values.
	 * @param string $ga_dimmensions_JS_object_name The name of the JS object which contains the GA dimmensions
	 *
	 * @param null
	 *
	 * @return bool True on success, false on failure.
	 */
	public function send_experiment_data_to_ga_v2( $dimensions = array(), $dimension_map = array(), $ga_dimmensions_object = '' ) {
		if ( empty( $this->current_experiment ) || ! is_array( $this->current_experiment ) ) {
			return false;
		}

		$ga_exp_set_var = $this->current_experiment['experiment_id'] . '.' . $this->current_experiment['experiment_variation'];
		?>
		ga( 'set', 'exp', <?php echo wp_json_encode( $ga_exp_set_var ); ?> );
		<?php

		return true;
	}

	/**
	 * Enqueue the experiment loader JS, the data it uses,
	 * and possibly an experiment JS file if there is one.
	 *
	 * @param null
	 *
	 * @return bool True on success, false on failure.
	 */
	public function enqueue_experiment_javascript() {

		// Every running experiment will have localized data
		// and the experiment loader JS will be enqueued.
		// However, only some experiments will have their own
		// JS file of experiment variations. The logic below
		// determines if that's the case, and ensures the data
		// is localized to the correct JS enqueue handle.
		if ( empty( $this->current_experiment ) || ! is_array( $this->current_experiment ) ) {
			return false;
		}

		$handle_for_localized_data = 'pmc-google-content-experiment';
		$experiment_loader_dependencies = array( 'jquery' );

		if ( ! empty( $this->current_experiment['experiment_js_src'] ) ) {
			$variations_script_handle = 'pmc-google-content-experiment-variations';
			$handle_for_localized_data = $variations_script_handle;
			$experiment_loader_dependencies[] = $variations_script_handle;

			wp_enqueue_script(
				$variations_script_handle,
				$this->current_experiment['experiment_js_src'],
				array( 'jquery' )
			);
		}

		wp_enqueue_script(
			'pmc-google-content-experiment',
			PMC_GOOGLE_CX_URL . 'js/pmc-google-content-experiments.js',
			$experiment_loader_dependencies
		);

		$localized_data = $this->current_experiment;

		// Also localize an array of the no-longer enabled experiments
		// so that we can remove them from the user cookie—just to clean
		// things up and ensure they receive the correct batcache bucket.
		$disabled_experiments = pmc_get_disabled_content_experiments();
		if ( ! empty( $disabled_experiments ) && is_array( $disabled_experiments ) ) {
			$localized_data['disabled_experiments'] = $disabled_experiments;
		}

		wp_localize_script(
			$handle_for_localized_data,
			'pmc_google_content_experiment',
			$localized_data
		);

		return true;
	}
}

// EOF
