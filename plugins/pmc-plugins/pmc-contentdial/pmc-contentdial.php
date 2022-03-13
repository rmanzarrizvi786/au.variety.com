<?php
/**
 * Plugin Name: PMC Content Dial
 * Plugin URI: http://www.pmc.com
 * Description: This plugin was written to add Content Dial script tags to the sponsored articles.
 * Version: 1.1
 * Authors: Vinod Tella, PMC
 * License: PMC Proprietary. All rights reserved.
 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

\PMC\ContentDial\Plugin::get_instance();
