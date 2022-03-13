<?php

namespace PMC\Listicle_Gallery;

use PMC\Listicle_Slideshow\Listicle_Slideshow;
use \PMC\Global_Functions\Traits\Singleton;

/**
 * This class handles the Listicle Gallery setup
 *
 */
class Listicle_Gallery {

	use Singleton;

	/**
	 * class constructor
	 */
	protected function __construct() {

		add_filter( 'pmc_ga_event_tracking', [ $this, 'filter_pmc_ga_event_tracking' ] );

	}

  /**
   * Adds gallery scripts, styles, and settings
   *
   */
  public static function load_scripts() {

    wp_enqueue_style( 'listicle-gallery-css', plugin_dir_url( __FILE__ ) . '../assets/css/listicle-gallery.css' );
    wp_enqueue_style( 'bootstrap-css', plugin_dir_url( __FILE__ ) . '../assets/css/bootstrap.min.css' );
    wp_enqueue_script( 'listicle-gallery-js', plugin_dir_url( __FILE__ ) . '../assets/js/listicle-gallery.js', [], FALSE, TRUE );
    wp_enqueue_script( 'bootstrap-js', plugin_dir_url( __FILE__ ) . '../assets/js/bootstrap.min.js', [], FALSE, TRUE );

    // configure the settings to be passed to the JS code
    $settings = [ 'thumbs_count' => LISTICLE_GALLERY_THUMBNAILS ];
    wp_localize_script( 'listicle-gallery-js', 'settings', $settings );
  }

  /**
   * Generate event tracking for the gallery buttons
   *
   * @param array $events
   * @return array
   */
  public function filter_pmc_ga_event_tracking( $events = [ ] ) {

    if ( is_singular( Listicle_Slideshow::POST_TYPE ) ) {
      return array_merge( [
        [
          'selector' => '.pmc-listicle-gallery-prev',
          'category' => 'Navigation',
          'label' => 'Previous Gallery',
        ],
        [
          'selector' => '.pmc-listicle-gallery-next',
          'category' => 'Navigation',
          'label' => 'Next Gallery',
        ],
        [
          'selector' => '.pmc-listicle-gallery .carousel-control.left',
          'category' => 'Navigation',
          'label' => 'Previous Slide',
          'nonInteraction' => true,
        ],
        [
          'selector' => '.pmc-listicle-gallery .carousel-control.right',
          'category' => 'Navigation',
          'label' => 'Next Slide',
          'nonInteraction' => true,
        ],
        [
          'selector' => '.pmc-listicle-gallery-thumbs .carousel-control.left',
          'category' => 'Navigation',
          'label' => 'Scroll Thumbs Left to Right',
          'nonInteraction' => true,
        ],
        [
          'selector' => '.pmc-listicle-gallery-thumbs .carousel-control.right',
          'category' => 'Navigation',
          'label' => 'Scroll Thumbs Right to Left',
          'nonInteraction' => true,
        ],
      ], $events );
    }

    return $events;
  }
}

Listicle_Gallery::get_instance();
