<?php
/**
 * Plugin Name: Variety Commissioned Articles
 * Plugin URI: http://pmc.com/
 * Description: Register custom post type for Commissioned articles and add custom rewrite rules for it
 * Version: 1.0
 * Author: PMC, Adaeze Esiobu
 * License: PMC proprietary.  All rights reserved.
 */

/**
 * Initilize the class.
 *
 * @return void
 */
function variety_exclusive_articles_loader() {

	\Variety\Plugins\Exclusive_Articles\Plugin::get_instance();

}

variety_exclusive_articles_loader();
