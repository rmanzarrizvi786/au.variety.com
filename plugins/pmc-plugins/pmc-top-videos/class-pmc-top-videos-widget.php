<?php
	// Register our widget
	add_action( 'widgets_init', function() {
		register_widget( "PMC_Top_Videos_widget" );
	} );

	/**
	 * PMC Top Video Widget
	 *
	 * Displays the 5 most recently added videos
	 * 	+ One large featured video (most recently added to the site)
	 *  + 3 smaller videos below (the next older videos)
	 */
	class PMC_Top_Videos_widget extends WP_Widget {
		// Object Variables
		const cache_key   = 'pmc_top_videos_widget_2';
		const cache_group = 'widget';
		const cache_ttl   = 3600; // 1 hour - widget content cache expiry

		/**
		 * Widget Constructor
		 */
		public function __construct() {
			// Instantiate the parent object
			parent::__construct( false, __( 'PMC - Top Videos','pmc-plugins' ) );
		} // __construct

		/**
		 * @param array $instance
		 *
		 * @return string|void
		 */
		public function form ( $instance ) { ?>
			<p><strong><?php esc_html_e( 'Display the most recent Top Videos','pmc-plugins' ); ?></strong></p><?php

			// Set some default values
			$widget_title         = __( 'Video Gallery', 'pmc-plugins' );
			$top_video_playlist   = '';
			$top_video_post_count = 3;

			// Override the default title with one previously saved
			if ( isset( $instance['widget_title'] ) ) {
				$widget_title = $instance['widget_title'];
			}

			// Override the default top video post count with one previously saved
			if ( isset( $instance['top_video_post_count'] ) ) {
				$top_video_post_count = $instance['top_video_post_count'];
			}

			// Override the default top video playlist with one previously saved
			if ( isset( $instance['top_video_playlist'] ) ) {
				$top_video_playlist = $instance['top_video_playlist'];
			} ?>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'widget_title' ) ); ?>"><?php esc_html_e( 'Widget Title:' ); ?></label>
				<br />
				<input id="<?php echo esc_attr( $this->get_field_id( 'widget_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widget_title' ) ); ?>" type="text" value="<?php echo esc_attr( $widget_title ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'top_video_post_count' ) ); ?>"><?php esc_html_e( 'Number of Videos to Display:' ); ?></label>
				<br />
				<select id="<?php echo esc_attr( $this->get_field_id( 'top_video_post_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'top_video_post_count' ) ); ?>">
					<option>Select One</option>
					<option value="1" <?php selected( $top_video_post_count, 1 ); ?>>1</option>
					<option value="2" <?php selected( $top_video_post_count, 2 ); ?>>2</option>
					<option value="3" <?php selected( $top_video_post_count, 3 ); ?>>3</option>
					<option value="4" <?php selected( $top_video_post_count, 4 ); ?>>4</option>
					<option value="5" <?php selected( $top_video_post_count, 5 ); ?>>5</option>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'top_video_playlist' ) ); ?>"><?php esc_html_e( 'Display videos from a specific playlist:' ); ?></label>
				<br />
				<select id="<?php echo esc_attr( $this->get_field_id( 'top_video_playlist' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'top_video_playlist' ) ); ?>">
					<option><?php esc_html_e( 'Select One' ); ?></option><?php

					$playlists = PMC_Top_Videos::get_available_channels();
					if ( count( $playlists ) > 0 ) {
						foreach ( $playlists as $playlist_slug => $playlist_name ) { ?>

					<option value="<?php echo esc_attr( $playlist_slug ); ?>" <?php selected( $top_video_playlist, $playlist_slug ); ?>>
						<?php echo esc_html_e( $playlist_name ); ?>
					</option><?php

						} // foreach
					} // if ?>

				</select>
			</p><?php

		} // form

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();

			// Store the updated widget title
			if ( ! empty( $new_instance['widget_title'] ) ) {
				$instance['widget_title'] = strip_tags( $new_instance['widget_title'] );
			} else {
				$instance['widget_title'] = '';
			}

			// Store the updated top video post count
			if ( $new_instance['top_video_post_count'] != 'Select One' ) {
				$instance['top_video_post_count'] = $new_instance['top_video_post_count'] ;
			}

			// Store the chosen video playlist
			if ( $new_instance['top_video_playlist'] != 'Select One' ) {
				$instance['top_video_playlist'] = ( ! empty( $new_instance['top_video_playlist'] ) ) ? $new_instance['top_video_playlist'] : '';
			}

			// Also clear the widget output HTML cache on update
			wp_cache_delete( self::cache_key, self::cache_group );

			// Return the new widget instance
			return $instance;
		} // update

		/**
		 * Top Video Widget
		 *
		 * This widget displays one large top video (the most recent one), and 4 small
		 * top videos below. Each is just the video featured image, and links to the
		 * single video where the user may actually watch the video
		 *
		 * @param  array $args     The Arguments passed in from the sidebar (i.e. before widget markup)
		 * @param  array $instance Any data stored in the widget, i.e. if there was an editable text field in the widget
		 * @return null
		 */
		public function widget ( $args, $instance ) {

			// Ensure $post is available for use within the scope of this function
			global $post ;

			// See if we've already cached this HTML
			// Note: We're only expecting 1 widget, otherwise we'd want to serialize the args + ID
			$html = wp_cache_get( self::cache_key, self::cache_group );

			// Is there any cached HTML we can use?
			if ( $html !== false ) {
				// Yes there is, echo the cached HTML
				echo $html;

				// Bail..
				return;
			} // fi

			// There is no cache.. proceeding as normal

			// Set a default post count
			$top_video_post_count = 3 ;

			// Override the default post count with the option saved in the widget settings
			if ( isset( $instance['top_video_post_count'] ) ) {
				if ( ! empty( $instance['top_video_post_count'] ) ) {
					$top_video_post_count = $instance['top_video_post_count'];
				}
			}

			// Set a default video playlist
			$top_video_playlist = '';

			// Override the default playlist with the option saved in the widget settings
			if ( isset( $instance['top_video_playlist'] ) ) {
				if ( ! empty( $instance['top_video_playlist'] ) ) {
					$top_video_playlist = $instance['top_video_playlist'];
				}
			}

			// Query for the posts we want to display
			$top_videos_query_arguments = array(
				'post_type'      => 'pmc_top_video',
				'post_status'    => 'publish',
				'order'          => 'DESC',
				'orderby'        => 'post_date',
				'posts_per_page' => $top_video_post_count,

				// Speed up the query, we don't need pagination
				'no_found_rows'  => true,

				// Speed up the query, we don't need a post meta query
				'update_post_meta_cache' => false
			);

			// Only attempt a tax query if a video playlist was chosen
			if ( ! empty( $top_video_playlist ) ) {
				// Filter based on the selected video playlist
				$top_videos_query_arguments['tax_query'] = array(
					array(
						'taxonomy' => 'vcategory',
						'field'    => 'slug',
						'terms'    => array( $top_video_playlist )
					)
				);
			}

			$top_videos = new WP_Query( $top_videos_query_arguments );
			$html = '';

			// Only proceed if there are video posts available
			if ( $top_videos->have_posts() ) {

				// We'll use PHP Output Buffering to make our code below easier to read/manage
				ob_start();

				// Output any preceeding widget content dictated by the sidebar
				echo $args['before_widget'];
			?>

				<section class="top-videos-widget"><?php
					// Set a default widget title
					$widget_title = __( 'Video', 'pmc-plugins' );

					// Override the default widget title with the option saved in the widget settings
					if ( isset( $instance['widget_title'] ) ) {
						if ( ! empty( $instance['widget_title'] ) ) {
							$widget_title = __( $instance['widget_title'], 'pmc-plugins' );
						}
					} ?>

					<h2><?php echo esc_html_e( $widget_title, 'pmc-plugins' ); ?></h2>

					<ul class="top-videos-list"><?php
						while ( $top_videos->have_posts() ) {
							// Populate $post with the currently looped item
							$top_videos->the_post();

							// Prepare the video's featured attachment
							$video_featured_attachment_id = get_post_thumbnail_id( $post->ID );
							$video_featured_attachment    = wp_get_attachment_image_src( $video_featured_attachment_id, 'pmc-top-videos-widget' ); ?>

						<li class="top-video col-xs-3 col-md-3">
							<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="thumb">
								<div class="top-video-image">
									<div class="icon-video"></div>
									<img src="<?php echo esc_url( $video_featured_attachment[0] ) ?>" alt="<?php echo esc_attr( get_the_title( $post->ID ) ); ?>" />
								</div>
								<div class="video-text-wrap">
									<div class="video-static-title">
										<?php echo esc_html( $this->get_vertical_name( $post ) ); ?>
									</div>
									<div class="video-title">
										<h4><?php echo esc_html_e( get_the_title( $post->ID ), 'pmc-plugins' ); ?></h4>
									</div>
									<div class="video-caption-widget"><?php echo wp_kses_post( PMC_Top_Videos::get_instance()->get_meta_data( $post->ID, "caption" ) ); ?></div>
								</div>
							</a>
						</li><?php

						} // while ?>

					</ul>

				</section><?php

				// Revert $post to what it was before our top videos loop
				wp_reset_postdata();

				// Output any post widget content dictacted by the sidebar
				echo $args['after_widget'];

				// Gather the content we printed to the screen and caught by PHP's output buffering
				$html = ob_get_contents();
				ob_end_clean();

			}

			// Set a cache of the HTML so we don't regenerate it too often
			wp_cache_set( self::cache_key, $html, self::cache_group, self::cache_ttl );

			// Output the final HTML
			echo $html;

		} // widget

		public function get_vertical_name( $post ) {

			$verticals = get_the_terms( $post, 'vertical' );

			if ( ! empty( $verticals ) && ! is_wp_error( $verticals ) ) {
				$vertical = array_shift( $verticals ); //grab first vertical

				return PMC::untexturize( $vertical->name );
			}

			return "Rough Cuts";

		}
	} // PMC_Top_Videos_widget


	//EOF