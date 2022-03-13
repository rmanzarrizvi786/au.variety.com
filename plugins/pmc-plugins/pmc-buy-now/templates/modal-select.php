<label>
	<span><?php echo esc_html( $title ); ?></span>
	<select name="<?php echo esc_attr( $name ); ?>">
		<?php
		if ( is_array( $select_options ) ) {
			foreach ( $select_options as $key => $value ) {
				?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
				<?php
			}
		}
		?>
	</select>
</label>
