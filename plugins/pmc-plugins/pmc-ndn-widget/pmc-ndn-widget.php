<?php
wpcom_vip_load_plugin( 'pmc-encapsulation-widget', 'pmc-plugins' );

if ( ! class_exists( "Pmc_Encapsulation_Widget" ) ) {
	return null;
}

add_action( 'widgets_init', function() {
	register_widget( 'Pmc_Ndn_Widget' );

} );
class Pmc_Ndn_Widget extends Pmc_Encapsulation_Widget {


	public function __construct() {
		$this->set_widget_property( 'NDN right rail Widget.', null, 'NDN right rail widgets' );
		parent::__construct();
	}

	public function form( $instance ) {

		$widget_type = isset( $instance['ndn_widget_type'] ) ? $instance['ndn_widget_type'] : 'inline300';
		$widget_id   = isset( $instance['ndn_widget_id'] ) ? $instance['ndn_widget_id'] : '';
		$site_section_id = isset( $instance['site_section_id'] ) ? $instance['site_section_id'] : '';

		?>
	<label for="<?php echo $this->get_field_id( 'inline300' ); ?>">Please select the kind of NDN widget you will like to display. </label></br></br>
			<input type=radio id="<?php echo $this->get_field_id( 'inline300' ); ?>" name=<?php echo $this->get_field_name( 'ndn_widget_type' );?>	value="inline300" <?php checked( $widget_type, 'inline300' ); ?>> Inline 300 widget</input></br>
			<input type=radio name=<?php echo $this->get_field_name( 'ndn_widget_type' );?>	value="300by250" <?php checked( $widget_type, '300by250' ); ?>>300 by 250</input></br>
			<input type=radio name=<?php echo $this->get_field_name( 'ndn_widget_type' );?>	value="playlistslider" <?php checked( $widget_type, 'playlistslider' ); ?>>Play List Slider</input></br></br>

				<label for="<?php echo $this->get_field_id( 'ndn_widget_id' ); ?>">Please enter the NDN WidgetID. </label></br>
			<input type=text id="<?php echo $this->get_field_id( 'ndn_widget_id' ); ?>" name="<?php echo $this->get_field_name( 'ndn_widget_id' );?>" value="<?php echo esc_attr( $widget_id ); ?>" />
				</br></br>
				<label for="<?php echo $this->get_field_id( 'site_section_id' ); ?>">Please enter the Site Section ID. </label></br>
			<input type=text id="<?php echo $this->get_field_id( 'site_section_id' ); ?>" name="<?php echo $this->get_field_name( 'site_section_id' );?>" value="<?php echo esc_attr( $site_section_id ); ?>" />
			<?php

	}

	public function update( $new_instance, $old_instance ) {
		$ndn_widget_types            = array( 'inline300', '300by250', 'playlistslider' );
		$instance                    = array();
		$instance['ndn_widget_type'] = isset( $new_instance['ndn_widget_type'] ) && in_array( $new_instance['ndn_widget_type'], $ndn_widget_types ) ? sanitize_title( $new_instance['ndn_widget_type'] ) : 'inline300';
		$instance['ndn_widget_id']   = isset( $new_instance['ndn_widget_id'] ) ? sanitize_text_field( $new_instance['ndn_widget_id'] ) : '';
		$instance['site_section_id'] = isset( $new_instance['site_section_id'] ) ? sanitize_text_field( $new_instance['site_section_id'] ) : '';
		return $instance;
	}

	public function widget( $args, $instance ) {
		$widget_type        = isset( $instance['ndn_widget_type'] ) ? $instance['ndn_widget_type'] : '';
		$widget_id          = isset( $instance['ndn_widget_id'] ) ? $instance['ndn_widget_id'] : '';
		$site_section_id          = isset( $instance['site_section_id'] ) ? $instance['site_section_id'] : 'hollywoodlife'; // default the site section to hollywoodlife

		$tracking_group_ids = array(
			'hollywoodlife.com' => 91871,
			'variety.com'			 => 91211,
			'tvline.com'				=> 91916,
		);
		$host               = parse_url( home_url(), PHP_URL_HOST );

		$tracking_id = isset( $tracking_group_ids[$host] ) ? $tracking_group_ids[$host] : $tracking_group_ids['variety.com'];


		switch ( $widget_type ) {
			case 'inline300':
				$type = "VideoPlayer/Inline300";
				break;
			case '300by250':
				$type = "VideoLauncher/Slider300x250";
				break;
			case 'playlistslider':
				$type = "VideoLauncher/Playlist300x250";
				break;
			default:
				$type = "VideoPlayer/Inline300";
				break;
		}
		?>
	<script type='text/javascript'>
		if (typeof window.ndn_script_is_loaded == "undefined" && pmc !== undefined) {
			window.ndn_script_is_loaded = true;
			pmc.load_script( '//launch.newsinc.com/js/embed.js', '', '_nw2e-js' );

		}
	</script>
	<div class="ndn_embed ndn_embed_widget" data-config-widget-id="<?php echo esc_attr( $widget_id );?>" data-config-type="<?php echo esc_attr( $type ); ?>" data-config-tracking-group="<?php echo esc_attr( $tracking_id ); ?>" data-config-site-section="<?php echo esc_attr( $site_section_id); ?>"></div>
	<?php
	}
}
