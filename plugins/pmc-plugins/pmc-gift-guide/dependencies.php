<?php
/**
 * To load dependant plugins.
 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// Required for drag and drop reordering of hierarchical post types
pmc_load_plugin( 'simple-page-ordering', 'pmc-plugins' );

if ( ! class_exists( 'Fieldmanager_Group' ) ) {
	pmc_load_plugin( 'fieldmanager', false, '1.1' );
}

pmc_load_plugin( 'pmc-linkcontent', 'pmc-plugins' );
