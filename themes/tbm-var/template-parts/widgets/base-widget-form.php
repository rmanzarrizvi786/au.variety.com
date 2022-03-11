<?php
/**
 * Template part of Base widget form.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

switch ( $field['type'] ) {
	case 'text':
	case 'url':
		?>
		<p>
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>">
		</p>
		<?php
		break;
	case 'select':
		if ( empty( $field['options'] ) ) {
			break;
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $field['label'] ); ?></label><br />
			<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>">
				<?php foreach ( $field['options'] as $key => $option ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $value, true ); ?>><?php echo esc_html( $option ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
		break;
}
