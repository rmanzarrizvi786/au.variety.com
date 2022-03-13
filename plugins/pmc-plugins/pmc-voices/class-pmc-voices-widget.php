<?php
class PMC_Voices_Widget extends WP_Widget {

	const cache_key   = '_pmc_voices';
	const cache_group = '_pmc_voices_widget';
	const cache_ttl   = 21600; // 6 hours - widget content cache expiry

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'_pmc_voices', // Base ID
			'PMC Voices', // Name
			array(
				 'description' => 'Shows Title, Picture and Latest post of one Author',
			)
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		if ( isset($instance['menu']) && $instance['menu'] == 'true' )
			$ajax = '_ajax1';
		else
			$ajax = '';

		$html = wp_cache_get( self::cache_key . $instance['author'] . $ajax, self::cache_group );

		if ( $html ) {
			echo $html;

			return;
		}

		ob_start();

		extract( $args );
		// Selected Author's ID
		$author_id = $instance['author'];

		$voices_author = PMC_Voices::get_instance()->get_guest_author_by_id( $author_id );

		// Single Loop
		if ( !empty( $voices_author ) ):

			//Last post Details
			$author_last_post           = PMC_Voices::get_instance()->guest_author_last_post( $voices_author->ID, $voices_author->user_login );
			$author_last_post_url       = '';
			$author_last_post_title     = '';
			if ( isset( $author_last_post['url'] ) ) {
				$author_last_post_url = $author_last_post['url'];
			}
			if ( isset( $author_last_post['title'] ) ) {
				$author_last_post_title = $author_last_post['title'];
			}

			//Image and Title Link to Author Page so putting in common a
			if ( $ajax == '_ajax1' ) {

				if ( has_post_thumbnail( $voices_author->ID ) ) : ?>
					<div class="thumb">
						<a href="<?php echo esc_url( get_author_posts_url( $voices_author->ID, $voices_author->user_login ) ); ?>">
							<?php
							if ( has_post_thumbnail( $voices_author->ID ) )
								echo get_the_post_thumbnail( $voices_author->ID, array(55,69) );
							else
								echo get_avatar( $voices_author->user_email, 55 );
							?>
						</a>
					</div>
				<?php endif; ?>
				<div class="overview">
					<h3>
						<a rel="author" href="<?php echo esc_url( get_author_posts_url( $voices_author->ID, $voices_author->user_login ) ); ?>">
							<?php echo esc_html( $voices_author->display_name ); ?>
						</a>
					</h3>
					<h4><a href="<?php echo esc_url( $author_last_post_url ); ?>"><?php echo esc_html( pmc_truncate( $author_last_post_title, 70 ) ); ?></a></h4>
				</div>
				<?php
			} else {
				?>
				<h3>
					<a rel="author" href="<?php echo esc_url( get_author_posts_url( $voices_author->ID, $voices_author->user_login ) ); ?>"
					   title="<?php echo esc_attr( $voices_author->display_name ); ?>">
						<?php echo $before_title . esc_html( $voices_author->display_name ) . $after_title; ?>
					</a>
				</h3>
				<a rel="author" href="<?php echo esc_url( get_author_posts_url( $voices_author->ID, $voices_author->user_login ) ); ?>"
				   title="<?php echo esc_attr( $voices_author->display_name ); ?>" rel="nofollow">
					<?php
					//Image

					if ( has_post_thumbnail( $voices_author->ID ) )
						echo get_the_post_thumbnail( $voices_author->ID, 'author-thumb' );
					else
						echo get_avatar( $voices_author->user_email, 52 );
					?>
				</a>
				<?php
				echo "<h4><a href='" . esc_url( $author_last_post_url ) . "'>";
				echo "<p>" . esc_html( pmc_truncate( $author_last_post_title, 70 ) ) . "</p>";
				echo "</a></h4>";
			}
		endif;
		wp_reset_postdata();

		echo $after_widget;

		$html = ob_get_clean();

		wp_cache_set( self::cache_key . $instance['author'] . $ajax, $html, self::cache_group, self::cache_ttl );

		echo $html;
	}

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

		$instance['author'] = intval( $new_instance['author'] );

		wp_cache_delete( self::cache_key, self::cache_group );

		return $instance;
	}

	/**
	 * Back end wiget form.
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		global $post;

		//Out put the Drop down
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'author' ) ); ?>"><?php _e( 'Author Name:' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'author' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'author' ) ); ?>">
				<?php

				//Get data
				$pmc_author_voices = PMC_Voices::get_instance()->get_guest_author_voices();

				foreach ( $pmc_author_voices as $author_voice ) {
					echo '<option value="';
					echo esc_attr( $author_voice->ID ) . '" ';

					if ( isset( $instance['author'] ) && $instance['author'] == $author_voice->ID ) {
						selected( $instance["author"], $author_voice->ID );
					}

					echo '>';
					echo esc_html( get_the_title( $author_voice->ID ) );
					echo '</option>';
				}
				?>
			</select>
		</p>
	<?php

	}

	public static function register() {
		register_widget( "PMC_Voices_Widget" );
	}

}

add_action( "widgets_init", array( 'PMC_Voices_Widget', 'register' ) );

//EOF