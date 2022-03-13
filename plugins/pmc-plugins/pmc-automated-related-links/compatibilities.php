<?php
/**
 * To load all backward compatibilities.
 */

//load up plugin class
require_once( PMC_AUTOMATED_RELATED_LINKS_PLUGIN_PATH . '/class-pmc-automated-related-links.php' );

//initialize plugin
$GLOBALS['pmc_automated_related_links'] = PMC_Automated_Related_Links::get_instance();
