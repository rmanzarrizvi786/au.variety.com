<?php

/**
 * Class containing Instant Articles feed related template tags
 *
 * @author Archana Mandhare, PMC
 *
 * @since 2015-10-29
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Facebook_Instant_Articles {

	use Singleton;

	const VIDEO_WIDTH = 320;
	const VIDEO_HEIGHT = 180;
	const AD_WIDTH = 300;
	const AD_HEIGHT = 250;

	/**
	 * Initialize class
	 *
	 * @since 2015-10-30
	 * @version 2015-10-30 Archana Mandhare PMCVIP-411
	 *
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	/**
	 * Setup hooks in init action
	 *
	 * @since 2015-10-30
	 * @version 2015-10-30 Archana Mandhare PMCVIP-411
	 *
	 */
	public function action_init() {

		add_action( 'pmc_custom_feed_start', array( $this, 'action_pmc_custom_feed_start' ), 10, 3 );
		add_filter( 'pmc_custom_feed_content', array( $this, 'facebook_instant_article_inject_comscore' ), 10, 5 );
	}


	/**
	 * This function loads current feed's options if not already loaded
	 *
	 * @since 2015-10-30
	 * @version 2015-10-30 Archana Mandhare PMCVIP-411
	 * @return void
	 */
	function action_pmc_custom_feed_start( $feed, $feed_options, $template_name ) {

		// only continue if the feed is instant articles feed
		if ( 'feed-facebook-instant-articles.php' !== $template_name ) {
			return;
		}

		remove_filter( 'pmc_custom_feed_content', array( 'PMC_Custom_Feed_Helper', 'pmc_custom_feed_content' ), 10, 4 );

		/*
		 * Use this filter to process the required shortcodes since after the filter the rest of the shortcodes are stripped.
		 * Also run this filter on priority 9 before the filter is run in the theme
		 */
		add_filter( 'pmc_custom_feed_facebook_instant_articles_content', array( $this, 'filter_pmc_custom_feed_facebook_instant_articles_content' ), 9, 3 );
		add_filter( 'pmc_custom_feed_facebook_instant_articles_content', array( $this, 'filter_pmc_custom_feed_facebook_instant_articles_cover_video' ), 11, 2 );

		// process the content just before printing it in the rss
		add_filter( 'pmc_custom_feed_content', array( $this, 'pmc_custom_feed_content' ), 11, 4 );

		// featured image caption text sanitization
		add_filter( 'pmc_custom_feed_featured_image_caption', array( $this, 'filter_strip_image_credit_caption_unicode' ), 10, 2 );

		// Remove the more link and any html associated with it.
		add_filter( 'the_content_more_link', '__return_empty_string', 11 );

		add_filter( 'embed_defaults', array( $this, 'default_embed_size' ), 10, 2 );
		add_filter( 'embed_handler_html', array( $this, 'facebook_embed_handler' ), 10 );
		add_filter( 'video_embed_html', array( $this, 'youtube_embed_html' ), 10 );
		add_filter( 'youtube_width', function() { return self::VIDEO_WIDTH; });
		add_filter( 'youtube_height', function() { return self::VIDEO_HEIGHT; });

	}

	/**
	 * Add comScore to Facebook Instant Article template.
	 *
	 * @param $content
	 * @param $feed
	 * @param $post
	 * @param $feed_options
	 * @param $template_name
	 *
	 * @return string
	 */
	public function facebook_instant_article_inject_comscore( $content, $feed, $post, $feed_options, $template_name ) {
		if ( 'feed-facebook-instant-articles.php' === $template_name ) {
			ob_start();
			?>
			<figure class="op-tracker"><iframe><script>
						var _comscore = _comscore || [];
						_comscore.push({ c1: "2", c2: "6035310" });
						(function() {
							var s = document.createElement("script"), el = document.getElementsByTagName("script")[0]; s.async = true;
							s.src = (document.location.protocol == "https:" ? "https://sb" : "http://b") + ".scorecardresearch.com/beacon.js";
							el.parentNode.insertBefore(s, el);
						})();
					</script></iframe></figure>
			<?php
			$content .= ob_get_clean();
		}

		return $content;
	}

	/**
	 * Filter to modify youtube embed html code
	 *
	 * @since 2015-12-11
	 * @version 2015-12-11 Archana Mandhare PMCVIP-411
	 *
	 * @param $html string
	 *
	 * @return string
	 */
	public function youtube_embed_html( $html ) {

		if ( empty( $html ) || ! is_string( $html ) ) {
			return $html;
		}

		// If the oEmbed has already been wrapped, return the html.
		if ( false !== strpos( $html, 'op-social' ) ) {
			return $html;
		}

		if ( false !== strpos( $html, 'youtube-player' ) ) {
			$html = preg_replace( '/<span[^>]*>/i', '', $html );
			$html = preg_replace( '/<\/span>/i', '', $html );
			return '<figure class="op-social">' . $html . '</figure>';
		}

		return '<figure class="op-social"><iframe>' . $html . '</iframe></figure>';
	}

	/**
	 * Filter callback function to modify the fb:post to a blockquote tag
	 *
	 * @since 2015-12-11
	 * @version 2015-12-11 Archana Mandhare PMCVIP-411
	 *
	 * @param $html string
	 *
	 * @return string
	 */
	public function facebook_embed_handler( $html ) {

		if ( empty( $html ) || ! is_string( $html ) ) {
			return $html;
		}

		if ( false !== strpos( $html, '<fb:post' ) ) {
			$html = str_replace( '<fb:post', '<blockquote data-width="' . self::VIDEO_WIDTH . '" ', $html );
			$html = str_replace( '</fb:post>', '<p>FB embed</p></blockquote>', $html );
			$html = str_replace( 'href', 'cite', $html );
		}

		return $html;
	}

	/**
	 * Filter for setting the embed size of the youtube video
	 *
	 * @since 2015-12-21
	 * @version 2015-12-21 Archana Mandhare PMCVIP-411
	 *
	 * @param array $args
	 * @param string $url
	 *
	 * @return array
	 */
	public function default_embed_size( $args, $url ) {

		$args['width']  = self::VIDEO_WIDTH;
		$args['height'] = self::VIDEO_HEIGHT;

		return $args;
	}

	/**
	 * Filter for Custom feed content modification
	 *
	 * @since 2015-12-10
	 * @version 2015-12-10 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 * @param string $feed
	 * @param obj $post
	 * @param array $feed_options
	 *
	 * @return string
	 */
	public function pmc_custom_feed_content( $content, $feed, $post, $feed_options ) {

		if ( 'feed-facebook-instant-articles' !== $feed_options['template'] ) {
			return $content;
		}

		//generate HTML5 for the images
		$content = $this->strip_unwanted_image_tag( $content );

		//replace tags such as header, article, footer, table etc with p tag
		$content = $this->replace_unsupported_tags( $content );

		//Only h1 and h2 tags are allowed in Instant Articles - replace all the h3,h4,h5,h6 with h2
		$content = $this->replace_h_tags( $content );

		//strip out all the span with more tags from the content
		$content = $this->remove_more_tags( $content, $post );

		//Remove scripts added for embed since they are not required. Facebook adds them
		$content = $this->remove_embed_script_tags( $content );

		//Remove empty tags
		$content = $this->remove_empty_tags( $content );

		//strip out all the empty p tags from the content
		$content = $this->remove_empty_p_tags( $content );

		// Add GA tracking query strings to all the internal links
		// so that we can track the outbound traffic from Instant Articles
		$content = $this->add_utm_tracking_query_string_to_internal_url( $content );

		return $content;
	}

	/**
	 * Function to add utm query params for GA tracking the outbound traffic from Instant Articles.
	 *
	 * @since 2016-01-28
	 * @version 2016-01-28 Archana Mandhare PMCVIP-865
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function add_utm_tracking_query_string_to_internal_url( $content ){

		$regex          = '/<a[^>]*href="([^"]+)"[^>]*>|' . "<a[^>]*href='([^']+)'[^>]*>/si";

		$return_content = preg_replace_callback( $regex,

			function ( $match ) {
				$ga_tracking_qs = array(
					'utm_source'   => 'Instant',
					'utm_medium'   => 'Facebook',
					'utm_campaign' => 'Instant Articles'
				);

				$content_url_host = parse_url( $match[1], PHP_URL_HOST );
				$domain = parse_url( home_url(), PHP_URL_HOST );

				if ( strtolower( $content_url_host )  === strtolower( $domain ) ) {
					$newurl = esc_url( add_query_arg( $ga_tracking_qs, $match[1] ) );
					$atag   = str_replace( $match[1], $newurl, $match[0] );
					return $atag;
				} else {
					return $match[0];
				}
			},
			$content );

		return $return_content;

	}

	/**
	 * function to replace unsupported types with p tags
	 *
	 * @since 2015-12-10
	 * @version 2015-12-10 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function replace_unsupported_tags( $content ){

		$content = preg_replace( '/<header(.*?)<\/header>/si', '<p$1</p>', $content );
		$content = preg_replace( '/<article(.*?)<\/article>/si', '<p$1</p>', $content );
		$content = preg_replace( '/<footer(.*?)<\/footer>/si', '<p$1</p>', $content );
		$content = preg_replace( '/<table(.*?)<\/table>/si', '<figure class="op-interactive"><iframe><table$1</table></iframe></figure>', $content );
		return $content;

	}

	/**
	 * function to remove the twitter and instagram script
	 *
	 * @since 2015-12-10
	 * @version 2015-12-10 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function remove_embed_script_tags( $content ){

		/*
		 * below are twitter and instagram scripts that need to be stripped off since FB adds them on their end again
		 * platform.instagram.com/en_US/embeds.js
		 * platform.twitter.com/widgets.js
		 *
		 */

		$content = preg_replace( '#<script[^>]+?src="//platform\.instagram\.com/(.+?)/embeds\.js"(.*?)></script>#ix', '', $content );

		$content =  preg_replace('#<script[^>]+?src="//platform\.twitter\.com/widgets\.js"(.*?)></script>#ix', '',$content );

		return $content;
	}

	/**
	 * Strip off any empty tags that are not required or not part of exclusion list
	 *
	 * @since 2015-12-10
	 * @version 2015-12-10 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function remove_empty_tags( $content ){

		$exclude_tags = array( 'img', 'iframe', 'script', 'audio', 'video', 'embed', 'blockquote', 'source', 'figure' );

		$regexps = '/<(\w+)(?:\s[^>]*)?>\s*<\/\1>/';

		do {
			$string  = $content;
			$replacements = preg_match_all( $regexps, $content, $matches );

			// check if preg_replace does not return null in case of error
			if ( ! empty( $replacements ) ) {

				if ( ! empty( $matches ) ) {

					$tags_matched = $matches[1];

					foreach ( $matches[0] as $match ) {

						$skip_match = false;

						// skip match if the tags are part of exclusion list
						foreach ( $tags_matched as $tag ) {

							if ( in_array( $tag, $exclude_tags ) && false !== strpos( $match, $tag ) ) {
								$skip_match = true;
								break;
							}

						}
						// if match does not contain excluded tags allow the match
						if ( ! $skip_match ) {

							$replaced = str_replace( $match, '', $content );
							if ( ! empty( $replaced ) ) {
								$content = $replaced;
							}

						}
					}
				}
			}
		} while ( $content != $string );

		return $content;
	}

	/**
	 * function stripping out empty span that is rendered in place of more tag
	 *
	 * @since 2015-12-14
	 * @version 2015-12-14 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 * @param obj|int $curr_post
	 *
	 * @return string
	 */
	public function remove_more_tags( $content, $curr_post ) {

		/*
		 * This is a hack since $more is set to 1 for the feed
		 * and in spite of the filter "the_content_more_link" returning blank an empty span tag is rendered.
		 */
		$curr_post = get_post( $curr_post );
		$search    = '<span id="more-' . $curr_post->ID . '"></span>';
		$content   = str_replace( $search, '', $content );
		return $content;
	}

	/**
	 * Filter to modify caption for Instant Articles
	 *
	 * @since 2015-12-10
	 * @version 2015-12-10 Archana Mandhare PMCVIP-411
	 *
	 * @param string $text
	 * @param int $id
	 *
	 * @return string
	 */
	public function filter_strip_image_credit_caption_unicode( $text, $id ) {
		$text = PMC_Custom_Feed_Reuters::get_instance()->filter_unicode_convert_strip_all_tags( $text );
		return $text;
	}

	/**
	 * Filter to add cover video for Instant Articles
	 *
	 * @since 2015-12-23
	 * @version 2015-12-23 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 * @param obj $feed_post
	 *
	 * @return string $content
	 */
	public function filter_pmc_custom_feed_facebook_instant_articles_cover_video( $content, $feed_post ) {

		$feed_post = get_post( $feed_post );

		if ( empty( $feed_post ) ) {
			return $content;
		}

		// Add the featured video as the cover video of the feed
		$content = $this->add_cover_video( $content, $feed_post );

		return $content;
	}

	/**
	 * Filter to modify image and video tags for Instant Articles
	 *
	 * @since 2015-12-02
	 * @version 2015-12-02 Archana Mandhare PMCVIP-411
	 * @version 2016-02-24 Archana Mandhare PMCVIP-905
	 *
	 * @param string $content
	 * @param obj $feed_post
	 * @param array $feed_options
	 *
	 * @return string $content
	 *
	 */
	public function filter_pmc_custom_feed_facebook_instant_articles_content( $content, $feed_post, $feed_options ) {

		$feed_post = get_post( $feed_post );

		if ( empty( $feed_post ) ) {
			return $content;
		}

		//Only h1 and h2 tags are allowed in Instant Articles - replace all the h3,h4,h5,h6 with h2
		$content = $this->replace_h_tags( $content );

		$content = $this->strip_unwanted_html_tags( $content );

		//generate HTML5 for the shortcodes that are required e.g videos embed, related-links etc
		$content = PMC_Custom_Feed_Helper::process_required_shortcodes( $content, $feed_options, [ 'jwplatform', 'youtube', 'pmc-related-link', 'protected-iframe' ] );

		return $content;
	}

	/**
	 * Strip off unwanted HTML tags
	 *
	 * @since 2015-12-02
	 * @version 2015-12-02 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 *
	 * @return string $content
	 */
	public function strip_unwanted_html_tags( $content ) {
		$content = strip_tags( $content, '<p><a><img><em><strong><ul><li><ol><h1><h2><span><table><tbody><td><tr><blockquote>' );
		return $content;
	}

	/**
	 * Filter to add video as a feed cover
	 *
	 * @since 2015-12-19
	 * @version 2015-12-19 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 * @param obj|int $feed_post
	 *
	 * @return string $content
	 */
	public function add_cover_video( $content, $feed_post ){

		$feed_post = get_post( $feed_post );

		if ( class_exists( 'PMC_Featured_Video_Override' ) ) {
			$video = PMC_Featured_Video_Override::get_video_html5( $feed_post->ID, array(
				'width' => self::VIDEO_WIDTH,
				'height' => self::VIDEO_HEIGHT,
			) );
		}

		if ( ! empty ( $video ) ) {
			$content =  $video . $content;
		}
		return $content;
	}

	/**
	 * Function to strip off image tags that do not have src or are tracking scripts
	 *
	 * @since 2015-12-22
	 * @version 2015-12-22 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content string
	 *
	 * @return string $content
	 */
	public function strip_unwanted_image_tag( $content ) {

		$image_re = '/<img\s+([^>]*)src=["' . "'](https?:\/\/([^'" . '"]*))["' . "']\s+([^>]*)(.*?)>/i";

		$orig_content = $content;

		$find    = array();
		$replace = array();

		// Step 1: Find all IMG tags in the content
		if ( ! preg_match_all( $image_re, $content, $matches ) ) {
			return $content;
		}

		if ( ! empty( $matches[0] ) ) {
			$image_tags   = $matches[0];
			$image_source = $matches[2];
		}

		if ( ! empty( $image_tags ) ) {

			$i = 0;
			// Step 2: Loop over the IMG tags and wrap it with figure tag
			foreach ( $image_tags as $image_tag ) {
				$is_image = false;
				// If we didn't accidentally end up with an empty IMG tag, and the new tag isn't the same as the original tag, add them to arrays to be passed to str_replace
				if ( ! empty ( $image_tag ) ) {
					if ( ! empty ( $image_source[ $i ] ) ) {
						$info = pathinfo( parse_url( $image_source[ $i ], PHP_URL_PATH ) );
						if ( ! empty( $info['extension'] ) ) {
							//check this to validate that we have an image. Sometimes an <img> tag has tracking codes which we do not want to wrap with <figure> tag
							$is_image = in_array( strtolower( $info['extension'] ), array(
								"jpg",
								"jpeg",
								"gif",
								"png",
								"bmp"
							) );
						}
					}
				}

				if ( ! $is_image ) {
					// the IMG tag with tracking code, strip it off
					$find[]    = $image_tag;
					$replace[] = '';

				} else {
					$find[]    = $image_tag;
					if( false === strpos( $image_source[ $i ], '?') ) {
						$replace[] = '<figure><img src="' . esc_url( $image_source[ $i ] ) . '" /></figure>';
					} else {
						$image_url = substr( $image_source[ $i ], 0, strpos( $image_source[ $i ], '?' ) );
						$image_url = empty( $image_url ) ? $image_source[ $i ] : $image_url;
						$replace[] = '<figure><img src="' . esc_url( $image_url ) . '" /></figure>';
					}
				}

				$i++;
			}

			$content = str_replace( $find, $replace, $content );

		}

		if ( ! empty( $content ) && strlen( $content ) > 100 ) {
			return $content;
		} else {
			return $orig_content;
		}

		// clean up empty tag after images are stripped
		$content = PMC_Custom_Feed_Helper::remove_empty_tags( $content );

		//remove empty anchor tag.
		$content = preg_replace( '/<a[^>]*>\s*<\/a>/i', '', $content );

		return $content;
	}

	/**
	 * Replace h3-h6 tags with h2. Strip off the empty h tags
	 *
	 * @since 2015-10-30
	 * @version 2015-10-30 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 *
	 * @return string $content
	 *
	 */
	public function replace_h_tags( $content ) {

		$content = preg_replace( '/<h[3-6]>(.*?)<\/h[3-6]>/', '<h2>$1</h2>', $content );
		$content = preg_replace( '/<h[3-6](.*?)<\/h[3-6]>/si', '<h2$1</h2>', $content );

		// strip off the empty h tags
		$content = preg_replace( '/<h[1-6]><\/h[1-6]>/', '', $content );

		return $content;
	}

	/**
	 * Remove the empty p tags from the content
	 *
	 * @since 2015-11-23
	 * @version 2015-11-23 Archana Mandhare PMCVIP-411
	 *
	 * @param $content string
	 * @return string
	 *
	 */
	public function remove_empty_p_tags( $content ) {

		$content = preg_replace( '/<br \/>/iU', '', $content );

		$content = preg_replace ( '/<!--(.*)-->/Uis', '', $content );

		//remove empty p tags
		$content = preg_replace( '#<p>(\s|&nbsp;)*+(<br\s*/*>)*(\s|&nbsp;)*</p>#i', '', $content );

		// Under certain strange conditions it could create a P of entirely whitespace.
		$content = preg_replace( '|<p>\s*</p>|', '', $content );

		//remove empty p tags
		$content = preg_replace( '#<p>(\s|&nbsp;|</?\s?br\s?/?>)*</?p>#', '', $content );

		return $content;
	}

	/**
	 * get the authors for the post and render the tags
	 *
	 * @since 2015-10-29
	 * @version 2015-10-29 Archana Mandhare PMCVIP-411
	 *
	 * @param $post_id int
	 * @param $format string
	 *
	 */
	public function render_authors( $post_id, $format = 'rss' ) {

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$coauthors = PMC_Custom_Feed_Helper::get_authors( $post_id );

		$feed_authors = apply_filters( 'pmc_custom_feed_facebook_instant_articles_authors', $coauthors, $post_id );

		if ( ! empty( $feed_authors ) && is_array( $feed_authors ) ) {

			foreach ( $feed_authors as $coauthor ) {

				if ( empty( $coauthor->website ) ) {
					$coauthor->website = ! empty( $coauthor->user_url ) ? $coauthor->user_url : '';
				}

				switch ( $format ) {
					case 'rss':
						printf( '<author>%1$s</author>', PMC_Custom_Feed_Helper::esc_xml( $coauthor->display_name ) );
						break;
					case 'html5':

						if ( ! empty( $coauthor->website ) ) {

							$author = '<address>';
							$author .= '<a href="' . esc_url( $coauthor->website ) . '"';
							if ( ! empty( $coauthor->title ) ) {
								$author .= ' title="' . PMC_Custom_Feed_Helper::esc_xml( $coauthor->title ) .'"';
							}
							$author .= '>';
							$author .= PMC_Custom_Feed_Helper::esc_xml( $coauthor->display_name );
							$author .= '</a>';

							if ( ! empty( $coauthor->title ) ) {
								$author .= PMC_Custom_Feed_Helper::esc_xml( $coauthor->title );
							}

							if ( ! empty( $coauthor->twitter ) ) {
								$author .= '<a href="' . esc_url( 'http://twitter.com/' . $coauthor->twitter ) . '" >';
								$author .= PMC_Custom_Feed_Helper::esc_xml( $coauthor->twitter );
								$author .= '</a>';
							}

							$author .= '</address>';
							echo $author;

						} else {
							printf( '<address><a>%1$s</a></address>', PMC_Custom_Feed_Helper::esc_xml( $coauthor->display_name ) );
						}
						break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * called from the templates for rendering post publish date-time.
	 *
	 * @since 2015-11-10
	 * @version 2015-11-10 Archana Mandhare PMCVIP-411
	 *
	 * @param obj $curr_post
	 *
	 * @return string
	 *
	 */
	public function render_publish_time( $curr_post ) {

		$curr_post = get_post( $curr_post );

		printf( esc_html( get_post_time( 'D, F j, Y g:ia T', true, $curr_post ) ) );

	}

	/**
	 * called from the templates for rendering post publish date-time.
	 *
	 * @since 2015-11-10
	 * @version 2015-11-10 Archana Mandhare PMCVIP-411
	 *
	 * @param obj $curr_post
	 *
	 * @return string
	 *
	 */
	public function render_sub_title( $curr_post ) {

		$curr_post = get_post( $curr_post );

		if ( empty( $curr_post ) ) {
			return;
		}

		$secondary_title = apply_filters( 'pmc_custom_feed_facebook_instant_articles_sub_title', '', $curr_post );

		if ( ! empty( $secondary_title ) ) {
			printf( '<h2>' . esc_html( $secondary_title ) . '</h2>' );
		}

	}

	/**
	 * called from the templates for rendering kicker in header.
	 *
	 * @since 2015-10-29
	 * @version 2015-10-29 Archana Mandhare PMCVIP-411
	 *
	 * @param obj $curr_post
	 * @return string
	 *
	 */
	public function render_kicker( $curr_post ) {

		$curr_post = get_post( $curr_post );

		if ( empty( $curr_post ) ) {
			return;
		}

		$kicker = $this->get_primary_taxonomy( $curr_post );

		$kicker = apply_filters( 'pmc_custom_feed_facebook_instant_articles_kicker', $kicker, $curr_post );

		if ( ! empty( $kicker ) ) {
			printf( '<h3 class="op-kicker">'. wp_kses_post( $kicker ) . '</h3>' ); // $kicker already escaped
		}

	}

	/**
	 * called from the templates for rendering image or video in header.
	 *
	 * @since 2015-10-29
	 * @version 2015-10-29 Archana Mandhare PMCVIP-411
	 *
	 * @param obj|int $curr_post
	 *
	 * @return string
	 *
	 */
	public function render_cover_image_in_post( $curr_post ) {

		$curr_post = get_post( $curr_post );

		if ( empty( $curr_post ) ) {
			return;
		}

		$image = PMC_Custom_Feed_Helper::get_featured_or_first_image_in_post( $curr_post->ID );

		$image = apply_filters( 'pmc_custom_feed_facebook_instant_articles_featured_image', $image, $curr_post );

		$image_id = intval( $image['image_id'] );

		if ( empty( $image['url'] ) || empty ( $image_id ) ) {
			return;
		}

		if ( empty( $image['credit'] ) ) {
			$image['credit'] = get_post_meta( $image_id, '_image_credit', true );
		}

		$image['credit'] = apply_filters( 'pmc_custom_feed_image_credit', $image['credit'], $image_id );

		$image['caption'] = apply_filters( 'pmc_custom_feed_featured_image_caption', $image['caption'], $curr_post->ID );

		echo $this->render_image_html5( $image['url'], $image['caption'], $image['credit'], false ); // output already escaped;

	}

	/**
	 * render the image tag for instant articles for a post
	 *
	 * @since 2015-12-15
	 * @version 2015-12-15 Archana Mandhare PMCVIP-411
	 *
	 * @param string $src
	 * @param string $caption
	 * @param string $source
	 * @param bool $echo
	 *
	 * @return string
	 *
	 */
	public function render_image_html5( $src, $caption, $source, $echo = true ) {

		if( false !== strpos( $src, '?') ) {
			$image_url = substr( $src, 0, strpos( $src, '?' ) );
			$src = empty( $image_url ) ? $src : $image_url;
		}

		$image = '<figure>';

		$image .= '<img src="' . esc_url( $src ) . '" />';

		if ( ! empty( $caption ) || ! empty( $source )  ) {

			$caption = PMC::strip_control_characters( $caption );

			$image .= '<figcaption class="op-medium op-vertical-below op-left">';

			if ( ! empty( $caption ) ) {
				$image .= '<h1>' . PMC_Custom_Feed_Helper::esc_xml( $caption ) . '</h1>';
			}

			if ( ! empty( $source ) ) {
				$image .= '<cite>' . PMC_Custom_Feed_Helper::esc_xml( $source ) . '</cite>';
			}

			$image .= '</figcaption>';
		}

		$image .= '</figure>';

		if ( $echo ) {
			echo $image;
		} else {
			return $image;
		}
	}

	/**
	 * Render the Facebook Audience Network Ad tag into the header section
	 *
	 * @since 2015-12-20
	 * @version 2015-12-20 Archana Mandhare PMCVIP-411
	 *
	 */
	public function render_facebook_audience_network_ad(){

		$placement = defined( 'PMC_FACEBOOK_AUDIENCE_NETWORK_PLACEMENT' ) ? PMC_FACEBOOK_AUDIENCE_NETWORK_PLACEMENT : '';

		if ( ! empty( $placement ) ) {

			$pattern = '<figure class="op-ad"><iframe src="%s" width="%s" height="%s" style="border:0;margin:0;"></iframe></figure>';

			$script_args = array(
				'placement' => $placement,
				'adtype'    => 'banner'. self::AD_WIDTH . 'x' . self::AD_HEIGHT
			);

			$script_args = array_map( 'rawurlencode', $script_args );

			$ad_url = add_query_arg( $script_args, 'https://www.facebook.com/adnw_request' );

			printf(
				$pattern,
				esc_url( $ad_url ),
				esc_attr( self::AD_WIDTH ),
				esc_attr( self::AD_HEIGHT )
			);
		}

	}

	/*
	 * Gets the primary taxonomy for the post
	 *
	 * @since 2015-12-20
	 * @version 2015-12-20 Archana Mandhare PMCVIP-411
	 *
	 * @param obj $curr_post
	 * @return string
	 */
	public function get_primary_taxonomy( $curr_post ){

		$vertical = '';

		if ( taxonomy_exists( 'vertical' ) ) {

			if ( class_exists( 'PMC_Primary_Taxonomy' ) ) {
				$primary_vertical = PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $curr_post, 'vertical' );

				if ( is_object( $primary_vertical ) && ! empty( $primary_vertical->name ) ) {
					$vertical = $primary_vertical->name;
				}
			}

			if ( empty( $vertical ) ) {
				$verticals = get_the_terms( $curr_post->ID, 'vertical' );
				if ( ! empty( $verticals ) && is_array( $verticals ) ) {
					$verticals = wp_list_pluck( array_values( $verticals ), 'name' );
					$vertical  = $verticals[0];
				}
			}

		} else {

			if ( class_exists( 'PMC_Primary_Taxonomy' ) ) {
				$category = PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $curr_post, 'category' );

				if ( is_object( $category ) && ! empty( $category->name ) ) {
					$vertical = $category->name;
				}
			}

			if ( empty( $vertical ) ) {
				$categories = get_the_category();
				if ( ! empty( $categories ) && is_array( $categories ) ) {
					$cat_names = wp_list_pluck( array_values( $categories ), 'name' );
					$vertical  = $cat_names[0];
				}
			}
		}

		return $vertical;
	}

	/*
	 * Decide if the post is not appropriate for instant articles when it has any shortcodes, linked gallery or embed codes.
	 *
	 * @since 2015-12-25
	 * @version 2015-12-25 Archana Mandhare PMCVIP-411
	 * @version 2016-02-24 Archana Mandhare PMCVIP-905
	 *
	 * @param obj $post
	 * @param array $feed_options
	 *
	 * @return bool
	 */
	public function post_inappropriate_for_instant_articles( $post, $feed_options ) {

		$post = get_post( $post );

		if ( empty( $post ) || empty( $post->post_content ) ) {
			return true;
		}

		$inappropriate = apply_filters( 'pmc_custom_feed_facebook_instant_articles_inappropriate_content', false, $post );

		if ( ! $inappropriate ) {

			if ( class_exists( 'PMC_Featured_Video_Override' ) ) {

				// Allow only youtube and jwplayer videos
				if ( PMC_Featured_Video_Override::has_featured_video( $post->ID ) ) {
					if ( ! PMC_Featured_Video_Override::is_jwplayer_or_youtube_video( $post->ID ) ) {
						return true;
					}
				}
			}

			if ( empty ( $feed_options['enable-protected-iframe-embeds'] ) ) {
				if ( has_shortcode( $post->post_content, 'protected-iframe' ) ) {
					return true;
				}
			}

			if ( has_shortcode( $post->post_content, 'hl_tribune_embed' ) ) {
				return true;
			}

			if ( has_shortcode( $post->post_content, 'pmc_boombox' ) ) {
				return true;
			}

			if ( has_shortcode( $post->post_content, 'pmc_qzzr' ) ) {
				return true;
			}

			if ( has_shortcode( $post->post_content, 'pmc-ndn' ) ) {
				return true;
			}

			if ( has_shortcode( $post->post_content, 'polldaddy' ) ) {
				return true;
			}

			if ( has_shortcode( $post->post_content, 'pmc_onescreen' ) ) {
				return true;
			}

			if ( $this->has_social_embeds( $post->post_content ) ) {
				return true;
			}

			if( $this->has_image_in_content( $post->post_content ) ){
				return true;
			}
		}

		return $inappropriate;
	}

	/*
	 * Decide if the post has social media embeds
	 *
	 * @since 2015-12-25
	 * @version 2015-12-25 Archana Mandhare PMCVIP-411
	 *
	 * @param obj $post
	 * @return bool
	 */
	public function has_social_embeds( $content ) {

		if ( $this->has_facebook_embed( $content ) ) {
			return true;
		}

		return false;
	}

	/*
	 * Decide if the post has facebook embed
	 *
	 * @since 2015-12-25
	 * @version 2015-12-25 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 * @return bool
	 */
	public function has_facebook_embed( $content ) {
		if ( defined( 'JETPACK_FACEBOOK_EMBED_REGEX' ) ) {
			preg_match( JETPACK_FACEBOOK_EMBED_REGEX, $content, $matches );
			if ( ! empty( $matches ) ) {
				return true;
			}
		}
		unset( $matches );
		if ( defined( 'JETPACK_FACEBOOK_ALTERNATE_EMBED_REGEX' ) ) {
			preg_match( JETPACK_FACEBOOK_ALTERNATE_EMBED_REGEX, $content, $matches );
			if ( ! empty( $matches ) ) {
				return true;
			}
		}
		unset( $matches );
		if ( defined( 'JETPACK_FACEBOOK_PHOTO_EMBED_REGEX' ) ) {
			preg_match( JETPACK_FACEBOOK_PHOTO_EMBED_REGEX, $content, $matches );
			if ( ! empty( $matches ) ) {
				return true;
			}
		}
		unset( $matches );
		if ( defined( 'JETPACK_FACEBOOK_PHOTO_ALTERNATE_EMBED_REGEX' ) ) {
			preg_match( JETPACK_FACEBOOK_PHOTO_ALTERNATE_EMBED_REGEX, $content, $matches );
			if ( ! empty( $matches ) ) {
				return true;
			}
		}
		unset( $matches );
		if ( defined( 'JETPACK_FACEBOOK_VIDEO_EMBED_REGEX' ) ) {
			preg_match( JETPACK_FACEBOOK_VIDEO_EMBED_REGEX, $content, $matches );
			if ( ! empty( $matches ) ) {
				return true;
			}
		}
		unset( $matches );
		if ( defined( 'JETPACK_FACEBOOK_VIDEO_ALTERNATE_EMBED_REGEX' ) ) {
			preg_match( JETPACK_FACEBOOK_VIDEO_ALTERNATE_EMBED_REGEX, $content, $matches );
			if ( ! empty( $matches ) ) {
				return true;
			}
		}
		unset( $matches );

		if ( has_shortcode( $content, 'facebook' ) ) {
			return true;
		}

		return false;
	}

	/*
	 * Decide if the post has twitter embed
	 *
	 * @since 2015-12-25
	 * @version 2015-12-25 Archana Mandhare PMCVIP-411
	 *
	 * @param obj $post
	 * @return bool
	 */
	public function has_twitter_embed( $content ) {
		$has_twitter_url = preg_match( '#https?://(www\.)?twitter\.com/.+?/status(es)?/.*#i', $content, $matches );
		if ( ! empty ( $has_twitter_url ) && ! empty( $matches ) ) {
			return true;
		}
		unset( $matches, $has_twitter_url );
		return false;
	}

	/*
	 * Decide if the post has instagram embed
	 *
	 * @since 2015-12-25
	 * @version 2015-12-25 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 * @return bool
	 */
	public function has_instagram_embed( $content ) {

		$has_instagram_url = preg_match( '#http(s?)://instagr(\.am|am\.com)/p/([^/]*)#i', $content, $matches );
		if ( ! empty ( $has_instagram_url ) && ! empty( $matches ) ) {
			return true;
		}
		unset( $matches, $has_instagram_url );

		$has_instagram_url = preg_match( '#http(s?)://instagr(\.am|am\.com)/([^/]*)/p/([^/]*)#i', $content, $matches );
		if ( ! empty ( $has_instagram_url ) && ! empty( $matches ) ) {
			return true;
		}
		unset( $matches, $has_instagram_url );

		if ( has_shortcode( $content, 'instagram' ) ) {
			return true;
		}

		return false;
	}

	/*
	 * Decide if the post has youtube embed
	 *
	 * @since 2015-12-25
	 * @version 2015-12-25 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 * @return bool
	 */
	public function has_youtube_embed( $content ) {

		$has_youtube_url = preg_match('!(?:\n|\A)https?://(?:www\.)?(?:youtube.com/(?:v/|playlist|watch[/\#?])|youtu\.be/)[^\s]+?(?:\n|\Z)!i', $content, $matches );
		if ( ! empty ( $has_youtube_url ) && ! empty( $matches ) ) {
			return true;
		}
		unset( $matches, $has_youtube_url );

		if ( has_shortcode( $content, 'youtube' ) ) {
			return true;
		}
		return false;
	}

	/*
	 * Decide if the post has img tags
	 *
	 * @since 2016-01-05
	 * @version 2015-01-05 Archana Mandhare PMCVIP-411
	 *
	 * @param string $content
	 * @return bool
	 */
	public function has_image_in_content( $content ){

		$content = $this->strip_unwanted_image_tag( $content );

		$image_re = '/<img\s+([^>]*)src=["' . "'](https?:\/\/([^'" . '"]*))["' . "']\s+([^>]*)(.*?)>/i";

		if ( ! preg_match_all( $image_re, $content, $matches ) ) {
			return false;
		} else {
			return true;
		}

	}

}    //end of class


//initialize class
PMC_Custom_Feed_Facebook_Instant_Articles::get_instance();


//EOF
