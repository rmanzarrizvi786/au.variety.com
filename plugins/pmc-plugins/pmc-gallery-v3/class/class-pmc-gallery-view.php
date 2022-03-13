<?php

/**
 * Displays linked galleries
 *
 * The Gallery Rendering Class
 * Handles 3 kinds of Galleries
 *  Stand Alone
 *  Enbedded
 *  Preview
 *
 * Loads Gallery and shows the images which currently needs to be shown
 * For eg . for a 15 image gallery if 3 thumbs are shown at a time
 * the gallery will load 1 full image and 3 thumbs
 *
 * For Galleries other than Preview the JS handles the below
 * The next possible needed image is loaded as well
 * For eg. Continuing the above Example
 * 2 thumbs to the right
 * 1 thum to the left and
 * 2 full Images
 * are loaded in the background
 * As the gallery moves the same pattern is continued
 * Any Image the user can possibly click on is loaded in the background
 *
 * The gallery has basic elements
 * Stage
 * Thumbs
 * Navigation Butons
 * Title
 * Caption
 * Meta
 * Count
 *
 * each of the above is a list of data with only the relevant visible
 *
 *
 * @package PMC Gallery Plugin
 * @since 1/1/2013 Vicky Biswas
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Gallery_View {

	use Singleton;

	protected $_js_obj = array(
		'gallery_first'               => 0,
		'ad_refresh_clicks'           => 2,
		'enable_interstitial'         => false,
		'interstitial_refresh_clicks' => 25,
		'interstitial_duration'       => 10,
		'start_with_interstitial'     => false,
		'variable_height'             => false,
		'gallery_start'               => 0,
		'gallery_count'               => 0,
		'auto_start_delay'            => 0,
		'continuous_cycle'            => true,
		'imageparts'                  => array(),
		'multiparts'                  => array(),
	);

	public static $linked_gallery = null;
	public static $id = null;
	protected static $_data_cache_group = 'pmc-gallery-data';
	protected $_data = null;
	public $is_shortcode = false;
	protected $_has_linked_gallery = null;
	protected $_has_gallery = null;
	protected $_number_of_images = null;

	// use to save the pageview events that we remove so we can override and add hash value ro page url
	protected $_pageview_event_names = array();

	private $_term_id_adjacent_post;

	/**
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		add_filter( 'pmc_google_analytics_track_pageview', array(
			$this,
			'filter_pmc_google_analytics_track_pageview'
		) );

		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'footer_scripts' ) );

		// Run this late so that themes have a change to register their own default	sizes
		add_action( 'init', array( $this, 'register_defaults' ), 99 );

		add_filter( 'post_gallery', array( $this, 'gallery_shortcode' ), 100, 2 );

		add_action( 'init', array( $this, 'action_init' ) );
		add_filter( 'pmc_gallery_get_the_meta', array( $this, 'pmc_gallery_get_the_meta' ), 10, 5 );

		// To remove the responsive ad skins from all the gallery pages of all brands.
		add_filter( 'pmc_adm_dfp_skin_enabled', array( $this, 'remove_responsive_ad_skins' ) );

		add_filter( 'pmc_seo_tweaks_googlebot_news_override', array( $this, 'maybe_exclude_googlebot_news_tag' ) );

	}

	public function action_init() {
		// add this filter to allow feed to fetch gallery data without making call to function directly
		add_filter( 'pmc_fetch_gallery', array( $this, 'filter_pmc_fetch_gallery' ), 10, 2 );
		add_filter( 'pmc_gallery_linked_post', array( $this, 'filter_pmc_gallery_linked_post' ), 10, 2 );
	}

	// do fetch gallery data and return
	public function filter_pmc_fetch_gallery( $gallery_data = array(), $id = 0 ) {
		if ( PMC_Gallery_Defaults::name == get_post_type( $id ) ) {
			$gallery_data = self::fetch_gallery( $id );
		}

		return $gallery_data;
	}

	/**
	 * Fetch linked post ID.
	 *
	 * @param int $linked_post_id Post ID to which the gallery is linked.
	 * @param int $gallery_id     Gallery ID.
	 *
	 * @return int|bool Post ID or false if $gallery_id is not assigned to any post.
	 */
	public function filter_pmc_gallery_linked_post( $linked_post_id, $gallery_id ) {

		$gallery_id = absint( $gallery_id );

		if ( 0 !== $gallery_id && PMC_Gallery_Defaults::name === get_post_type( $gallery_id ) ) {
			$linked_post_id = absint( self::get_linked_post_id( $gallery_id ) );
		}

		return $linked_post_id;
	}

	public function register_defaults() {
		$this->_register_default_settings();
		$this->_register_default_image_sizes();
	}

	protected function _register_default_settings() {
		//Get Settings
		$settings      = PMC_Gallery_Settings::get_instance()->get_options();
		$this->_js_obj = wp_parse_args( $settings, $this->_js_obj );

		if ( defined( 'PMC_GALLERY_VARIABLE_HEIGHT' ) ) {
			$this->_js_obj['variable_height'] = (bool) PMC_GALLERY_VARIABLE_HEIGHT;
		}

	}

	protected function _register_default_image_sizes() {
		// $max_height gets calculated by aspect ratio
		$aspect_ratio_w = 4;
		$aspect_ratio_h = 3;

		if ( isset( $GLOBALS['content_width'] ) ) {
			// Make it a little smaller than the $content_width to allow for
			// margines and padding.
			$max_width = intval( $GLOBALS['content_width'] * 0.8 );
		} else {
			$max_width = 600;
		}

		$max_height       = round( ( $aspect_ratio_h * $max_width ) / $aspect_ratio_w );
		$registered_sizes = \PMC\Image\get_intermediate_image_sizes();

		if ( ! in_array( 'pmc-gallery-image', $registered_sizes, true ) ) {
			add_image_size( 'pmc-gallery-image', $max_width, $max_height, true );
		}

		// default gallery-thumb size, each theme may override this default via add_image_size
		// note: gallery-thumb name is use for backward compatible with existing theme
		if ( ! in_array( 'gallery-thumb', $registered_sizes, true ) ) {
			$width  = intval( $max_width / 4 );
			$height = round( ( $aspect_ratio_h * $width ) / $aspect_ratio_w );
			add_image_size( 'gallery-thumb', $width, $height, true );
		}
	}

	protected function _get_image_count() {
		if ( is_null( $this->_number_of_images ) && ! is_null( $this->_data ) ) {
			$this->_number_of_images = ( $this->_data );

		}

		return $this->_number_of_images;
	}

	public function has_linked_gallery() {
		if ( is_null( $this->_has_linked_gallery ) ) {
			$this->_has_linked_gallery = false;

			// Being very verbose with "has preview" tests to make it clear what's being tested and what the hierarchy is.
			// There's only one test now, but it's possible for it to grow to more tests like $this->has_gallery()
			if ( is_singular() ) {
				if ( ! $this->_has_linked_gallery ) {
					$this->_has_linked_gallery = (bool) get_post_meta( get_queried_object_id(), 'pmc-gallery-linked-gallery', true );
				}
			}
		}

		return $this->_has_linked_gallery;
	}

	public function has_gallery() {
		if ( is_null( $this->_has_gallery ) ) {
			$this->_has_gallery = false;

			// Being very verbose with "has gallery" tests to make it clear what's being tested and what the hierarchy is.
			if ( is_singular() ) {
				if ( ! $this->_has_gallery ) {
					$this->_has_gallery = ( PMC_Gallery_Defaults::name === get_post_type() );
				}

				if ( ! $this->_has_gallery ) {
					$this->_has_gallery = ( isset( $GLOBALS['post']->post_content ) && ( strpos( $GLOBALS['post']->post_content, '[gallery' ) !== false ) );
				}
			}
		}

		return $this->_has_gallery;
	}

	/**
	 * Enqueue styles, scripts and script data
	 */
	public function action_wp_enqueue_scripts() {
		if ( $this->has_gallery() ) {
			wp_enqueue_script( 'swipe-js' );
			wp_enqueue_script( 'qs-js' );
			wp_enqueue_script( 'jquery-hashchange' );
			wp_enqueue_script( 'browserdetect' );
			wp_enqueue_script( 'pmc-gallery' );
		}

		if ( $this->has_gallery() || $this->has_linked_gallery() ) {
			wp_enqueue_style( PMC_Gallery_Defaults::name );
		}

	}

	/**
	 * Footer
	 */
	public function footer_scripts() {
		if ( ! $this->has_gallery() ) {
			return;
		}

		// Force-disable the interstitial if the post is in our blocklist
		if ( PMC_Gallery_Settings::get_instance()->no_ads_on_this_post() ) {
			$this->_js_obj['enable_interstitial'] = false;
		}

		$js_data = ( empty( $this->_js_obj ) ) ? '""' : json_encode( $this->_js_obj );
		?>
		<script type="text/javascript">

			var pmc_gallery_jsdata = <?php echo $js_data; ?>;


		</script>
		<?php
	}

	/**
	 * Load Gallery
	 * @todo: Preview Enum Define
	 *
	 * @param null|int $linked_gallery
	 *                   null No Preview
	 *                   0 Preview with first image provided by gallery
	 *                   1 Preview with first image provided by theme
	 *                   <0 Treated as 1
	 */
	public function load_gallery( $gallery = null, $linked_gallery = null ) {
		//sanitize $linked_gallery
		if ( ! is_null( $linked_gallery ) ) {
			$linked_gallery = (int) $linked_gallery;
			if ( $linked_gallery < 0 ) {
				$linked_gallery = 1;
			}
		}

		$prev = '';
		$next = '';

		self::$linked_gallery = $linked_gallery;

		$data = self::fetch_gallery( $gallery );

		if ( ! $data ) {
			return $this;
		}


		$this->render_js_variable( wp_list_pluck( $data, 'slug' ), $data[0]['slug'] );

		if ( ! $this->has_gallery() && ! $this->has_linked_gallery() && is_null( $linked_gallery ) ) {
			return $this;
		}

		/**
		 * AE: by default set all LOB's to non continous. LOBs can opt in to the countinous
		 * gallery functionality.
		 */
		$continuous = apply_filters( 'pmc-gallery-continuous', false );


		//get prev n next galleries
		if ( get_post_type() === PMC_Gallery_Defaults::name && $continuous ) {
			$prev_post = apply_filters( 'pmc-gallery-get-next-post', null );
			if ( empty( $prev_post ) ) {
				$prev_post = get_next_post();
			}

			if ( $prev_post ) {
				$prev                           = self::fetch_gallery( $prev_post->ID );
				$this->_js_obj['gallery_start'] = 1;
			}

			$next_post = apply_filters( 'pmc-gallery-get-previous-post', null );
			if ( empty( $next_post ) ) {
				$next_post = get_previous_post();
			}

			if ( $next_post ) {
				$next                           = self::fetch_gallery( $next_post->ID );
				$this->_js_obj['gallery_start'] = 0;
			}
		}
		$this->_js_obj['gallery_count'] = count( $data );

		if ( $prev == '' && $next == '' ) {
			// We need to start at 0 since we have no prev/next set of data
			// Swipe will trigger circular swipe properly.
			$this->_js_obj['gallery_start'] = 0;
			$this->_number_of_images        = count( $data );
		} else {

			//Fetch other galleries
			//AE: if in fact this post has a gallery next to it and it isn't the last gallery we want to merge
			//the next gallery to the currrent gallery so that when we render swipe.js can go through all the photos.
			if ( is_array( $next ) ) {

				/*
				 * Merge whole next gallery into $data as we'd need to pick
				 * more than one thumb from next gallery based on how much
				 * padding we're adding. All that is decided later on at time
				 * of render. Adding extra items in an array here & then discarding
				 * it down the line is not much of an overhead since we already
				 * have fetched whole of next gallery.
				 *
				 * @since 2016-08-03 Amit Gupta
				 */
				$data = array_merge( $data, $next );

			}

			//AE: There are 2 scenarious here:
			//Scenario one: you have a previous gallery and you have a next gallery in this case we want to add the last item of the previous gallery to the first position of the current gallery. that way if you hit the back button and you are on the first photo you should be able to see the last photo of the previous gallery.
			//Scenario two: you have a previous Gallery, but there is no next gallery. so if you hit the forward button and you are the end of the gallery you should go to the first photo of the previous Gallery. in a circular motion. and if you are at the first photo of the last gallery and hit the back button you should still be able to go to the last photo of the previous gallery like described in scenario one. I sure do hope this makes sense to anyone reading this.
			//scenario one
			if ( is_array( $prev ) ) {
				// prepend last item from previous gallery
				array_unshift( $data, end( $prev ) );
				$this->_js_obj['gallery_start'] = 1; // current first item start at 1 since prepend a new item above
			}
			//Scenario two
			if ( is_array( $prev ) && $next == '' ) {
				reset( $prev );
				array_push( $data, current( $prev ) );
			}
			$this->_number_of_images = count( $data );
		}
		//respecting _escaped_fragment_
		$_escaped_fragment = ( isset( $_GET['_escaped_fragment_'] ) ) ? intval( $_GET['_escaped_fragment_'] ) : 0;
		if ( $_escaped_fragment > 0 && $_escaped_fragment <= $this->_js_obj['gallery_count'] ) {
			$this->_js_obj['gallery_first'] = ( $_escaped_fragment - 1 );
		}

		//create object and return
		$this->_data = $data;

		return $this;

	}

	/**
	 * Fetch Gallery
	 *
	 * @param mixed $gallery May be a string or int with a single gallery ID, an
	 *                         ordered array of WP_Post objects, an unordered
	 *                         array of post arrays, or an unordered array of post
	 *                         IDs
	 * @param bool $invalidated Not used
	 */
	public static function fetch_gallery( $gallery = null, $invalidated = false ) {
		global $post;
		$id           = 0;
		$gallery_data = [];

		// Nothing passed use post id.
		if ( $gallery == null ) {
			if ( isset( $post->ID ) ) {
				$gallery = $post->ID;
			} else {
				return null;
			}
		}

		/**
		 * Get gallery Id.
		 *
		 * @todo $this->load_gallery() calls this method multiple times
		 * for different galleries (e.g., previous & next gallery).     So in some
		 * circumstances this internal pointer gets set to an ID that's *not*
		 * the current gallery.     This looks like a bug.
		 *
		 * @todo The var name of self::$id indicates it expects a single ID, but
		 * $gallery may be a whole bunch of different things. This looks like a bug.
		 */
		self::$id = $gallery;
		// Gallery ID Passed.
		if ( is_int( $gallery ) ) {
			if ( $gallery == 0 ) {
				return null;
			}
			// Saving post id for use later on.
			$id = $gallery;
			// If gallery has meta.
			$meta = get_post_meta( $gallery, PMC_Gallery_Defaults::name, true );
			$gallery = $meta;
		} elseif ( is_string( $gallery ) ) {
			/**
			 * Convert gallery id string into array.
			 *
			 * @todo $gallery may be equal to $GLOBALS['post']->ID, which may be
			 * a string.  That means this condition may be met when we really
			 * wanted the previous condition.  This looks like a bug.
			 */
			$gallery = explode( ',', $gallery );
		}

		if ( is_array( $gallery ) ) {
			$cache_key    = md5( $id . implode( '|', $gallery ) );
			$cache        = new PMC_Cache( $cache_key, self::$_data_cache_group );
			$gallery_data = $cache->expires_in( HOUR_IN_SECONDS )
				->updates_with(
					static function () use ( $gallery, $id ): array {
						return self::_get_gallery_data( $gallery, $id );
					}
				)
				->get();

			// For back-compat. Previously, an empty array was passed to the `pmc_gallery_data` filter if items weren't found.
			if ( ! is_array( $gallery_data ) ) {
				// A non-array is only possible if PMC_Cache is called incorrectly, which would cause existing gallery-creation tests to fail.
				$gallery_data = []; // @codeCoverageIgnore
			}
		}

		return apply_filters( 'pmc_gallery_data', $gallery_data, $gallery, $invalidated );
	}

	/**
	 * Retrieve a gallery's components.
	 *
	 * @param array $gallery Gallery items.
	 * @param int   $id      Gallery ID.
	 * @return array
	 */
	protected static function _get_gallery_data( array $gallery, int $id ): array {
		$gallery_data       = [];
		$pos                = 0;
		$gallery_link       = get_permalink( $id );
		$gallery_attachment = \PMC\Gallery\Attachment\Detail::get_instance();

		// Ready gallery data for response.
		foreach ( $gallery as $variant_id => $id ) {
			$variant_id = intval( $variant_id );
			$id         = intval( $id );
			// Get variant data.
			$variant = get_post( $variant_id );
			// Get attachment detail which is use as default data.
			$attachment = get_post( $id );

			// if no attachment found with give id then skip it.
			if ( ! $attachment ) {
				continue;
			}
			$gallery_custom_data = [];
			if ( $variant && \PMC\Gallery\Attachment\Detail::name === $variant->post_type ) {
				$variant_meta = $gallery_attachment->get_variant_meta( $variant_id );
				if ( ! empty( $variant_meta ) && is_array( $variant_meta ) ) {
					$gallery_custom_data = $variant_meta;
				}
			}

			$attachment_meta = get_post_meta( $id );
			$data_to_fill    = [
				'title'        => $attachment->post_title,
				'description'  => $attachment->post_content,
				'caption'      => $attachment->post_excerpt,
				'alt'          => ! empty( $attachment_meta['_wp_attachment_image_alt'][0] ) ? $attachment_meta['_wp_attachment_image_alt'][0] : '',
				'image_credit' => ! empty( $attachment_meta['_image_credit'][0] ) ? $attachment_meta['_image_credit'][0] : '',
			];

			foreach ( $data_to_fill as $key => $value ) {
				if ( empty( $gallery_custom_data[ $key ] ) ) {
					$gallery_custom_data[ $key ] = $value;
				}
			}

			$pos++;
			$gallery_data[] = array(
				'ID'                    => $id,
				'image'                 => $attachment->guid,
				'date'                  => $variant->post_date,
				'modified'              => $variant->post_modified,
				'title'                 => apply_filters( 'the_title', $gallery_custom_data['title'] ),
				'slug'                  => $attachment->post_name,
				'description'           => sanitize_text_field( $gallery_custom_data['description'] ),
				'pinterest_description' => sanitize_text_field( $gallery_custom_data['pinterest_description'] ),
				'image_credit'          => sanitize_text_field( $gallery_custom_data['image_credit'] ),
				'caption'               => apply_filters( 'the_content', $gallery_custom_data['caption'] ),
				'position'              => $pos,
				'url'                   => $gallery_link,
				'mime_type'             => $attachment->post_mime_type,
			);
		}

		return $gallery_data;
	}

	/**
	 * Wrapper Function to fetch Image. This loads the image which will be main image in the stage.
	 *
	 * @param string $size
	 * @param int $images_to_show extra images count that needs to be shown for cyclic gallery thumbs
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

		// @todo change the css classes to class="gallery-image ' . esc_attr( $size ) . '"
		$output .=
			'<div class="gallery-image gallery-image-' . esc_attr( $size ) . '" >';
		$output .= '<div class="gallery-multi">';

		// Preview with first image provided by theme
		// Not sure what that means exactly, but this is for gallery previews where the preview image == the first image of the gallery.
		if ( 1 === self::$linked_gallery ) {
			list( $src, $width, $height ) = wp_get_attachment_image_src( $this->_data[ $position ]['ID'], $size );
			$slug = $this->_data[ $position ]['slug'];
			$alt  = $this->_data[ $position ]['title'];
			$pos  = $this->_data[ $position ]['position'];

			$post_url = parse_url( get_permalink() );

			$image_url = get_permalink( self::$id ) . '#!' . intval( $pos ) . '/' . esc_attr( $slug );

			// Add ref param to the gallery link to trigger "Return to article" link
			$ref_path = parse_url( get_permalink(), PHP_URL_PATH );
			if ( $ref_path ) {
				// Not using add_query_arg() here because we're adding a param to a hash, not querystring
				$image_url .= '&ref=' . $ref_path . 'pos=';
			}

			$output .= '<figure>';
			$output .=
				'<a href="' .
				esc_url( $image_url ) .
				'" >';
			$output .=
				'<img data-original="' .
				esc_url( $src ) .
				'" src="' .
				esc_url( $src ) .
				'" alt="' .
				esc_attr( $alt ) .
				'" class="full lazy" scale="0" style="display: block;">';
			$output .= '</a>';
			$output .= '</figure>';

		} // No Preview
		elseif ( is_null( self::$linked_gallery ) ) {

			$image_count = $this->_get_image_count();
			// if we want image padding ( extra images count ) for cyclic gallery
			if ( ! empty( $images_to_show ) ) {
				$image_count = $this->get_count_for_cyclic_image( $images_to_show );
				$image_total = $this->_get_image_count();
			}

			for ( $i = 0; $i < $image_count; $i ++ ) {

				if ( ! empty( $images_to_show ) && ! empty( $image_total ) ) {
					//Modifying the counter here for cyclic gallery. Since the number of images in $this->_data will be less, we will need to reset counter back ( strarting from 0 ) to get images data.
					$counter = ( $i + $image_total ) % $image_total;
				} else {
					$counter = $i;
				}

				$image_html = '';
				list( $src, $width, $height ) = wp_get_attachment_image_src( $this->_data[ $counter ]['ID'], $size );

				$credits = get_post_meta( $this->_data[ $counter ]['ID'], '_image_credit', true );
				if ( $counter == $position ) {
					$image_html .= '<div data-src="' .
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
						$image_html .=
							'<div class="credits">' .
							esc_html( $credits ) .
							'</div>';
					}
					$image_html .=
						'<img src="' .
						esc_url( $src ) .
						'" alt="' .
						esc_attr( $this->_data[ $counter ]['title'] ) .
						'" data-slug="' .
						esc_attr( $this->_data[ $counter ]['slug'] ) .
						'" />';
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
						$image_html .= '<span class="credits">' .
						               esc_html( $credits ) .
						               '</span>';
					}
					$image_html .= '</div>';
				}

				$output .= apply_filters( 'pmc_gallery_the_image', $image_html, $this->_data[ $counter ] );
			} // for

		}

		$output .= '</div>'; // close gallery-multi

		// Add the Gallery interstitial ad to the markup at the end of the images
		if ( function_exists( 'pmc_adm_render_ads' ) ) {
			$ad_code = pmc_adm_render_ads( 'Gallery Interstitial', '', false );
		} else {
			$ad_code = '';
		}

		$output .= '<span class="gallery-interstitial">';

		// Display the 'Skip Ad' link if it's been enabled
		// in the Cheezcap settings
		if ( ! empty( $this->_js_obj['skip_ad'] ) && true === $this->_js_obj['skip_ad'] ) {
			$output .= '<span class="skip-ad">' . __( 'Skip Ad', 'pmc-plugins' ) . '</span>';
		}

		$output .= "<div class='ad'>$ad_code</div>";
		$output .= '</span>';

		$output .= '</div>'; // close gallery-image

		return $output;
	}

	/**
	 * @param string $size
	 * @param int $images_to_show extra images count that needs to be shown for cyclic gallery thumbs
	 */
	public function the_image( $size = 'pmc-gallery-image', $images_to_show = 0 ) {
		echo $this->get_the_image( $size, $images_to_show );
	}

	/**
	 * Wrapper Function to fetch Thumbs
	 * @todo this method is trying to do too much. It's trying to grab thumbnail images for multiple layout types, for linked gallery previews, and also for the galleries themselves.
	 * @todo Remove backwards compatibility args after themes are updated
	 */
	public function get_the_thumbs( $args = array( 'thumbnail_image_size' => 'gallery-thumb' ), $deprecated = 'filmstrip', $deprecated2 = 5, $deprecated3 = 'thumb', $deprecated4 = PMC_Gallery_Defaults::previous_link_html, $deprecated5 = PMC_Gallery_Defaults::next_link_html ) {

		// Backwards compatibility
		// @todo Remove backwards compatibility after themes are updated
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
			'thumbnail_previous_link_text' => PMC_Gallery_Defaults::previous_link_html,
			'thumbnail_next_link_text'     => PMC_Gallery_Defaults::next_link_html,
		);
		$args         = wp_parse_args( $args, $default_args );
		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization
		$args['thumbnail_image_size'] = (string) $args['thumbnail_image_size'];
		$valid_sizes                  = \PMC\Image\get_intermediate_image_sizes();
		if ( ! in_array( $args['thumbnail_image_size'], $valid_sizes, true ) ) {
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
			'loaded' => array()
		);

		// Render HTML
		// @todo class="gallery-thumbs ' . esc_attr( $args['thumbnail_image_size'] ) . '"
		$output =
			'<div class="gallery-thumbs gallery-thumbs-' .
			esc_attr( $args['thumbnail_image_size'] ) .
			'" data-style="' .
			esc_attr( $args['thumbnail_layout'] ) .
			'">';

		if ( is_null( self::$linked_gallery ) && 'all' !== $args['thumbnail_layout'] ) {
			// @todo Don't use hrefs here
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
			$linkback = parse_url( get_permalink(), PHP_URL_PATH );
			if ( $linkback ) {
				// Not using add_query_arg() here because we're adding a param to a hash, not querystring
				$ref_path .= '&ref=' . $linkback . 'pos=';
			}
		}

		//Next Gallery Flagging using url
		$old_url = '';
		if ( is_null( self::$linked_gallery ) ) {
			$limit = $limit + $args['number_to_show']; // allows us to have more thumbs to fill in the blannk spaces.
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
				$title = '';
				if ( 'thumb-title' === $args['thumbnail_structure'] && isset( $this->_data[ $data_index ]['title'] ) ) {
					$title = $this->_data[ $data_index ]['title'];
					$title = apply_filters( 'the_title', $title );
					$title = esc_html( strip_tags( $title ) );
				}
				$link_url = get_permalink( self::$id ) . '#!' . ( $image_position ) . '/' . esc_attr( $this->_data[ $data_index ]['slug'] ) . $ref_path;
				$output .= '<div>';
				$output .= '<a href="' . esc_url( $link_url ) . '" >';
				$output .=
					'<img src="' .
					esc_url( $src ) . '"';
				if ( ! empty( $width ) ) {
					$output .= ' width="' . intval( $width ) . '"';
				}
				if ( ! empty( $height ) ) {
					$output .= ' height="' . intval( $height ) . '"';
				}
				$output .= '" alt="' .
				           esc_attr( $title ) .
				           '" data-slug="' .
				           esc_attr( $this->_data[ $data_index ]['slug'] ) .
				           '" />';
				$output .= '</a>';
				$output .= wp_kses_post( $title );
				$output .= '</div>';

			} else {

				$visible         = ( ( $counter >= $start ) && ( $counter < ( $args['number_to_show'] + $start ) ) ); //thumb visible by default
				$current_gallery = ( ( $counter >= $start ) && ( $counter < ( $this->_js_obj['gallery_count'] + $start ) ) ); //thumb in current gallery
				$all             = ( ( 'all' === $args['thumbnail_layout'] ) && $current_gallery ); //show only current gallery in case of all

				/*
				 * lets assume we'll be moving to next gallery after last image
				 * of current gallery, so we need to show thumbs from next
				 * gallery after showing all thumbs of current gallery
				 *
				 * @since 2016-08-03 Amit Gupta
				 */
				$position = $counter;

				if ( ( $this->_js_obj['continuous_cycle'] !== true || empty( $this->_data[ $position ] ) ) && intval( $this->_js_obj['gallery_count'] ) > 0 ) {
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
					$title = '';
					if ( 'thumb-title' === $args['thumbnail_structure'] && isset( $this->_data[ $position ]['title'] ) ) {
						$title = $this->_data[ $counter ]['title'];
						$title = apply_filters( 'the_title', $title );
						$title = esc_html( strip_tags( $title ) );
					}

					$this->_js_obj['imageparts'][ 'gallery-thumbs-' . $args['thumbnail_image_size'] ]['loaded'][ $position ] = ( $position < $args['number_to_show'] );

					$next_gallery = '';
					if ( $old_url && $old_url !== $this->_data[ $position ]['url'] && $position > 1 ) {
						$next_gallery = '<span>Next Gallery</span>';
					}
					//AE: ran into a very bizzare case where $width and $height were not set on certain images on QA. will check for existence before rendering.
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

			}	//end if/else

		}	//end for loop

		$output .= '</div>'; // close gallery-multi
		$output .= '</div>'; // close gallery-thumbs

		return $output;
	}


	/**
	 * Wrapper Function to fetch Thumbs for Instant Article
	 *
	 * @since 2015-12-08
	 * @version 2015-12-08 Archana Mandhare PCMVIP-411
	 *
	 * @param $args string | array
	 * @param $deprecated string
	 * @param $deprecated2 int
	 * @param $deprecated3 string
	 * @param $deprecated4 string
	 * @param $deprecated5 string
	 *
	 * @return string
	 *
	 */
	public function get_the_thumbs_html5( $args = array( 'thumbnail_image_size' => 'gallery-thumb' ), $deprecated = 'filmstrip', $deprecated2 = 5, $deprecated3 = 'thumb', $deprecated4 = PMC_Gallery_Defaults::previous_link_html, $deprecated5 = PMC_Gallery_Defaults::next_link_html ) {

		// Backwards compatibility
		// @todo Remove backwards compatibility after themes are updated
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
			'thumbnail_previous_link_text' => PMC_Gallery_Defaults::previous_link_html,
			'thumbnail_next_link_text'     => PMC_Gallery_Defaults::next_link_html,
		);
		$args         = wp_parse_args( $args, $default_args );

		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization
		$args['thumbnail_image_size'] = (string) $args['thumbnail_image_size'];
		$valid_sizes                  = \PMC\Image\get_intermediate_image_sizes();
		if ( ! in_array( $args['thumbnail_image_size'], $valid_sizes, true ) ) {
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

		$ref_path = '';
		if ( ! is_null( self::$linked_gallery ) ) {
			// Add ref param to the gallery link to trigger "Return to article" link
			// @todo This snippet of logic is used throughout, refactor to helper method
			$linkback = parse_url( get_permalink(), PHP_URL_PATH );
			if ( $linkback ) {
				// Not using add_query_arg() here because we're adding a param to a hash, not querystring
				$ref_path .= '&ref=' . $linkback . 'pos=';
			}
		}

		$output = '';

		if ( ! empty( $limit ) ) {

			// Render HTML
			$output = '<figure class="op-slideshow">';

			//Next Gallery Flagging using url
			for ( $counter = 0; $counter < $limit; $counter ++ ) {
				if ( ! is_null( self::$linked_gallery ) ) {
					if ( $counter <= self::$linked_gallery ) {
						continue;
					}

					$data_index     = $counter - 1 + $start;
					$image_position = $counter;

					// @todo $src is the full image src, probably a bug in _register_default_image_sizes()
					list( $src, $width, $height ) = wp_get_attachment_image_src( $this->_data[ $data_index ]['ID'], $args['thumbnail_image_size'] );
					$title = '';
					if ( 'thumb-title' === $args['thumbnail_structure'] && isset( $this->_data[ $data_index ]['title'] ) ) {
						$title = $this->_data[ $data_index ]['title'];
						$title = apply_filters( 'the_title', $title );
						$title = esc_html( strip_tags( $title ) );
					}

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
	 *
	 * @todo Remove backwards compatibility args after themes are updated
	 */
	public function the_thumbs( $args = array( 'thumbnail_image_size' => 'gallery-thumb' ), $deprecated = 'filmstrip', $deprecated2 = 5, $deprecated3 = 'thumb', $deprecated4 = PMC_Gallery_Defaults::previous_link_html, $deprecated5 = PMC_Gallery_Defaults::next_link_html ) {
		echo $this->get_the_thumbs( $args, $deprecated, $deprecated2, $deprecated3, $deprecated4, $deprecated5 );
	}

	/**
	 * Returns Meta attached to current image or '' if none with Markup
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
		// @todo change gallery-meta-' . esc_attr( $meta_name ) . ' to ' . esc_attr( $meta_name ) . '
		$output = '<div class="gallery-meta gallery-meta-' . esc_attr( $meta_name ) . '">';
		$output .= '<div class="gallery-multi">';

		for ( $counter = 0; $counter < $this->_get_image_count(); $counter ++ ) {
			$extra = '';

			// If this is a linked gallery preview, skip ahead to the preview image. Linked gallery preview will stop after one iteration of the loop.
			if ( ! is_null( self::$linked_gallery ) ) {
				$counter = $position;
			}

			if ( $counter != $position ) {
				$extra = ' style="display:none;"';
			}
			$data = get_post_meta( $this->_data[ $counter ]['ID'], $meta_name, true );

			if ( $data && ( $prefix || $suffix ) ) {
				$data = $prefix . $data . $suffix;
			}

			$output .= '<div' . $extra . '>' . esc_html( $data ) . '</div>';

			if ( ! is_null( self::$linked_gallery ) ) {
				break;
			}
		}

		$output .= '</div>'; // end gallery-multi
		$output .= '</div>'; // end gallery-meta

		$this->_js_obj['multiparts'][] = 'gallery-meta-' . $meta_name;

		return apply_filters( 'pmc_gallery_get_the_meta', $output, $this, $meta_name, $prefix, $suffix );
	}

	public function the_meta( $meta_name, $prefix = '', $suffix = '' ) {
		echo $this->get_the_meta( $meta_name, $prefix, $suffix );
	}

	/**
	 * Function is used to overwrite the HTML output of get_the_meta
	 * for `image_credit` field.
	 *
	 * @filter pmc_gallery_get_the_meta
	 * @param string			$output HTML Output.
	 * @param \PMC_Gallery_View $context cusrrent object of .
	 * @param string			$meta_name meta key.
	 * @param string			$prefix string that will prepend brfore data.
	 * @param string			$suffix string that will append after data.
	 * @return string
	 */
	public function pmc_gallery_get_the_meta( $output, $context, $meta_name, $prefix, $suffix ) {
		if ( '_image_credit' === $meta_name ) {
			$position = ( intval( $context->_js_obj['gallery_start'] ) + intval( $context->_js_obj['gallery_first'] ) );
			$output = '<div class="gallery-meta gallery-meta-' . esc_attr( $meta_name ) . '">';
			$output .= '<div class="gallery-multi">';
			for ( $counter = 0; $counter < $context->_get_image_count(); $counter ++ ) {
				// If this is a linked gallery preview, skip ahead to the preview image. Linked gallery preview will stop after one iteration of the loop.
				if ( ! is_null( self::$linked_gallery ) ) {
					$counter = $position;
				}

				$extra = '';
				if ( $counter !== $position ) {
					$extra = ' style="display:none;"';
				}
				$data = sanitize_text_field( $context->_data[ $counter ]['image_credit'] );

				if ( $data && ( $prefix || $suffix ) ) {
					$data = $prefix . $data . $suffix;
				}
				$output .= '<div' . $extra . '>' . esc_html( $data ) . '</div>';
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
	 * Returns Title attached to current image with Markup
	 */
	public function get_the_title( $length = 30 ) {
		return $this->_field_length( 'title', $length );
	}

	public function the_title( $length = 30 ) {
		echo $this->get_the_title( $length );
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

	private function _field_length( $field, $length ) {
		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization
		$length = intval( $length );

		$position = ( intval( $this->_js_obj['gallery_start'] ) + intval( $this->_js_obj['gallery_first'] ) );

		// Render HTML
		$output = '<div class="gallery-' . esc_attr( $field ) . ' gallery-' . esc_attr( $field ) . '-' . esc_attr( $length ) . '">';
		$output .= '<div class="gallery-multi">';

		for ( $counter = 0; $counter < $this->_get_image_count(); $counter ++ ) {
			$extra = '';

			if ( ! is_null( self::$linked_gallery ) ) {
				$counter = $position;
			}

			if ( $counter !== $position ) {
				$extra = ' style="display:none;"';
			}

			$output .= '<div' . $extra . '>' . force_balance_tags( pmc_truncate( $this->_data[ $counter ][ $field ], $length ) ) . '</div>';

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
	 * Returns Date attached to current image with Markup
	 */
	public function get_the_date( $format = 'd-M-Y' ) {

		// No Image
		if ( 0 == count( $this->_data ) ) {
			return '';
		}

		// Sanitization
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

			if ( $counter != $position ) {
				$extra = ' style="display:none;" ';
			}

			$output .= '<div' . $extra . '>' . esc_html( mysql2date( $format, $this->_data[ $counter ]['date'] ) ) . '</div>';

			if ( 0 === self::$linked_gallery ) {
				break;
			}
		}
		$output .= '</div></div>';

		$this->_js_obj['multiparts'][] = "gallery-date";

		return $output;
	}

	public function the_date( $format = 'd-M-Y' ) {
		echo $this->get_the_date( $format );
	}

	/**
	 * Helper Function to Fetch Gallery Navigation
	 * @todo Remove deprecated args after themes have been refactored
	 */
	public function get_the_navigation(
		$deprecated = 'both', $options = array(
		PMC_Gallery_Defaults::previous_link_html,
		PMC_Gallery_Defaults::next_link_html
	)
	) {
		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization
		$previous_label = ( isset( $options[0] ) ) ? (string) $options[0] : PMC_Gallery_Defaults::previous_link_html;
		$next_label     = ( isset( $options[1] ) ) ? (string) $options[1] : PMC_Gallery_Defaults::next_link_html;

		// @todo Don't use hrefs
		$output = '<a  class="gallery-navigation gallery-navigation-previous">';
		$output .= wp_kses_post( $previous_label );
		$output .= '</a>';
		$output .= '<a class="gallery-navigation gallery-navigation-next">';
		$output .= wp_kses_post( $next_label );
		$output .= '</a>';

		return $output;
	}

	/**
	 * @todo Remove deprecated args after themes have been refactored
	 */
	public function the_navigation(
		$deprecated = 'both', $options = array(
		PMC_Gallery_Defaults::previous_link_html,
		PMC_Gallery_Defaults::next_link_html
	)
	) {
		echo wp_kses_post( $this->get_the_navigation( $deprecated, $options ) );
	}

	/**
	 * Helper Function to Fetch Gallery back to post link
	 *
	 * @param string $backtext
	 *
	 * @return string
	 */
	public function get_the_backlink( $backtext = 'Return to Article &crarr;' ) {
		// @todo Remove hrefs
		$output = '<div class="pmc-gallery-top"><a  class="gallery-back" >';
		$output .= esc_html( $backtext );
		$output .= '</a></div>';

		return $output;
	}

	public function the_backlink( $backtext = 'Return to Article' ) {
		echo wp_kses_post( $this->get_the_backlink( $backtext ) );
	}

	/**
	 * Helper Function to Fetch Gallery Image Count
	 *
	 * Style can be XofY or total
	 * for 3 of 6 or 6
	 * this can be extended to have a better glue 'of'
	 *
	 */
	public function get_the_count( $style = 'XofY' ) {
		// No Image
		if ( 0 === $this->_get_image_count() ) {
			return '';
		}

		// Sanitization
		$style = (string) $style;
		if ( ! in_array( $style, array( 'XofY', 'total' ), true ) ) {
			$style = 'XofY';
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

	public function the_count( $style = 'XofY' ) {
		echo $this->get_the_count( $style );
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
	 *
	 */
	public function the_permalink() {
		echo $this->get_the_permalink();
	}

	/**
	 * Override WP the default gallery_shortcode() output
	 * @see gallery_shortcode()
	 *
	 * @param string $not_used
	 * @param array $attr
	 *
	 * @return string $output
	 */
	public function gallery_shortcode( $not_used, $attr ) {

		// disallowing Gallery short code for pages other than single pages
		if ( ! is_singular() ) {
			return '<a href="' . esc_url( get_permalink() ) . '">View Gallery &rsaquo;</a>';
		}

		$post = get_post();

		if ( ! empty( $attr['ids'] ) ) {
			// 'ids' is explicitly ordered, unless you specify otherwise.
			if ( empty( $attr['orderby'] ) ) {
				$attr['orderby'] = 'post__in';
			}
			$attr['include'] = $attr['ids'];
		}

		// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
		if ( isset( $attr['orderby'] ) ) {
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
			if ( ! $attr['orderby'] ) {
				unset( $attr['orderby'] );
			}
		}

		$attr = shortcode_atts( array(
			'order'   => 'ASC',
			'orderby' => 'menu_order ID',
			'id'      => $post ? $post->ID : 0,
			'include' => '',
			'exclude' => '',
		), $attr, 'gallery' );

		$id      = intval( $attr['id'] );
		$order   = $attr['order'];
		$orderby = $attr['orderby'];
		$include = $attr['include'];
		$exclude = $attr['exclude'];

		if ( 'RAND' == $order ) {
			$orderby = 'none';
		}

		if ( ! empty( $include ) ) {
			$_attachments = get_posts( array(
				'include'        => $include,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => $order,
				'orderby'        => $orderby,
				'numberposts'    => 200,
				// Default is 5, we have posts with galleries numbering > 100 images, but 200 should be safe
			) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[ $val->ID ] = $_attachments[ $key ];
			}
		} elseif ( ! empty( $exclude ) ) {
			$attachments = get_children( array(
				'post_parent'    => $id,
				'exclude'        => $exclude,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => $order,
				'orderby'        => $orderby,
				'numberposts'    => 200,
				// Default is 5, we have posts with galleries numbering > 100 images, but 200 should be safe
			) );
		} else {
			$attachments = get_children( array(
				'post_parent'    => $id,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'order'          => $order,
				'orderby'        => $orderby,
				'numberposts'    => 200,
				// Default is 5, we have posts with galleries numbering > 100 images, but 200 should be safe
			) );
		}

		if ( empty( $attachments ) ) {
			return '<!-- No attachments -->'; // Because we're overriding the gallery shortcode via the post_gallery filter, we need to return *something* or else WP will use the default gallery, which is not intended.
		}

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment ) {
				$output .= wp_get_attachment_link( $att_id, 'thumbnail', true ) . "\n";
			}

			return $output;
		}

		// @todo Recursively loading the gallery? This might be a bug.
		$gallery = PMC_Gallery_View::get_instance();
		$gallery->load_gallery( $attachments );
		$gallery->is_shortcode = true;

		// Don't execute any more shortcodes, our custom gallery JS will break
		remove_shortcode( 'gallery' );
		add_shortcode( 'gallery', '__return_null' );

		$possible_templates = array(
			get_stylesheet_directory() . '/pmc-gallery-template.php',
			get_template_directory() . '/pmc-gallery-template.php',
			plugin_dir_path( __FILE__ ) . 'default-pmc-gallery-template.php',
		);

		$gallery_template = '';
		foreach ( $possible_templates as $template_path ) {
			if ( file_exists( $template_path ) ) {
				$gallery_template = $template_path;
				break;
			}
		}

		if ( ! $gallery_template ) {
			return '<!-- No gallery template -->'; // Because we're overriding the gallery shortcode via the post_gallery filter, we need to return *something* or else WP will use the default gallery, which is not intended.
		}

		ob_start();
		// not "include_once" because we may support multiple gallery shortcodes in a single post, and each gallery would need to execute the template again to show new images.
		// not "require", because the site doesn't need to break if including a gallery template fails.
		include $gallery_template;

		return ob_get_clean();

	}

	/**
	 * Remove google analytics pageview tracking on page load.
	 * This we are doing so that proper page url is attributed in analytics.
	 * During this time the hash of image is not present, so we remove it to be fired later on.
	 *
	 * @param $push
	 *
	 * @return array
	 */
	public function filter_pmc_google_analytics_track_pageview( $push ) {

		// remove default page view tracking if current post has gallery
		// page view will be tracked via footer_scripts
		if ( is_single() && $this->has_gallery() ) {

			for ( $i = 0; $i < count( $push ); $i ++ ) {
				if ( preg_match( '/^(?:[^\.]+\.)?_trackPageview$/', $push[ $i ][0] ) || preg_match( '/^(?:[^\.]+\.)?pageview$/', $push[ $i ][0] ) ) {
					// save the page view event name for use at footer_scripts
					$this->_pageview_event_names[] = $push[ $i ][0];
					unset( $push[ $i ] );
				}
			}

			$push = array_values( $push );
		}

		return $push;
	}

	/**
	 * Render js variable so that we know that pmc-gallery is being rendered
	 * If its gallery then url should have hash, is no hash then attribute analytics with first images hash.
	 *
	 * @param array $data
	 * @param string $first_image_name
	 *
	 * @todo Refactor so that scripting is in external JS
	 */
	function render_js_variable( $data = array(), $first_image_name = '' ) {

		if (
			( ! is_single() ) ||
			( get_post_type() !== PMC_Gallery_Defaults::name ) ||
			( ! $this->is_shortcode )
		) {
			return;
		}
		?>
		<script type="text/javascript">
			var pmc_gallery_front = {
				is_gallery: true,
				replaced: false,
				first_image_hash: "<?php echo esc_js( $first_image_name ); ?>",
				data: <?php echo json_encode( $data ); ?>
			};

			try {
				if ( "" != window.location.hash && 0 != window.location.hash.indexOf( "#!" ) ) {
					//This is to make sure that url without ! but with # are properly attributed in analytics.
					//Detect old HL url
					url_format_hl = /^(\d+)\-(\d+)\-(.*)/.exec( window.location.hash.replace( "#", "" ) );
					if ( url_format_hl != null && url_format_hl[2] > 0 ) {
						//1027327-8-08-Kristen-Stewart
						pmc_gallery_front.replaced = true;
						if ( typeof pmc_gallery_front.data[url_format_hl[2] - 1] != 'undefined' ) {
							window.location.hash = '!' + url_format_hl[2] + '/' + pmc_gallery_front.data[url_format_hl[2] - 1];
						}
					}

					//Detect old Variety url
					url_format_old_var = /^(\d+)\/(.*)/.exec( window.location.hash.replace( "#", "" ) );
					if ( url_format_old_var != null && url_format_old_var[1] > 0 ) {
						//05/Jumpin-Baby
						pmc_gallery_front.replaced = true;
						if ( typeof pmc_gallery_front.data[url_format_old_var[1] - 1] != 'undefined' ) {
							window.location.hash = "!" + url_format_old_var[1] + '/' + pmc_gallery_front.data[url_format_old_var[1] - 1];
						}
					}
					if ( ! pmc_gallery_front.replaced ) {
						window.location.hash = "!1/<?php echo esc_js( $first_image_name ); ?>/";
					}
				}
				if ( 0 == window.location.hash.indexOf( "#!" ) ) {
					var hash_arr = window.location.hash.replace( "#!", "" ).split( "/" );
					hash_arr = hash_arr.filter( function ( e ) {
						return e;
					} );
					var img_pos = "";
					if ( hash_arr && 1 == hash_arr.length ) {
						img_pos = hash_arr[0];
						if ( jQuery.isNumeric( img_pos ) ) {
							if ( typeof pmc_gallery_front.data[img_pos - 1] != 'undefined' ) {
								window.location.hash = "!" + img_pos + "/" + pmc_gallery_front.data[img_pos - 1] + "/";
							}
						}
					}
				}
			} catch ( e ) {
			}
		</script>
		<?php
	}

	/**
	 * renders out an inline gallery
	 * An inline gallery consists of a gallery title, the main gallery image, the first image title
	 * and the count of the gallery in the format XofY. An inline Gallery allows the user click the image
	 * and then move on to the gallery page. It does not allow the user navigate through images.
	 * input
	 *
	 * @param $arguments -- array of arguments for rendering. expected values ['thumbnail_size','return_link']
	 *
	 * @return
	 */
	public function render_inline_gallery( $arguments = array() ) {

		if( empty( self::$id ) ){
			return;
		}


		$thumbnail_size = empty( $arguments['thumbnail_size'] )? 'pmc-gallery-thumb' : $arguments['thumbnail_size'] ;

		$title = get_the_title( self::$id );

		$title = !empty( $title ) ?  force_balance_tags( strip_tags( $title, '<b>,<i>,<strong>,<em>') ): '';

		$gallery_link = empty( $arguments['return_link'] )? $this->get_the_permalink( true ) : $this->get_the_permalink( false ) .'#!'.$arguments['return_link'];



		?>
	<div class="view-gallery">
		<a href="<?php echo esc_url( $gallery_link );  ?>">
			<i class="pmc-icon-gallery"></i>
		<div class="gallery-title">
						<?php echo wp_kses_post( $title ); ?>
			</div>
		<?php
		if ( has_post_thumbnail( self::$id ) || !empty( $this->_data[0]['ID']) ) {
			echo '<div class="inline-gallery-image">' ;
			if( has_post_thumbnail( self::$id ) ){
				echo get_the_post_thumbnail( self::$id , $thumbnail_size );

			}else{
				list( $src, $width, $height ) = wp_get_attachment_image_src( $this->_data[0]['ID'], $thumbnail_size );
				echo '<img src="'.esc_url( $src ).'" width="'. absint( $width ) .'" height="'. absint( $height ).'">';

			} ?>
			<span class="inline-gallery-nav">
				<span class="inline-gallery-launch-gallery-text"> <?php esc_html_e('Launch Gallery','pmc-plugins'); ?></span><i class="fa fa-5x fa-angle-right"></i>
				</span>

				</div>
				<?php
		}
		?>

	</a>
	</div>
	<?php
	}

	public static function get_linked_gallery_data( $post_id ) {

		if ( empty( $post_id ) ) {
			return;
		}

		$linked_data = get_post_meta( $post_id, PMC_Gallery_Defaults::name . '-linked-gallery', true );
		if ( ! empty( $linked_data ) ) {
			$linked_data = json_decode( $linked_data, true );

			return $linked_data;
		}

		return null;
	}

	/**
	 * Helper function to return the linked post_id
	 *
	 * @param int $gallery_id
	 *
	 * @return mixed The linked post ID or false if not found
	 */
	public static function get_linked_post_id( $gallery_id ) {
		// retrieve the linked post id from gallery post meta
		if ( $post_id = get_post_meta( $gallery_id, PMC_Gallery_Defaults::name . '-linked-post_id', true ) ) {
			// we need to double check to make sure the post actually have the same linked data, linked gallery might be removed
			if ( $linked_gallery = self::get_linked_gallery_data( $post_id ) ) {
				if ( $linked_gallery['id'] == $gallery_id ) {
					return $post_id;
				}
			}
		}

		return false;
	}

	public function get_count_for_cyclic_image( $count_images_padding ) {

		if ( empty( $count_images_padding ) ) {
			return;
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
	 * Return next gallery, works in loop only
	 *          1. next gallery based on `pmc_gallery_next_gallery` filter provided
	 *          2. next gallery based on top tag on count attached to current gallery post
	 *          3. next gallery based on top category on count attached to current gallery post
	 *          4. Just return adjacent gallery if above 3 fails
	 *
	 * @since 2017-08-24 Amit Sannad PMCER-187
	 *
	 * @param array $args
	 *
	 * @return array|bool|mixed|void
	 */
	public function get_adjacent_gallery( $args = [ ] ) {

		$post_id = get_the_ID();

		if ( ! is_int( $post_id ) ) {
			return false;
		}

		if ( PMC_Gallery_Defaults::name !== get_post_type( $post_id ) ) {
			return false;
		}

		//Shortcircuit the function and return your own
		$filtered_post = apply_filters( 'pmc_gallery_adjacent_gallery', null, $post_id, $args );

		if ( null !== $filtered_post ) {
			return $filtered_post;
		}

		//Previous = true here means post newer & false = older post hence gonna flip it
		$args = wp_parse_args( $args, [ 'prev' => true ] );
		$args['prev'] = ! $args['prev'];

		$top_tag_post = $this->_get_adjacent_post( $post_id, 'post_tag', $args['prev'] );
		if ( ! empty( $top_tag_post ) ) {
			return [ 'post' => $top_tag_post, 'type' => 'post_tag' ];
		}

		$top_category_post = $this->_get_adjacent_post( $post_id, 'category', $args['prev'] );
		if ( ! empty( $top_category_post ) ) {
			return [ 'post' => $top_category_post, 'type' => 'category' ];
		}

		$up_next_post = wpcom_vip_get_adjacent_post( false, false, $args['prev'] );

		if ( ! empty( $up_next_post ) ) {
			return [ 'post' => $up_next_post, 'type' => 'adjacent_only' ];
		}

		return false;

	}

	/**
	 * Return adjacent post based on taxonomy term
	 *
	 * @since 2017-08-24 Amit Sannad PMCER-187
	 *
	 * @param      $post_id
	 * @param      $taxonomy
	 * @param bool $prev
	 *
	 * @return bool|null|string|WP_Post
	 */
	private function _get_adjacent_post( $post_id, $taxonomy, $prev = true ) {

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms[0]->count ) ) {
			return false;
		}

		//Even I dont like anonymous function, but for this, I am taking a excuse, feel free to yell at me
		usort( $terms, function ( $a, $b ) {
			return $b->count - $a->count;
		} );

		if ( ! empty( $terms[0]->term_id ) ) {

			$this->_term_id_adjacent_post = $terms[0]->term_id;

			add_filter( 'wpcom_vip_limit_adjacent_post_term_id', array( $this, 'filter_next_post_term_id' ) );

			// Select previous galleries.. there will always be previous ones,
			// if we only selected next posts the user would likely get to the end
			// and have no more posts to see.
			$up_next_post = wpcom_vip_get_adjacent_post( true, false, $prev, $taxonomy );

			$this->_term_id_adjacent_post = null;

			remove_filter( 'wpcom_vip_limit_adjacent_post_term_id', array( $this, 'filter_next_post_term_id' ) );

			if ( ! empty( $up_next_post ) ) {
				return $up_next_post;
			}

		}

		return false;
	}

	/**
	 * Just a filter to restrict term id for adjacent post
	 *
	 * @since 2017-08-24 Amit Sannad PMCER-187
	 *
	 * @param $term_id_to_search
	 *
	 * @return mixed
	 */
	public function filter_next_post_term_id( $term_id_to_search ) {

		if ( ! empty( $this->_term_id_adjacent_post ) ) {
			$term_id_to_search = $this->_term_id_adjacent_post;
		}

		return $term_id_to_search;
	}

	/**
	 * A filter to remove responsive ad skins from all gallery pages of all brands.
	 *
	 * @param bool Default value of should skins be enabled.
	 *
	 * @since 2018-06-11 Kelin Chauhan READS-1196
	 *
	 * @return bool
	 * @codeCoverageIgnore
	 */
	public function remove_responsive_ad_skins( $enabled ) {

		if ( is_singular( 'pmc-gallery' ) ) {
			return false;
		}

		return $enabled;

	}

	/**
	 * A filter to override googlebot_news meta tag for pmc-gallery posts
	 *
	 * @param $gn_exclude bool
	 *
	 * @since 2018-07-31 Jignesh Nakrani READS-1378
	 *
	 * @return bool
	 */
	public function maybe_exclude_googlebot_news_tag( $gn_exclude ) {

		if ( is_singular( PMC_Gallery_Defaults::name ) ) {

			$gn_exclude = true;

		}

		return $gn_exclude;

	}

}

PMC_Gallery_View::get_instance();

//EOF
