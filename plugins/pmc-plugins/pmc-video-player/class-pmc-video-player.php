<?php

/**
 * class PMC_Video_Player
 *
 * @since ? Gabriel Koen
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Video_Player {
	use Singleton;

	/**
	 * JW Player license key
	 *
	 * @since 7.0
	 *
	 * @var string
	 */
	const jwplayer_key = 'iCsaUXUw0u1sZNDuZKdPUPLN9Gq1u0Gv7u1Rhw==';

	/**
	 * Should be 2 integers separated by a colon, example 4:3 or 16:9
	 *
	 * @since 7.0
	 *
	 * @var string
	 */
	const default_aspect_ratio = '16:9';

	/**
	 * URL to JW Player files
	 *
	 * @since 7.0
	 *
	 * @var string
	 */
	const jwplayer_url = 'http://www.vimg.net/jwplayer-6.8';

	/**
	 * URL to JW Player skin files
	 *
	 * @since 7.0
	 *
	 * @var string
	 */
	const jwplayer_skins_url = 'http://www.vimg.net/jwplayer-skins-premium-6.8';

	/**
	 * @since 1.0.0.0
	 *
	 * @var array
	 */
	public $usedids = array();

	/**
	 * @since 1.0.0.0
	 *
	 * @var array
	 */
	protected $_settings = array();

	/**
	 * @since 1.0.0.0
	 * @version 7.0 2013-03-09 Gabriel Koen
	 *
	 * @var array
	 */
	protected $_settings_defaults = array(
		'google_analytics' => array(
			'enable' => true,
		),
		'vast' => array(
			'tag' => '',
		),
	);

	// variable to control if script already print to prevent script being output multiple time.
	protected $_script_printed = false;

	/**
	 * @since 1.0.0.0
	 * @version 1.0.0.0 2012-02-28 Gabriel Koen
	 * @version 7.0 2013-03-09 Gabriel Koen
	 */
	protected function __construct() {
		// Set up options, override defaults with user-defined preferences
		$this->_settings = wp_parse_args( get_option( 'pmc_video_player_settings', $this->_settings_defaults ), $this->_settings_defaults );

		// Set up action hooks and filters
		add_filter( 'widget_text', 'do_shortcode', 11 ); // Videos in the text widget

		// Add video embed shortcodes
		// IMPORTANT: Update the list in the function below for any new shortcode:
		// PMC_Custom_Feed_Functions::handle_shortcode_tag_for_feed().
		add_shortcode( 'aol', array( $this, 'shortcode_aol' ) );
		add_shortcode( 'flv', array( $this, 'shortcode_flv' ) );
		add_shortcode( 'cbs', array( $this, 'shortcode_cbs' ) );
		add_shortcode( 'nbc', array( $this, 'shortcode_nbc' ) );
		add_shortcode( 'theplatform', array( $this, 'shortcode_theplatform' ) );
		add_shortcode( 'funnyordie', array( $this, 'shortcode_funnyordie' ) );
		add_shortcode( 'usa', array( $this, 'shortcode_usa' ) );
		add_shortcode( 'starz', array( $this, 'shortcode_starz' ) );
		add_shortcode( 'brightcove', array( $this, 'shortcode_brightcove' ) );
		add_shortcode( 'comedycentral', array( $this, 'shortcode_comedycentral' ) );
		add_shortcode( 'espn', array( $this, 'shortcode_espn' ) );
		add_shortcode( 'msnbc', array( $this, 'shortcode_theplatform' ) );
		add_shortcode( 'abc', array( $this, 'shortcode_abc' ) );
		add_shortcode( 'theview', array( $this, 'shortcode_abc' ) );
		add_shortcode( 'cnbc', array( $this, 'shortcode_cnbc' ) );
		add_shortcode( 'foxnews', array( $this, 'shortcode_foxnews' ) );
		add_shortcode( 'abcnews', array( $this, 'shortcode_abcnews' ) );
		add_shortcode( 'teamcoco', array( $this, 'shortcode_teamcoco' ) );
		add_shortcode( 'bloomberg', array( $this, 'shortcode_bloomberg' ) );
		add_shortcode( 'yahoo', array( $this, 'shortcode_yahoo' ) );
		add_shortcode( 'pmc_iframe', array( $this, 'shortcode_pmc_iframe' ) );
		add_shortcode( 'ooyala', array( $this, 'shortcode_ooyala' ) );
		add_shortcode( 'scribblelive', array( $this, 'shortcode_scribblelive' ) );
		add_shortcode( 'vevo', [ $this, 'shortcode_vevo' ] );

		// Only add these video embed shortcodes outside of VIP.
		if( ! defined( 'WPCOM_IS_VIP_ENV' ) || ! WPCOM_IS_VIP_ENV ) {
			add_shortcode( 'hulu', array( $this, 'shortcode_hulu' ) );
		}

		add_filter( 'video_embed_html', array( $this, 'filter_hulu_video_embed_html' ) );

		add_action( 'wp_head', array( $this, 'Head' ) );

		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'all_admin_notices', array( $this, 'display_settings_errors' ) );

	}

	/**
	 * Handle an error
	 *
	 * While this method is currently a simple passthrough,
	 * it could be used to record or alter the message as needed.
	 *
	 * @since 7.1
	 * @version 7.1 2013-03-26 Nick Daugherty
	 *
	 * @param string $message The error message
	 * @return string The original error message
	 */
	public function error( $message ) {
		return $message;
	}



	/**
	 * @since 1.0.0.0
	 * @version 1.0.0.0 2012-02-28 Gabriel Koen
	 * @version 7.0 2013-03-09 Gabriel Koen
	 *
	 * @return void
	 */
	public function add_settings() {
		register_setting( 'media', 'pmc_video_player_settings', array( &$this, 'validate_video_settings' ) );

		add_settings_section( 'pmc-video-player-main', 'Custom Video Player', array( $this, 'settings_section_description_main' ), 'media' );

		add_settings_field(
			'pmc-video-player-group-google_analytics',
			'Enable Google Analytics Play Tracking',
			array( $this, 'google_analytics_settings_field' ),
			'media',
			'pmc-video-player-main'
		);

		add_settings_field(
			'pmc-video-player-group-vast',
			'VAST Tag',
			array( $this, 'vast_settings_field' ),
			'media',
			'pmc-video-player-main'
		);
	}

	/**
	 * @since 1.0.0.0
	 * @version 1.0.0.0 2012-02-28 Gabriel Koen
	 *
	 * @return void
	 */
	public function settings_section_description_main() {
		echo 'These settings apply to our self-hosted videos played through JW Player.';
	}

	/**
	 * @since 1.0.0.0
	 * @version 1.0.0.0 2012-02-28 Gabriel Koen
	 *
	 * @return void
	 */
	public function google_analytics_settings_field() {
		echo '<input id="pmc_video_player_google_analytics" name="pmc_video_player_settings[google_analytics][enable]" type="checkbox" value="1" ' . checked($this->_settings['google_analytics']['enable'], true, false) . '/>';
	}
	public function vast_settings_field() {
		$tag = $this->_settings['vast']['tag'];
		$tag = str_replace( '##sqs', '[', $tag );
		$tag = str_replace( '##sqe', ']', $tag );
		echo '<input class="regular-text" type="text" id="vast_tag" name="pmc_video_player_settings[vast][tag]" value="' . esc_attr( $tag ) . '"/>';
	}

	/**
	 * @since 1.0.0.0
	 * @version 1.0.0.0 2012-02-28 Gabriel Koen
	 * @version 7.0 2013-03-08 Gabriel Koen
	 *
	 * @param $settings
	 * @return null|string
	 */
	public function validate_video_settings( $settings ) {
		if ( empty( $settings ) ) {
			return null;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error( 'pmc_video_player_settings', 'pmc_video_player', 'Only administrators can change the video player settings.' );

			return $this->_settings;
		}

		if ( isset( $settings['google_analytics']['enable']) && ! class_exists( 'Pmc_Google_Analytics' ) ) {
			add_settings_error( 'pmc_video_player_settings', 'pmc_video_player', 'Google Analytics support requires the PMC Google Analytics plugin.' );

			return $this->_settings;
		}

		// Ensure enable flags are booleans.
		$settings['google_analytics']['enable'] = ( isset( $settings['google_analytics']['enable'] ) ) ? (bool) $settings['google_analytics']['enable'] : false;

		// Sanitize Vast Tag. This is url and domain can be anything for ads, so can not use whitelist domains.
		// Therefore checking if it is proper url or not.
		if ( ! empty( $settings['vast']['tag'] ) ) {
			$host = wp_parse_url( $settings['vast']['tag'], PHP_URL_HOST );
			if ( ! empty( $host ) ) {
				// Using ##sqs & ##sqe replacer since esc_url_raw will remove it.
				$tag                     = str_replace( '[', '##sqs', $settings['vast']['tag'] );
				$tag                     = str_replace( ']', '##sqe', $tag );
				$settings['vast']['tag'] = esc_url_raw( $tag );
			}
		}

		add_settings_error( 'pmc_video_player_settings', 'pmc_video_player_settings', 'Video player settings have been updated.', 'updated' );

		// Re-set the current object's settings
		$this->_settings = wp_parse_args( $settings, $this->_settings_defaults );

		return $settings;
	}

	/**
	 * @since 1.0.0.0
	 * @version 1.0.0.0 2012-02-28 Gabriel Koen
	 *
	 * @return void
	 */
	public function display_settings_errors() {
		settings_errors( 'pmc_video_player_settings' );
	}

	// Reverse the parts we care about (and probably some we don't) of wptexturize() which gets applied before shortcodes.
	public function wpuntexturize( $text ) {
		$find = array( '&#8211;', '&#8212;', '&#215;', '&#8230;', '&#8220;', '&#8217;s', '&#8221;', '&#038;' );
		$replace = array( '--', '---', 'x', '...', '``', '\'s', '\'\'', '&' );
		return str_replace( $find, $replace, $text );
	}

	// Return a link to the post for use in the feed.
	public function postlink() {
		global $post;

		if ( empty( $post->ID ) )
			return ''; // This should never happen (I hope).

		return apply_filters( 'vvq_feedoutput', '<p><a href="' . get_permalink( $post->ID ) . '">Click here to view the embedded video.</a></p>' );
	}

	// parse_str() but allow periods in the keys
	// Also returns instead of setting a variable
	public function parse_str_periods( $string ) {
		$string = str_replace( '.', '{{vvqperiod}}', $string );
		parse_str( $string, $result_raw );

		// Reset placeholders.
		$result = array();
		foreach ( $result_raw as $key => $value ) {
			$key = str_replace( '{{vvqperiod}}', '.', $key );
			$result[ $key ] = str_replace( '{{vvqperiod}}', '.', $value );
		}

		return $result;
	}

	// Generate a placeholder ID.
	public function videoid( $type ) {
		global $post;

		if ( empty( $post ) || empty( $post->ID ) ) {
			$objectid = uniqid( "vvq-$type-" );
		} else {
			$count = 1;
			$objectid = 'vvq-' . $post->ID . '-' . $type . '-' . $count;

			while ( ! empty( $this->usedids[ $objectid ] ) ) {
				$count++;
				$objectid = 'vvq-' . $post->ID . '-' . $type . '-' . $count;
			}

			$this->usedids[ $objectid ] = true;
		}

		return $objectid;
	}

	public function shortcode_flv( $atts, $content = '' ) {

		$shortcircuit = apply_filters( 'pmc_shortcode_flv_shortcircuit', false );

		if ( false !== $shortcircuit ) {
			return $shortcircuit;
		}

		global $feed;
		$content = $this->wpuntexturize( $content );
		$origatts = $atts;
		if ( empty( $content ) )
			return $this->error( sprintf( __( 'No URL was passed to the %s BBCode', 'vipers-video-quicktags' ), __( 'FLV', 'vipers-video-quicktags' ) ) );

		$feed_array = array( 'feed', 'rss', 'rss2' , 'atom', 'rdf', 'comments' );
		if ( ( is_feed() && in_array( $feed, $feed_array, true ) ) ) {
			return $this->postlink();
		}

		// Capture whether width, height, and ratio have been manually defined to help intelligently determine aspect ratio calculation later.
		$manually_defined_width = ( isset( $atts['width'] ) );
		$manually_defined_height = ( isset( $atts['height'] ) );
		$manually_defined_ratio = ( isset( $atts['ratio'] ) && false !== strpos( $atts['ratio'], ':' ) );

		// Set any missing $atts items to the defaults
		$ratio = explode( ':', self::default_aspect_ratio );
		$width = ( isset( $GLOBALS['content_width'] ) ) ? $GLOBALS['content_width'] : 600;
		$height = round( ( $ratio[1] * $width ) / $ratio[0] );
		$atts = shortcode_atts( array(
				'width'       => $width,
				'height'      => $height,
				'ratio'       => self::default_aspect_ratio,
				'image'       => null,
				'mediaid'     => '',
				'programid'   => '',
				'title'       => '',
				'description' => '',
				'vast'        => '',
				'autostart'   => '',
				'mute'        => '',
				'objectid'    => '',
				'primary'     => '',
				'playlist'    => '',
			), $atts, 'pmc_flv'
		);

		// Adjust the embed size based on aspect ratio, as needed.
		if ( $manually_defined_width || $manually_defined_height || $manually_defined_ratio ) {
			$ratio = explode( ':', $atts['ratio'] );
			// If a width + height is defined, then don't resize, because the embedder knows what they're doing.  A strict comparison here will break because manually set values come through as strings
			if ( $atts['width'] !== $width && ! $manually_defined_height ) {
				$atts['height'] = round( ( intval( $ratio[1] ) * intval( $atts['width'] ) ) / intval( $ratio[0] ) );
			} elseif ( $atts['height'] !== $height && ! $manually_defined_width ) {
				$atts['width'] = round( ( intval( $ratio[0] ) * intval( $atts['height'] ) ) / intval( $ratio[1] ) );
			}
		}

		$flashvars = array(
			'wmode'        => 'transparent', // Allow skins with transparency to have the background color shine through (props rich)
			'file'         => $content,
		);

		if ( $atts['image'] ) {
			$flashvars['image'] = $atts['image'];
		}

		// If URL is a RTMP stream, adjust vars accordingly
		if ( 'rtmp' === substr( $content, 0, 4 ) ) {
			$flv_pos = strrpos( $content, '/' );
			$flashvars['file'] = substr( $content, $flv_pos + 1 );
			$flashvars['streamer'] = substr( $content, 0, $flv_pos );
		}

		if ( ! empty( $atts['objectid'] ) ) {
			$objectid = sanitize_title_with_dashes( $atts['objectid'] );
		} else {
			$objectid = $this->videoid( pathinfo( $content, PATHINFO_EXTENSION ) );
		}

		if ( is_feed() ) {
			 return $this->render_inline_object_tag( array(
				'width'      => $atts['width'],
				'height'     => $atts['height'],
				'url'        => self::jwplayer_url . '/jwplayer.flash.swf',
				'flashvars'  => $flashvars
			 ), $objectid );
		 }

		$skin_url = PMC::esc_url_ssl_friendly( self::jwplayer_skins_url . '/bekle.xml' );

		$return = '<div class="vvqbox"><div id="' . esc_attr( $objectid ) . '">Loading video...</div></div>';
		$return .= '<script type="text/javascript">pmc_jwplayer("' . esc_js( $objectid ) . '").setup({';
		$return .= 'width: ' . intval( $atts['width'] ) . ',';
		$return .= 'height: ' . intval( $atts['height'] ) . ',';
		//$return .= '"skin": "' . esc_url( $skin_url ) . '",';
		if ( 1 === $this->_settings['google_analytics']['enable']
			&& class_exists( 'Pmc_Google_Analytics' ) ) {
			$return .= 'ga: {},';
		}
		$return .= 'wmode: \'opaque\','; // Allow skins with transparency to have the background color shine through (props rich)
		if ( $atts['image'] ) {
			$return .= 'image: "' . esc_js( $atts['image'] ) . '", ';
		}
		if ( $atts['description'] ) {
			$return .= 'description: "' . esc_js( $atts['description'] ) . '", ';
		}
		if ( ! empty( $atts['title'] ) ) {
			$return .= 'title: "' . esc_js( $atts['title'] ) . '",';
		}
		if ( ! empty( $atts['mediaid'] ) ) {
			$return .= 'mediaid: "' . esc_js( $atts['mediaid'] ) . '", ';
		}
		else {
			// mediaid is required, generate out own unique ID
			$return .= 'mediaid: "' . esc_js( md5( $content ) ) . '", ';
		}
		if ( ! empty( $atts['programid'] ) ) {
			$programid = $atts['programid'];
		}

		if ( ! empty( $atts['primary'] ) ) {
			$return .= "primary:'" . $atts['primary'] . "',";
		}

		if ( ! empty( $atts['autostart'] ) ) {
			if ( true === $atts['autostart'] || 'yes' === $atts['autostart'] ) {
				$return .= 'autostart:true,';
			} else {
				$return .= 'autostart:false,';
			}
		}

		if ( ! empty( $atts['mute'] ) ) {
			if ( true === $atts['mute'] || 'yes' === $atts['mute'] ) {
				$return .= 'mute:true,';
			} else {
				$return .= 'mute:false,';
			}
		}

		$return .= $this->_render_vast_tag( $atts['vast'] );
		if ( ! empty( $atts['playlist'] ) ) {
			$return .= "playlist: '" . esc_js( $atts['playlist'] ) . "'"; // last object property, no trailing comma
		} else {
			$return .= "file: '" . esc_js( $content ) . "'"; // last object property, no trailing comma
		}
		$return .= '});</script>';

		return apply_filters( 'pmc_shortcode_flv', $return, $origatts, $content );
	}

	private function _render_vast_tag( $tag = "" ) {

		if ( empty( $tag ) && ! empty( $this->_settings['vast']['tag'] ) ) {
			$tag = $this->_settings['vast']['tag'];
		}

		if ( empty( $tag ) ) {
			return "";
		}

		// Need to use esc_url_raw, since in ads esc_js converts & to &amp; it breaks
		// And esc_url converts & to encoded value which also breaks ads.
		// htmlspecialchars_decode is needed since atts thats passed as part of shortcode are already encoded
		// Using ##sqs & ##sqe replacer since esc_url_raw will remove it.
		$tag = esc_url_raw( htmlspecialchars_decode( $tag ) );
		$tag = str_replace( '##sqs', '[', $tag );
		$tag = str_replace( '##sqe', ']', $tag );
		return "advertising: {
			client: 'vast',
			tag: '{$tag}' }, ";

	}

	/**
	 *  Renders inline swf tags for feeds in which javascript
	 * is not allowed.
	 */
	public function render_inline_object_tag( $embed, $objectid ) {
		require_once( __DIR__ . '/class-pmc-swfobject.php' );
		$swf_obj = new PMC_Swfobjects( htmlspecialchars( $embed['url'] ), $embed['width'], $embed['height'], '', $objectid );

		$vvqflashvars = array();
		$vvqparams = array( 'wmode' => "transparent",
							'allowfullscreen' => "true",
							'allowscriptaccess' => "always",
							'allownetworking' => "all" );

		$vvqattributes = array();

		if ( empty( $embed['flashvars'] ) || ! is_array( $embed['flashvars'] ) ) {
			$swf_obj->set_variables( $vvqflashvars );
		} else {
			$embed['flashvars'] = array_merge( array( 'wmode' => 'opaque',
													'allowfullscreen' => 'true',
													'allowscriptaccess' => 'always',
													'allownetworking' => 'all' ), $embed['flashvars'] );
			$swf_obj->set_variables( $embed['flashvars'] );
		}
		$swf_obj->set_attributes( $vvqattributes );
		$swf_obj->set_params( $vvqparams );
		return $swf_obj->get_content_to_render();
	}

	/**
	 * Output default styling
	 * @since 1.0
	 * @version 7.0 2013-03-09 Gabriel Koen
	 */
	public function Head() {
		echo "\n" . '<style type="text/css">
.vvqbox {
	visibility: visible !important;
	display: block;
	clear: both;
	width: 100%;
}
.vvqbox div {
	margin: 10px auto;
}
.vvqbox img {
	max-width: 100%;
	height: 100%;
}
.vvqbox object {
	max-width: 100%;
}
</style>' . "\n";
	}

	/**
	 * Ensure the domain is what we expect
	 *
	 * @param string|array $whitelisted_domains
	 * @param string $url
	 * @return bool $is_valid
	 */
	public function is_valid_domain( $whitelisted_domains, $url ) {
		$whitelisted_domains = (array) $whitelisted_domains;

		$domain = wp_parse_url( $url, PHP_URL_HOST );

		// Check if we match the domain exactly
		if ( in_array( $domain, $whitelisted_domains, true ) )
			return true;

		$valid = false;

		foreach ( $whitelisted_domains as $whitelisted_domain ) {
			$whitelisted_domain = '.' . $whitelisted_domain; // Prevent things like 'evilsitetime.com'
			if ( strpos( $domain, $whitelisted_domain ) === ( strlen( $domain ) - strlen( $whitelisted_domain ) ) ) {
				$valid = true;
				break;
			}
		}

		return $valid;
	}

	/**
	 * Collection of functions to clean a URL passed through a shortcode attribute.
	 *
	 * @param string $url
	 * @return string $cleaned_url
	 */
	public function clean_shortcode_url( $url ) {
		$url = htmlspecialchars_decode( $url );

		return $url;
	}

	/**
	 * Minor processing of query param names to address things like square brackets and colons.
	 *
	 * @param string $url
	 * @return string $cleaned_url
	 */
	public function esc_query_arg_name( $param ) {
		$param = str_replace(
			array( '[',   ']',   ':',   ),
			array( '%5B', '%5D', '%3A', ),
			$param
		);

		return $param;
	}

	/**
	 * Basic checks to ensure that a number (usually height/width) is numeric, falling back to the default if not
	 *
	 */
	public function set_int( $num, $default ) {
		if ( ! is_scalar( $num ) ) {
			$num = $default;
		}

		$num = absint( $num );

		if ( empty( $num ) ) {
			$num = $default;
		}

		return $num;
	}

	/**
	 * Detect theplatform.com videos.
	 *
	 * @param string $src
	 * @param string $atts shortcode attributes
	 * @param string $content shortcode content
	 * @return bool true|false video is handled by shortcode_theplatform or not
	 */
	public function is_theplatform_video( $src, $atts = array(), $content = null ) {
		$domain = strtolower( wp_parse_url( $src, PHP_URL_HOST ) );

		return 'player.theplatform.com' === $domain;
	}

	/**
	 * Legacy CBS video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_cbs_legacy( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 480,
			'height'  => 270,
			'src'     => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'www.cbs.com', $src ) ) {
			return $content;
		}

		$instance++;
		$embed = '<object id="' . esc_attr( 'legacy-cbs-video-widget-' . $instance ) . '" class="pmc-video-widget legacy-cbs-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '">';
		$embed .= '<param name="movie" value="' . esc_url( $src ) . '" /></param>';
		$embed .= '<param name="allowFullScreen" value="true"></param>';
		$embed .= '<param name="allowScriptAccess" value="always"></param>';
		$embed .= '<embed width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" allowFullScreen="true" allowScriptAccess="always" type="application/x-shockwave-flash"></embed>';
		$embed .= '</object>';

		return $embed . $content;
	}

	/**
	 * CBS video shortcode (2014+)
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_cbs( $atts, $content = null ) {
		static $instance = 0;
		$use_passed_src = false;

		// Legacy CBS shortcode -- don't apply defaults here, to preserve our legacy height/width
		if ( ! empty( $atts['src'] ) && $this->is_valid_domain( 'www.cbs.com', $atts['src'] ) ) {
			return $this->shortcode_cbs_legacy( $atts, $content );
		}

		$default_atts = array(
			'width'      => 400,
			'height'     => 380,
			'id'         => '',
			'src'        => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src  = $this->clean_shortcode_url( $atts['src'] );
		$id  = $this->clean_shortcode_url( $atts['id'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $id ) && ! empty( $src ) && $this->is_valid_domain( 'can.cbs.com', $src ) ) {
			$use_passed_src = true;
		}
		if ( empty( $id ) && ! $use_passed_src ) {
			return $content;
		}

		if ( ! $use_passed_src ) {
			$src = 'http://can.cbs.com/thunder/player/chrome/canplayer.swf?pid=' . $id . '&partner=cbs&gen=1';
		}

		$instance++;
		$embed = '<object id="' . esc_attr( 'cbs-video-widget-' . $instance ) . '" class="pmc-video-widget cbs-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '">';
		$embed .= '<param name="movie" value="' . esc_url( $src ) . '">';
		$embed .= '<param name="allowFullScreen" value="true">';
		$embed .= '<param name="allowScriptAccess" value="always">';
		$embed .= '<embed width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" allowFullScreen="true" allowScriptAccess="always" type="application/x-shockwave-flash"></embed>';
		$embed .= '</object>';

		return $embed . $content;
	}


	/**
	 * NBC video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_nbc( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 512,
			'height'  => 347,
			'src'     => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		// Handle new (2014+?) NBC links with theplatform.com shortcode
		if ( $this->is_theplatform_video( $src, $atts, $content ) ) {
			return $this->shortcode_theplatform( $atts, $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'www.nbc.com', $src ) ) {
			return $content;
		}

		$instance++;
		$embed = '<iframe id="' . esc_attr( 'nbc-video-widget-' . $instance ) . '" class="pmc-video-widget nbc-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" frameborder="0"></iframe>';

		return $embed . $content;
	}

	/**
	 * Funny or Die video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_funnyordie( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'    => 512,
			'height'   => 347,
			'videoid'  => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$videoid = $atts['videoid'];

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $videoid ) ) {
			return $content;
		}

		$instance++;
		$embed = '<iframe src="' . esc_url( 'http://www.funnyordie.com/embed/' . $videoid ) . '" width="' . absint( $width ) . '" height="' . absint( $height ) . '" frameborder="0"></iframe>' ;

		return $embed . $content;

	}


	/**
	 * USA video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_usa( $atts, $content = null ) {
		$default_atts = array(
			'width'    => 512,
			'height'   => 347,
			'videoid'  => '', // Legacy videos, does not appear to be in use any more
			'src'      => '', // Newer (2014+?) videos are hosted on theplatform.com
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$videoid = $atts['videoid'];
		$src = $atts['src'];

		$embed = '';

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		// Handle new (2014+?) USA embeds with theplatform.com shortcode
		if ( $this->is_theplatform_video( $src, $atts, $content ) ) {
			return $this->shortcode_theplatform( $atts, $content );
		}

		// Supreme edge case -- someone tosses a theplatform.com link into the videoid field.
		if ( $this->is_theplatform_video( $videoid, $atts, $content ) ) {
			$atts['src'] = $videoid;
			return $this->shortcode_theplatform( $atts, $content );
		}

		if ( empty( $src ) && empty( $videoid ) ) {
			return $content;
		}

		// Legacy USA embeds no longer works
		if ( empty( $src ) && ! empty( $videoid ) ) {
			$embed = '<em>This video is no longer available.</em>';

			if ( is_user_logged_in() ) {
				$embed .= '<br /><br /><strong>Notice:</strong> <em>The [usa] shortcode is obsolete, and no longer works with a <strong>videoid</strong> parameter. (This message is visible to logged in users only)</em>';
			}
			 $embed = '<p>' . $embed . '</p>';

		}

		return $embed . $content;

	}


	/**
	 * Starz video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_starz( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 397,
			'height'  => 298,
			'src'     => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'www.starz.com', $src ) ) {
			return $content;
		}

		$instance++;
		$embed = '<iframe id="' . esc_attr( 'starz-video-widget-' . $instance ) . '" class="pmc-video-widget starz-video-widget"width="' . absint( $width ) . '" height="' . absint( $height ) . '" frameborder="0" src="' . esc_url( $src ) . '" ></iframe>';

		return $embed . $content;
	}

	/**
	 * Brightcove video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_brightcove( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'      => 326,
			'height'     => 292,
			'src'        => '',
			'flashvars'  => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );
		$flashvars = $atts['flashvars'];

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'brightcove.com', $src ) ) {
			return $content;
		}

		$instance++;
		$embed = '<embed id="' . esc_attr( 'brightcove-video-widget-' . $instance ) . '" class="pmc-video-widget brightcove-video-widget"src="' . esc_url( $src ) . '" bgcolor="#FFFFFF" flashVars="' . esc_attr( $flashvars ) . '" base="http://admin.brightcove.com" name="flashObj" width="' . absint( $width ) . '" height="' . absint( $height ) . '" seamlesstabbing="false" type="application/x-shockwave-flash" swLiveConnect="true" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></embed>';

		return $embed . $content;
	}

	/**
	 * theplatform.com video shortcode - used by NBC, NBCU, USA
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_theplatform( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 512,
			'height'  => 347,
			'src'     => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );

		// Force embed urls to be https when served on from a secure page
		if ( is_ssl() ) {
			$src = str_replace( 'http://', 'https://', $src );
		}

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		// URL Pattern: http?://player\.theplatform\.com/.*/select/([\w_\d-]+)
		if ( empty( $src ) || ! $this->is_valid_domain( 'player.theplatform.com', $src ) ) {
			return $content;
		}

		$query_args = array(
			'autoPlay'  => 'false',
		);

		$src = add_query_arg( $query_args, $src );

		$instance++;
		$embed = '<iframe id="' . esc_attr( 'theplatform-video-widget-' . $instance ) . '" class="pmc-video-widget theplatform-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" frameborder="0"></iframe>';

		return $embed . $content;
	}

	/**
	 * abc.go.com video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_abc( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 644,
			'height'  => 362,
			'src'     => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'abc.go.com', $src ) ) {
			return $content;
		}

		$instance++;
		$embed = '<iframe id="' . esc_attr( 'abc-video-widget-' . $instance ) . '" class="pmc-video-widget abc-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" frameborder="0"></iframe>';

		return $embed . $content;
	}

	/**
	 * cnbc.com video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_cnbc( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'      => 400,
			'height'     => 380,
			'id'         => '',
			'starttime'  => 0,
			'endtime'    => 0,
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$id  = $this->clean_shortcode_url( $atts['id'] );
		$starttime = $this->clean_shortcode_url( $atts['starttime'] );
		$endtime = $this->clean_shortcode_url( $atts['endtime'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $id ) ) {
			return $content;
		}
		$src = 'http://plus.cnbc.com/rssvideosearch/action/player/id/' . $id . '/code/cnbcplayershare';

		$instance++;
		$embed = '<object id="' . esc_attr( 'cnbc-video-widget-' . $instance ) . '" class="pmc-video-widget cnbc-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" >';
		$embed .= '<param name="type" value="application/x-shockwave-flash"/>';
		$embed .= '<param name="allowfullscreen" value="true"/>';
		$embed .= '<param name="allowscriptaccess" value="always"/>';
		$embed .= '<param name="quality" value="best"/>';
		$embed .= '<param name="scale" value="noscale" /> <param name="wmode" value="transparent"/> <param name="bgcolor" value="#000000"/> <param name="salign" value="lt"/>';
		$embed .= '<param name="flashVars" value="' . esc_attr( 'startTime=' . $starttime ) . '"/>';
		$embed .= '<param name="flashVars" value="' . esc_attr( 'endTime=' . $endtime ) . '"/>';
		$embed .= '<param name="movie" value="' . esc_url( $src ) . '" />';
		$embed .= '<embed name="cnbcplayer" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer" allowfullscreen="true" allowscriptaccess="always" bgcolor="#000000" width="' . absint( $width ) . '" height="' . absint( $height ) . '" quality="best" wmode="transparent" scale="noscale" salign="lt" src="' . esc_url( $src ) . '" type="application/x-shockwave-flash" />';
		$embed .= '</object>';

		return $embed . $content;
	}


	/**
	 * Hulu video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_hulu( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 512,
			'height'  => 288,
			'id'      => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$id = $atts['id'];

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $id ) ) {
			return $content;
		}

		$src = "http://www.hulu.com/embed.html?eid=" . $id;

		$instance++;
		$embed = '<iframe id="' . esc_attr( 'hulu-video-widget-' . $instance ) . '" class="pmc-video-widget hulu-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>';

		return $embed . $content;
	}

	/**
	 * comedycentral.com video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_comedycentral( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 512,
			'height'  => 288,
			'src'     => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		// URL Pattern: http://media.mtvnservices.com/embed/mgid:arc:video:thedailyshow.com:b94c1236-e15d-43b5-b30d-ecc65786daf8
		if ( empty( $src ) || ! $this->is_valid_domain( 'media.mtvnservices.com', $src ) ) {
			return $content;
		}

		$instance++;
		$embed = '<iframe id="' . esc_attr( 'comedycentral-video-widget-' . $instance ) . '" class="pmc-video-widget comedycentral-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" frameborder="0"></iframe>';

		return $embed . $content;
	}


	/**
	 * espn.com video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_espn( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 576,
			'height'  => 324,
			'src'     => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		// URL Pattern: http://player.espn.com/player.js?pcode=1kNG061cgfaoolOncv54OAO1ceO-I&width=576&height=324&externalId=espn:11102940
		if ( empty( $src ) || ! $this->is_valid_domain( 'player.espn.com', $src ) ) {
			return $content;
		}
		$height = absint( $height );
		$width = absint( $width );

		$query_args = array(
			'width'     => $width,
			'height'    => $height,
			$this->esc_query_arg_name( 'thruParam_espn-ui[autoPlay]' )  => 'false',
		);
		$src = add_query_arg( $query_args, $src );

		$instance++;
		$embed = '<script id="' . esc_attr( 'espn-video-widget-' . $instance ) . '" class="pmc-video-widget espn-video-widget" src="' .  esc_url( $src ) . '"></script>';

		return $embed . $content;
	}

	/**
	 * foxnews video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_foxnews( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 466,
			'height'  => 263,
			'src'     => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'video.foxnews.com', $src ) ) {
			return $content;
		}
		$height = absint( $height );
		$width = absint( $width );

		$query_args = array(
			'width'     => $width,
			'height'    => $height,
		);
		$src = add_query_arg( $query_args, $src );

		$instance++;
		$embed = '<script id="' . esc_attr( 'foxnews-video-widget-' . $instance ) . '" class="pmc-video-widget foxnews-video-widget" src="' .  esc_url( $src ) . '"></script>';

		return $embed . $content;
	}

	/**
	 * abcnews.go.com video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_abcnews( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 640,
			'height'  => 360,
			'src'     => '',
			'id'      => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );
		$id = $atts['id'];

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( ! empty( $id ) ) {
			$src = 'http://abcnews.go.com/video/embed?id=' . $id;
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'abcnews.go.com', $src ) ) {
			return $content;
		}

		$instance++;
		$embed = '<iframe id="' . esc_attr( 'abcnews-video-widget-' . $instance ) . '" class="pmc-video-widget abcnews-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" frameborder="0" scrolling="no"></iframe>';

		return $embed . $content;
	}

	/**
	 * teamcoco video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_teamcoco( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'   => 640,
			'height'  => 360,
			'src'     => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'teamcoco.com', $src ) ) {
			return $content;
		}

		$instance++;
		$embed = '<iframe id="' . esc_attr( 'teamcoco-video-widget-' . $instance ) . '" class="pmc-video-widget teamcoco-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" frameborder="0" scrolling="no"></iframe>';

		return $embed . $content;
	}

	/**
	 * Bloomberg video shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 */
	public function shortcode_bloomberg( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'      => 640,
			'height'     => 430,
			'src'        => '',
		);
		$atts = shortcode_atts( $default_atts, $atts );

		$width = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src  = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'www.bloomberg.com', $src ) ) {
			return $content;
		}

		$query_args = array(
			'width'     => $width,
			'height'    => $height,
		);
		$src = add_query_arg( $query_args, $src );

		$instance++;
		$embed = '<object id="' . esc_attr( 'bloomberg-video-widget-' . $instance ) . '" class="pmc-video-widget bloomberg-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" data="' . esc_url( $src ) . '" style="overflow:hidden;"></object>';

		return $embed . $content;
	}


	/**
	 * Yahoo movies video shortcode
	 *
	 * @param array       $atts
	 * @param string|null $content
	 *
	 * @return string|null
	 */
	public function shortcode_yahoo( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'  => 624,
			'height' => 351,
			'src'    => '',
		);
		$atts         = shortcode_atts( $default_atts, $atts );

		$width  = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src    = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'yahoo.com', $src ) ) {
			return $content;
		}

		$instance ++;
		$embed = '<iframe id="' . esc_attr( 'yahoo-video-widget-' . $instance ) . '" class="pmc-video-widget yahoo-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" frameborder="0" scrolling="no"></iframe>';

		return $embed . $content;
	}

	/**
	 * pmc_iframe custom shortcode to add iframe's on post
	 *
	 * @param array       $atts
	 * @param string|null $content
	 *
	 * @return string|null
	 */
	public function shortcode_pmc_iframe( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'  => 640,
			'height' => 360,
			'src'    => '',
		);
		$atts         = shortcode_atts( $default_atts, $atts );

		$width  = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src    = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		$valid_domains = array( 'trailers.apple.com', 'www.fandango.com', 'media.mtvnservices.com' );
		if ( empty( $src ) || ! $this->is_valid_domain( $valid_domains, $src ) ) {
			return $content;
		}

		$instance ++;
		$embed = '<iframe id="' . esc_attr( 'pmc-iframe-video-widget-' . $instance ) . '" class="pmc-video-widget pmc-iframe-video-widget" width="' . absint( $width ) . '" height="' . absint( $height ) . '" src="' . esc_url( $src ) . '" frameborder="0" scrolling="no"></iframe>';

		return $embed . $content;
	}


	/**
	 * aol video shortcode
	 *
	 * @param array       $atts
	 * @param string|null $content
	 *
	 * @return string|null
	 */
	public function shortcode_aol( $atts, $content = null ) {
		static $instance = 0;

		$default_atts = array(
			'width'  => 560,
			'height' => 345,
			'src'    => '',
		);
		$atts         = shortcode_atts( $default_atts, $atts );

		$width  = $this->set_int( $atts['width'], $default_atts['width'] );
		$height = $this->set_int( $atts['height'], $default_atts['height'] );
		$src    = $this->clean_shortcode_url( $atts['src'] );

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		if ( empty( $src ) || ! $this->is_valid_domain( 'pshared.5min.com', $src ) ) {
			return $content;
		}
		$height = absint( $height );
		$width  = absint( $width );

		$query_args = array(
			'width'  => $width,
			'height' => $height,
		);
		$src        = add_query_arg( $query_args, $src );

		$instance ++;
		$embed = '<script id="' . esc_attr( 'aol-video-widget-' . $instance ) . '" class="pmc-video-widget aol-video-widget" src="' . esc_url( $src ) . '"></script>';

		return $embed . $content;
	}


	/**
	 * Ooyala video embed shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 *
	 * @since 2014-08-08 Amit Gupta
	 * @ticket PPT-3034
	 */
	public function shortcode_ooyala( $atts, $content = null ) {
		$default_atts = array(
			'width'  => 580,
			'height' => 320,
			'src' => '',
		);

		$atts = shortcode_atts( $default_atts, $atts );

		if ( empty( $atts['src'] ) ) {
			//no embed URL, bail out
			return;
		}

		$embed_code = sprintf(
			'<script height="%d" width="%d" src="%s"></script>',
			intval( $atts['height'] ),
			intval( $atts['width'] ),
			esc_url( $atts['src'] )
		);

		return $embed_code;
	}

	/**
	 * scribblelive embed shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @return string|null
	 *
	 * @since 2014-08-21 Sachin Rajput
	 * @ticket PPT-3223
	 */
	public function shortcode_scribblelive( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'src' => '',
		), $atts ) );

		if ( empty( $atts['src'] ) ) {
			//we don't have anything, bail out
			return;
		}

		//generate shortcode html embed code
		$embed_code = sprintf('<div class="scrbbl-embed" data-src="%s"></div><script>(function(d, s, id) {var js,ijs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="//embed.scribblelive.com/widgets/embed.js";ijs.parentNode.insertBefore(js, ijs);}(document, "script", "scrbbl-js"));</script>',
			esc_attr( $atts['src'] )
		);

		return $embed_code;
	}

	/**
	 * The chrome autoplay policy need to add allow=”autoplay; encrypted-media” at top iframe
	 * Ref: https://github.com/Automattic/jetpack/blob/master/modules/shortcodes/hulu.php#L157
	 *
	 * @since 2018-06-28 Jignesh Nakrani READS-1341
	 *
	 * @param $html string The embed code for the Hulu video.
	 *
	 * @return string      Modified embed code for the Hulu video.
	 */
	public function filter_hulu_video_embed_html( $html ) {

		if ( ! empty( $html ) && false !== strpos( $html, 'hulu.com/embed.html' ) ) {
			$html = str_ireplace( 'webkitAllowFullScreen', 'webkitAllowFullScreen allow="autoplay; encrypted-media"', $html );
		}

		return $html;
	}

	/**
	 * Vevo video embed shortcode
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 *
	 * @since 2018-08-08 Amit Gupta
	 */
	public function shortcode_vevo( $atts = [], $content = '' ) {

		static $instance = 0;

		$default_atts = [
			'width'           => 640,
			'height'          => 390,
			'src'             => '',
			'allowfullscreen' => 'true',
		];

		$atts            = shortcode_atts( $default_atts, $atts );
		$width           = $this->set_int( $atts['width'], $default_atts['width'] );
		$height          = $this->set_int( $atts['height'], $default_atts['height'] );
		$src             = $this->clean_shortcode_url( $atts['src'] );
		$allowfullscreen = ( 'true' === strtolower( $atts['allowfullscreen'] ) ) ? 'true' : 'false';

		if ( ! empty( $content ) ) {
			$content = wp_kses_post( $content );
		}

		/*
		 * Domain whitelist
		 * so that we load URLs of only whitelisted domains
		 */
		$domains_whitelist = [
			'vevo.com',
			'embed.vevo.com',
		];

		if (
			empty( $src ) || strpos( $src, 'https://' ) !== 0
			|| ! $this->is_valid_domain( $domains_whitelist, $src )
		) {
			return $content;
		}

		$instance ++;

		$embed = sprintf(
			'<iframe id="%s" class="pmc-video-widget pmc-vevo-video-widget" width="%d" height="%d" src="%s" frameborder="0" scrolling="no" allowfullscreen="%s"></iframe>',
			esc_attr( 'pmc-vevo-video-widget-' . $instance ),
			absint( $width ),
			absint( $height ),
			esc_url( $src ),
			esc_attr( $allowfullscreen )
		);

		return $embed . $content;

	}

}    // end class

/**
 * Load plugin and activate shortcodes
 *
 * Loading the object into a global so that other plugins can
 * interact with it as necessary
 */
$GLOBALS['pmc_video_player'] = PMC_Video_Player::get_instance();

//EOF
