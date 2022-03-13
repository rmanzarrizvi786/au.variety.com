<?php
/**
 * admin UI for PMC Video Player Widget
 *
 * @since 2014-08-07 Amit Gupta
 */
?>
<p>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'widget_title' ) ); ?>">Widget Title:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'widget_title' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'widget_title' ) ); ?>" type="text" value="<?php echo esc_attr( $player['widget_title'] ); ?>">
	<br>

	<label for="<?php echo esc_attr( $widget->get_field_id( 'width' ) ); ?>">Width:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'width' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'width' ) ); ?>" type="number" value="<?php echo esc_attr( $player['width'] ); ?>" size="3" required>
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'height' ) ); ?>">Height:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'height' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'height' ) ); ?>" type="number" value="<?php echo esc_attr( $player['height'] ); ?>" size="3">
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'ratio' ) ); ?>">Aspect Ratio:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'ratio' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'ratio' ) ); ?>" type="text" value="<?php echo esc_attr( $player['ratio'] ); ?>" size="5">
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'image' ) ); ?>">Image:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'image' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'image' ) ); ?>" type="url" value="<?php echo esc_attr( $player['image'] ); ?>" style="width: 100%;">
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'title' ) ); ?>">Title:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $player['title'] ); ?>" style="width: 100%;">
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'description' ) ); ?>">Description:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'description' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'description' ) ); ?>" type="text" value="<?php echo esc_attr( $player['description'] ); ?>" style="width: 100%;">
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'primary' ) ); ?>">Primary:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'primary' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'primary' ) ); ?>" type="text" value="<?php echo esc_attr( $player['primary'] ); ?>" size="10">
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'content' ) ); ?>">Media <?php echo esc_html( empty( $cdn_path  ) ? 'URL' : 'Filename' ); ?>:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'content' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'content' ) ); ?>" type="url" value="<?php echo esc_attr( $player['content'] ); ?>" style="width: 100%;" required>
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'playlist' ) ); ?>">Playlist URL:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'playlist' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'playlist' ) ); ?>" type="url" value="<?php echo esc_attr( $player['playlist'] ); ?>" size="10">
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'vast' ) ); ?>">VAST URL:</label>
	<input id="<?php echo esc_attr( $widget->get_field_id( 'vast' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'vast' ) ); ?>" type="url" value="<?php echo esc_attr( $player['vast'] ); ?>" size="10">
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'autostart' ) ); ?>">
		Autostart: &nbsp;
		<input id="<?php echo esc_attr( $widget->get_field_id( 'autostart' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'autostart' ) ); ?>" type="checkbox" value="yes" <?php checked( 'yes', $player['autostart'] ) ?>>
	</label>
	<br>
	<label for="<?php echo esc_attr( $widget->get_field_id( 'startmute' ) ); ?>">
		Start Mute: &nbsp;
		<input id="<?php echo esc_attr( $widget->get_field_id( 'startmute' ) ); ?>" name="<?php echo esc_attr( $widget->get_field_name( 'startmute' ) ); ?>" type="checkbox" value="yes" <?php checked( 'yes', $player['startmute'] ) ?>>
	</label>
</p>
