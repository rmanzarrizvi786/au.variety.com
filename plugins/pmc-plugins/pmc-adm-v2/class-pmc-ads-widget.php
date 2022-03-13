<?php
/**
 * Widget for PMC Ads plugin
 *
 * @since ?
 * @version 2013-08-02 Amit Gupta
 * @version 2013-12-23 Amit Gupta
 */

class PMC_Ads_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( 'pmc-ads-widget', 'PMC Ads Widget',
			array(
				 'description' => 'Display ads defined in the ads admin',
			) );
	}

	public function get_ads() {
		$ads = PMC_Ads::get_instance()->get_ads( true, 'widget' );

		if ( empty( $ads ) ) {
			return;
		}

		$ad_distinct_titles = array_unique( (array) wp_list_pluck( $ads, 'post_title' ) );
		sort( $ad_distinct_titles );

		return $ad_distinct_titles;
	}

	public function widget( $args, $instance ) {
		if ( isset( $instance['ad_title'] ) ) {

			$html = pmc_adm_render_ads( 'widget', sanitize_text_field( $instance['ad_title'] ), false );

			if ( empty( $html ) ) {
				return;
			}

			if ( ! empty( $args['before_widget'] ) ) {
				echo wp_kses_post( $args['before_widget'] );
			}

			if ( !empty( $instance['wrap_div_class'] ) ) {
				printf( '<div class="%s">%s%s</div>',
					esc_attr( $instance['wrap_div_class'] ),
					$html, // this need to be raw html
					// apply inline clear style
					strpos( $instance['wrap_div_class'], 'clear') != false ? '<div class="clear"></div>' : ''
					);
			} else {
				echo $html; // output the raw html
			}

			if ( ! empty( $args['after_widget'] ) ) {
				echo wp_kses_post( $args['after_widget'] );
			}

		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if ( isset( $new_instance['ad_title'] ) ) {
			$instance['ad_title'] = sanitize_text_field( $new_instance['ad_title'] );
		}

		if ( isset( $new_instance['wrap_div_class'] ) ) {
			$instance['wrap_div_class'] = sanitize_text_field( $new_instance['wrap_div_class'] );
		}

		return $instance;
	}

	public function form( $instance ) {
		$ad_titles = $this->get_ads();

		if ( empty( $ad_titles ) ) {
			echo '<p>No Ads defined for Widget.</p>';
			return;
		}

		$ad_title = ( ! empty( $instance['ad_title'] ) ) ? sanitize_title( $instance['ad_title'] ) : '';
		$instance = wp_parse_args( (array)$instance, array( 'ad_title' => "", 'wrap_div_class' => '' ) );
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'ad_title' ); ?>"></label>
			<select id="<?php echo $this->get_field_id( 'ad_title' ); ?>"
					name="<?php echo $this->get_field_name( 'ad_title' ); ?>" class="widefat">
<?php
				$count = count( $ad_titles );

				for ( $i = 0; $i < $count; $i++ ) {
					$title = sanitize_text_field( $ad_titles[ $i ] );
					$title_val = sanitize_title( $title );
?>
					<option value="<?php echo esc_attr( $title_val ); ?>" <?php selected( $title_val, $ad_title ) ?>><?php echo esc_html( $title ); ?></option>
<?php
					unset( $title_val, $title );
				}
?>
			</select>

		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'wrap_div_class' ); ?>">Wrap div with following class</label>
			<input type="text" id="<?php echo $this->get_field_id( 'wrap_div_class' ); ?>"
					name="<?php echo $this->get_field_name( 'wrap_div_class' ); ?>" class="widefat"
					value="<?php echo esc_attr( $instance['wrap_div_class'] ); ?>"
					/>
		</p>
<?php
	}

}

add_action( 'widgets_init', function () {
	return register_widget( 'PMC_Ads_Widget' );
} );


//EOF
