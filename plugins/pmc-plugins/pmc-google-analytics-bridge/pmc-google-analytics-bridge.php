<?php
/**
 * Plugin Name: PMC Google Analytics Bridge
 * Description: Adds some basic functionality around the Google Analytics Bridge plugin.
 * Version: 1.0
 * Author: PMC
 * License: PMC Proprietary.  All rights reserved.
 * Text Domain: pmc-google-analytics-bridge
 *
 * @package pmc-google-analytics-bridge
 */

namespace PMC\Google_Analytics_Bridge;

require_once __DIR__ . '/dependencies.php';

Cron::get_instance();
Plugin::get_instance();
