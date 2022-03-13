<?php
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

if ( ! class_exists( 'Fieldmanager_Group' ) ) {
	pmc_load_plugin( 'fieldmanager', false, '1.1' );
}

\PMC\Touts\Plugin::get_instance();

//EOF
