<?php
/*
Plugin Name: PMC Quantcast Consent Manager Provider
Description: Add js code for GDPR consent compliance. This needs to have the pmc-frisbee plugin enabled in order to save the consent info audit trail.
Version: 1.0
Author: PMC, Dan Berko, SheKnows
License: PMC Proprietary. All rights reserved.
*/
/**
 * Initialize PMC Quantcast CMP admin
 *
 * @since 2018-10-31
 * @version 2018-10-31 Dan Berko PEP-1545
 *
 */
define( 'CMP_INIT_SRC', plugins_url( 'assets/js/cmp-init.js', __FILE__ ) );
define( 'CMP_REPORT_SRC_MIN', plugins_url( 'assets/js/qqcreporter.min.js', __FILE__ ) );
define( 'CMP_REPORT_SRC', plugins_url( 'assets/js/qqcreporter.js', __FILE__ ) );

// Load pmc-frisbee/Frisbeee.js as dependency for consent recording.
pmc_load_plugin( 'pmc-frisbee', 'pmc-plugins' );

PMC\Quantcast_CMP\Admin::get_instance();

//EOF


