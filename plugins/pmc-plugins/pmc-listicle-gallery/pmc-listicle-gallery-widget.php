<?php

namespace PMC\Listicle_Gallery;

use PMC;
use WP_Widget;

/**
 * A widget to display a listicle gallery
 */
class Listicle_Gallery_Widget extends WP_Widget {

  const WIDGET_BASE_ID = 'listicle_gallery_widget';

  public function __construct() {

    parent::__construct(
      self::WIDGET_BASE_ID,                           // Base ID
      __( 'Listicle Gallery Widget' ),                // Name
      [ 'description' => __( 'Displays a Gallery' ) ] // Args
    );
  }

  /**
   * Displays the widget content
   *
   * @param array $args
   * @param array $instance
   */
  public function widget( $args, $instance ) {

    Listicle_Gallery::load_scripts();

    $slides = [ ];

    if ( isset( $instance[ 'slides' ] ) && is_array( $instance[ 'slides' ] ) ) {
      $slides = $instance[ 'slides' ];
    }

    $controls               = isset( $instance[ 'controls' ] ) ? $instance[ 'controls' ] : TRUE;
    $indicators             = isset( $instance[ 'indicators' ] ) ? $instance[ 'indicators' ] : FALSE;
    $wrap                   = isset( $instance[ 'wrap' ] ) ? $instance[ 'wrap' ] : TRUE;
    $start_index            = isset( $instance[ 'start_index' ] ) ? $instance[ 'start_index' ] : 0;
    $interval               = isset( $instance[ 'interval' ] ) ? $instance[ 'interval' ] : 0;
    $pause                  = isset( $instance[ 'pause' ] ) ? $instance[ 'pause' ] : 'hover';
    $title                  = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
    $alt                    = isset( $instance[ 'alt' ] ) ? $instance[ 'alt' ] : '';
    $caption                = isset( $instance[ 'caption' ] ) ? $instance[ 'caption' ] : '';
    $credit                 = isset( $instance[ 'credit' ] ) ? $instance[ 'credit' ] : '';
    $body                   = isset( $instance[ 'body' ] ) ? $instance[ 'body' ] : '';
    $prev_gallery_url       = isset( $instance[ 'prev_gallery_url' ] ) ? $instance[ 'prev_gallery_url' ] : '';
    $next_gallery_url       = isset( $instance[ 'next_gallery_url' ] ) ? $instance[ 'next_gallery_url' ] : '';
    $current_gallery_number = isset( $instance[ 'current_gallery_number' ] ) ? $instance[ 'current_gallery_number' ] : '';
    $total_galleries        = isset( $instance[ 'total_galleries' ] ) ? $instance[ 'total_galleries' ] : '';
    $authors                = isset( $instance[ 'authors' ] ) ? $instance[ 'authors' ] : '';

    if ( !empty( $slides ) ) {
      echo PMC::render_template( __DIR__ . '/templates/listicle-gallery.php', [
        'gallery' => [
          'id'                     => 'pmc-listicle-gallery-' . time(),
          'slides'                 => $slides,
          'controls'               => $controls,
          'indicators'             => $indicators,
          'wrap'                   => $wrap,
          'start_index'            => $start_index,
          'interval'               => $interval,
          'pause'                  => $pause,
          'title'                  => $title,
          'alt'                    => $alt,
          'caption'                => $caption,
          'credit'                 => $credit,
          'body'                   => $body,
          'prev_gallery_url'       => $prev_gallery_url,
          'next_gallery_url'       => $next_gallery_url,
          'current_gallery_number' => $current_gallery_number,
          'total_galleries'        => $total_galleries,
          'authors'                => $authors,
          'post_date'              => $instance[ 'post_date' ],
        ]
      ] );
    }
  }
}
