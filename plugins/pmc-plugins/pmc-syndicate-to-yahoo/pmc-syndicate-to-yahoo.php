<?php
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
require_once( __DIR__ . '/class-pmc-syndicate-to-yahoo.php' );
PMC_Syndicate_To_Yahoo::get_instance();