<?php
/**
 * Implements PMC Featured Programs Feature.
 *
 * @package pmc-featured-programs
 */
define( 'PMC_FP_ROOT', trailingslashit( __DIR__ ) );
define( 'PMC_FP_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

define( 'PMC_FP_IMAGES_ROOT', trailingslashit( PMC_FP_ROOT . 'assets/images' ) );
define( 'PMC_FP_IMAGES_URL', trailingslashit( PMC_FP_URL . 'assets/images' ) );

//phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once( PMC_FP_ROOT . 'classes/class-plugin.php' );
require_once( PMC_FP_ROOT . 'classes/class-config.php' );
// phpcs:enable

\PMC\Featured_Program\Plugin::get_instance();
