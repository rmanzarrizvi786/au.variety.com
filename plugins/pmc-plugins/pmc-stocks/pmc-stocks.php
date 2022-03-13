<?php

define( 'PMC_STOCKS_VERSION', '1.0' );
define( 'PMC_STOCKS_ROOT', __DIR__ );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_load_plugin( 'pmc-options', 'pmc-plugins' );
pmc_load_plugin( 'pmc-js-libraries', 'pmc-plugins' );
pmc_load_plugin( 'pmc-google-oauth2', 'pmc-plugins' );

PMC\Stocks\Api::get_instance();
PMC\Stocks\Plugin::get_instance();
PMC\Stocks\Shortcodes::get_instance();
PMC\Stocks\Cron::get_instance();

// wp-cli command
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once PMC_STOCKS_ROOT . '/classes/cli.php';
	WP_CLI::add_command( 'pmc_stocks', 'PMC\Stocks\Cli' );
}

// EOF