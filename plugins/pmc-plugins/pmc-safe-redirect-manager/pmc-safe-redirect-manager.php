<?php
/**
 * Plugin Name:     PMC Safe Redirect Manager.
 * Plugin URI:      https://pmc.com/
 * Description:     Extends Safe Redirect Manager plugin to provide extra features.
 * Author:          Kelin Chauhan <kelin.chauhan@rtcamp.com>
 * License:         PMC Proprietary. All rights reserved.
 * Version:         1.0
 */

define( 'PMC_SAFE_REDIRECT_MANAGER_ROOT', __DIR__ );
define( 'PMC_SAFE_REDIRECT_MANAGER_URI', plugin_dir_url( __FILE__ ) );
define( 'PMC_SAFE_REDIRECT_MANAGER_VERSION', '1.0' );

require_once PMC_SAFE_REDIRECT_MANAGER_ROOT . '/dependencies.php';

PMC\Safe_Redirect_Manager\Plugin::get_instance();
