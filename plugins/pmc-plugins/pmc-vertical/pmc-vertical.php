<?php
/*
Plugin Name: PMC Vertical
Plugin URI: http://www.pmc.com
Description: Enable the Vertical parent taxonomy that provides unique URLs and categorization for posts.
Version: 0.1
Author: Miles Johnson
Author URI: http://www.pmc.com
Author Email: mjohnson@pmc.com
License: PMC Proprietary. All rights reserved.

@revision 2014-11-06 Hau - move code to class and adjust code to work for all lob

*/
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_load_plugin( 'pmc-primary-taxonomy', 'pmc-plugins' );

require_once __DIR__ . '/class-pmc-vertical.php';