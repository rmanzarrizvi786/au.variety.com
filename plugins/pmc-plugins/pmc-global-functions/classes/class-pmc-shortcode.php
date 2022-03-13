<?php
/**
 * Class containing all the globally available PMC shortcodes
 */

class PMC_Shortcode {

	private function __construct() {}

	/**
	 * Conditional method to determine if shortcode should be rendered on current page or not.
	 *
	 * @return boolean Returns TRUE if shortcode should be rendered else FALSE
	 */
	protected static function _should_render_shortcode() {

		if ( ! function_exists( 'is_amp_endpoint' ) || ! is_amp_endpoint() ) {
			return true;
		}

		return false;

	}

	/**
	 * Register all shortcodes here
	 */
	public static function load() {

		add_shortcode( 'pmc_twitter_followme', [ 'PMC_Shortcode', 'twitter_followme' ] );
		add_shortcode( 'pmc_facebook_likebox', [ 'PMC_Shortcode', 'facebook_like_box' ] );
		add_shortcode( 'pmc-ndn', [ 'PMC_Shortcode', 'pmc_ndn' ] );
		add_shortcode( 'pmc_onescreen', [ __CLASS__, 'pmc_onescreen' ] );
		add_shortcode( 'pmc_qzzr', [ 'PMC_Shortcode', 'pmc_qzrr' ] );
		add_shortcode( 'pmc_local_measure', [ 'PMC_Shortcode', 'pmc_local_measure' ] );
		add_shortcode( 'pmc_boombox', [ 'PMC_Shortcode', 'pmc_create_boombox_embed_script' ] );
		add_shortcode( 'pmc_lively_fundraising', [ 'PMC_Shortcode', 'pmc_lively_fundraising' ] );

		add_filter( 'wpcom_protected_embed_html', [ __CLASS__, 'maybe_remove_video_microdata' ] );
	}

	/**
	 * Render script for twitter follow me.
	 */
	public static function print_footer_script_twitter_followme() {
?>
		<script type="text/javascript" charset="utf-8">

			window.twttr = (function (d,s,id) {
						var t, js, fjs = d.getElementsByTagName(s)[0];
						if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;
						js.src="//platform.twitter.com/widgets.js"; fjs.parentNode.insertBefore(js, fjs);
						return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });
					  }(document, "script", "twitter-wjs"));
		</script>
<?php
	}

	/**
	 * "Follow me on Twitter" shortcode.
	 * IMPORTANT: Requires http://platform.twitter.com/widgets.js which should
	 * be enqueued in the theme's functions.php
	 *
	 * @since 2012-03-20 Gabriel Koen
	 * @version 2012-03-20 Gabriel Koen
	 */
	public static function twitter_followme( $atts ) {
		extract( shortcode_atts( array(
			'username' => 'TVLineNews',
			'eventracking' => '',
		), $atts ) );
		add_action( 'wp_print_footer_scripts', array( 'PMC_Shortcode', 'print_footer_script_twitter_followme' ) );
		// We're just replacing invalid characters with nothing, so it could lead to invalid usernames
		$username = preg_replace( '/([^a-zA-Z0-9-_])/', '', $username );

		$html= '<span class="pmc_twitter_followme'.esc_attr($eventracking).'"><a href="'.esc_url( 'https://twitter.com/'.$username).'" class="twitter-follow-button" data-show-count="true" data-size="large">Follow @'.esc_html($username).'</a><span>';

		if( !empty( $eventracking ) ){
				$html = $html. "
				<script>jQuery(document).ready(function() {
					var pmc_twitter_followme = false;

					function pmc_track_twitter(intent_event) {
						if (intent_event) {
							if( intent_event.data.screen_name == '".esc_js($username)."'){
								_gaq.push(['_trackEvent','".esc_js( $eventracking )."', 'twitter', 'click', 0, true ]);
							}
						}
					}
					twttr.ready(function (twttr) {
						twttr.events.bind('follow', pmc_track_twitter);
						twttr.events.bind('retweet', pmc_track_twitter);
					});
				  });</script>";
		}

		return $html;
	}

	/**
	 * render out the facebook like box iframe for tvline_facebook_like_box shortcode
	 * @param $atts
	 * @param null $content
	 * @return string
	 */
	public static function facebook_like_box( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'href'         => 'https://www.facebook.com/Movieline',
			'width'        => '625',
			'height'       => '258',
			'show_faces'   => 'true',
			'border_color' => '#ffffff',
			'stream'       => 'false',
			'header'       => 'false',
			'eventracking' => '',
			'appid'        => '210702705726465',
		), $atts ) );

		$url_parts = parse_url( $href );

		$url_host = isset( $url_parts['host'] ) ? $url_parts['host'] : '';
		if($url_host != "www.facebook.com"){
			return  wp_kses_post( $content );
		}

		$appid = (int)$appid;

	ob_start();

?>
		<div id="fb-root"></div>
		<script type="text/javascript">
			(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		</script>
	<?php

			if( !empty( $eventracking ) ) {
	?>
		<script type='text/javascript'>

			jQuery(document).ready(function() {

			window.fbAsyncInit = function() {
				FB.init({
				  appId      : '<?php echo esc_js($appid); ?>',
				  channelUrl : window.location.protocol + '//' + window.location.hostname + '/wp-content/themes/vip/pmc-plugins/partner/facebook/channel.html',
				  status     : true,
				  cookie     : true,
				  xfbml      : true,
				  oauth		 : true
				});

				FB.Event.subscribe('edge.create', function(href){
					var original_href  = '<?php echo esc_url( $href );?>';

					if( href == original_href){
						_gaq.push(['_trackEvent','<?php echo esc_js( $eventracking );?>', 'facebook', 'click', 0, true]);
					}
				});

				FB.Event.subscribe('edge.remove', function(href) {
					var original_href  = '<?php echo esc_url( $href );?>';

					if( href == original_href){
						_gaq.push(['_trackEvent','<?php echo esc_js( $eventracking );?>', 'facebook', 'click', 0, true]);
					}
				});
			};
		});
		</script>
			<?php
		} // end if

		?>
			<div class='pmc-fb-like-box<?php echo esc_attr( $eventracking ); ?>'>
				<div class='fb-like-box' data-href='<?php echo esc_attr( $href ); ?>' data-width='<?php echo esc_attr( $width ); ?>' data-height='<?php echo esc_attr( $height ); ?>' data-show-faces='<?php echo esc_attr( $show_faces ); ?>' data-border-color='<?php echo esc_attr( $border_color ); ?>' data-stream='<?php echo esc_attr( $stream ); ?>' data-header='<?php echo esc_attr( $header ); ?>'></div>
			</div>
		<?php

		$facebook_like_box = ob_get_clean();

		if ( ! is_null( $content ) ) {
			$content = wp_kses_post( $content );
		}

		return $facebook_like_box . $content;

	}

	public static function pmc_ndn( $atts, $content = null ) {

		if ( ! static::_should_render_shortcode() ) {
			return '';
		}

		$atts = apply_filters( 'pmc-ndn-defaults', $atts );

		if ( ! isset( $atts['videoid'] ) || $atts['videoid'] == '' ) {
			return $content;
		} else {
			$videoid = $atts['videoid'];
		}

		$height        = '';
		$width         = '';
		$trackinggroup = '';
		$sitesection   = '';
		$playlistid    = '';
		$placementid   = '';


		if ( isset( $atts['class'] ) ) {
			$class = 'ndn_embed ' . $atts['class'];
		} else {
			$class = 'ndn_embed';
		}

		if ( isset( $atts['sitesection'] ) ) {
			$sitesection = $atts['sitesection'];
		}
		if ( isset( $atts['trackinggroup'] ) ) {
			$trackinggroup = $atts['trackinggroup'];
		}
		if ( isset( $atts['height'] ) ) {
			$height = $atts['height'];
		}
		if ( isset( $atts['width'] ) ) {
			$width = $atts['width'];
		}
		if ( isset( $atts['playlistid'] ) ) {
			$playlistid = $atts['playlistid'];
		}
		if ( isset( $atts['placementid'] ) ) {
			$placementid = $atts['placementid'];
		}

		$html = PMC::render_template( sprintf( '%s/templates/shortcodes/pmc-ndn.php', untrailingslashit( PMC_GLOBAL_FUNCTIONS_PATH ) ), array(

			'placementid'   => $placementid,
			'class'         => $class,
			'playlistid'    => $playlistid,
			'trackinggroup' => $trackinggroup,
			'sitesection'   => $sitesection,
			'width'         => $width,
			'height'        => $height,
			'videoid'       => $videoid,

		) );

		return $html . $content;

	}

	/**
	 * Parser for [pmc_onescreen] shortcode
	 *
	 * @see OneScreen JS API http://share.adaptivem.com/resources/HelpDocuments/appdoc/configuration-properties.html
	 * @ticket PPT-3816
	 * @since 2014-12-17 Amit Gupta
	 */
	public static function pmc_onescreen( $atts = array() ) {
		/*
		 * Actual config keys mapped to shortcode attributes. The keys are not
		 * used directly as shortcode attributes as they're in camelcase. Shortcode
		 * attributes are not case sensitive while JS object keys are. This way
		 * there's no risk of error if someone enters wrong case for an attribute.
		 *
		 * IMPORTANT: The order of keys in this array must be same as in default
		 * attributes array below in order for swap to work.
		 */
		$config_keys = array(
			'id'                => 'playlist_id',
			'item'              => 'videoStartId',
			'auto_play'         => 'autoPlay',
			'start_index'       => 'startIndex',
			'height'            => 'height',
			'width'             => 'width',
			'player_fill'       => 'playerFill',
			'ads'               => 'ads',
			'force_companions'  => 'forceCompanions',
			'companion_target'  => 'companionTargets',
			'playback_priority' => 'playbackPriority',
			'force_html5'       => 'forceHTML5',
			'show_thumbs'       => 'showThumbs',
		);

		$atts = shortcode_atts( array(
			'playlist_id'       => '',
			'item'              => 'false',
			'auto_play'         => 'false',
			'start_index'       => 1,
			'height'            => 250,
			'width'             => 300,
			'player_fill'       => 'both',
			'ads'               => '',
			'force_companions'  => 'true',
			'companion_target'  => 'sample_div_id',
			'playback_priority' => 'html5,flash',
			'force_html5'       => 'true',
			'show_thumbs'       => 'false',
			'target_div'        => 'pmc_onescreen_player',
			'widget_id'         => '',
		), (array) $atts );

		//set target Div in its own var and remove from the attribute array
		$target_div = $atts['target_div'];

		$app_id = apply_filters( 'pmc_onescreen_app_id', '' );

		if ( ! empty( $atts['widget_id'] ) && is_string( $atts['widget_id'] ) ) {
			$app_id = $atts['widget_id'];
		}

		unset( $atts['target_div'], $atts['widget_id'] );

		if ( empty( $app_id ) || ! is_string( $app_id ) ) {
			return;
		}

		/*
		 * swap shortcode attributes with corresponding config keys
		 * and weed out empty keys
		 */
		$config = array_filter( array_combine( $config_keys, $atts ) );

		if ( isset( $config['companionTargets'] ) ) {
			//companionTargets property expects an array of objects, so lets provide one
			$config['companionTargets'] = sprintf( "[%s]", json_encode( array( 'id' => $config['companionTargets'] ) ) );
		}

		return PMC::render_template( dirname( __DIR__ ) . '/templates/shortcodes/pmc-onescreen-ui.php', array(
			'app_id'     => $app_id,
			'target_div' => $target_div,
			'config'     => $config,
		) );
	}	//end pmc_onescreen()

	/**
	 * [pmc_qzzr quiz="42746"] will default to:
	 * [pmc_qzzr quiz="42746" width="100%" height="auto" redirect="true"]
	 * @static
	 * @param $args
	 * @param null $content
	 * @return null|string
	 * @since 03-02-2015 Adaeze Esiobu
	 */
	public static function pmc_qzrr( $args, $content = null ){
		if( empty( $args['quiz'] ) ){
			return $content;
		}else{
			$quizz = $args['quiz'];
		}

		if( empty( $args['width'] ) ){
			$width = '100%';
		}else{
			$width = $args['width'];
		}

		if( empty( $args['height'] ) ){
			$height = 'auto';
		}else{
			$height = $args['height'];
		}

		if(empty( $args['redirect'] ) ){
			$auto_redirect = 'true';
		}else{
			$auto_redirect = $args['redirect'];
		}

		$shortcode_content = '<div class="quizz-container" data-width="'.esc_attr( $width ).'" data-height="'.esc_attr( $height ).'" data-auto-redirect="'.esc_attr( $auto_redirect ).'" data-quiz="'.esc_attr( $quizz );
		$shortcode_content .= '"></div><script src="//dcc4iyjchzom0.cloudfront.net/widget/loader.js" async></script>';
		return $shortcode_content. $content;
	}


	/**
	 * [pmc_local_measure id=".." lmwidget=".."]
	 * @static
	 * @param array $atts
	 * @param null $content
	 * @return null|string
	 * @since 2015-03-02 Adaeze Esiobu
	 */
	public static function  pmc_local_measure( $atts = array() ,$content = null  ){

		if( empty( $atts['id'] ) || empty( $atts['lmwidget'] ) ){
			return $content;
		}

		$id = $atts['id'];
		$lmwidget = $atts['lmwidget'];

		$shortcode_content  = '<script src="https://cdn.getlocalmeasure.com/embed/widgets.js" type="text/javascript"></script>';
		$shortcode_content .= '<div id="'.esc_attr( $id ).'" data-lmwidget="'.esc_attr( $lmwidget ).'"></div>';

		return $shortcode_content . $content;
	}

	/*
	 * Boombox WP shortcode Plugin
	 *
	 * Example Usage:
	 * [pmc_boombox widget="poll" id="123" width="100%" height="auto"]
	 *
	 * @since 2015-09-22
	 * @version 2015-09-22 Archana Mandhare PMCVIP-138
	 *
	 * @param array $atts, string $content
	 *
	 */
	public static function pmc_create_boombox_embed_script( $atts, $content = null ) {

		wp_enqueue_style( 'pmc-boombox-css', pmc_global_functions_url( '/css/pmc-boombox.css' ) );

		$atts = shortcode_atts( array(
			'widget' => '',
			'id'     => '',
			'height' => 'auto',
			'width'  => '100%',
			'offset' => ''
		), (array) $atts );

		if ( empty( $atts['widget'] ) ) {

			$error = "<div class='boombox_wrapper'><h3>Uh oh!</h3><span>Something is wrong with your Boombox shortcode. If you copy and paste it from the Boombox share screen, you should be good.</span></div>";

			return $error;

		} else if ( 'quiz' === $atts['widget'] ) {

			wp_enqueue_script( 'pmc-boombox-quiz-script', '//dcc4iyjchzom0.cloudfront.net/widget/loader.js', array(), false, true );

			$boomboxHook = '<div class="quizz-container" data-quiz="' . esc_attr( $atts['id'] ) . '" data-width="' . esc_attr( $atts['width'] ) . '" data-height="' . esc_attr( $atts['height'] ) . '" ';

			if ( $atts['offset'] ) {
				$boomboxHook .= ' data-offset="' . esc_attr( $atts['offset'] ) . '"';
			}

			$boomboxHook .= '></div>';

			return $boomboxHook;

		} else {

			wp_enqueue_script( 'pmc-boombox-script', '//d6launbk5pe1s.cloudfront.net/widget.js', array(), false, true );

			$boomboxHook = '<div class="mv-widget" data-widget="' . esc_attr( $atts['widget'] ) . '" data-id="' . esc_attr( $atts['id'] ) . '" data-width="' . esc_attr( $atts['width'] ) . '" data-height="' . esc_attr( $atts['height'] ) . '" ></div>';

			return $boomboxHook;

		}
	}

	/**
	 * ROP-2123 Give Lively fundraising shortcode
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function pmc_lively_fundraising( $atts = [] ): string {

		wp_enqueue_script(
			'pmc-lively-script',
			'https://secure.givelively.org/widgets/simple_donation/team-rubicon/hail-the-heroes.js?show_suggested_amount_buttons=true&show_in_honor_of=true&address_required=false&has_required_custom_question=false&suggested_donation_amounts[]=25&suggested_donation_amounts[]=50&suggested_donation_amounts[]=100&suggested_donation_amounts[]=250',
			[],
			false,
			true
		);
		$shortcode_content = '<div id="give-lively-widget" class="gl-simple-donation-widget"></div>';

		return $shortcode_content;
	}

	/**
	 * Maybe remove video microdata from the html string.
	 * When the video is embedded inside an iframe, we don't know some important metadata upfront.
	 * So instead of showing incomplete or wrong metadata, it's better not to show at all.
	 *
	 * @see https://jira.pmcdev.io/browse/PASE-413
	 *
	 * @param string $embed
	 *
	 * @return string
	 */
	public static function maybe_remove_video_microdata( $embed ) {
		if ( ! is_string( $embed ) ) {
			return $embed;
		}

		$video_source_to_block = apply_filters(
			'pmc_global_functions_shortcode_video_source_to_block',
			[
				'player.theplatform.com',
			]
		);

		$should_block = false;

		foreach ( $video_source_to_block as $platform ) {
			$should_block = false !== strpos( $embed, $platform );

			if ( $should_block ) {
				break;
			}
		}

		if ( ! $should_block ) {
			return $embed;
		}

		$opening_div = '(?:<div\sitemprop="video(object)?"\sitemscope=?("([\w]+)?")?\sitemtype="https?:\/\/schema.org\/videoobject">)'; // Select the opening div if it matches the string like `<div itemprop="video" itemscope itemtype="http://schema.org/VideoObject">`
		$iframe      = '(?<iframe>.+(?=<\/div>$))'; // Select all characters except the closing </div> and name it iframe;
		$closing_div = '(?:<\/div>)'; // Select the closing div;
		$pattern     = "/{$opening_div}{$iframe}{$closing_div}/i";

		preg_match( $pattern, $embed, $matches );

		return ! empty( $matches['iframe'] ) ? $matches['iframe'] : $embed;
	}

}    //end of class

PMC_Shortcode::load();


//EOF
