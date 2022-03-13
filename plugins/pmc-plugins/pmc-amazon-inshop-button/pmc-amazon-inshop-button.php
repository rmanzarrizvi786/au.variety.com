<?php
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
require_once( __DIR__ . '/classes/button.php' );
PMC\Amazon_InShop_Button\Button::get_instance();
//EOF