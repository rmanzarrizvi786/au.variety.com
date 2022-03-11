<?php
/**
 * Variety Digital Subscriber Plugin
 */

require_once( __DIR__ . '/class/class-variety-digital-subscriber.php' );
require_once( __DIR__ . '/class/class-variety-digital-feed.php' );

Variety_Digital_Subscriber::get_instance();
Variety_Digital_Feed::get_instance();
//EOF
