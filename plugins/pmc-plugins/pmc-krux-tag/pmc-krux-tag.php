<?php

/*
Plugin Name: PMC Krux Tag
Description: Add Krux tags to all pages, all sites.  Plugin is activate via cheezcap setting
Author: PMC, Hau Vong
Version: 1.0.0
License: PMC Proprietary.  All rights reserved.
*/
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_load_plugin( 'pmc-geo-uniques', 'pmc-plugins' );
pmc_geo_add_location( 'us' ); //support for us

require_once __DIR__ . '/class-pmc-krux-tag.php';

// EOF