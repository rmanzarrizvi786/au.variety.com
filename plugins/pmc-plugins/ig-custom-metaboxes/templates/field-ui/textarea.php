<?php
/**
 * Textarea field UI template
 *
 * @package iG:Custom Metaboxes v1.0
 */

$attributes['id'] = 'ig-cmf-' . $attributes['id'];
?>
	<label for="<?php echo esc_attr( $attributes['id'] ); ?>">
		<strong><?php echo wp_kses_post( $label ); ?></strong>
	</label>
	<br>
	<textarea
<?php
	foreach ( $attributes as $attribute => $value ) {
		printf( ' %s="%s"', $attribute, esc_attr( $value ) );
	}

	echo esc_attr( $status );
?>
	><?php echo esc_attr( $field_value ); ?></textarea>
<?php
if ( ! empty( $description ) ) {
?>
	<br>
	<span class="description"><?php echo wp_kses_post( $description ); ?></span>
<?php
}

//EOF