<?php

define( 'PMC_GOOGLE_OAUTH2_ROOT', __DIR__ );
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_load_plugin( 'pmc-options', 'pmc-plugins' );

PMC\Google_OAuth2\OAuth2::get_instance();

// EOF