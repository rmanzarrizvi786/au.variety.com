<?php
/**
 * Template to add option to user profile
 */
?>
<tr>
	<th>
		<label for="<?php echo esc_attr( $option_key ); ?>">Disable Live Chat?</label>
	</th>
	<td>
		<label for="<?php echo esc_attr( $option_key ); ?>">
			<input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>" id="<?php echo esc_attr( $option_key ); ?>" value="1" <?php checked( 1, intval( $disable_chat ) ); ?>>
			&nbsp;
			<em>(Select to disable the Live Chat in wp-admin)</em>
		</label>
	</td>
</tr>
