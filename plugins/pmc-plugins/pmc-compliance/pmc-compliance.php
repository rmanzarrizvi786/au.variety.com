<?php
/*
Plugin Name: PMC Compliance
Description: Implement compliance/regulation related items
Version: 1.0
License: PMC Proprietary.  All rights reserved.
*/
define( 'PMC_COMPLIANCE_ROOT', __DIR__ );

require_once PMC_COMPLIANCE_ROOT . '/dependencies.php';

PMC\Compliance\Accessibility::get_instance();
