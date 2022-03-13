<?php
/**
 * Class PMC_Custom_Feed_Reuters
 *
 * This class implement override for reuters feed specific requirements
 *
 * @author PMC, Hau Vong
 * @version 2014-07-18
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Reuters {

	use Singleton;

	const RELATED_LINK_AGE_IN_DAYS = 15;
	const IMAGE_THRESHOLD_IN_DAYS = 30; // any images older than this should be excluded
	const LAST_MODIFIED_CUTOFF_DATE = '2015-10-02 00:00:00';

	// variables to track last modified date
	private $_last_modified_list = array();
	private $_last_generated_info = array();
	private $_modified_item_list = array();
	private $_dirty = false;

	private $_feed_options = false;

	// Maximum number of related items to include with a "Package"
	protected $_related_item_limit = 3;

	// These fields will be used to calculate the md5sum for item node
	protected $_hash_fields = array(
		'ID',
		'id',
		'post_date',
		'post_date_gmt',
		'post_author',
		'post_content',
		'post_title',
		'post_excerpt',
		'post_name',
		'post_modified',
		'post_modified_gmt',
		'feed_images',
		'feed_gallery_images',
		'feed_videos',
		'modified_gmt',
		'md5_media_group',
	);

	protected function __construct() {
		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
	}

	// action hook before feed template start
	public function action_pmc_custom_feed_start( $feed = false, $feed_options = false, $template = '' ) {

		// only continue if the feed is reuters feed
		if ( empty( $feed_options['reuters-feed'] ) ) {
			return;
		}

		// assign feed options for later use
		$this->_feed_options = $feed_options;

		// retrieve last generation information
		if ( !empty( $feed_options['feed_id'] ) ) {
			$this->_last_generated_info = get_post_meta( $feed_options['feed_id'], PMC_Custom_Feed::meta_custom_name .'last_generated_info', true );
			if ( empty( $this->_last_generated_info ) ) {
				$this->_last_generated_info = array();
			}
		}

		/**
		 * PPT-3149 Set locale to en_US.UTF-8 to work around bug in iconv.
		 * ref: http://php.net/manual/en/function.iconv.php#108643
		 * ref: http://php.net/manual/en/function.iconv.php#74101
		 * @author 2014-08-12 Corey Gilmore
		 */
		$cur_locale = setlocale( LC_ALL, 0 );
		if( in_array( $cur_locale, array( 'C', 'POSIX' ) ) ) {
			$locale = apply_filters( 'pmc_custom_feed_iconv_locale_bug', 'en_US.UTF-8', $cur_locale );
			setlocale( LC_ALL, $locale );
		}

		add_action( 'pmc_custom_feed_featured_image_caption', array( $this, 'filter_strip_image_credit_caption_unicode' ), 10, 2 );

		// use priority 9 to process the content before other normal filters
		add_filter( 'the_content', array( $this, 'filter_remove_see_also' ), 9 );

		// apply this here so text only may override as needed
		add_filter( 'the_excerpt_rss', array( $this, 'filter_the_excerpt_rss' ), 11 );

		// text only
		if ( !empty( $this->_feed_options['text-only'] ) ) {
			// use priority 11 to make sure unicode convert call after all other normal filters are applied
			add_filter( 'pmc_custom_feed_content', array( $this, 'filter_unicode_convert_keep_p_tags' ), 11 );
			add_filter( 'the_excerpt_rss', array( $this, 'filter_unicode_convert_strip_all_tags' ), 11 );
			add_filter( 'pmc_custom_feed_cdata', array( $this, 'filter_unicode_convert_strip_all_tags' ), 11 );
			add_filter( 'pmc_custom_feed_data', array( $this, 'filter_unicode_convert_strip_all_tags' ), 11 );
			add_filter( 'pmc_custom_feed_title', array( $this, 'filter_unicode_convert_strip_all_tags' ), 11 );

			// NOTE: title need to be escaped after unicode conversion since template doesn't do any escape on output
			add_filter( 'the_title_rss', array( $this, 'filter_unicode_convert_esc_xml' ), 11 );
			add_filter( 'wp_title_rss', array( $this, 'filter_unicode_convert_esc_xml' ), 11 );

		} else {
			// We always want to strip pmc-related-link (shortcode) links in a Reuters Multimedia feed -- they are extracted and bundled into a <related-items> node
			$this->_feed_options['strip_related_links'] = true; // forcibly enable stripping of related links in filter::the_content

			add_filter( 'pmc_custom_feed_content', array( $this, 'filter_keep_html' ), 11 );
			add_filter( 'the_title_rss', array( $this, 'filter_esc_xml' ), 11 );
			add_filter( 'wp_title_rss', array( $this, 'filter_esc_xml' ), 11 );

			// Use this hook to include additional post types in the automatic related posts selection
			// add_filter( 'pmc_custom_feed_related_links_post_types', array( $this, 'filter_pmc_custom_feed_related_links_post_types' ), 11 );
		}

		// disable more links
		add_filter( 'the_content_more_link', '__return_empty_string', 11 );
		add_filter( 'excerpt_more', '__return_empty_string', 11 );

		add_filter( 'render_featured_or_first_image_in_post_params', '__return_false' );
		add_filter( 'pmc_custom_feed_render_first_image_in_gallery', '__return_false' );
		add_filter( 'pmc_custom_feed_render_image_in_post', '__return_false' );
		add_filter( 'pmc_custom_feed_esc_xml_strict', '__return_true' );
		add_filter( 'rss_enclosure', '__return_empty_string' );

		add_filter( 'pmc_custom_feed_attr_item', array( $this, 'filter_pmc_custom_feed_attr_item' ), 10 );
		add_filter( 'pmc_custom_feed_post_start', array( $this, 'filter_pmc_custom_feed_post_start'), 11, 2 );

		add_action( 'pmc_custom_feed_item', array( $this, 'action_pmc_custom_feed_item' ), 10, 2 );
		add_action( 'pmc_custom_feed_item_media_group', array( $this, 'action_pmc_custom_feed_item_media_group' ) );

		add_action( 'pmc_custom_feed_channel', array( $this, 'action_pmc_custom_feed_channel' ), 10, 2 );

		// restrict related links to n days
		add_filter( 'pmc_custom_feed_related_links_last_n_days', function() {
			return PMC_Custom_Feed_Reuters::RELATED_LINK_AGE_IN_DAYS;
		} );

		// intercept and add links to track modified list
		add_filter( 'pmc_custom_feed_image_detail', array( $this, 'add_to_modified_list' ) );
		add_filter( 'pmc_custom_feed_link_detail', array( $this, 'add_to_modified_list' ) );

		add_action( 'pmc_custom_feed_end', array( $this, 'action_pmc_custom_feed_end' ) );
		add_filter( 'pmc_custom_feed_attr_media_group', array( $this, 'filter_pmc_custom_feed_attr_media_group' ) );
		add_filter( 'pmc_custom_feed_attr_related', array( $this, 'filter_pmc_custom_feed_attr_related' ) );
		add_filter( 'pmc_custom_feed_image_detail', array( $this, 'filter_pmc_custom_feed_image_detail' ) );
	}

	public function filter_pmc_custom_feed_image_detail( $image ) {
		unset( $image['caption'] );
		$image['title'] = wp_strip_all_tags( $image['title'] );
		return $image;
	}

	// filter to return the excerpt for rss
	public function filter_the_excerpt_rss( $content ) {

		if ( !empty( $this->_feed_options['one-sentence-excerpt'] ) ) {
			$content = wp_strip_all_tags( $content );
			$content = PMC::strip_control_characters( $content );
			$content = $this->fix_wp_quote_prime_bug( $content );
			$content = PMC::untexturize( $content );
			$content = trim( html_entity_decode( $content, ENT_QUOTES ) );
			$content = current( explode("\n", $content, 2 ) );
			// return first sentences
			$content = current( explode('. ', $content, 2 ) );

		} else {
			$content = trim( html_entity_decode( $content, ENT_QUOTES ) );
		}

		// since we call html_entity_decode, we need to re-encode to make it xml compliant
		return PMC_Custom_Feed_Helper::esc_xml( $content );
	}

	/**
	 * this filter responsible for extracting inline images, links, and retrieve gallery images
	 * @param object $post The post object
	 * @param array $feed_options Optional feed options
	 * @return object $post The modified post object
	 */
	public function filter_pmc_custom_feed_post_start( $post, $feed_options = false ) {

		if ( empty( $post ) || empty( $post->ID ) || !empty( $post->_reuter_feed_processed ) ) {
			return $post;
		}

		$post->guid = add_query_arg( 'p', $post->ID, trailingslashit( get_home_url() ) );

		if ( isset( $this->_last_generated_info['modified_item'][$post->ID] ) ) {
			$this->_modified_item_list[$post->ID] = $this->_last_generated_info['modified_item'][$post->ID];
		} else {
			$this->_modified_item_list[$post->ID]['related'] = array( 'md5' => '', 'modified' => '' );
			$this->_modified_item_list[$post->ID]['media_group'] = array( 'md5' => '', 'modified' => '' );
			$this->_modified_item_list[$post->ID]['item'] = array( 'md5' => '', 'modified' => '' );
		}

		$post->_reuter_feed_processed = true;

		// we do not want to proceed any further if feed is text only
		if ( !empty( $this->_feed_options['text-only'] ) ) {
			return $post;
		}

		$post->feed_images = array();
		$post->feed_gallery_images = array();
		$post->feed_videos = array();

		if ( !isset( $post->feed_related ) ) {
			$post->feed_related = array();
		}


		$related_link_max_age = apply_filters( 'pmc_custom_feed_related_links_last_n_days', self::RELATED_LINK_AGE_IN_DAYS );

		// PPT-3778
		// Extract pmc-related-link shortcode links first
		$links = array();
		$matches = pmc_find_all_shortcode( $post->post_content , 'pmc-related-link' );
		foreach( $matches as $m ) {
			if( !empty( $m['attr']['href'] ) ) {
				$link = array(
					'type'    => 'pmc-related',
					'url'     => $m['attr']['href'],
					'caption' => '',
				);

				$id = PMC_Custom_Feed_Helper::url_to_postid( $link['url'] );
				if ( !empty( $id ) ) { // this is a local post
					$link = PMC_Custom_Feed_Helper::get_link_detail( $id, $link );
					$links[$link['url']] = $link;
				}
			}
		}

		// auto extract related links from article content and strip tag
		$inline_links = PMC_Custom_Feed_Helper::extract_links( $post, true );
		if( !empty( $inline_links) && is_array( $inline_links ) ) {
			$links += $inline_links; // this won't clobber existing keys
		}
		foreach ( $links as $link ) {
			if ( !empty( $link['published_gmt'] ) && !empty( $link['guid'] ) && ! isset( $post->feed_related[ $link['guid'] ] ) ) {
				$timestamp = $link['published_gmt'];

				if ( !is_numeric( $timestamp ) ) {
					$timestamp = strtotime( $timestamp );
				}

				$age = time() - $timestamp;
				if ( $age/DAY_IN_SECONDS <= $related_link_max_age ) {
					$post->feed_related[ $link['guid'] ] = $link;
				}

			}

		} // foreach

		// Limit the number of related items we include (per Reuters request)
		if( is_array( $post->feed_related ) ) {
			$post->feed_related = array_slice( $post->feed_related, 0, $this->_related_item_limit );
		}

		// extract images from <img> and [gallery] and remove tags from $post->post_content
		$post->feed_images = array_merge( $post->feed_images, PMC_Custom_Feed_Helper::extract_images( $post, true ) );

		$keywords = array();

		$terms = wp_get_post_terms( $post->ID, 'post_tag', array( 'fields' => 'names') );
		if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
			$keywords = array_merge( $keywords, $terms );
		}

		$terms = wp_get_post_terms( $post->ID, 'category', array( 'fields' => 'names') );
		if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
			$keywords = array_merge( $keywords, $terms );
		}

		unset( $terms );
		$keywords = implode(', ', array_unique( $keywords ) );

		$args = array(
			'post_type'   => 'attachment',
			'numberposts' => 50, // we shouldn't be having that much attachments to a post, avoiding -1 value
			'post_status' => 'any',
			'post_parent' => $post->ID
		);
		$attachments = get_posts( $args );
		$attachments_lookup = array();

		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$attachments_lookup[ parse_url( wp_get_attachment_url ( $attachment->ID ), PHP_URL_PATH ) ] = $attachment->ID;
			}
		}
		unset( $attachments );

		// move gallery images into $post->feed_gallery_images
		foreach ( $post->feed_images as $key => $image ) {

			// image doesn't have a guid?
			if ( empty( $post->feed_images[$key]['guid'] ) ) {
				$lookup_path = parse_url( $image['url'], PHP_URL_PATH );

				if ( !empty( $attachments_lookup[ $lookup_path ] ) ) {
					$lookup_image = PMC_Custom_Feed_Helper::get_image_detail( $attachments_lookup[ $lookup_path ] );
					if ( !empty( $lookup_image ) ) {
						$post->feed_images[$key] = $lookup_image;
					}
				}

				// still can't find the guid? Let's assign some default value
				if ( empty( $post->feed_images[$key]['guid'] ) ) {
					$post->feed_images[$key]['guid'] = '';
					$post->feed_images[$key]['modified_gmt'] = $post->post_modified_gmt;
				}
			}

			if ( !empty( $keywords ) ) {
				if ( empty( $post->feed_images[$key]['keywords'] ) ) {
					$post->feed_images[$key]['keywords'] = $keywords;
				} else {
					$post->feed_images[$key]['keywords'] .= ',' . $keywords;
				}
				$image = $post->feed_images[$key];
			}

			if ( !empty( $image['type'] ) && 'gallery' == $image['type'] ) {
				$post->feed_gallery_images[ $image['url'] ] = $image;
				unset( $post->feed_images[$key] );
			}

		} // foreach

		// use filter to fetch gallery for compatibility between pmc-gallery 1.0 vs 2.0
		$gallery_data = apply_filters( 'pmc_fetch_gallery', false, $post->ID );
		if ( !empty( $gallery_data ) ) {

			foreach ( $gallery_data as $item ) {

				if ( empty( $item['ID'] ) ) {
					continue;
				} // if item is empty

				$image = PMC_Custom_Feed_Helper::get_image_detail( $item['ID'], array( 'type' => 'gallery' ) );

				if ( !empty( $image['url'] ) ) {

					if ( !empty( $keywords ) ) {
						if ( empty( $image['keywords'] ) ) {
							$image['keywords'] = $keywords;
						} else {
							$image['keywords'] .= ',' . $keywords;
						}
					} // if keywords is not empty

					$post->feed_gallery_images[ $image['url'] ] = $image;
				} // if image url is not empty

			} // foreach gallery item

		} // if gallery is not empty

		// feature image
		if ( has_post_thumbnail( $post->ID ) ) {
			$image = PMC_Custom_Feed_Helper::get_image_detail( get_post_thumbnail_id( $post->ID ), array( 'type' => 'featured' ) );
			if ( !empty( $image['url'] ) ) {

				// if image alread in gallery, just flag the image to avoid duplicates
				if ( !empty( $post->feed_gallery_images[ $image['url'] ] ) ) {
					$post->feed_gallery_images[ $image['url'] ]['is_featured'] = true;
				} else {
					if ( !empty( $keywords ) ) {
						if ( empty( $image['keywords'] ) ) {
							$image['keywords'] = $keywords;
						} else {
							$image['keywords'] .= ',' . $keywords;
						}
					}
					$post->feed_images[ $image['url'] ] = $image;
				}

			}
		}

		// variety's youtube video
		if ( 'variety_top_video' == get_post_type( $post ) ) {

			$vdata = get_post_meta( $post->ID, '_variety_top_video_data', true );

			if ( !empty( $vdata['link'] ) ) {
				$post->feed_videos[ $vdata['link'] ] = array(
						'type'                 => 'youtube',
						'url'                  => $vdata['link'],
						'title'                => !empty( $vdata['title'] ) ? $vdata['title'] : '',
						'thumbnail'            => !empty( $vdata['thumbnail'] ) ? $vdata['thumbnail'] : '',
						'description'          => !empty( $vdata['desc'] ) ? $vdata['desc'] : '',
						'duration'             => !empty( $vdata['duration'] ) ? $vdata['duration'] : false,
					);
			}

		}

		$enclosure = get_post_meta( $post->ID, 'enclosure', false );

		if ( !empty( $enclosure ) ) {

			foreach ( $enclosure as $string ) {
				$tokens = preg_split( '/\n/', $string );

				if ( 3 <= count( $tokens ) && !empty( $tokens[1] ) ) {
					$url = $tokens[0];
					// enclose might have duplicate, make sure we do de-dup
					if ( !empty( $post->feed_videos[ $url ] ) ) {
						continue;
					}
					$post->feed_videos[ $url ] = array(
							'url'  => $url,
							'size' => $tokens[1],
							'type' => $tokens[2],
						);
				} // if

			} // foreach

		} // if ! empty enclosure

		// if we only have single video, move it to feed_video to output as single media rss
		if ( 1 == count( $post->feed_videos ) ) {
			$post->feed_video = reset( $post->feed_videos );
			$post->feed_videos = array();
		}

		// calculate the md5 for media:group node to detect changes
		$post->md5_media_group = md5( serialize( $post->feed_images ) . serialize(  $post->feed_gallery_images ) . serialize( $post->feed_videos ) );
		// calculate the md5 for related node to detect changes
		$post->md5_related = md5( serialize( $post->feed_related ) );

		$hash_object = array();

		//allow new post modified time only if its after our cutoff
		$timezone_string = get_option( 'timezone_string' );
		if ( empty( $timezone_string ) ) {
			$timezone_string = 'America/Los_Angeles';
		}

		$timemachine = PMC_TimeMachine::create( $timezone_string )->from_time( 'Y-m-d H:i:s', self::LAST_MODIFIED_CUTOFF_DATE );

		if ( in_array( 'md5_related', $this->_hash_fields ) ) {
			unset( $this->_hash_fields[ array_search( 'md5_related', $this->_hash_fields ) ] );
		}

		if ( intval( get_post_modified_time( 'U', false, $post->ID ) ) > intval( $timemachine->format_as( 'U' ) ) ) {
			//add related item md5 only if post last modified date greater than our cutoff
			$this->_hash_fields[] = 'md5_related';
		}

		// Only include specific fields when calculating the hash of a post object
		foreach( $this->_hash_fields as $prop ) {
			if( property_exists( $post, $prop ) ) {
				$hash_object[$prop] = empty( $post->$prop ) ? 0 : $post->$prop;
			}
		}
		$post->md5_item = md5( serialize( $hash_object ) );

		// track last modified
		$this->add_to_modified_list( $post );

		return $post;

	}	//end filter_pmc_custom_feed_post_start()

	// filter to return feed <item> attributes
	public function filter_pmc_custom_feed_attr_item( $attrs ) {

		$post = get_post();

		if ( empty( $post ) ) {
			return $attrs;
		}

		if ( !is_array( $attrs ) ) {
			$attrs = array();
		}

		if ( !empty( $post->order ) ) {
			$attrs['order'] = $post->order;
		}

		// add the last-modified attribute
		return $this->_filter_attr_modified ( $attrs, 'item' );
	}

	// filter to add <media:group> attributes
	public function filter_pmc_custom_feed_attr_media_group( $attrs ) {
		return $this->_filter_attr_modified ( $attrs, 'media_group' );
	} // function

	// filter to add <related> attributes
	public function filter_pmc_custom_feed_attr_related( $attrs ) {
		return $this->_filter_attr_modified ( $attrs, 'related' );
	}

	/**
	 * helper function to generate last-modified attribute
	 * @param array $attrs The associate array of attributes list
	 * @param string $filter The filter suffix string to apply
	 * return array
	 */
	private function _filter_attr_modified( $attrs, $filter ) {

		// default modified date
		$modified = 0;

		$post = get_post();
		if ( empty( $post ) ) {
			return $attrs;
		}

		if ( !is_array( $attrs ) ) {
			$attrs = array();
		}

		// the md5 field name from the post to check
		$md5_field = 'md5_'. $filter;

		// BEG: PPT-3747 Debugging -- Add extra attributes to help diagnose duplicate feed items
		$debug_filter = ! empty( $filter ) ? $filter : 'x-unknown-filter-' . str_replace( '.', '-', microtime( true ) );
		// Set our last-modified column first to avoid disrupting the order of attributes in case Reuters has a horrible parser (they do)
		$attrs['last-modified'] = 0; // this will be set correctly later

		if ( empty( $post->$md5_field ) ) {
			$attrs['x-no-md5-' . $debug_filter] = $debug_filter;
		}

		if ( $filter == 'item' && ! empty( $post->post_modified_gmt ) ) {
			$attrs['x-post_modified_gmt-ts'] = mysql2date( 'D, d M Y H:i:s +0000', $post->post_modified_gmt );
		}
		// END: PPT-3747 Debugging

		// Do we have md5 calculated for this post?
		if ( !empty( $post->$md5_field ) ) {
			// was there anything changed?
			// BEG: PPT-3747 Debugging -- Add extra attributes to help diagnose duplicate feed items
			// $attrs["x-dbg-$filter-modified"] = $this->_modified_item_list[$post->ID][$filter]['modified'];
			$attrs["x-dbg-$filter-md5"] = $this->_modified_item_list[$post->ID][$filter]['md5'];
			$attrs["x-dbg-$filter-new-md5--$md5_field"] = $post->$md5_field;
			// END: PPT-3747 Debugging

			if ( empty( $this->_modified_item_list[$post->ID][$filter]['modified'] )
				|| empty( $this->_modified_item_list[$post->ID][$filter]['md5'] )
				|| $this->_modified_item_list[$post->ID][$filter]['md5'] != $post->$md5_field
				) {
					// there are changes, update modified to the current timestamp
					$modified = time();
					$prev_modified = $this->_modified_item_list[$post->ID][$filter]['modified'];
					$this->_modified_item_list[$post->ID][$filter]['modified'] = $modified;
					$this->_modified_item_list[$post->ID][$filter]['md5'] = !empty( $post->$md5_field ) ? $post->$md5_field : '';
					// BEG: PPT-3747 Debugging -- Add extra attributes to help diagnose duplicate feed items
					$attrs['x-orig-modified-date'] = $prev_modified;
					$attrs['x-new-hash'] = $post->$md5_field;
					$attrs['x-modified-field-name-' . $debug_filter] = gmdate( 'D, d M Y H:i:s +0000', $modified );
					// END: PPT-3747 Debugging
			} else {
				// nothing changed, retain the current modified TS
				$modified = $this->_modified_item_list[$post->ID][$filter]['modified'];
				$attrs['x-no-change'] = "1";
			}
		} else {
			// We don't have an md5 for this field, we're going to assume that this is a glitch, and *not* trigger an update to the last-modified value (PPT-3747)
			if( ! empty( $post->post_modified_gmt ) ) {
				$modified = mysql2date( 'D, d M Y H:i:s +0000', $post->post_modified_gmt ); // <<< I don't think this is correct
			}

		}

		if( ! empty( $modified ) ) {
			// generate the last-modified attribute value -- only if we've set it correctly
			$attrs['last-modified'] = gmdate( 'D, d M Y H:i:s +0000', $modified );
		}
		return $attrs;
	}

	/**
	 * Listener bound to 'pmc_custom_feed_item' action hook
	 *
	 * @param WP_Post $post Post object of the current post in the loop
	 * @param array $feed_options Array containing current feed options
	 * @return void
	 *
	 * @since 2014-07-14 Amit Gupta
	 * @version 2014-07-21 Hau Vong - moved and modifed from PMC_Custom_Feed_Helper::pmc_custom_feed_item
	 */
	public function action_pmc_custom_feed_item( $post, $feed_options = false ) {
		if ( ! empty( $post->post_modified_gmt ) ) {
			echo sprintf( '<lastModified>%s</lastModified>', mysql2date( 'D, d M Y H:i:s +0000', $post->post_modified_gmt ) );
		}

		$slug = apply_filters( 'pmc_custom_feed_item_slug', '', $post );
		if ( !empty( $slug ) ) {
			printf( '<slug>%s</slug>', PMC_Custom_Feed_Helper::esc_xml( $slug ) );
		}

		// single video
		if( !empty( $post->feed_video ) ) {
			PMC_Custom_Feed_Helper::render_video_node( $post->feed_video );
			unset( $post->feed_video );
		}

	} // function

	/**
	 * Filter function to remove "SEE ALSO:" link variants
	 *
	 * Tests (drop into a post):
	 * // 1: STRONG|SEE ALSO:|LINK|xxx|/LINK|/STRONG
	 * VARIANT 1 START <b>b_tag</b><strong>SEE ALSO:<a href="xx">(inside text)VARIANT 1(end inside text)</a></strong> aa bb <b>b_tag</b> VARIANT 1 END
	 *
	 * // 4: LINK|SEE ALSO:|STRONG|xxx|/STRONG|/LINK
	 * VARIANT 4 START <b>b_tag</b> <a href="xx">SEE ALSO: <strong>(inside text)VARIANT 4(end inside text)</strong></a> aa bb <b>b_tag</b> VARIANT 4 END
	 *
	 * // 2: STRONG|LINK|SEE ALSO: xxx|/LINK|/STRONG
	 * VARIANT 2 START <b>b_tag</b><strong><a href="xx">SEE ALSO: (inside text)VARIANT 2(end inside text)</a></strong> aa bb <b>b_tag</b> VARIANT 2 END
	 *
	 * // http://variety.com/2014/film/news/lauren-bacall-star-of-hollywoods-golden-age-dies-at-89-1201281523/
	 * // 3: LINK|STRONG|SEE ALSO:|/STRONG|/LINK
	 * VARIANT 3 START <b>b_tag</b><a href="xx"><strong>SEE ALSO: (inside text)VARIANT 3(end inside text)</strong></a> aa bb <b>b_tag</b> VARIANT 3 END
	 *
	 * // Never match
	 * <strong><a href="xx">This shouldn't match</a></strong>
	 * NEVER MATCH START <b>SEE ALSO: (b_tag)</b><strong>SEE<a href="xx">SEE ALSO: (in)VARIANT NEVER MATCH(end in)</a></strong> aa bb <b>t</b> NEVER MATCH END
	 *
	 * @version 2014-07-17 Hau Vong Initial version (PPT-2903)
	 * @version 2014-08-13 Corey Gilmore Add support for additional variants of SEE ALSO. (PPT-3148)
	 *
	 */
	public function filter_remove_see_also( $content ) {
		// simple check to see if SEE ALSO exist in string, avoiding expensive regular expression for now
		if ( stristr( $content, 'SEE ALSO:' ) !== false ) {
			// Use regular expressions to remove SEE ALSO links
			// Reuters Phase 2 might involve extracting the content of related nodes
			$re = array(
				'/<(?:stron|a )[^>]+>\s*SEE ALSO:\s*<(?:stron|a )[^>]+>[^<]+<\/(?:a|strong)>\s*<\/(?:a|strong)>/im', // Matches 1 and 4
				'/<(?:stron|a )[^>]+><(?:stron|a )[^>]+>\s*SEE ALSO:\s*[^<]+<\/(?:a|strong)>\s*<\/(?:a|strong)>/im', // Matches 2 and 3
			);

			$content = preg_replace( $re, '', $content, -1, $matches );
		}
		return $content;
	}

	public function filter_strip_image_credit_caption_unicode( $text, $id ) {
		$text = $this->filter_unicode_convert_strip_all_tags( $text );

		return $text;
	}

	// apply xml escape after unicode conversion
	public function filter_unicode_convert_esc_xml( $content ) {
		return PMC_Custom_Feed_Helper::esc_xml( $this->filter_unicode_convert_strip_all_tags( $content ) );
	}

	// apply xml escape after html decode
	public function filter_esc_xml( $content ) {
		$content = PMC_Custom_Feed_Helper::esc_xml( html_entity_decode( $content, ENT_QUOTES ) );
		return $content;
	}

	// do unicode convert, strip all tags except <p>
	public function filter_unicode_convert_keep_p_tags( $content ) {

		// append new line after each paragraph to preserve paragraph marker
		// we want to strip all tags so that any pre-html add to content
		// will be part of first paragraph
		$content = str_replace( '</p>',"</p>\n", $content );
		$content = $this->filter_unicode_convert_strip_all_tags( $content );

		// convert plaintext lines break into paragraph with <p> tag only
		$content = wpautop( $content, false );

		return $content;
	}

	/**
	 * Address edge cases of characters that aren't converted or replaced properly in the plain-text reuters feed.
	 *
	 * @version 2014-08-13 Corey Gilmore Inital version
	 *
	 */
	public function convert_weird_unicode( $text ) {
		$text = str_replace( array( '©', "\xC2\x8A", 'ø', 'Ø', ), array( '(c)', '', 'o', 'O', ), $text );

		return $text;

	}

	/**
	 * Fix more one-off issues with the plain text feed, specifically single/double quotes following a number being converted into a prime/double prime.
	 * This function is possibly overkill, but we keep running into more edge cases and I'm tired of addressing them.
	 *
	 * @see https://core.trac.wordpress.org/ticket/8775
	 * @version 2014-08-18 Corey Gilmore Inital version
	 *
	 */
	public function fix_wp_quote_prime_bug( $text ) {
		static $utf8_find = array(); // store as static so we don't have to rebuild our arrays. Not even sure if this will result in a noticeable performance gain
		static $ascii_replace = array();

		if( empty( $utf8_find ) ) {
			$characters = array(
				// double prime -- https://core.trac.wordpress.org/ticket/8775
				// http://www.fileformat.info/info/unicode/char/2033/index.htm
				array(
					'utf8'        => "\xE2\x80\xB3",
					'dec_entity'  => '&#8243;',
					'hex_entity'  => '&#x2033;',
					'replace'     => '"',
				),

				// left (reversed) double prime quotation mark
				// http://www.fileformat.info/info/unicode/char/301D/index.htm
				array(
					'utf8'        => "\xE2\x80\x9D",
					'dec_entity'  => '&#12317;',
					'hex_entity'  => '&#x301d;',
					'replace'     => '"',
				),

				// right double prime quotation mark
				// http://www.fileformat.info/info/unicode/char/301e/index.htm
				array(
					'utf8'        => "\xE2\x80\x9E",
					'dec_entity'  => '&#12318;',
					'hex_entity'  => '&#x301e;',
					'replace'     => '"',
				),

				// left (reversed) single prime
				// http://www.fileformat.info/info/unicode/char/2035/index.htm
				array(
					'utf8'        => "\xE2\x80\xB5",
					'dec_entity'  => '&#8245;',
					'hex_entity'  => '&#x2035;',
					'replace'     => "'",
				),

				// right single prime
				// http://www.fileformat.info/info/unicode/char/2032/index.htm
				array(
					'utf8'        => "\xE2\x80\xB2",
					'dec_entity'  => '&#8242;',
					'hex_entity'  => '&#x2032;',
					'replace'     => "'",
				),

			);

			foreach( $characters as $char ) {
				$utf8_find[] = $char['utf8'];
				$utf8_find[] = $char['dec_entity'];
				$utf8_find[] = $char['hex_entity'];

				$ascii_replace[] = $char['replace'];
				$ascii_replace[] = $char['replace'];
				$ascii_replace[] = $char['replace'];
			}
		}

		$text = str_ireplace( $utf8_find, $ascii_replace, $text );

		return $text;
	}

	public function filter_keep_html( $content ) {
		// do we need to strip any html tag here?
		$content = html_entity_decode( $content, ENT_QUOTES );
		return  PMC_Custom_Feed_Helper::encode_numericentity( $content );
	}

	// do unicode convert, strip all tags
	public function filter_unicode_convert_strip_all_tags( $content, $keep_tags = '' ) {

		$content = wp_strip_all_tags( $content );
		$content = PMC::strip_control_characters( $content );

		$content = $this->fix_wp_quote_prime_bug( $content );
		$content = PMC::untexturize( $content );
		$content = html_entity_decode( $content, ENT_QUOTES );
		$content = $this->convert_weird_unicode( $content );
		$content = $this->utf8_to_ascii( $content );

		return trim( $content );
	}

	// code taken from http://php.net/manual/en/function.iconv.php
	function utf8_to_ascii( $text ) {
		$limit = 1000; // maximum number of characters to process at once. Protect against too deep of a recursion stack.

		if ( is_string( $text ) ) {
			// Includes combinations of characters that present as a single glyph
			$len = mb_strlen( $text );
			$strings = array();
			if( $len >= $limit ) {
				for( $x = 0; $x < $len; $x += $limit ) {
					$strings[] = mb_substr( $text, $x, $limit );
				}
			} else {
				$strings = array( $text );
			}

			// Process the strings in chunks of $limit; with 48k characters we were hitting a limit
			foreach( $strings as $x => $string ) {
				$strings[$x] = preg_replace_callback( '/\X/u', array( $this, __FUNCTION__ ) , $strings[$x] );
			}

			$text = implode( '', $strings );

		} elseif ( is_array($text) && count($text) == 1 && is_string( $text[0] ) ) {
			// IGNORE characters that can't be TRANSLITerated to ASCII
			$text = iconv("UTF-8", "ASCII//TRANSLIT", $text[0]);
			// The documentation says that iconv() returns false on failure but it returns ''
			if ($text === '' || !is_string($text)) {
				$text = '';
			}
			elseif (preg_match('/\w/', $text)) {            // If the text contains any letters...
				$text = preg_replace('/\W+/', '', $text);   // ...then remove all non-letters
			}
		} else {  // $text was not a string
			$text = '';
		}
		return $text;
	} // function

	public function action_pmc_custom_feed_item_media_group( $post ) {

		// we don't want to render media node if feed is text only
		if ( !empty( $this->_feed_options['text-only'] ) ) {
			return;
		}

		if ( !empty( $post->feed_gallery_images ) ) {
			$slide_order = 0;

			foreach ( $post->feed_gallery_images as $image ) {
				$image['slide_order'] = ++$slide_order;
				PMC_Custom_Feed_Helper::render_image_node( $image );
			}

		} // if gallery images

		if ( !empty( $post->feed_images ) ) {
			$now = time(); // used for calculating the age of an image
			foreach ( $post->feed_images as $image ) {
				if ( !empty( $image['type'] ) && 'featured' == $image['type'] ) {
					$image['is_featured'] = true;

					// PPT-3778: If a featured image is > 30 days old, set the modified dates of the node
					//  to the post publish date; Reuters only delivers 30 days of content to
					//  partners, and we don't want to exclude featured images.
					$timestamp = $image['modified_gmt'];

					if ( !is_numeric( $timestamp ) ) {
						$timestamp = strtotime( $timestamp );
					}

					$age = $now - $timestamp;
					if ( $age/DAY_IN_SECONDS > PMC_Custom_Feed_Reuters::IMAGE_THRESHOLD_IN_DAYS ) {
						// preserve the original (real) modified timestamps for extra output
						$image['orig_modified_gmt'] = $image['modified_gmt'];
						$image['orig_modified'] = $image['modified'];

						$image['modified_gmt'] = $post->post_date_gmt;
						$image['modified'] = $post->post_date;
					}
				}

				PMC_Custom_Feed_Helper::render_image_node( $image );
			}

			unset ( $post->feed_images );

		} // if images

		if ( !empty( $post->feed_videos ) ) {

			foreach ( $post->feed_videos as $video ) {
				PMC_Custom_Feed_Helper::render_video_node( $video );
			} // foreach

			unset( $post->feed_videos );
		} // if videos

	} // function

	// feed action within rss <channel> node
	public function action_pmc_custom_feed_channel( $posts, $feed_options = false ) {
		$last_modified = time();

		// if feed id is pass, we should use the the feed meta to keep track of package last modified date.
		if ( !empty( $feed_options['feed_id'] ) ) {

			// since we don't want to wait until all posts are process before we can generate the data
			// so we manually call our post start filter function to do the job. @see filter_pmc_custom_feed_post_start for more detail

			if ( !is_array( $posts ) ) {
				$posts = array( $posts );
			}

			foreach ( $posts as $post ) {
				$post = apply_filters( 'pmc_custom_feed_post_start', $post, $feed_options );
			}

			// generate md5 hash to keep track of feed generation to detect if feed has changed
			$md5_hash = md5( serialize( $this->_last_modified_list ) );

			unset( $this->_last_modified_list );
			$this->_last_modified_list = array();

			// check to see if we need to update the feed post meta
			if ( empty( $this->_last_generated_info ) // need update if we don't have last generation info
					|| empty( $this->_last_generated_info['md5_hash'] ) // not a valid md5
					|| empty( $this->_last_generated_info['last_modified'] ) // not a valid last modified date
					|| $this->_last_generated_info['md5_hash'] != $md5_hash // md5 does not match
				) {
				$this->_last_generated_info['md5_hash'] = $md5_hash;
				$this->_last_generated_info['last_modified'] = $last_modified;
				$this->_dirty = true;
			} else {
				// feed is the same, so get last modified date from last generation
				$last_modified = $this->_last_generated_info['last_modified'];
			}

			// add our feed info to track last modified
			$this->add_to_modified_list( get_post( $feed_options['feed_id'] ) );

		} // if

		printf( '<lastModified>%s</lastModified>', PMC_Custom_Feed_Helper::esc_xml( gmdate( 'D, d M Y H:i:s +0000', $last_modified ) ) );

	} // function

	// action hook end of feed, need to save last generated info if anything changes
	public function action_pmc_custom_feed_end() {
		if ( $this->_dirty && !empty( $this->_last_generated_info ) && !empty( $this->_feed_options['feed_id'] ) ) {
			$this->_last_generated_info['modified_item'] = $this->_modified_item_list;
			// update the post meta with generation information
			update_post_meta( $this->_feed_options['feed_id'], PMC_Custom_Feed::meta_custom_name .'last_generated_info', $this->_last_generated_info );
			$this->_dirty = false;
		}
	}

	/**
	 * This function is use to extract the neccessary data to piece together the package last modified date
	 * @param mixed $data Object or array that contains id & modified date.
	 * return $data
	 */
	function add_to_modified_list( $data ) {
		if ( is_object( $data ) ) {
			$data = (array)$data;
		}
		if ( !is_array( $data ) ) {
			return;
		}

		// list of fields to track for changes
		$fields = array( 'ID', 'id', 'modified_gmt', 'post_modified_gmt', 'md5_item', 'md5_related', 'md5_media_group' );
		$modified = array();

		foreach ( $fields as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$modified[ $key ] = $data[ $key ];
			}
		}
		if ( !empty( $modified ) ) {
			// make sure we have unique data
			$key = md5( serialize( $modified ) );
			$this->_last_modified_list[ $key ] = $modified;
		}

		return $data;

	} // function

}

PMC_Custom_Feed_Reuters::get_instance();
// EOF
