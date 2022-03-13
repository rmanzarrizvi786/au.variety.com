<?php
/**
 * Manifest file for WP CLI commands.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since BR-309
 *
 * @package pmc-sitemaps
 */

WP_CLI::add_command( 'pmc-sitemaps', 'PMC\Sitemaps\PMC_Sitemaps_CLI' );
