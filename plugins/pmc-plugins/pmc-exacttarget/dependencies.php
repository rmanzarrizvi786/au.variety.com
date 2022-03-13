<?php

/**
 * Load the dependencies for this plugin
 */

// This should have been loaded at theme or mu-plugins loaded events, added here just in case for completeness
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// Depending on these plugins
load_pmc_plugins(
	[

		'plugins'     => [
			'cheezcap',
		],

		'pmc-plugins' => [
			'fuelsdk-php',
			'pmc-editorial',
			'pmc-touts',
			'pmc-zoninator-extended',
		],

	]
);


//EOF
