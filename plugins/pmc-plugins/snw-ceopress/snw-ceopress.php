<?php

define( 'SNW_CEO_DIR', __DIR__ );


function snw_ceopress_loader() {

	require_once SNW_CEO_DIR . '/dependencies.php';
	require_once SNW_CEO_DIR . '/helpers.php';
	require_once SNW_CEO_DIR . '/classes/traits/trait-posts.php';
	require_once SNW_CEO_DIR . '/classes/class-api-settings.php';
	require_once SNW_CEO_DIR . '/classes/class-ceopress.php';
	require_once SNW_CEO_DIR . '/classes/class-feed.php';
	require_once SNW_CEO_DIR . '/classes/class-feed-table.php';
	require_once SNW_CEO_DIR . '/classes/class-admin-ui.php';

	SNW\CEO_Press\API_Settings::get_instance();
	SNW\CEO_Press\CEOPress::get_instance();
	SNW\CEO_Press\Admin_UI::get_instance();

	if ( is_admin() ) {
		SNW\CEO_Press\Feed::get_instance();
	}

}

snw_ceopress_loader();

//EOF
