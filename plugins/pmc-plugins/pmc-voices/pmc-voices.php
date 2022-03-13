<?php
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
require_once( __DIR__."/class-pmc-voices.php" );
require_once( __DIR__."/class-pmc-voices-setting.php" );
require_once( __DIR__."/class-pmc-voices-widget.php" );

pmc_load_plugin( 'pmc-field-overrides', 'pmc-plugins' );

PMC_Voices::get_instance();
//EOF