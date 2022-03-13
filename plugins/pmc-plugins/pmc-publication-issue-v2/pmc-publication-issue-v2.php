<?php
/*
Plugin Name: PMC Publication Issue V2
Plugin URI: http://pmc.com/
Description: Creating a custom post type to represent publication print issue
Version: 1.0
Author: PMC, Hau Vong, XWP
License: PMC Proprietary.  All rights reserved.

The plugin use object to object plugin to map posts to publication issue relationship.
Current supported features:
- Assigning posts to an issue and re-ordering posts within an issue
- Treat publication issue post as archive type to allow template integration with wp pagination
- Attaching pdf file to an issue
- Add a feature image
- Attaching a lead article to an issue
- Add custom taxonomy to represent a publication
*/


define( 'PMC_PUBLICATION_ISSUE_DIR', __DIR__ );

function pmc_publication_issue_v2_loader() {

	require_once PMC_PUBLICATION_ISSUE_DIR . '/dependencies.php';

	\PMC\Publication_Issue_V2\Publication_Issue::get_instance();

	/*
	 * Load plugin configs
	 */
	require_once __DIR__ . '/plugins/config/loader.php';

}

pmc_publication_issue_v2_loader();

//EOF
