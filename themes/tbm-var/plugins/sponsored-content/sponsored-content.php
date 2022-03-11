<?php
/**
 * Plugin Name: Variety Sponsored Content
 * Plugin URI: http://www.variety.com
 * Version: 1.0
 * License: PMC proprietary. All rights reserved.
 *
 * This plugin adds capability to set a post 'sponsored' and adds visual flag on the post.
 * This requires to be instantiated for the post/page in templates as required.
 *
 */

/* All classes initialized here */

\Variety\Plugins\Sponsored_Content\Sponsored_Content::get_instance();
