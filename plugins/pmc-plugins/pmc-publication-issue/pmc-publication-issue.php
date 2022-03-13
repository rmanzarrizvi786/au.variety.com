<?php
/*
Plugin Name: PMC Publication Issue
Plugin URI: http://pmc.com/
Description: Creating a custom post type to represent publication print issue
Version: 1.0
Author: PMC, Hau Vong
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

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
require_once __DIR__ .'/class-pmc-publication-issue.php';


/*
 * Load plugin configs
 */
require_once __DIR__ . '/plugins/config/loader.php';



//EOF