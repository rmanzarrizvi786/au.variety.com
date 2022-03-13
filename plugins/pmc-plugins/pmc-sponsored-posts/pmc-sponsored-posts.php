<?php
/**
 * Plugin Name: PMC Sponsored Posts.
 * Description: Easily manage and promote sponsored posts on any pmc-core-v2 theme.
 * Author: PMC
 * License: PMC Proprietary. All rights reserved.
 *
 * @package pmc-plugins
 */
namespace PMC\Sponsored_Posts;

define( 'PMC_SPONSORED_POSTS_DIR', __DIR__ );
define( 'PMC_SPONSORED_POSTS_URL', plugins_url( null, __FILE__ ) );

require_once( __DIR__ . '/dependencies.php' );

Admin::get_instance();
Utility::get_instance();
