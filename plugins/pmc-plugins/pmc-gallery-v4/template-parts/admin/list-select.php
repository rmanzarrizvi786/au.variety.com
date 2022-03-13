<table class="form-table">
	<tbody>
	<tr>
		<td>
			<p><?php esc_html_e( 'Selected List:', 'pmc-gallery-v4' ); ?></p>
			<p class="pmc_list_selected" style="font-weight: 700;">
				<?php
				if ( ! empty( $list_id ) ) {
					echo esc_html( get_the_title( $list_id ) );
				} else {
					esc_html_e( 'None', 'pmc-gallery-v4' );
				}
				?>
			</p>
			<p><input
					type="text"
					id="pmc_list_name_v4"
					name="pmc_list_name_v4"
					class="widefat"
					placeholder="<?php esc_attr_e( 'Search', 'pmc-gallery-v4' ); ?>"
					value="" />

				<input
					type="hidden"
					id="pmc_list_id"
					name="pmc_list_id"
					value="<?php echo ! empty( $list_id ) ? intval( $list_id ) : ''; ?>" />
			</p>

			<p class="description">
				<?php esc_html_e( 'Start typing to search for a list to add this list item to.', 'pmc-gallery-v4' ); ?>
			</p>
		</td>
	</tr>
	<tr><th style="padding-bottom:4px;">Recent lists:</th></tr>
	<tr><td>
			<span class="list-description ">Select a list to add this list item to it.</span>
			<ul id="pmc-recent-lists" class="pmc-recent-lists">
				<?php foreach ( $recent_lists as $list ) : ?>
					<li class="pmc-recent-lists-li"><a href="#" class="no-click" data-pmc-list-id="<?php echo esc_attr( intval( $list->ID ) ); ?>" data-pmc-list-name="<?php echo esc_attr( $list->post_title ); ?>"><?php echo esc_html( $list->post_title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			</td></tr>
	</tbody>
</table>
