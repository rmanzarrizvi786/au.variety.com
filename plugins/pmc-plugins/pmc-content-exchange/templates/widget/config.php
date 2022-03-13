<?php
/**
 * Admin config template for PMC Content Exchange widget
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @ticket CDWE-195
 * @since 2017-02-24
 */

if ( ! empty( $publications ) && is_array( $publications ) ) {
	?>
	<p>
		<label for="<?php echo esc_attr( $widget->get_field_id( 'module_id' ) ); ?>">
			<?php esc_html_e( 'Publications', 'pmc-content-exchange' ); ?>
		</label>
		<br>
		<select
			id="<?php echo esc_attr( $widget->get_field_id( 'module_id' ) ); ?>"
			name="<?php echo esc_attr( $widget->get_field_name( 'module_id' ) ); ?>">
			<option value="0"><?php esc_html_e( 'Select one ...', 'pmc-content-exchange' ); ?></option>
			<?php
			foreach ( $publications as $slug => $label ) {
				?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $module_id, $slug ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
				<?php
			}
			?>
		</select>
	</p>

	<p>
		<label for="<?php echo esc_attr( $widget->get_field_id( 'show_dummy_image' ) ); ?>">
			<?php esc_html_e( 'Show Dummy Image (Non Production)', 'pmc-content-exchange' ); ?>
		</label><br />

		<input type="checkbox"
			name="<?php echo esc_attr( $widget->get_field_name( 'show_dummy_image' ) ); ?>"
			id="<?php echo esc_attr( $widget->get_field_id( 'show_dummy_image' ) ); ?>"
			<?php checked( $show_dummy_image, 1, true ); ?>
			value="1"
		/>
	</p>
	<?php
}

//EOF
