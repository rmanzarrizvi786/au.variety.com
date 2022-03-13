<?php
/**
 * Plugin Name: PMC Profiles
 * Description: PMC profiles
 * Version: 1.0
 * Author: PMC
 * License: PMC Proprietary.  All rights reserved.
 * Text Domain: pmc-profiles
 *
 * @package pmc-profiles
 */

define( 'PROFILES_ROOT', __DIR__ );
define( 'PROFILES_URL', plugin_dir_url( __FILE__ ) );
define( 'PROFILES_POST_PER_PAGE', 48 );

require_once PROFILES_ROOT . '/dependencies.php';
require_once PROFILES_ROOT . '/classes/class-admin.php';
require_once PROFILES_ROOT . '/classes/class-post-type.php';
require_once PROFILES_ROOT . '/classes/class-pmc-profiles.php';

PMC\PMC_Profiles\Post_Type::get_instance();
PMC\PMC_Profiles\Admin::get_instance();
PMC\PMC_Profiles\PMC_Profiles::get_instance();
