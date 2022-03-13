<?php
/**
 * Load dependant plugins.
 */
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// Load only if Fieldmanager not already loaded.
if ( ! class_exists( 'Fieldmanager_Field' ) ) {
	pmc_load_plugin( 'fieldmanager', false, apply_filters( 'pmc_fieldmanager_version', '1.1' ) );
}
pmc_load_plugin( 'fm-zones', 'pmc-plugins' );
