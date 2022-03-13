<?php

// To prevent PHP 7.2 warning errors, we need to define the mcrypt constant
if ( ! defined( 'MCRYPT_RIJNDAEL_128' ) ) {
	define( 'MCRYPT_RIJNDAEL_128', 'rijndael-128' );
}
if ( ! defined( 'MCRYPT_MODE_CBC' ) ) {
	define( 'MCRYPT_MODE_CBC', 'cbc' );
}
if ( ! defined( 'MCRYPT_TRIPLEDES' ) ) {
	define( 'MCRYPT_TRIPLEDES', 'tripledes' );
}

require( __DIR__ . '/vendor/autoload.php' );