<?php

/**
 * Widget for PMC Video Player
 *
 * @author Amit Gupta
 * @since  2014-08-07
 */
class PMC_Video_Player_Widget extends WP_Widget {

	const widget_id = 'pmc_video_player_widget'; //unique widget ID

	protected $_default_options = array(
		'widget_title' => '',
		'width'        => 300,
		'height'       => 250,
		'ratio'        => '',
		'image'        => '',
		'title'        => '',
		'description'  => '',
		'content'      => '',
		'vast'         => '',
		'autostart'    => 'no',
		'startmute'    => 'no',
		'primary'      => 'flash',
		'playlist'     => '',
	);


	public function __construct() {
		parent::__construct(
			self::widget_id,
			'PMC Video Player',
			array(
				'description' => '',
			)
		);
	}

	/**
	 * This function renders the form for widget configuration when the widget's
	 * instance is added to a sidebar
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->_default_options );

		// override cdn path
		$cdn_path = apply_filters( 'pmc_video_player_widget_cdn_path', false );
		if ( ! empty( $cdn_path ) ) {
			$parts = wp_parse_url( $instance['content'] );

			if ( empty( $parts['scheme'] )
					|| empty( $parts['host'] )
					|| ( apply_filters( 'pmc_video_player_widget_cdn_override', false, $instance['content'] )
						&& 'rtmp' !== $parts['scheme'] )
				) {
					if ( ! empty( $parts['path'] ) ) {
						// return the file name only for the admin input form
						$instance['content'] = basename( $parts['path'] );
					}
			}

		}

		//all ok, print out the configuration form
		PMC::render_template(
			__DIR__ . '/templates/widget-admin.php',
			array(
				'widget'   => $this,
				'player'   => $instance,
				'cdn_path' => $cdn_path,
			),
			true
		);

	}

	/**
	 * This function processes widget options to be saved
	 */
	public function update( $new, $old ) {
		$clean_values = $this->_default_options;

		if ( ! empty( $old ) && is_array( $old ) ) {
			$clean_values = wp_parse_args( $old, $clean_values );
		}

		if ( ! empty( $new['widget_title'] ) ) {
			$clean_values['widget_title'] = sanitize_text_field( $new['widget_title'] );
		}

		if ( ! empty( $new['width'] ) && intval( $new['width'] ) > 0 ) {
			$clean_values['width'] = intval( $new['width'] );
		}

		if ( ! empty( $new['height'] ) && intval( $new['height'] ) > 0 ) {
			$clean_values['height'] = intval( $new['height'] );
		} else {
			//Default aspect ratio will kick in.
			$clean_values['height'] = "";
		}

		if ( ! empty( $new['ratio'] ) && strpos( $new['ratio'], ':' ) > 0 ) {
			$clean_values['ratio'] = sanitize_text_field( $new['ratio'] );
		}

		if ( ! empty( $new['image'] ) ) {
			$clean_values['image'] = esc_url_raw( $new['image'] );
		}

		if ( ! empty( $new['title'] ) ) {
			$clean_values['title'] = sanitize_text_field( $new['title'] );
		}

		if ( ! empty( $new['description'] ) ) {
			$clean_values['description'] = sanitize_text_field( $new['description'] );
		}

		if ( ! empty( $new['content'] ) ) {
			if ( 0 === stripos( $new['content'], 'rtmp' ) ) {
				$clean_values['content'] = esc_url_raw( $new['content'], array( "rtmp" ) );
			} else {
				if ( 0 === stripos( $new['content'], 'http' ) ) {
					$clean_values['content'] = esc_url_raw( $new['content'] );
				} else {
					$clean_values['content'] = sanitize_text_field( $new['content'] );
				}
			}
		}

		if ( ! empty( $new['playlist'] ) ) {
			$clean_values['playlist'] = esc_url_raw( $new['playlist'] );
		} else {
			$clean_values['playlist'] = "";
		}

		if ( ! empty( $new['primary'] ) ) {
			$clean_values['primary'] = sanitize_text_field( $new['primary'] );
		}

		if ( ! empty( $new['vast'] ) ) {
			$new['vast']          = str_replace( '[', '##sqs', $new['vast'] );
			$new['vast']          = str_replace( ']', '##sqe', $new['vast'] );
			$clean_values['vast'] = esc_url_raw( $new['vast'] );
		} else {
			$clean_values['vast'] = "";
		}

		$clean_values['autostart'] = ( ! empty( $new['autostart'] ) ) ? 'yes' : 'no';

		$clean_values['startmute'] = ( ! empty( $new['startmute'] ) ) ? 'yes' : 'no';

		// override cdn path
		$cdn_path = apply_filters( 'pmc_video_player_widget_cdn_path', false );
		if ( ! empty( $cdn_path ) ) {
			$parts = wp_parse_url( $clean_values['content'] );

			if ( empty( $parts['scheme'] )
					|| empty( $parts['host'] )
					|| ( apply_filters( 'pmc_video_player_widget_cdn_override', false, $clean_values['content'] )
						&& 'rtmp' !== $parts['scheme'] )
				) {
					if ( ! empty( $parts['path'] ) ) {
						// override link with cdn path
						$clean_values['content'] = untrailingslashit( $cdn_path ) . '/' . basename( $parts['path'] );
					}
			}

		}

		return $clean_values;
	}

	/**
	 * This function outputs the content of the widget
	 * @since  2014-08-07 Uses FLV shortcode function to render widget.
	 */
	public function widget( $args, $instance ) {

		$render_widget = apply_filters( 'pmc_video_player_widget_render', true );

		if ( false === $render_widget ) {
			return;
		}

		// override cdn path
		$cdn_path = apply_filters( 'pmc_video_player_widget_cdn_path', false );
		if ( !empty( $cdn_path ) ) {
			$parts = wp_parse_url( $instance['content'] );

			if ( empty( $parts['scheme'] )
					|| empty( $parts['host'] )
					|| ( apply_filters( 'pmc_video_player_widget_cdn_override', false, $instance['content'] )
						&& 'rtmp' !== $parts['scheme'] )
				) {
					if ( ! empty( $parts['path'] ) ) {
						$instance['content'] = untrailingslashit( $cdn_path ) . '/' . basename( $parts['path'] );
					}
			}

		}

		echo $args['before_widget']; // WPCS: XSS ok.
		?>
		<div class="pmc-video-player-widget-wrapper">
			<?php
			$title = apply_filters( 'pmc_video_player_widget_title', $instance['widget_title'] );

			if ( $title ) {
				echo $args['before_title'] . wp_kses_post( $title ) . $args['after_title']; // WPCS: XSS ok.
			}

			$shortcode_array = array();

			if ( ! empty( $instance['width'] ) ) {
				$shortcode_array['width'] = intval( $instance['width'] );
			}

			if ( ! empty( $instance['height'] ) ) {
				$shortcode_array['height'] = intval( $instance['height'] );
			}

			if ( ! empty( $instance['ratio'] ) ) {
				$shortcode_array['ratio'] = sanitize_text_field( $instance['ratio'] );
			}

			if ( ! empty( $instance['image'] ) ) {
				$shortcode_array['image'] = esc_url_raw( $instance['image'] );
			}

			if ( ! empty( $instance['title'] ) ) {
				$shortcode_array['title'] = sanitize_text_field( $instance['title'] );
			}

			if ( ! empty( $instance['description'] ) ) {
				$shortcode_array['description'] = sanitize_text_field( $instance['description'] );
			}

			if ( ! empty( $instance['primary'] ) ) {
				$shortcode_array['primary'] = sanitize_text_field( $instance['primary'] );
			}

			$content = "";
			if ( ! empty( $instance['content'] ) ) {
				if ( 0 === stripos( $instance['content'], "rtmp" ) ) {
					$content = esc_url_raw( $instance['content'], array( "rtmp" ) );
				} else {
					$content = esc_url_raw( $instance['content'] );
				}
			}

			if ( ! empty( $instance['playlist'] ) ) {
				$shortcode_array['playlist'] = esc_url_raw( $instance['playlist'] );
			}

			if ( ! empty( $instance['vast'] ) ) {

				$request_url             = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
				$current_url             = esc_url( esc_url( home_url() . $request_url ) );
				$instance['vast']        = str_replace( '[', '##sqs', $instance['vast'] );
				$instance['vast']        = str_replace( ']', '##sqe', $instance['vast'] );
				$shortcode_array['vast'] = esc_url_raw( $instance['vast'] );
				$shortcode_array['vast'] = str_replace( '##sqscontent_page_url##sqe', $current_url, $shortcode_array['vast'] );
			}

			if ( 'yes' === $instance['autostart'] ) {
				$shortcode_array['autostart'] = true;
			} elseif ( 'no' === $instance['autostart'] ) {
				$shortcode_array['autostart'] = false;
			}

			$id = PMC_Video_Player::get_instance()->videoid( pathinfo( $content, PATHINFO_EXTENSION ) );
			$shortcode_array['objectid'] = $id;

			if ( 'yes' === $instance['startmute'] ) :
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					try {
						var playerInstance = jwplayer("<?php echo esc_attr( $id ); ?>");

						if ( 'object' === typeof playerInstance ) {
							if ( 'function' === typeof playerInstance.setVolume ) {
								jQuery(".pmc-video-player-widget .vvqbox").hover(function () {
									playerInstance.setVolume(100);
								});

								playerInstance.setVolume(0);

								playerInstance.on('ready', function () {
										playerInstance.setVolume(0);
									}
								);
							}

							if ( 'function' === typeof playerInstance.getVolume ) {
								playerInstance.on('volume', function (event) {
									if (0 != playerInstance.getVolume()) {
										jQuery(".pmc-video-player-widget .vvqbox").off();
									}
								});
							}

						}


					} catch (e) {

					}
				});
			</script>
			<?php endif; ?>
			<div class="pmc-video-player-widget">
				<?php
				//This already returns escaped html. Is used for rendering FLV shortcode.
				echo PMC_Video_Player::get_instance()->shortcode_flv( $shortcode_array, $content ); // WPCS: XSS ok.
				?>
			</div>
		</div>
		<?php
		echo $args['after_widget']; // WPCS: XSS ok.

	}

} //end of class


/**
 * Setup widget for initialization
 */
add_action(
	'widgets_init', function () {
		register_widget( 'PMC_Video_Player_Widget' );
	}
);


//EOF
