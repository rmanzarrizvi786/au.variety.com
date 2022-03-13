<?php
/**
*
* Load plugin dependencies.
*
*/

pmc_load_plugin( 'fm-widgets', 'pmc-plugins' );

// Load only if Fieldmanager is not already loaded.
if ( ! class_exists( 'Fieldmanager_Field', false ) ) {
	pmc_load_plugin( 'fieldmanager', false, apply_filters( 'pmc_fieldmanager_version', '1.1' ) );
}
