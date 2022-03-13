<?php
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
require_once __DIR__ . '/class-pmc-post-listing-filters.php';

PMC_Post_Listing_Filters::get_instance();
//EOF