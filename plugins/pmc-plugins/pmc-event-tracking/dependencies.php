<?php
/**
 * Plugin dependencies for Event Tracking
 *
 * @since 2017-01-13 - Amit Gupta
 */


wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

load_pmc_plugins( array(

	'plugins' => array(
		'cheezcap',
	),

	'pmc-plugins' => array(
		'pmc-js-libraries',
		'pmc-google-universal-analytics',
	),

) );


//EOF
