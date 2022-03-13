<?php
/**
 * Widget for PMC Ads plugin locations
 *
 * Displays ads for the selected ad location
 *
 * @version 2016-08-02 James Mehorter
 */
class PMC_Ads_Location_Widget extends WP_Widget {

	const NONCE_KEY = '_pmc_adm_ads_location_widget';

	/**
	 * PMC_Ads_Location_Widget constructor
	 */
	public function __construct() {
		parent::__construct(
			'pmc-ads-location-widget',
			'PMC Ads Location Widget',
			array(
				 'description' => 'Display ads assigned to the selected location.',
			)
		);
		// adding ajax callbacks
		add_action( 'wp_ajax_get_locations_for_provider', array( $this, 'get_locations_for_provider' ) ); // admin

	}

	/**
	 * Ajax callback to return the locations for the selected provider
	 *
	 * @return string json
	 */
	public function get_locations_for_provider() {

		$post_nonce = ( ! empty( $_POST[ self::NONCE_KEY ] ) ) ? $_POST[ self::NONCE_KEY ] : false;

		if ( ! empty( $post_nonce ) && false !== wp_verify_nonce( $post_nonce, self::NONCE_KEY ) ) {

			$provider = PMC::filter_input( INPUT_POST, 'provider', FILTER_SANITIZE_STRING );

			if ( defined( 'DEFAULT_AD_PROVIDER' ) && empty( $provider ) ) {
				$provider = DEFAULT_AD_PROVIDER;
			}

			$ad_locations = PMC_Ads::get_instance()->get_locations( $provider );

			if ( is_array( $ad_locations ) ) {
				wp_send_json_success( array(
					'locations' => $ad_locations
				) );
			} else {
				wp_send_json_error( array(
					'error' => 'Ad locations not fetched'
				) );
			}
		}
	}

	/**
	 * Display the widget admin options form
	 *
	 * @param array $instance An array of saved widget options
	 *
	 * @return null
	 */
	public function form( $instance = array() ) {

		$providers = PMC_Ads::get_instance()->get_providers();
		$selected_provider = ( ! empty( $instance['provider'] ) ) ? $instance['provider'] : DEFAULT_AD_PROVIDER;

		if( ! empty( $selected_provider ) ) {
			$ad_locations = PMC_Ads::get_instance()->get_locations( $selected_provider );
		}

		if ( empty( $ad_locations ) || ! is_array( $ad_locations ) ) {
			esc_html_e( 'Please define some ad locations in your theme with the pmc_adm_add_locations() function.', 'adm' );
			return;
		}

		$selected_ad_location = ( ! empty( $instance['ad_location'] ) ) ? $instance['ad_location'] : '';
		$selected_wrap_div_class = ( ! empty( $instance['wrap_div_class'] ) ) ? $instance['wrap_div_class'] : '';

		PMC::render_template( PMC_ADM_DIR . '/templates/location-widget-admin-form.php', array(
			'ad_location_field_id'      => $this->get_field_id( 'ad_location' ),
			'ad_location_field_name'    => $this->get_field_name( 'ad_location' ),
			'wrap_div_class_field_id'   => $this->get_field_id( 'wrap_div_class' ),
			'wrap_div_class_field_name' => $this->get_field_name( 'wrap_div_class' ),
			'provider_field_id'         => $this->get_field_id( 'provider' ),
			'provider_field_name'       => $this->get_field_name( 'provider' ),
			'selected_ad_location'      => $selected_ad_location,
			'selected_wrap_div_class'   => $selected_wrap_div_class,
			'ad_locations'              => $ad_locations,
			'selected_provider'         => $selected_provider,
			'providers'                 => array_keys( $providers ),
			'admin_url'                 => admin_url( 'admin-ajax.php' ),
			'nonce_key'                 => self::NONCE_KEY ,
			'nonce_field'               => wp_create_nonce( self::NONCE_KEY ),
		), true );
	}

	/**
	 * Save the widget options
	 *
	 * @param array $new_instance The new widget options
	 * @param array $old_instance The old widget options
	 *
	 * @return array The sanitized widget options
	 */
	public function update( $new_instance = array() , $old_instance = array() ) {
		$instance = $old_instance;

		if ( isset( $new_instance['ad_location'] ) ) {
			$instance['ad_location'] = sanitize_text_field( $new_instance['ad_location'] );
		}

		if ( isset( $new_instance['wrap_div_class'] ) ) {
			$instance['wrap_div_class'] = sanitize_text_field( $new_instance['wrap_div_class'] );
		}

		if ( isset( $new_instance['provider'] ) ) {
			$instance['provider'] = sanitize_text_field( $new_instance['provider'] );
		}

		return $instance;
	}

	/**
	 * Display the widget output on the frontend
	 *
	 * @param array $args     Possible widget arguments
	 * @param array $instance The widget's saved options
	 *
	 * @return null
	 */
	public function widget( $args = array(), $instance = array() ) {

		if ( empty( $instance['ad_location'] ) ) {
			return;
		}

		$default_provider = '';

		if ( defined( 'DEFAULT_AD_PROVIDER' ) ) {
			$default_provider = DEFAULT_AD_PROVIDER;
		}

		$provider = ( ! empty( $instance['provider'] ) ) ? $instance['provider'] : $default_provider;
		$html     = pmc_adm_render_ads( $instance['ad_location'], '', false, $provider );

		if ( empty( $html ) ) {
			return;
		}

		if ( ! empty( $instance['wrap_div_class'] ) ) {
			$html = sprintf(
				'<div class="%s">%s%s</div>',
				$instance['wrap_div_class'],
				$html,
				( false !== strpos( $instance['wrap_div_class'], 'clear' ) ) ? '<div class="clear"></div>' : ''
			);
		}

		echo wp_kses_post( $html );
	}
}

/**
 * Register the widget
 */
add_action( 'widgets_init', function () {
	return register_widget( 'PMC_Ads_Location_Widget' );
} );


//EOF
