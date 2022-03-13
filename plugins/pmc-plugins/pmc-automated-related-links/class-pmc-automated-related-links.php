<?php
/**
 * PMC Automated Related Links plugin
 * Based on Variety See Also Links plugin
 *
 * To provide backward compatibility.
 *
 * @package pmc-automated-related-links
 */

use PMC\Automated_Related_Links\Plugin;

class PMC_Automated_Related_Links {

	public static function get_instance() {
		return Plugin::get_instance();
	}

}
