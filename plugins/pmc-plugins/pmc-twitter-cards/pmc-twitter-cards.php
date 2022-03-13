<?php
/*
  Plugin Name: PMC Twitter Cards Plugin
  Description: Display Twitter card tags
  Version: 1.0
  Author: PMC, Amit Sannad
 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

/**
 * Class to handle Twitter card functionality.
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Twitter_Cards {

	use Singleton;

	protected function __construct() {

		add_filter( 'jetpack_open_graph_tags',  array( $this, 'twitter_cards' ) );

	}

	/**
	 * @param $tags
	 * @return array
	 */

	public function twitter_cards( $tags ) {

		$tags = $this->twitter_site_username( $tags );

		if( is_singular() ){
			$tags = $this->twitter_author_username( $tags );
		}

		$tags['twitter:card'] = 'summary';

		$tags = $this->summary_card_with_large_image( $tags );

		$tags = $this->_maybe_player_card( $tags );

		return $tags;
	}

	/**
	 * Display Twitter card  twitter:site
	 */
	public function twitter_site_username( $tags ) {

		if ( defined( 'PMC_TWITTER_SITE_USERNAME' ) && is_array( $tags ) ){

			$tags[ 'twitter:site' ] = '@'.PMC_TWITTER_SITE_USERNAME;

		}
		return $tags;
	}

	/**
	 * Display posts twitter user from contributor's ( postytpe = pmc_team or tvline_authors )
	 */
	public function twitter_author_username( $tags ) {

		global $post;

		$twitter_author = apply_filters( 'pmc_twitter_cards_author_username', false, $post, $tags );
		if( $twitter_author !== false ) {
			$twitter_author = ltrim( $twitter_author, '@');
			$tags[ 'twitter:creator' ] = '@' . $twitter_author;
			return $tags;
		}

		if(class_exists('PMC_Guest_Authors')){
			$author_data = PMC_Guest_Authors::get_instance()->get_post_authors_data($post->ID);
			if($author_data != null && isset($author_data[0]['user_twitter']) && !empty($author_data[0]['user_twitter'])){
				$twitter_author = ltrim( $author_data[0]['user_twitter'], '@');
				$tags[ 'twitter:creator' ] = '@' . $twitter_author;
				return $tags;
			}
		}

		return $tags;
	}

	protected function _maybe_player_card( $tags ) {
		if ( ! is_single() ) {
			return $tags;
		}

		$player = $this->_get_player_data();

		if ( ! empty( $player ) ) {
			$player_card = array(
				'twitter:card' => 'player',
				'twitter:title' => strip_tags( get_the_title() ),
				'twitter:description' => strip_tags( PMC::get_the_excerpt( $GLOBALS['post']->ID ) ),
			);

			if ( ! empty( $player['url'] ) ) {
				$player_card['twitter:player'] = $player['url'];
			}
			if ( ! empty( $player['width'] ) ) {
				$player_card['twitter:player:width'] = $player['width'];
			}
			if ( ! empty( $player['height'] ) ) {
				$player_card['twitter:player:height'] = $player['height'];
			}
			if ( ! empty( $player['thumbnail'] ) ) {
				$player_card['twitter:image'] = $player['thumbnail'];
			}
		}

		if ( ! empty( $player_card ) && is_array( $player_card ) ) {
			$tags = array_merge( $tags, $player_card );
		}

		unset( $player, $player_card );

		return $tags;
	}

	/**
	 * Add a Summary Card with Large Image to all single pages.
	 *
	 * @since 2016-01-29
	 * @version 2016-01-29 Archana Mandhare PMCVIP-642
	 *
	 * @param $tags array
	 *
	 * @return array
	 *
	 */
	protected function summary_card_with_large_image( $tags ) {
		if ( ! is_single() ) {
			return $tags;
		}

		$post_id = get_the_ID();

			$summary_large_image_card = array(
				'twitter:card'        => 'summary_large_image',
				'twitter:title'       => ! empty( $tags['og:title'] ) ? strip_tags( $tags['og:title'] ) : strip_tags( get_the_title() ),
			);

			$description = get_post_meta( $post_id, 'mt_seo_description', true );

			if ( empty( $description ) && ! empty( $tags['og:description'] ) ) {
				$description = $tags['og:description'];
			}

			if ( ! empty( $description ) ) {
				$summary_large_image_card['twitter:description'] = $description;
			}

			$image_id = get_post_thumbnail_id( $post_id );

			if ( ! empty( $image_id ) ) {
				list( $image_src, $width, $height ) = wp_get_attachment_image_src( $image_id, 'large' );
				if ( ! empty( $image_src ) ) {
					$summary_large_image_card['twitter:image'] = $image_src;
				}
			}

		if ( ! empty( $summary_large_image_card ) && is_array( $summary_large_image_card ) ) {
			$tags = array_merge( $tags, $summary_large_image_card );
		}

		unset( $summary_large_image_card );

		return $tags;
	}

	protected function _get_player_data() {
		if ( ! is_single() ) {
			return false;
		}

		$player = array();

		$meta_key = '';
		$current_post_type = get_post_type( $GLOBALS['post'] );

		switch ( $current_post_type ) {
			case 'pmc-trailer':
				//get youtube video url
				$meta_key = '_pmc_trailer_link';
				break;
			case 'pmc-video':
				//get youtube video url
				$meta_key = '_pmc_video_url';
				break;
			case 'variety_top_video':
				//get youtube video url
				$meta_key = '_variety_top_video_link';
				break;
			default:
				//check if featured video override is present
				$meta_key = '_pmc_featured_video_override_data';
				break;
		}

		$video = get_post_meta( $GLOBALS['post']->ID, $meta_key, true );
		if ( ! is_numeric( $video ) && ! empty( $video ) ) {
			$player = $this->_get_youtube_data( $video );
		}

		if ( ! empty( $player ) ) {
			return $player;
		}

		return false;
	}

	protected function _get_youtube_data( $youtube_url ) {
		if ( empty( $youtube_url ) || is_numeric( $youtube_url ) || is_array( $youtube_url ) || is_object( $youtube_url ) ) {
			return false;
		}

		$player = array(
			'url' => '',
			'thumbnail' => '',
			'width' => 768,
			'height' => 432,
		);

		$youtube_domains = array(
			'youtu.be',
			'www.youtube.com',
			'youtube.com',
		);

		if ( ! wpcom_vip_is_valid_domain( $youtube_url, $youtube_domains ) ) {
			return false;
		}

		$url = parse_url( $youtube_url );

		$youtube_id = '';

		if ( strpos( $url['path'], 'embed' ) !== false ) {
			$path = explode( '/', ltrim( trim( $url['path'], '/' ), 'embed/' ) );
			$youtube_id = array_shift( $path );

			unset( $path );
		} elseif ( strtolower( $url['host'] ) == 'youtu.be' ) {
			$youtube_id = ltrim( $url['path'], '/' );
		} elseif ( strpos( $url['path'], 'watch' ) !== false && ! empty( $url['query'] ) ) {
			parse_str( $url['query'], $youtube_query_vars );

			if ( ! empty( $youtube_query_vars['v'] ) ) {
				$youtube_id = $youtube_query_vars['v'];
			} elseif ( ! empty( $youtube_query_vars['V'] ) ) {
				$youtube_id = $youtube_query_vars['V'];
			}

			unset( $youtube_query_vars );
		}

		if ( ! empty( $youtube_id ) ) {
			$player['url'] = 'https://www.youtube.com/embed/' . $youtube_id;

			// Commenting out the following call to the YT v2 API as it's since
			// deprecated. We'll soon be updating this to work with their v3 API.
			/*
			$data = wpcom_vip_file_get_contents( 'http://gdata.youtube.com/feeds/api/videos/' . $youtube_id . '?v=2&alt=json' );

			if ( ! empty( $data ) ) {
				$data = json_decode( $data );
				$player['thumbnail'] = set_url_scheme( $data->entry->{'media$group'}->{'media$thumbnail'}[2]->url, 'https' );
				$player['width'] = intval( $data->entry->{'media$group'}->{'media$thumbnail'}[2]->width );
				$player['height'] = intval( $data->entry->{'media$group'}->{'media$thumbnail'}[2]->height );
			}

			unset( $data );
			*/
		}

		if ( ! empty( $player['url'] ) && ! empty( $player['thumbnail'] ) && $player['width'] > 0 && $player['height'] > 0 ) {
			return $player;
		}

		return false;
	}
}

PMC_Twitter_Cards::get_instance();

// EOF
