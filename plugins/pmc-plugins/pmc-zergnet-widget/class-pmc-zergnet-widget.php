<?php
/**
 *  Shared Zergnet widget
 */

class Zergnet_Widget extends WP_Widget{

	public function __construct(){

		parent::__construct(
			'zergnet',
			'Zergnet',
			array(
				'description' => 'Zergnet widget for PMC Brands',
			)
		);
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		if ( isset( $new_instance['title'] ) ) {
			$instance['title'] = sanitize_text_field( $new_instance['title'] );
		}

		if ( isset( $new_instance['zergnet_id'] ) ) {
			$instance['zergnet_id'] = sanitize_text_field( $new_instance['zergnet_id'] );
		}

		return $instance;
	}

	public function form( $instance ) { ?>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Zergnet Title</label>
		<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			   value="<?php echo esc_attr( isset( $instance['title'] ) ? $instance['title'] : '' ); ?>"
			   class="widefat" type="text" />
	</p>
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'zergnet_id' ) ); ?>">Zergnet ID</label>
		<input id="<?php echo esc_attr( $this->get_field_id( 'zergnet_id' ) ); ?>"
			   name="<?php echo esc_attr( $this->get_field_name( 'zergnet_id' ) ); ?>"
			   value="<?php echo esc_attr( isset( $instance['zergnet_id'] ) ? $instance['zergnet_id'] : '' ); ?>"
			   class="widefat" type="text" />
	</p>

<?php
	}

	public function widget( $args, $instance ) {
		echo PMC::render_template( __DIR__ . '/templates/pmc-zergnet-widget.php', $instance );
	}
}