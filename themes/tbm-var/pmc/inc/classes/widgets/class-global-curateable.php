<?php
namespace PMC\Core\Inc\Widgets;

if ( class_exists( '\FM_Widget' ) ) {

	/**
	 * PMC Global Curation Widget
	 */
	abstract class Global_Curateable extends \FM_Widget {

		/**
		 * Get the widget Fieldmanager fields.
		 *
		 * @return array
		 */
		public static function get_fields() {
			return [];
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
			$key = $this->group_name();
			echo wp_kses_post( $args['before_widget'] );

			do_action( 'pmc_core_global_curation_' . sanitize_key( $key ) . '_before' );

			if ( empty( $instance['override_data'] ) ) {
				$settings = get_option( 'global_curation', [] );
				if ( ! empty( $settings[ 'tab_' . $key ][ $key ] ) ) {
					$this->_output( $settings[ 'tab_' . $key ][ $key ] );
				} else {
					$this->_output( [] );
				}
			} else {
				if ( ! isset( $instance['overrides'][ $key ] ) ) {
					$instance['overrides'][ $key ] = [];
				}
				$this->_output( $instance['overrides'][ $key ] );
			}

			do_action( 'pmc_core_global_curation_' . sanitize_key( $key ) . '_after' );

			echo wp_kses_post( $args['after_widget'] );
		}

		/**
		 * Output the widget on the frontend. This will load the template part
		 * `template-parts/widgets/<group name, lowercased and dasherized>` by
		 * default, but this may be overridden on individual widgets.
		 *
		 * @param  array $data Widget data, from global data or overrides.
		 */
		protected function _output( $data ) {
			$path = locate_template( 'template-parts/widgets/' . str_replace( '_', '-', $this->group_name() ) . '.php' );
			echo \PMC::render_template( $path, [ 'data' => $data ] );
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
			$instance = parent::update( $new_instance, $old_instance );
			if ( empty( $instance['override_data'] ) ) {
				$settings = get_option( 'global_curation', [] );
				if ( ! isset( $new_instance['overrides'] ) ) {
					$new_instance['overrides'] = [];
				}
				$settings[ $this->group_name() ] = $new_instance['overrides'];
				update_option( 'global_curation', $settings );
			}

			return $instance;
		}

		/**
		 * Return the FM fields driving the widget form.
		 *
		 * @return array Fieldmanager_Group children.
		 */
		protected function _fieldmanager_children() {
			return [
				'override_data' => new \Fieldmanager_Checkbox( [
					'label' => __( 'Override Global Data', 'pmc-core' ),
					'default_value' => false,
				] ),
				'overrides' => new \Fieldmanager_Group( [
					'display_if' => [
						'src' => 'override_data',
						'value' => true,
					],
					'children' => static::get_fields(),
				] ),
			];
		}
	}

}
