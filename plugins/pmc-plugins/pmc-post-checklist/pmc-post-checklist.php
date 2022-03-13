<?php
/*
Plugin Name: PMC Post Checklist
Plugin URI: http://pmc.com/
Version: 1.0
License: PMC Proprietary.  All rights reserved.

Author: PMC, Hau Vong

Derived from pmc-deadline/plugins/deadline-post-checklist

Display a Post Checklist meta box within the edit post view
It's purpose is to aid authors in completing all required content

*/
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
require_once __DIR__ . '/class-pmc-post-checklist.php';

// EOF