<?php

use PMC\Google_Content_Experiments as CX;

/**
 * Get an array of all the currently-registered experiments.
 *
 * @param null
 *
 * @return array An empty array if there are no registered experiments.
 *               An array of all the currently-registered experiments.
 *               NOTE: experiment_condition is executed on the 'wp' action hook!
 *
 *               Keyed by the experiment ids. Example:
 *
 *               [
 *                   'Aa2rX5sfTIq0jVH9Va3V0A' => [
 *                       'experiment_name'      => 'PPT-7046 - Right Rail Promotion Conversion',
 *                       'experiment_js_src'    => 'http://path.to.experiment.js',
 *                       'experiment_condition' => true,
 *                   ],
 *                   'alOdA9KxR_2qquRwKiHN_A' => [
 *                       'experiment_name'      => 'PPT-7051 - Interactions in Galleries',
 *                       'experiment_js_src'    => 'http://path.to.another.experiment.js',
 *                       'experiment_condition' => false,
 *                   ],
 *                   '2UhVzh5MQumULRDr5C0DVA' => [
 *                      'experiment_name'      => 'Testing Serverside Experiment',
 *                      'experiment_condition' => is_singular( 'post' ) && ! PMC::is_mobile(),
 *                   ]
 *                   ...
 *               ]
 */
function pmc_get_registered_content_experiments() {
	return apply_filters( 'pmc_google_content_experiments', array() );
}

/**
 * Simple fetcher to grab the currently-enabled experiment via CheezCap
 *
 * @param null
 *
 * @return array An array of currently-enabled experiment ids. Example:
 *               [
 *                  0 => 'Aa2rX5sfTIq0jVH9Va3V0A',
 *                  1 => '2UhVzh5MQumULRDr5C0DVA',
 *               ]
 */
function pmc_get_enabled_content_experiments() {
	return get_option( 'cap_pmc_google_cx_enabled_experiment' );
}

/**
 * Get an array of all the currently-disabled experiments.
 *
 * @param null
 *
 * @return array An array of the currently-disabled experiment ids. Example:
 *                    [
 *                        0 => 'Aa2rX5sfTIq0jVH9Va3V0A',
 *                        1 => '2UhVzh5MQumULRDr5C0DVA',
 *                    ]
 *               An empty array when there are no currently-disabled experiments.
 */
function pmc_get_disabled_content_experiments() {
	// Get an array of all the currently-registered experiments.
	$registered_experiments = pmc_get_registered_content_experiments();
	if ( empty( $registered_experiments ) || ! is_array( $registered_experiments ) ) {
		return array();
	}

	// Get an array of the currently-enabled experiments
	// (enabled via CheezCap)
	$enabled_experiments = pmc_get_enabled_content_experiments();
	if ( empty( $enabled_experiments ) || ! is_array( $enabled_experiments ) ) {
		$enabled_experiments = array();
	}

	$disabled_experiments = false;

	// Loop through the registered experiments, see if they're enabled,
	// check them for valid data, and return the last experiment which
	// validates all the checks/experiment conditions.
	foreach( $registered_experiments as $experiment_id => $experiment_data ) {
		if ( ! in_array( $experiment_id, $enabled_experiments ) ) {
			$disabled_experiments[] = $experiment_id;
		}
	}

	if ( ! empty( $disabled_experiments ) ) {
		return $disabled_experiments;
	}

	return array();
}

/**
 * Check if a specific experiment is running (on the current page).
 *
 * NOTE! This function will only work after the 'wp' action
 * has finished executing.
 *
 * @param string $experiment_id The experiment ID to fetch data for.
 *
 * @return bool
 */
function pmc_is_content_experiment_running( $experiment_id = '' ) {

	if ( empty( $experiment_id ) ) {
		return false;
	}

	$loader = CX\Loader::get_instance();

	if ( empty( $loader->current_experiment ) || ! is_array( $loader->current_experiment ) ) {
		return false;
	}

	if ( $experiment_id === $loader->current_experiment['experiment_id'] ) {
		return true;
	}

	return false;
}

/**
 * Get the currently-running experiment data.
 *
 * NOTE! This function will only work after the 'wp' action
 * has finished executing.
 *
 * Example:
 *
 * if ( $experiment = pmc_get_current_content_experiment( '2UhVzh5MQumULRDr5C0DVA' ) ) {
 *     if ( ! empty( $experiment ) && is_array( $experiment ) ) {
 *         switch ( $experiment['experiment_variation'] ) {
 *             case 0 :
 *                 // do nothing-this is the original site
 *             break;
 *             case 1 :
 *                 // do something to output variation 1
 *             break;
 *             case 2 :
 *                 // do something to output variation 2
 *             break;
 *             ... etc etc
 *         }
 *     }
 * }
 *
 * @param string $experiment_id The experiment ID to fetch data for.
 *
 * @return array|bool Array of experiment data on success.
 *                    False if the experiment is not enabled/or not running on the current page.
 *                    Example Array:
 *                    [
 *                        'experiment_id'        => 'wYdqtPuLT-yAaUqoYXJUnA'
 *                        'experiment_name'      => 'PPT-7079 - Sitcky Nav Experiment',
 *                        'experiment_js_src'    => 'http://some.file.js',
 *                        'experiment_condition' => true,
 *                        'experiment_variation' => 3
 *                    ]
 */
function pmc_get_current_content_experiment() {
	$loader = CX\Loader::get_instance();
	$current_experiment = $loader->current_experiment;

	if ( empty( $current_experiment ) || ! is_array( $current_experiment ) ) {
		return false;
	}

	return $current_experiment;
}

// EOF