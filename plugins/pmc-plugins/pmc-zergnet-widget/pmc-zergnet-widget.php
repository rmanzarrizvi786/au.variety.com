<?php
require_once __DIR__ . '/class-pmc-zergnet-widget.php';

add_action( 'widgets_init', function() {
	register_widget( 'Zergnet_Widget' );
} );