<?php
namespace PMC\Fastly;

// Auto load Fastly plugin if it wasn't activated yet.
if ( ! class_exists( 'Purgely' ) ) {
	pmc_load_plugin( 'fastly', 'pmc-plugins' );
}

// We can only activate the plugin if fastly plugin is successfully loaded
if ( class_exists( 'Purgely' ) ) {
	\PMC\Fastly\Override::get_instance();
}

