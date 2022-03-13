<?php
/*
Plugin Name: PMC Listicle Gallery
Description: Defines a listicle gallery
Version: 1.0.0
Author: Fardin Pakravan
Author URI: http://www.pmc.com
Author Email: fpakravan@pmc.com
License: PMC Proprietary. All rights reserved.
*/

namespace PMC\Listicle_Gallery;

if (!defined ( 'LISTICLE_GALLERY_THUMBNAILS' )) {
  define( 'LISTICLE_GALLERY_THUMBNAILS', 4 );
}

require_once( __DIR__ . '/pmc-listicle-gallery-widget.php' );
require_once( __DIR__ . '/classes/listicle-gallery.php' );


add_action( 'widgets_init', __NAMESPACE__ . '\\action_widgets_init' );

function action_widgets_init() {
  register_widget( __NAMESPACE__ . '\\Listicle_Gallery_Widget' );
}