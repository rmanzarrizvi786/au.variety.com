<?php

add_action(
	'admin_menu',
	function() {
		add_menu_page(
			'Exacttarget Newsletter',
			'Exacttarget',
			'publish_posts',
			'sailthru_setup',
			function() {
				require_once( __DIR__ . '/setup.php' );
			}
		);
	}
);


