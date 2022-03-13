<?php
/**
 * Module Name: PMC Image Widget
 * Module Description: Easily add images to your theme's sidebar.
 */

/**
* Register the widget for use in Appearance -> Widgets
*/
add_action( 'widgets_init', 'pmc_image_widget_init' );
function pmc_image_widget_init() {
	register_widget( 'PMC_Image_Widget' );
}

class PMC_Image_Widget extends WP_Widget {

	const widget_id = 'pmc_image_widget';

	/**
	* Register widget with WordPress.
	*/
	public function __construct() {
		parent::__construct( self::widget_id, __( 'PMC Image Widget', 'pmc' ), array(
			'description' => 'Display an image in your sidebar.',
			'classname'   => 'pmc-image-widget',
		) );
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

		echo wp_kses_post( $args['before_widget'] );

		$instance = wp_parse_args( $instance, array(
			'title' => '',
			'img_url' => ''
		) );

		/** This filter is documented in core/src/wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( $title ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}

		if ( ! empty( $instance['img_url'] ) ) {

			$output = '<img src="' . esc_url( $instance['img_url'] ) .'" ';

			if ( ! empty( $instance['alt_text'] ) ) {
				$output .= 'alt="' . esc_attr( $instance['alt_text'] ) .'" ';
			}

			if ( ! empty( $instance['img_title'] ) ) {
				$output .= 'title="' . esc_attr( $instance['img_title'] ) .'" ';
			}

			if ( ! empty( $instance['caption'] ) ) {
				$output .= 'class="align' . esc_attr( $instance['align'] ) . '" ';
			}

			if ( ! empty( $instance['img_width'] ) ) {
				$output .= 'width="' . absint( $instance['img_width'] ) .'" ';
			}

			if ( ! empty( $instance['img_height'] ) ) {
				$output .= 'height="' . absint( $instance['img_height'] ) .'" ';
			}

			$output .= '/>';

			if ( ! empty( $instance['link'] ) ) {
				$target = ! empty( $instance['link_target_blank'] ) ? '_blank' : '_self';
				$output = '<a target="' . $target . '" href="' . esc_url( $instance['link'] ) . '">' . $output . '</a>';
			}

			if ( ! empty( $instance['caption'] ) ) {
				/** This filter is documented in core/src/wp-includes/default-widgets.php */
				$caption   = apply_filters( 'widget_text', $instance['caption'] );
				$img_width = ( ! empty( $instance['img_width'] ) ? 'style="width: ' . absint( $instance['img_width'] ) .'px"' : '' );
				$output    = '<figure ' . $img_width .' class="wp-caption align' .  esc_attr( $instance['align'] ) . '">
					' . $output . '
					<figcaption class="wp-caption-text">' . $caption . '</figcaption>
				</figure>';
			}
			?>
			<div class="pmc-image-widget-container"><?php echo wp_kses_post( $output ); ?></div>
			<div style="clear: both;"></div>
			<?php
		}

		echo "\n" . wp_kses_post( $args['after_widget'] );
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

		$instance = $old_instance;

		$instance['title']             = sanitize_text_field( strip_tags( $new_instance['title'] ) );
		$instance['img_url']           = esc_url_raw( $new_instance['img_url'], null, 'display' );
		$instance['alt_text']          = sanitize_text_field( strip_tags( $new_instance['alt_text'] ) );
		$instance['img_title']         = sanitize_text_field( strip_tags( $new_instance['img_title'] ) );
		$instance['caption']           = wp_kses_post( stripslashes( $new_instance['caption'] ) );
		$instance['align']             = sanitize_text_field( $new_instance['align'] );
		$instance['img_width']         = sanitize_text_field( $new_instance['img_width'] );
		$instance['img_height']        = sanitize_text_field( $new_instance['img_height'] );
		$instance['link']              = esc_url_raw( $new_instance['link'], null, 'display' );
		$instance['link_target_blank'] = ! empty( $new_instance['link_target_blank'] ) ? (bool) $new_instance['link_target_blank'] : false;

		return $instance;
	}

	/**
	* Back-end widget form.
	*
	* @see WP_Widget::form()
	*
	* @param array $instance Previously saved values from database.
	* @return void
	*/
	public function form( $instance ) {
		// Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'img_url' => '', 'alt_text' => '', 'img_title' => '', 'caption' => '', 'align' => 'none', 'img_width' => '', 'img_height' => '', 'link' => '', 'link_target_blank' => false ) );

		$title             = $instance['title'];
		$img_url           = $instance['img_url'];
		$alt_text          = $instance['alt_text'];
		$img_title         = $instance['img_title'];
		$caption           = $instance['caption'];
		$align             = $instance['align'];
		$img_width         = $instance['img_width'];
		$img_height        = $instance['img_height'];
		$link_target_blank = $instance['link_target_blank'];
		$link              = $instance['link'];
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Widget title:', 'pmc' ); ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'img_url' ) ); ?>">
				<?php esc_html_e( 'Image URL:', 'pmc' ); ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'img_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'img_url' ) ); ?>" type="text" value="<?php echo esc_url( $img_url, null, 'display' ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'alt_text' ) ); ?>">
				<?php esc_html_e( 'Alternate text:', 'pmc' ); ?>&nbsp;
				<a href="https://support.wordpress.com/widgets/image-widget/#image-widget-alt-text" target="_blank">( ? )</a>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'alt_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'alt_text' ) ); ?>" type="text" value="<?php echo esc_attr( $alt_text ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'img_title' ) ); ?>">
				<?php esc_html_e( 'Image title:', 'pmc' ); ?>&nbsp;
				<a href="https://support.wordpress.com/widgets/image-widget/#image-widget-title" target="_blank">( ? )</a>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'img_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'img_title' ) ); ?>" type="text" value="<?php echo esc_attr( $img_title ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'caption' ) ); ?>">
				<?php esc_html_e( 'Caption:', 'pmc' ); ?>&nbsp;
				<a href="https://support.wordpress.com/widgets/image-widget/#image-widget-caption" target="_blank">( ? )</a>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'caption' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'caption' ) ); ?>" rows="2" cols="20"><?php echo esc_textarea( $caption ); ?></textarea>
			</label>
		</p>
		<?php

		$alignments = array(
			'none'   => __( 'None', 'pmc' ),
			'left'   => __( 'Left', 'pmc' ),
			'center' => __( 'Center', 'pmc' ),
			'right'  => __( 'Right', 'pmc' ),
		);

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'align' ) ); ?>">
				<?php esc_html_e( 'Image Alignment:', 'pmc' ); ?>
				<select id="<?php echo esc_attr( $this->get_field_id( 'align' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'align' ) ); ?>">
					<?php foreach ( $alignments as $alignment => $alignment_name ) {
						echo  '<option value="' . esc_attr( $alignment ) . '" ';
						if ( $alignment === $align ) {
							echo 'selected="selected" ';
						}
						echo '>' . esc_html( $alignment_name ) . "</option>\n";
					} ?>
				</select>
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'img_width' ) ); ?>">
				<?php esc_html_e( 'Width:', 'pmc' ); ?>
				<input size="3" id="<?php echo esc_attr( $this->get_field_id( 'img_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'img_width' ) ); ?>" type="text" value="<?php echo esc_attr( $img_width ); ?>" />
			</label>
			<label for="<?php echo esc_attr( $this->get_field_id( 'img_height' ) ); ?>">
				<?php esc_html_e( 'Height:', 'pmc' ); ?>
				<input size="3" id="<?php echo esc_attr( $this->get_field_id( 'img_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'img_height' ) ); ?>" type="text" value="<?php echo esc_attr( $img_height ); ?>" />
			</label>
			<br />
			<small>
				<?php esc_html_e( 'If empty, we will attempt to determine the image size.', 'pmc' ); ?>
			</small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>">
				<?php esc_html_e( 'Link URL (when the image is clicked):', 'pmc' ); ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" type="text" value="<?php echo esc_url( $link, null, 'display' ); ?>" />
			</label>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link_target_blank' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'link_target_blank' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'link_target_blank' ) ); ?>" value="1" <?php checked( $link_target_blank, true, true ); ?> />
				<?php esc_html_e( 'Open link in a new window/tab', 'pmc' ); ?>
			</label>
		</p>
		<script>
			(function($) {
				var $url = $(<?php echo wp_json_encode( '#' . $this->get_field_id( 'img_url' ) ); ?>),
					$width = $(<?php echo wp_json_encode( '#' . $this->get_field_id( 'img_width' ) ); ?>),
					$height = $(<?php echo wp_json_encode( '#' . $this->get_field_id( 'img_height' ) ); ?>);

				$url.blur(function(e) {
					try {
						var img = new Image();
						img.onload = function() {
							if (this.width && '' === $.trim($width.val())) {
								 $width.val(this.width);
							}
							if (this.height && '' === $.trim($height.val())) {
								$height.val(this.height);
							}
						};
						img.src = $(this).val();
					} catch(err) {
						console.log(err);
					}
				});
			})(jQuery);
		</script>
		<?php
	}
} // Class PMC_Image_Widget
