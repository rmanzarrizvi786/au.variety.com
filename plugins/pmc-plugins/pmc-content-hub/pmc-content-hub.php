<?php
/**
 * Plugin Name: PMC Content Hub
 * Description: PMC content hub.
 * Version: 1.0
 * Author: PMC
 * License: PMC Proprietary.  All rights reserved.
 * Text Domain: pmc-content-hub
 *
 * @package pmc-content-hub
 */

if ( ! class_exists( 'Fieldmanager_Field' ) ) {
	pmc_load_plugin( 'fieldmanager', false, apply_filters( 'pmc_fieldmanager_version', '1.1' ) );
}

\PMC\Content_Hub\Content_Hub::get_instance();
