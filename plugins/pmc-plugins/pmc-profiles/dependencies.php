<?php

// Load plugins not already loaded

if ( ! class_exists( 'Larva\Config' ) ) {
	pmc_load_plugin( 'pmc-larva', 'pmc-plugins' );
}

if ( ! class_exists( 'PMC_Field_Override' ) ) {
	pmc_load_plugin( 'pmc-field-overrides', 'pmc-plugins' );
}

if ( ! class_exists( 'Fieldmanager_Field' ) ) {
	pmc_load_plugin( 'fieldmanager', false, apply_filters( 'pmc_fieldmanager_version', '1.1' ) );
}

/**
 * Note:
 * This plugin is dependent on classes from the pmc-core-v2
 * parent theme.
 *
 * See pmc-indiewire-2019/plugins/pmc-core-v2 for details
 * on using this plugin on themes not using pmc-core-v2.
 **/
