<table class="form-table">
	<tbody>
	<tr>
		<td>
			<p><?php esc_html_e( 'Selected List:', 'pmc-lists' ); ?></p>
			<p class="pmc_list_selected" style="font-weight: 700;">
				<?php
				if ( ! empty( $list_id ) ) {
					echo esc_html( get_the_title( $list_id ) );
				} else {
					esc_html_e( 'None', 'pmc-lists' );
				}
				?>
			</p>
			<p><input
					type="text"
					id="pmc_list_name"
					name="pmc_list_name"
					class="widefat"
					placeholder="<?php esc_attr_e( 'Search', 'pmc-lists' ); ?>"
					value="" />

				<input
					type="hidden"
					id="pmc_list_id"
					name="pmc_list_id"
					value="<?php echo ! empty( $list_id ) ? intval( $list_id ) : ''; ?>" />
			</p>

			<p class="description">
				<?php esc_html_e( 'Start typing to search for a list to add this list item to.', 'pmc-lists' ); ?>
			</p>
		</td>
	</tr>
	</tbody>
</table>
