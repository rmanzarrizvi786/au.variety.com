<?php

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

require_once __DIR__ . '/classes/class-pmc-ajax-comments.php';
PMC\Ajax\Comments::get_instance();

//EOF