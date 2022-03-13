<?php
/**
 * Plugin Name: PMC GetEmails
 * Description: Plugin for GetEmails email collector
 * Version: 1.0.0
 * @package pmc-plugins
 */

namespace PMC\GetEmails;

define( 'PMC_GETEMAILS_VERSION', '1.1' );
define( 'PMC_GETEMAILS_DIR', trailingslashit( __DIR__ ) );

// Initiate the GetEmails class
Plugin::get_instance();
