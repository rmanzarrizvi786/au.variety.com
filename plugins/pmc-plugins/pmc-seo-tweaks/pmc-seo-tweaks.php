<?php
/*
Plugin Name: PMC SEO Tweaks
Plugin URI: http://www.pmc.com/
Version: 2.0
Author: Adaeze Esiobu, Mike Auteri, PMC
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_SEO_TWEAKS_ROOT', __DIR__ );

require_once( PMC_SEO_TWEAKS_ROOT . '/dependencies.php' );
require_once( PMC_SEO_TWEAKS_ROOT . '/deprecated.php' );

function pmc_seo_tweaks_loader() {
	PMC\SEO_Tweaks\Setup::get_instance();
	PMC\SEO_Tweaks\Taxonomy::get_instance();
	PMC\SEO_Tweaks\Canonical_Override::get_instance();
	PMC\SEO_Tweaks\Canonical_Redirect::get_instance();
}

/**
 * Class Aliases
 */
class_alias( 'PMC\SEO_Tweaks\Taxonomy', 'PMC_SEO_Tweaks_Taxonomy' );
class_alias( 'PMC\SEO_Tweaks\Canonical_Override', 'PMC_SEO_Tweaks_Canonical_Override' );

pmc_seo_tweaks_loader();

// EOF