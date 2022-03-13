<?php
/**
 * Plugin dependencies
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2021-02-23
 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

$pmc_adm_slug = ( defined( 'PMC_ADM_V2' ) && PMC_ADM_V2 ) ? 'pmc-adm-v2' : 'pmc-adm';

pmc_load_plugin( $pmc_adm_slug, 'pmc-plugins' );
pmc_load_plugin( 'pmc-related-articles', 'pmc-plugins' );
pmc_load_plugin( 'pmc-linkcontent', 'pmc-plugins' );

if ( ! class_exists( 'Fieldmanager_Field' ) ) {
	pmc_load_plugin( 'fieldmanager', false, apply_filters( 'pmc_fieldmanager_version', '1.1' ) );
}

// Load composer packages
// load these towards the end here after any/all plugin dependencies
require_once __DIR__ . '/vendor/autoload.php';

//EOF
