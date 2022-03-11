<?php
/**
 * Variety Base Widget
 *
 * Base class for creating Variety widgets.
 *
 * @see     Global_Curateable
 *
 * @package pmc-variety-2017
 * @since   2017.1.0
 */

namespace Variety\Inc\Widgets;

use \PMC_Cache;

abstract class Variety_Base_Widget extends \WP_Widget {

	const ID = 'variety-base-widget';

	const CACHE_LIFE = 300;     // 5 minutes

	const CACHE_GROUP_KEY = 'vy_widget_cache_grp';

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string|void
	 * @throws \Exception
	 * @since 2017.1.0
	 * @see   WP_Widget::form()
	 *
	 */
	public function form( $instance ) {

		foreach ( $this->get_fields() as $field_key => $field ) {

			if ( empty( $field['type'] ) || empty( $field['label'] ) ) {
				continue;
			}

			// Get the default value.
			$value = ! empty( $instance[ $field_key ] ) ? $instance[ $field_key ] : '';

			// Field values.
			$field_name = $this->get_field_name( $field_key );
			$field_id   = $this->get_field_id( $field_key );

			\PMC::render_template(
				CHILD_THEME_PATH . '/template-parts/widgets/base-widget-form.php',
				[
					'field_id'   => $field_id,
					'field_name' => $field_name,
					'value'      => $value,
					'field'      => $field,
				],
				true
			);

		}

	}

	/**
	 * Update widget form values.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @since 2017.1.0
	 * @see   WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = [];

		// Only save the fields we mean to save.
		foreach ( $this->get_fields() as $field_key => $field ) {
			if ( ! empty( $new_instance[ $field_key ] ) ) {
				$instance[ $field_key ] = $this->sanitize( $new_instance[ $field_key ], $field['type'] );
			}
		}

		return $instance;
	}

	/**
	 * Sanitize widget form values.
	 *
	 * @param array $data The data to sanitize.
	 * @param array $type The type of data.
	 *
	 * @return string Clean data.
	 * @since 2017.1.0
	 * @see   WP_Widget::update()
	 *
	 */
	private function sanitize( $data, $type ) {

		switch ( $type ) {
			case 'url':
				return esc_url_raw( $data );
			case 'text':
			default:
				return sanitize_text_field( $data );
		}
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @throws \Exception
	 * @since 2017.1.0
	 *
	 * @see   WP_Widget::widget()
	 *
	 */
	public function widget( $args, $instance ) {

		echo wp_kses_post( $args['before_widget'] );

		$this->_output( $instance );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Method to get cached data for output
	 *
	 * @param array $data Widget data, from global data or overrides.
	 *
	 * @return mixed
	 * @since 2017-10-04 Amit Gupta - CDWE-702
	 *
	 */
	protected function _get_cached_data( $data = [] ) {

		if ( static::CACHE_LIFE < 300 ) {
			// Cache is disabled on the widget
			return $this->get_uncached_data( $data );
		}

		$cache_key = sprintf( '%s-%s', static::ID, wp_json_encode( $data ) );

		$cache = new PMC_Cache( $cache_key, $this->_get_cache_group() );

		return $cache->expires_in( static::CACHE_LIFE )->updates_with( [ $this, 'get_uncached_data' ], [ $data ] )->get();

	}

	/**
	 * Placeholder method which returns anything passed to it as is.
	 * This is for implementation in child classes where it will fetch & process data
	 * and return to a method which passes it via cache before use on front-end.
	 *
	 * This is not meant for use directly to show any data.
	 *
	 * @param array $data Widget data, from global data or overrides.
	 *
	 * @return mixed
	 * @since 2017-10-04 Amit Gupta - CDWE-702
	 *
	 */
	public function get_uncached_data( $data = [] ) {

		return $data;
	}

	/**
	 * Output the widget on the frontend. This will load the template part
	 * `template-parts/widgets/<group name, lowercased and dasherized>` by
	 * default, but this may be overridden on individual widgets.
	 *
	 * @param array $data Widget data, from global data or overrides.
	 *
	 *
	 * @throws \Exception
	 * @since 2017.1.0
	 */
	protected function _output( $data ) {

		$data = $this->_get_cached_data( $data );

		\PMC::render_template(
			sprintf(
				'%s/template-parts/widgets/' . str_replace( '_', '-', $this->id_base ) . '.php',
				untrailingslashit( CHILD_THEME_PATH )
			),
			[ 'data' => $data ],
			true
		);

	}

	/**
	 * Get the widget option fields.
	 *
	 * @return array
	 * @since 2017.1.0
	 */
	protected function get_fields() {

		return [];
	}

	/**
	 *
	 * @return string
	 */
	protected function _get_cache_group() {

		$cache_key = self::CACHE_GROUP_KEY;

		$value = wp_cache_get( $cache_key );

		if ( empty( $value ) ) {
			$value = \pmc_get_option( $cache_key, self::CACHE_GROUP_KEY );
		}

		if ( empty( $value ) ) {

			$value = self::CACHE_GROUP_KEY . time();

			wp_cache_set( self::CACHE_GROUP_KEY, $value );

		}

		return $value;

	}

}

//EOF
