<?php

/**
 * Plugin Name: MCE Table Buttons
 * Plugin URI: http://10up.com/plugins-modules/wordpress-mce-table-buttons/
 * Description: Add <strong>controls for table editing</strong> to the visual content editor with this <strong>light weight</strong> plug-in.
 * Version: 3.2
 * Author: Jake Goldman, 10up, Oomph
 * Author URI: http://10up.com
 * License: GPLv2 or later
 *
 * *******************
 * Modifed for PMC use
 * *******************
 */

define( 'MCE_TABLE_BUTTONS_ROOT', __DIR__ );
define( 'MCE_TABLE_BUTTONS_URL', plugin_dir_url( __FILE__ ) );

require_once MCE_TABLE_BUTTONS_ROOT . '/autoload.php';

\MCE_Table_Buttons\MCE_Table_Buttons::get_instance();
