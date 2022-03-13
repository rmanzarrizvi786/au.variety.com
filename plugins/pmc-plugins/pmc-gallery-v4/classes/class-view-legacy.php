<?php
/**
 * PMC Gallery Legacy class.
 *
 * The class contains all the legacy methods from pmc-gallery-v3 and pmc-gallery-v3.
 *
 * @todo Remove this entire class after ensuring they are not being used in any brand or plugin.
 *
 * @since 2019-28-02 Sayed Taqui
 */

namespace PMC\Gallery;

/**
 * @codeCoverageIgnore
 */
abstract class View_Legacy {

	/**
	 * Number of images.
	 *
	 * @var null
	 */
	protected $_number_of_images = null;

	/**
	 * Linked gallery
	 *
	 * @var null
	 */
	public static $linked_gallery = null;

	/**
	 * Post id.
	 *
	 * @var null
	 */
	public static $id = null;

	/**
	 * Data.
	 *
	 * @var null
	 */
	protected $_data = null;

	/**
	 * JS object.
	 *
	 * @var array
	 */
	protected $_js_obj = array(
		'gallery_first'                => 0,
		'ad_refresh_clicks'            => 2,
		'enable_interstitial'          => false,
		'interstitial_refresh_clicks'  => 25,
		'interstitial_duration'        => 10,
		'start_with_interstitial'      => false,
		'variable_height'              => false,
		'gallery_start'                => 0,
		'gallery_count'                => 0,
		'auto_start_delay'             => 0,
		'continuous_cycle'             => true,
		'enable_pinterest_description' => true,
		'imageparts'                   => array(),
		'multiparts'                   => array(),
	);

	protected function __construct() {
		add_action( 'init', array( $this, 'register_defaults' ), 99 );
		add_filter( 'pmc_gallery_get_the_meta', array( $this, 'pmc_gallery_get_the_meta' ), 10, 5 );
	}

	/**
	 * Register defaults.
	 */
	public function register_defaults() {
		$this->_register_default_settings();
		$this->_register_default_image_sizes();
	}

	/**
	 * Register default settings.
	 */
	protected function _register_default_settings() {
		// Get Settings
		$settings      = Settings::get_instance()->get_options();
		$this->_js_obj = wp_parse_args( $settings, $this->_js_obj );

		if ( defined( 'PMC_GALLERY_VARIABLE_HEIGHT' ) ) {
			$this->_js_obj['variable_height'] = (bool) PMC_GALLERY_VARIABLE_HEIGHT;
		}

	}

	/**
	 * Register default image sizes.
	 *
	 */
	protected function _register_default_image_sizes() {
		// $max_height gets calculated by aspect ratio
		$aspect_ratio_w = 4;
		$aspect_ratio_h = 3;

		if ( isset( $GLOBALS['content_width'] ) ) {
			// Make it a little smaller than the $content_width to allow for
			// margin and padding.
			$max_width = intval( $GLOBALS['content_width'] * 0.8 );
		} else {
			$max_width = 600;
		}

		$max_height       = round( ( $aspect_ratio_h * $max_width ) / $aspect_ratio_w );
		$registered_sizes = \PMC\Image\get_intermediate_image_sizes();

		if ( ! in_array( 'pmc-gallery-image', (array) $registered_sizes, true ) ) {
			add_image_size( 'pmc-gallery-image', $max_width, $max_height, true );
		}

		/**
		 * default gallery-thumb size, each theme may override this default via add_image_size
		 * note: gallery-thumb name is use for backward compatible with existing theme
		 *
		 * @todo Is this required anymore?
		 */
		if ( ! in_array( 'gallery-thumb', (array) $registered_sizes, true ) ) {
			$width  = intval( $max_width / 4 );
			$height = round( ( $aspect_ratio_h * $width ) / $aspect_ratio_w );
			add_image_size( 'gallery-thumb', $width, $height, true );
		}

		// Register inline gallery image ratio ( size taken from RS theme ).
		if ( ! in_array( 'ratio-3x2', (array) $registered_sizes, true ) ) {
			add_image_size( 'ratio-3x2', 900, 600, true );
		}
	}

	/**
	 * Get image count.
	 *
	 * @return int
	 */
	protected function _get_image_count() {
		if ( is_null( $this->_number_of_images ) && ! is_null( $this->_data ) ) {
			$this->_number_of_images = ( $this->_data );

		}

		return $this->_number_of_images;
	}

	/**
	 * Wrapper Function to fetch Image. This loads the image which will be main image in the stage.
	 *
	 * @param string $size
	 * @param int    $images_to_show Extra images count that needs to be shown for cyclic gallery thumbs
	 *
	 * @return string
	 */
	public function get_the_image( $size = 'pmc-gallery-image', $images_to_show = 0 ) {
		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Preview with first image provided by gallery
		if ( 0 === self::$linked_gallery ) {
			return '';
		}

		$position = $this->_js_obj['gallery_start'] + $this->_js_obj['gallery_first'];

		$output = ' ';

		$output .= '<div class="gallery-image gallery-image-' . esc_attr( $size ) . '" >';
		$output .= '<div class="gallery-multi">';

		/**
		 * Preview with first image provided by theme
		 * Not sure what that means exactly, but this is for gallery previews where the preview image == the first image of the gallery.
		 */
		if ( 1 === self::$linked_gallery ) {
			$image_src = wp_get_attachment_image_src( $this->_data[ $position ]['ID'], $size );
			$src       = ( ! empty( $image_src['src'] ) ) ? $image_src['src'] : '';
			$slug      = $this->_data[ $position ]['slug'];
			$alt       = $this->_data[ $position ]['title'];
			$pos       = $this->_data[ $position ]['position'];

			$image_url = get_permalink( self::$id ) . '#!' . intval( $pos ) . '/' . esc_attr( $slug );

			// Add ref param to the gallery link to trigger "Return to article" link
			$ref_path = wp_parse_url( get_permalink(), PHP_URL_PATH );
			if ( $ref_path ) {
				// Not using add_query_arg() here because we're adding a param to a hash, not querystring
				$image_url .= '&ref=' . $ref_path . 'pos=';
			}

			$output .= '<figure>';
			$output .= '<a href="' . esc_url( $image_url ) . '" >';
			$output .= '<img data-original="' . esc_url( $src ) . '" src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" class="full lazy" scale="0" style="display: block;">';
			$output .= '</a>';
			$output .= '</figure>';

			// No preview.
		} elseif ( is_null( self::$linked_gallery ) ) {

			$image_count = $this->_get_image_count();
			// if we want image padding ( extra images count ) for cyclic gallery
			if ( ! empty( $images_to_show ) ) {
				$image_count = $this->get_count_for_cyclic_image( $images_to_show );
				$image_total = $this->_get_image_count();
			}

			for ( $i = 0; $i < $image_count; $i ++ ) {

				if ( ! empty( $images_to_show ) && ! empty( $image_total ) ) {

					// Modifying the counter here for cyclic gallery. Since the number of images in $this->_data will be less, we will need to reset counter back ( strarting from 0 ) to get images data.
					$counter = ( $i + $image_total ) % $image_total;
				} else {
					$counter = $i;
				}

				$image_html = '';
				$image_src  = wp_get_attachment_image_src( $this->_data[ $counter ]['ID'], $size );
				$src        = ( ! empty( $image_src['src'] ) ) ? $image_src['src'] : '';

				$credits = get_post_meta( $this->_data[ $counter ]['ID'], '_image_credit', true );
				if ( $counter === $position ) {
					$image_html .= '<div data-src="' . esc_url( $src ) . '" data-alt="' . esc_attr( $this->_data[ $counter ]['title'] ) . '" data-slug="' . esc_attr( $this->_data[ $counter ]['slug'] ) . '" data-pos="' . esc_attr( $this->_data[ $counter ]['position'] ) . '" data-url="' . esc_url( $this->_data[ $counter ]['url'] ) . '" >';
					if ( ! empty( $credits ) ) {
						$image_html .= '<div class="credits">' . esc_html( $credits ) . '</div>';
					}
					$image_html .= '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $this->_data[ $counter ]['title'] ) . '" data-slug="' . esc_attr( $this->_data[ $counter ]['slug'] ) . '" />';
					$image_html .= '</div>';
				} else {

					$image_html .=
						'<div class="swipe-elements" style="display:none" data-src="' .
						esc_url( $src ) .
						'" data-alt="' .
						esc_attr( $this->_data[ $counter ]['title'] ) .
						'" data-slug="' .
						esc_attr( $this->_data[ $counter ]['slug'] ) .
						'" data-pos="' .
						esc_attr( $this->_data[ $counter ]['position'] ) .
						'" data-url="' .
						esc_url( $this->_data[ $counter ]['url'] ) .
						'" >';
					if ( ! empty( $credits ) ) {
						$image_html .= '<span class="credits">' . esc_html( $credits ) . '</span>';
					}
					$image_html .= '</div>';
				}

				$output .= apply_filters( 'pmc_gallery_the_image', $image_html, $this->_data[ $counter ] );

			} // for ends.

		}

		$output .= '</div>'; // close gallery-multi

		// Add the Gallery interstitial ad to the markup at the end of the images
		if ( function_exists( 'pmc_adm_render_ads' ) ) {
			$ad_code = pmc_adm_render_ads( __( 'Gallery Interstitial', 'pmc-gallery-v4' ), '', false );
		} else {
			$ad_code = '';
		}

		$output .= '<span class="gallery-interstitial">';

		// Display the 'Skip Ad' link if it's been enabled
		// in the Cheezcap settings
		if ( ! empty( $this->_js_obj['skip_ad'] ) && true === $this->_js_obj['skip_ad'] ) {
			$output .= '<span class="skip-ad">' . __( 'Skip Ad', 'pmc-gallery-v4' ) . '</span>';
		}

		$output .= "<div class='ad'> $ad_code </div>";
		$output .= '</span>';

		$output .= '</div>'; // close gallery-image

		return $output;
	}

	/**
	 * @param string $size
	 * @param int    $images_to_show extra images count that needs to be shown for cyclic gallery thumbs
	 */
	public function the_image( $size = 'pmc-gallery-image', $images_to_show = 0 ) {
		echo $this->get_the_image( $size, $images_to_show ); // XSS okay - Escaping done in method.
	}

	/**
	 * Get count for cyclic image.
	 *
	 * @param int $count_images_padding Count images padding.
	 *
	 * @return int|null
	 */
	public function get_count_for_cyclic_image( $count_images_padding ) {

		if ( empty( $count_images_padding ) ) {
			return null;
		}

		$count_images_padding = intval( $count_images_padding );

		// If the preview number is greater than the number of images in the gallery, it will cause chaos.  So here we do a simple sanity check.
		if ( $count_images_padding > $this->_js_obj['gallery_count'] ) {
			$count_images_padding = $this->_js_obj['gallery_count'];
		}

		// Sanity check
		if ( $count_images_padding < 1 ) {
			$count_images_padding = 1;
		}

		if ( ! is_null( self::$linked_gallery ) ) {
			$limit = min( ( $count_images_padding + 1 ), $this->_get_image_count() );
		} else {
			$limit = max( ( $count_images_padding + 2 ), $this->_get_image_count() ) + $count_images_padding;
		}

		return $limit;

	}

	/**
	 * Wrapper Function to fetch Thumbs
	 *
	 * @todo this method is trying to do too much. It's trying to grab thumbnail images for multiple layout types, for linked gallery previews, and also for the galleries themselves.
	 * @todo Remove backwards compatibility args after themes are updated
	 *
	 * @param array  $args        Arguments.
	 * @param string $deprecated  Deprecated param filmstrip.
	 * @param int    $deprecated2 Deprecated param
	 * @param string $deprecated3 Deprecated param
	 * @param string $deprecated4 Deprecated param
	 * @param string $deprecated5 Deprecated param
	 *
	 * @return string
	 */
	public function get_the_thumbs( $args = array( 'thumbnail_image_size' => 'gallery-thumb' ), $deprecated = 'filmstrip', $deprecated2 = 5, $deprecated3 = 'thumb', $deprecated4 = Defaults::PREVIOUS_LINK_HTML, $deprecated5 = Defaults::NEXT_LINK_HTML ) {

		/**
		 * Backwards compatibility
		 * @todo Remove backwards compatibility after themes are updated
		 */
		if ( is_string( $args ) ) {
			$args = array(
				'thumbnail_image_size'         => $args,
				'thumbnail_layout'             => $deprecated,
				'number_to_show'               => $deprecated2,
				'thumbnail_structure'          => $deprecated3,
				'thumbnail_previous_link_text' => $deprecated4,
				'thumbnail_next_link_text'     => $deprecated5,
			);
		}

		$default_args = array(
			'thumbnail_image_size'         => 'gallery-thumb',
			'thumbnail_layout'             => 'filmstrip',
			'number_to_show'               => 5,
			'thumbnail_structure'          => 'thumb',
			'thumbnail_previous_link_text' => Defaults::PREVIOUS_LINK_HTML,
			'thumbnail_next_link_text'     => Defaults::NEXT_LINK_HTML,
		);

		$args = wp_parse_args( $args, $default_args );

		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization.
		$args['thumbnail_image_size'] = (string) $args['thumbnail_image_size'];
		$valid_sizes                  = \PMC\Image\get_intermediate_image_sizes();

		if ( ! in_array( $args['thumbnail_image_size'], (array) $valid_sizes, true ) ) {
			$args['thumbnail_image_size'] = 'gallery-thumb';
		}

		$args['thumbnail_layout'] = (string) $args['thumbnail_layout'];
		if ( ! in_array( $args['thumbnail_layout'], array( 'all', 'filmstrip', 'filmstrip-scroll' ), true ) ) {
			$args['thumbnail_layout'] = 'filmstrip';
		}

		$args['thumbnail_structure'] = (string) $args['thumbnail_structure'];
		if ( ! in_array( $args['thumbnail_structure'], array( 'thumb', 'thumb-title' ), true ) ) {
			$args['thumbnail_structure'] = 'thumb';
		}

		$args['number_to_show'] = intval( $args['number_to_show'] );

		// If the preview number is greater than the number of images in the gallery, it will cause chaos.  So here we do a simple sanity check.
		if ( $args['number_to_show'] > $this->_js_obj['gallery_count'] ) {
			$args['number_to_show'] = $this->_js_obj['gallery_count'];
		}

		// Sanity check
		if ( $args['number_to_show'] < 1 ) {
			$args['number_to_show'] = 1;
		}

		$start = ( intval( $this->_js_obj['gallery_start'] ) + intval( $this->_js_obj['gallery_first'] ) );

		$this->_js_obj['imageparts'][ 'gallery-thumbs-' . $args['thumbnail_image_size'] ] = array(
			'show'   => $args['number_to_show'],
			'loaded' => array(),
		);

		// Render HTML
		$output = '<div class="gallery-thumbs gallery-thumbs-' . esc_attr( $args['thumbnail_image_size'] ) . '" data-style="' . esc_attr( $args['thumbnail_layout'] ) . '">';

		if ( is_null( self::$linked_gallery ) && 'all' !== $args['thumbnail_layout'] ) {
			$output .= '<a href="javascript:void(0);" class="gallery-navigation-previous" >' . esc_html( $args['thumbnail_previous_link_text'] ) . '</a>';
			$output .= '<a href="javascript:void(0);" class="gallery-navigation-next" >' . esc_html( $args['thumbnail_next_link_text'] ) . '</a>';
		}

		$output .= '<div class="gallery-multi">';

		// Preview which Thumbs
		// @todo This seems a little...magical. Need to understand it better.
		if ( ! is_null( self::$linked_gallery ) ) {
			$limit = min( ( $args['number_to_show'] + 1 ), $this->_get_image_count() );
		} else {
			$limit = max( ( $args['number_to_show'] + 2 ), $this->_get_image_count() );
		}

		$ref_path = '';
		if ( ! is_null( self::$linked_gallery ) ) {
			// Add ref param to the gallery link to trigger "Return to article" link
			// @todo This snippet of logic is used throughout, refactor to helper method
			$linkback = wp_parse_url( get_permalink(), PHP_URL_PATH );

			if ( $linkback ) {
				// Not using add_query_arg() here because we're adding a param to a hash, not query string.
				$ref_path .= '&ref=' . $linkback . 'pos=';
			}
		}

		// Next Gallery Flagging using url
		$old_url = '';
		if ( is_null( self::$linked_gallery ) ) {
			$limit = $limit + $args['number_to_show']; // allows us to have more thumbs to fill in the blank spaces.
		}

		for ( $counter = 0; $counter < $limit; $counter ++ ) {

			if ( ! is_null( self::$linked_gallery ) ) {

				if ( $counter <= self::$linked_gallery ) {
					continue;
				}

				$data_index     = $counter - 1 + $start;
				$image_position = $counter;

				// @todo $src is the full image src, probably a bug in _register_default_image_sizes()
				list( $src, $width, $height ) = wp_get_attachment_image_src( $this->_data[ $data_index ]['ID'], $args['thumbnail_image_size'] );
				$title                        = '';

				if ( 'thumb-title' === $args['thumbnail_structure'] && isset( $this->_data[ $data_index ]['title'] ) ) {
					$title = $this->_data[ $data_index ]['title'];
					$title = apply_filters( 'the_title', $title );
					$title = esc_html( wp_strip_all_tags( $title ) );
				}

				$link_url = get_permalink( self::$id ) . '#!' . ( $image_position ) . '/' . esc_attr( $this->_data[ $data_index ]['slug'] ) . $ref_path;
				$output  .= '<div>';
				$output  .= '<a href="' . esc_url( $link_url ) . '" >';
				$output  .= '<img src="' . esc_url( $src ) . '"';

				if ( ! empty( $width ) ) {
					$output .= ' width="' . intval( $width ) . '"';
				}

				if ( ! empty( $height ) ) {
					$output .= ' height="' . intval( $height ) . '"';
				}

				$output .= '" alt="' . esc_attr( $title ) . '" data-slug="' . esc_attr( $this->_data[ $data_index ]['slug'] ) . '" />';
				$output .= '</a>';
				$output .= wp_kses_post( $title );
				$output .= '</div>';

			} else {

				$visible         = ( ( $counter >= $start ) && ( $counter < ( $args['number_to_show'] + $start ) ) ); // thumb visible by default
				$current_gallery = ( ( $counter >= $start ) && ( $counter < ( $this->_js_obj['gallery_count'] + $start ) ) ); // thumb in current gallery
				$all             = ( ( 'all' === $args['thumbnail_layout'] ) && $current_gallery ); // show only current gallery in case of all

				/*
				 * lets assume we'll be moving to next gallery after last image
				 * of current gallery, so we need to show thumbs from next
				 * gallery after showing all thumbs of current gallery
				 *
				 * @since 2016-08-03 Amit Gupta
				 */
				$position = $counter;

				if ( ( true !== $this->_js_obj['continuous_cycle'] || empty( $this->_data[ $position ] ) ) && intval( $this->_js_obj['gallery_count'] ) > 0 ) {
					/*
					 * calculating mod here so that we cycle back to 1st image
					 * thumb after the last image's thumb if we are not
					 * doing continuous galleries and cycling through
					 * current gallery's images only
					 *
					 * @since 2016-08-03 Amit Gupta
					 */
					$position = ( $counter % $this->_js_obj['gallery_count'] );
				}

				if ( ( $all || is_null( self::$linked_gallery ) || $visible ) && $position < count( $this->_data ) && ! empty( $this->_data[ $position ] ) ) {

					list( $src, $width, $height ) = wp_get_attachment_image_src( $this->_data[ $position ]['ID'], $args['thumbnail_image_size'] );
					$title                        = '';

					if ( 'thumb-title' === $args['thumbnail_structure'] && isset( $this->_data[ $position ]['title'] ) ) {
						$title = $this->_data[ $counter ]['title'];
						$title = apply_filters( 'the_title', $title );
						$title = esc_html( wp_strip_all_tags( $title ) );
					}

					$this->_js_obj['imageparts'][ 'gallery-thumbs-' . $args['thumbnail_image_size'] ]['loaded'][ $position ] = ( $position < $args['number_to_show'] );

					$next_gallery = '';
					if ( $old_url && $old_url !== $this->_data[ $position ]['url'] && $position > 1 ) {
						$next_gallery = sprintf( '<span>%s</span>', esc_html__( 'Next Gallery', 'pmc-gallery-v4' ) );
					}
					// AE: ran into a very bizzare case where $width and $height were not set on certain images on QA. will check for existence before rendering.
					if ( $visible || $all ) {
						$output .= '<div>';
						$output .= '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $title ) . '"';
						if ( $width ) {
							$output .= 'width="' . intval( $width ) . '"';
						}
						if ( $height ) {
							$output .= 'height="' . intval( $height ) . '"';
						}
						$output .= 'data-slug="' . esc_attr( $this->_data[ $position ]['slug'] ) . '" />';
						$output .= esc_html( $title ) . $next_gallery;
						$output .= '</div>';
					} else {
						$output .= '<div style="display:none;" data-content="' . esc_attr( $title ) . '" data-src="' . esc_url( $src ) . '" data-alt="' . esc_attr( $title ) . '" data-slug="' . esc_attr( $this->_data[ $position ]['slug'] ) . '" >';
						$output .= $next_gallery;
						$output .= '</div>';
					}
					$old_url = $this->_data[ $position ]['url'];
				}
			}    //end if/else
		}    //end for loop

		$output .= '</div>'; // close gallery-multi
		$output .= '</div>'; // close gallery-thumbs

		return $output;
	}

	/**
	 * Wrapper Function to fetch Thumbs for Instant Article
	 *
	 * @since   2015-12-08
	 * @version 2015-12-08 Archana Mandhare PCMVIP-411
	 *
	 * @param $args        string | array
	 * @param $deprecated  string
	 * @param $deprecated2 int
	 * @param $deprecated3 string
	 * @param $deprecated4 string
	 * @param $deprecated5 string
	 *
	 * @deprecated
	 *
	 * @return string
	 */
	public function get_the_thumbs_html5( $args = array( 'thumbnail_image_size' => 'gallery-thumb' ), $deprecated = 'filmstrip', $deprecated2 = 5, $deprecated3 = 'thumb', $deprecated4 = Defaults::PREVIOUS_LINK_HTML, $deprecated5 = Defaults::NEXT_LINK_HTML ) {

		/**
		 * Backwards compatibility
		 * @todo Remove backwards compatibility after themes are updated
		 */
		if ( is_string( $args ) ) {
			$args = array(
				'thumbnail_image_size'         => $args,
				'thumbnail_layout'             => $deprecated,
				'number_to_show'               => $deprecated2,
				'thumbnail_structure'          => $deprecated3,
				'thumbnail_previous_link_text' => $deprecated4,
				'thumbnail_next_link_text'     => $deprecated5,
			);
		}

		$default_args = array(
			'thumbnail_image_size'         => 'gallery-thumb',
			'thumbnail_layout'             => 'filmstrip',
			'number_to_show'               => 5,
			'thumbnail_structure'          => 'thumb',
			'thumbnail_previous_link_text' => Defaults::PREVIOUS_LINK_HTML,
			'thumbnail_next_link_text'     => Defaults::NEXT_LINK_HTML,
		);

		$args = wp_parse_args( $args, $default_args );

		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization
		$args['thumbnail_image_size'] = (string) $args['thumbnail_image_size'];
		$valid_sizes                  = \PMC\Image\get_intermediate_image_sizes();

		if ( ! in_array( $args['thumbnail_image_size'], (array) $valid_sizes, true ) ) {
			$args['thumbnail_image_size'] = 'gallery-thumb';
		}

		$args['thumbnail_layout'] = (string) $args['thumbnail_layout'];

		if ( ! in_array( $args['thumbnail_layout'], array( 'all', 'filmstrip', 'filmstrip-scroll' ), true ) ) {
			$args['thumbnail_layout'] = 'filmstrip';
		}

		$args['thumbnail_structure'] = (string) $args['thumbnail_structure'];

		if ( ! in_array( $args['thumbnail_structure'], array( 'thumb', 'thumb-title' ), true ) ) {
			$args['thumbnail_structure'] = 'thumb';
		}

		$args['number_to_show'] = intval( $args['number_to_show'] );

		// If the preview number is greater than the number of images in the gallery, it will cause chaos.  So here we do a simple sanity check.
		if ( $args['number_to_show'] > $this->_js_obj['gallery_count'] ) {
			$args['number_to_show'] = $this->_js_obj['gallery_count'];
		}

		// Sanity check
		if ( $args['number_to_show'] < 1 ) {
			$args['number_to_show'] = 1;
		}

		$start = ( intval( $this->_js_obj['gallery_start'] ) + intval( $this->_js_obj['gallery_first'] ) );

		// Preview which Thumbs
		// @todo This seems a little...magical. Need to understand it better.
		if ( ! is_null( self::$linked_gallery ) ) {
			$limit = min( ( $args['number_to_show'] + 1 ), $this->_get_image_count() );
		} else {
			$limit = max( ( $args['number_to_show'] + 2 ), $this->_get_image_count() );
		}

		$output = '';

		if ( ! empty( $limit ) ) {

			// Render HTML
			$output = '<figure class="op-slideshow">';

			// Next Gallery Flagging using url
			for ( $counter = 0; $counter < $limit; $counter ++ ) {
				if ( ! is_null( self::$linked_gallery ) ) {
					if ( $counter <= self::$linked_gallery ) {
						continue;
					}

					$data_index = $counter - 1 + $start;

					// @todo $src is the full image src, probably a bug in _register_default_image_sizes()
					$image_src = wp_get_attachment_image_src( $this->_data[ $data_index ]['ID'], $args['thumbnail_image_size'] );
					$src       = ( ! empty( $image_src['src'] ) ) ? $image_src['src'] : '';

					if ( false !== strpos( $src, '?' ) ) {
						$image_url = substr( $src, 0, strpos( $src, '?' ) );
						$src       = empty( $image_url ) ? $src : $image_url;
					}
					$output .= '<img src="' . esc_url( $src ) . '" />';
				}
			}
			$output .= '</figure>'; // close gallery-multi
		}

		return $output;
	}

	/**
	 * Get the thumbs.
	 *
	 * @param array  $args        Arguments.
	 * @param string $deprecated  Deprecated param filmstrip.
	 * @param int    $deprecated2 Deprecated param
	 * @param string $deprecated3 Deprecated param
	 * @param string $deprecated4 Deprecated param
	 * @param string $deprecated5 Deprecated param
	 *
	 *
	 * @todo Remove backwards compatibility args after themes are updated
	 *
	 * @deprecated
	 *
	 * @return void
	 */
	public function the_thumbs( $args = array( 'thumbnail_image_size' => 'gallery-thumb' ), $deprecated = 'filmstrip', $deprecated2 = 5, $deprecated3 = 'thumb', $deprecated4 = Defaults::PREVIOUS_LINK_HTML, $deprecated5 = Defaults::NEXT_LINK_HTML ) {
		echo $this->get_the_thumbs( $args, $deprecated, $deprecated2, $deprecated3, $deprecated4, $deprecated5 ); // XSS okay - Escaping done in the method.
	}

	/**
	 * Returns Meta attached to current image or '' if none with Markup
	 *
	 * @param string $meta_name Meta name.
	 * @param string $prefix    Prefix
	 * @param string $suffix    Suffix
	 *
	 * @return string
	 */
	public function get_the_meta( $meta_name, $prefix = '', $suffix = '' ) {
		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization
		$meta_name = (string) $meta_name;

		$position = ( intval( $this->_js_obj['gallery_start'] ) + intval( $this->_js_obj['gallery_first'] ) );

		// Render HTML
		$output  = '<div class="gallery-meta gallery-meta-' . esc_attr( $meta_name ) . '">';
		$output .= '<div class="gallery-multi">';

		for ( $counter = 0; $counter < $this->_get_image_count(); $counter ++ ) {
			$extra = '';

			// If this is a linked gallery preview, skip ahead to the preview image. Linked gallery preview will stop after one iteration of the loop.
			if ( ! is_null( self::$linked_gallery ) ) {
				$counter = $position;
			}

			if ( $counter !== $position ) {
				$extra = ' style=display:none;';
			}

			$data = get_post_meta( $this->_data[ $counter ]['ID'], $meta_name, true );

			if ( $data && ( $prefix || $suffix ) ) {
				$data = $prefix . $data . $suffix;
			}

			$output .= '<div' . esc_attr( $extra ) . '>' . esc_html( $data ) . '</div>';

			if ( ! is_null( self::$linked_gallery ) ) {
				break;
			}
		}

		$output .= '</div>'; // end gallery-multi
		$output .= '</div>'; // end gallery-meta

		$this->_js_obj['multiparts'][] = 'gallery-meta-' . $meta_name;

		return apply_filters( 'pmc_gallery_get_the_meta', $output, $this, $meta_name, $prefix, $suffix );
	}

	/**
	 * Get the meta.
	 *
	 * @param string $meta_name meta name.
	 * @param string $prefix Prefix.
	 * @param string $suffix Suffix
	 *
	 * @return void
	 */
	public function the_meta( $meta_name, $prefix = '', $suffix = '' ) {
		echo $this->get_the_meta( $meta_name, $prefix, $suffix ); // XSS okay - Escaping done in the method.
	}

	/**
	 * Function is used to overwrite the HTML output of get_the_meta
	 * for `image_credit` field.
	 *
	 * @filter pmc_gallery_get_the_meta
	 *
	 * @param string            $output    HTML Output.
	 * @param \PMC\Gallery\View $context   current object of .
	 * @param string            $meta_name meta key.
	 * @param string            $prefix    string that will prepend brfore data.
	 * @param string            $suffix    string that will append after data.
	 *
	 * @return string
	 */
	public function pmc_gallery_get_the_meta( $output, $context, $meta_name, $prefix, $suffix ) {
		if ( '_image_credit' === $meta_name ) {
			$position = ( intval( $context->_js_obj['gallery_start'] ) + intval( $context->_js_obj['gallery_first'] ) );
			$output   = '<div class="gallery-meta gallery-meta-' . esc_attr( $meta_name ) . '">';
			$output  .= '<div class="gallery-multi">';
			for ( $counter = 0; $counter < $context->_get_image_count(); $counter ++ ) {
				// If this is a linked gallery preview, skip ahead to the preview image. Linked gallery preview will stop after one iteration of the loop.
				if ( ! is_null( self::$linked_gallery ) ) {
					$counter = $position;
				}

				$extra = '';
				if ( $counter !== $position ) {
					$extra = ' style=display:none;';
				}
				$data = sanitize_text_field( $context->_data[ $counter ]['image_credit'] );

				if ( $data && ( $prefix || $suffix ) ) {
					$data = $prefix . $data . $suffix;
				}
				$output .= '<div' . esc_attr( $extra ) . '>' . esc_html( $data ) . '</div>';
				if ( ! is_null( self::$linked_gallery ) ) {
					break;
				}
			}
			$output .= '</div>'; // end gallery-multi.
			$output .= '</div>'; // end gallery-meta.
		}

		return $output;
	}

	/**
	 * Returns Excerpt attached to current image with Markup
	 */
	public function get_the_caption( $length = 30 ) {
		$caption = $this->_field_length( 'caption', $length );
		if ( ! empty( $caption ) ) {
			return $caption;
		} else {
			return $this->_field_length( 'description', $length );
		}
	}

	public function the_caption( $length = 30 ) {
		echo do_shortcode( $this->get_the_caption( $length ) );
	}

	protected function _field_length( $field, $length ) {
		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		$length = intval( $length );

		$position = ( intval( $this->_js_obj['gallery_start'] ) + intval( $this->_js_obj['gallery_first'] ) );

		// Render HTML
		$output  = '<div class="gallery-' . esc_attr( $field ) . ' gallery-' . esc_attr( $field ) . '-' . esc_attr( $length ) . '">';
		$output .= '<div class="gallery-multi">';

		for ( $counter = 0; $counter < $this->_get_image_count(); $counter++ ) {
			$extra = '';

			if ( ! is_null( self::$linked_gallery ) ) {
				$counter = $position;
			}

			if ( $counter !== $position ) {
				$extra = ' style=display:none;';
			}

			$output .= '<div' . esc_attr( $extra ) . '>' . wp_kses_post( force_balance_tags( \PMC::truncate( $this->_data[ $counter ][ $field ], $length ) ) ) . '</div>';

			if ( ! is_null( self::$linked_gallery ) ) {
				break;
			}
		}
		$output .= '</div>'; // end gallery-multi wrapper
		$output .= '</div>'; // end gallery-* wrapper

		$this->_js_obj['multiparts'][] = 'gallery-' . esc_js( $field ) . '-' . esc_js( $length );

		return $output;
	}

	/**
	 * Returns Title attached to current image with Markup
	 */
	public function get_the_title( $length = 30 ) {
		return $this->_field_length( 'title', $length );
	}

	public function the_title( $length = 30 ) {
		echo $this->get_the_title( $length ); // XSS okay - Escaping done in the method.
	}

	/**
	 * Returns Date attached to current image with Markup
	 *
	 * @param string $format Date format.
	 *
	 * @return string
	 */
	public function get_the_date( $format = 'd-M-Y' ) { // @codingStandardsIgnoreLine - gettexted okay.

		// No Image
		if ( 0 === count( $this->_data ) ) {
			return '';
		}

		$format = (string) $format;

		$output = '';

		$position = intval( $this->_js_obj['gallery_start'] ) + intval( $this->_js_obj['gallery_first'] );

		// Render HTML
		$output .= "<div class='gallery-date' ><div class='gallery-multi' >";

		for ( $counter = 0; $counter < count( $this->_data ); $counter ++ ) {
			$extra = '';

			if ( 0 === self::$linked_gallery ) {
				$counter = $position;
			}

			if ( $counter !== $position ) {
				$extra = ' style=display:none; ';
			}

			$output .= '<div' . esc_attr( $extra ) . '>' . esc_html( mysql2date( $format, $this->_data[ $counter ]['date'] ) ) . '</div>';

			if ( 0 === self::$linked_gallery ) {
				break;
			}
		}
		$output .= '</div></div>';

		$this->_js_obj['multiparts'][] = 'gallery-date';

		return $output;
	}

	public function the_date( $format = 'd-M-Y' ) { // @codingStandardsIgnoreLine - gettexted okay.
		echo $this->get_the_date( $format ); // XSS okay - Escaping done in the method.
	}

	/**
	 * Helper Function to Fetch Gallery Navigation
	 *
	 * @todo Remove deprecated args after themes have been refactored
	 *
	 * @param string $deprecated Deprecated.
	 * @param array  $options Options.
	 *
	 * @return string
	 */
	public function get_the_navigation(
		$deprecated = 'both', $options = array(
			Defaults::PREVIOUS_LINK_HTML,
			Defaults::NEXT_LINK_HTML,
		)
	) {
		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization
		$previous_label = ( isset( $options[0] ) ) ? (string) $options[0] : Defaults::PREVIOUS_LINK_HTML;
		$next_label     = ( isset( $options[1] ) ) ? (string) $options[1] : Defaults::NEXT_LINK_HTML;

		// @todo Don't use hrefs
		$output  = '<a  class="gallery-navigation gallery-navigation-previous">';
		$output .= wp_kses_post( $previous_label );
		$output .= '</a>';
		$output .= '<a class="gallery-navigation gallery-navigation-next">';
		$output .= wp_kses_post( $next_label );
		$output .= '</a>';

		return $output;
	}

	/**
	 * Get the navigation.
	 *
	 * @todo Remove deprecated args after themes have been refactored
	 *
	 * @param string $deprecated
	 * @param array  $options
	 *
	 * @return void
	 */
	public function the_navigation(
		$deprecated = 'both', $options = array(
			Defaults::PREVIOUS_LINK_HTML,
			Defaults::NEXT_LINK_HTML,
		)
	) {
		echo wp_kses_post( $this->get_the_navigation( $deprecated, $options ) );
	}

	/**
	 * Helper Function to Fetch Gallery back to post link
	 *
	 * @param string $backtext Back text
	 *
	 * @return string
	 */
	public function get_the_backlink( $backtext = '' ) {
		$backtext = ( empty( $backtext ) ) ? sprintf( '%s &crarr;', __( 'Return to Article', 'pmc-gallery-v4' ) ) : $backtext;

		// @todo Remove hrefs
		$output  = '<div class="pmc-gallery-top"><a  class="gallery-back" >';
		$output .= esc_html( $backtext );
		$output .= '</a></div>';

		return $output;
	}

	public function the_backlink( $backtext = '' ) {
		$backtext = empty( $backtext ) ? __( 'Return to Article', 'pmc-gallery-v4' ) : $backtext;
		echo wp_kses_post( $this->get_the_backlink( $backtext ) );
	}

	/**
	 * Helper Function to Fetch Gallery Image Count
	 *
	 * @param string $style Style.
	 *
	 * Style can be XofY or total
	 * for 3 of 6 or 6
	 * this can be extended to have a better glue 'of'
	 *
	 * @return string
	 */
	public function get_the_count( $style = 'XofY' ) { //@codingStandardsIgnoreLine - gettexted okay.
		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization
		$style = (string) $style;
		if ( ! in_array( $style, array( 'XofY', 'total' ), true ) ) {
			$style = 'XofY'; // @codingStandardsIgnoreLine - getText okay.
		}

		// Default start 1
		$current = ( intval( $this->_js_obj['gallery_first'] ) + 1 );
		$total   = intval( $this->_js_obj['gallery_count'] );

		// Output Image Count
		// fetch style from language
		// Replace X Y
		$output = '<span class="gallery-count gallery-count-' . esc_attr( $style ) . '" >';
		if ( 'XofY' === $style ) {
			$output .= '<span class="current">' . intval( $current ) . '</span> of ';
			$output .= '<span class="total">' . intval( $total ) . '</span>';
		} else {
			$output .= '<span class="count">' . intval( $total ) . '</span>';
		}
		$output .= '</span>';

		return $output;
	}

	public function the_count( $style = 'XofY' ) { // @codingStandardsIgnoreLine - gettexted Okay.
		echo $this->get_the_count( $style ); // XSS okay - Escaping done in the method.
	}

	/**
	 * Helper Function to Fetch Gallery URL
	 *
	 * @param bool $append_hash_bang
	 *
	 * @return string
	 */
	public function get_the_permalink( $append_hash_bang = false ) {
		// No Image
		if ( is_null( self::$id ) ) {
			return '';
		}

		$permalink = null;

		if ( $append_hash_bang && ! empty( $this->_data ) ) {
			$position = ( intval( $this->_js_obj['gallery_start'] ) + intval( $this->_js_obj['gallery_first'] ) );
			if ( ! empty( $this->_data[ $position ] ) ) {
				$slug      = $this->_data[ $position ]['slug'];
				$pos       = ( isset( $this->_data[ $position ]['position'] ) ) ? (int) $this->_data[ $position ]['position'] : 1;
				$permalink = get_permalink( self::$id ) . '#!' . esc_url( $pos ) . '/' . esc_url( $slug );
			}
		}

		if ( is_null( $permalink ) ) {
			$permalink = get_permalink( self::$id );
		}

		return $permalink;
	}

	/**
	 * Echos the URL for the gallery
	 */
	public function the_permalink() {
		echo esc_url( $this->get_the_permalink() );
	}

	/**
	 * Get gallery post content including featured image.
	 * Unused method.
	 *
	 * @param int $gallery_id Gallery id.
	 */
	public static function get_image_credit( $attachment_id ) {}

	/**
	 * Render js variable so that we know that pmc-gallery is being rendered
	 * If its gallery then url should have hash, is no hash then attribute analytics with first images hash.
	 *
	 * @param array  $data
	 * @param string $first_image_name
	 */
	public function render_js_variable( $data = array(), $first_image_name = '' ) {}

}
