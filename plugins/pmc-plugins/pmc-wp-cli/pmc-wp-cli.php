<?php
/**
 * Implements WP-CLI pmc command
 *
 * @package pmc-wp-cli
 *
 * Plugin Name: PMC WP-CLI
 */

// this file must be include first
require_once( __DIR__ . '/classes/class-pmc-wp-cli-base.php' );

// General taxonomy-related CLI commands
require_once( __DIR__ . '/classes/class-pmc-wp-cli-taxonomy.php' );

require_once( __DIR__ . '/classes/class-pmc-wp-cli-export-rss.php' );

require_once( __DIR__ . '/classes/class-pmc-wp-cli-http-cleanup.php' );

//Safe Redirect Manager related CLI commands (not included in original VIP plugin)
require_once( __DIR__ . '/classes/class-pmc-wp-cli-safe-rediret-manager.php' );

// this file must include last
require_once( __DIR__ . '/classes/class-pmc-wp-cli-delete-sitemap.php' );

require_once( __DIR__ . '/classes/class-pmc-wp-cli.php' );

require_once( __DIR__ . '/classes/class-associated-press.php' );

require_once( __DIR__ . '/classes/class-pmc-export-comments.php' );

// note: plugin may be load via pmc_load_plugin, class must be declare before this command run
/**
 * uncomment line below when command functions have been added to it for use
 *
 * @since 2013-11-19 Amit Gupta
 */
//WP_CLI::add_command( 'pmc', 'PMC_WP_CLI' );


/*
 * Since not all classes in this plugin use PHP Namespaces and class auto-loading,
 * the Command_Bus class must be instantiated after all classes have been loaded.
 */
\PMC\WP_CLI\Command_Bus::get_instance();

// EOF
