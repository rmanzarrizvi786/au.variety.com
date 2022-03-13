<?php
/*
 *
Plugin Name: PMC Google Breaking News Indexing
Description:
  - Allows to submit amp version of that post to google for Real time indexing

Version: 1.0.0
Author: PMC, Vinod Tella
License: PMC Proprietary.  All rights reserved.

*/

use PMC\Google_Breaking_News\Plugin;


define( 'PMC_GOOGLE_BREAKING_NEWS_VERSION', '1.0' );
define( 'PMC_GOOGLE_BREAKING_NEWS_ROOT', __DIR__ );

//We want indexing to work only for amp enabled sites.
if ( function_exists( 'is_amp_endpoint' ) ) {

	\PMC\Google_Breaking_News\Plugin::get_instance();
}

// EOF