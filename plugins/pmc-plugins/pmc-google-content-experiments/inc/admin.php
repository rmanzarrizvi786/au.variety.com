<?php

namespace PMC\Google_Content_Experiments;

/**
 * Setup the admin interface for enabling/disabling experiments.
 * Adds the found-experiments in a CheezCap checkbox fieldset to
 * enable/disable the experiments.
 */

/**
 * Filter the 'Global Theme Options' cheezcap group
 *
 * @param array $cheezcap_options The cheezcap options displayed in this group
 *
 * @return array The *possibly* modified cheezcap group of options
 */
function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {

	if ( ! is_admin() ) {
		return $cheezcap_options;
	}

	$registered_experiments = pmc_get_registered_content_experiments();

	// Bail if we don't have any experiments
	if ( empty( $registered_experiments ) || ! is_array( $registered_experiments ) ) {
		return $cheezcap_options;
	}

	$cheezcap_experiments = array();

	foreach( $registered_experiments as $experiment_id => $experiment_data ) {
		if ( ! empty( $experiment_data ) && is_array( $experiment_data ) ) {
			$experiment_name = $experiment_id;

			if ( ! empty( $experiment_data['experiment_name'] ) ) {
				$experiment_name = $experiment_data['experiment_name'];
			}

			$cheezcap_experiments[ $experiment_id ] = $experiment_name;
		}
	}

	// Bail if our experiments the above loops/checks failed.
	if ( empty( $cheezcap_experiments ) ) {
		return $cheezcap_options;
	}

	// Create a checkbox field of experiments for the user to select
	$cheezcap_options[] = new \CheezCapMultipleCheckboxesOption(
		__( 'Google Content Experiments', 'pmc-google-content-experiments' ),
		__( 'Choose experiments to run. If experiment conditions collide the later experiment will be used.', 'pmc-google-content-experiments' ),
		'pmc_google_cx_enabled_experiment',
		array_keys( $cheezcap_experiments ),
		array_values( $cheezcap_experiments ),
		'', // No default-selection checkboxes, pls
		array( 'PMC_Cheezcap', 'sanitize_cheezcap_checkboxes' )
	);

	return $cheezcap_options;
}
add_filter( 'pmc_global_cheezcap_options', 'PMC\Google_Content_Experiments\filter_pmc_global_cheezcap_options', 10, 1 );

// EOF