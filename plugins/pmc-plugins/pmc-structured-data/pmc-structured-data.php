<?php
/**
 * Plugin Name:     PMC Structured Data
 * Plugin URI:      https://pmc.com
 * Description:     Inserts JSON-LD code in the header.
 * Author:          PMC, Brandon Camenisch <bcamenisch@pmc.com>
 * Author URI:      https://pmc.com
 * Text Domain:     pmc-structured-data
 * Version:         1.0
 *
 * @package         Pmc_Structured_Data
 */

define( 'PMC_STRUCTURED_DATA_PATH', trailingslashit( __DIR__ ) );

\PMC\Structured_Data\Article_Data::get_instance();
