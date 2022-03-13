<div class="acm-ui-wrapper wrap">

	<h2><?php esc_html_e( 'PMC Video Manager', 'pmc-video-playlist-manager' ); ?></h2>
</div>

<div class="wrap nosubsub">
	<div id="col-container">

		<div>
			<div class="col-wrap" style="padding-right: 0">

				<div class="tablenav top">
					<div class="alignleft">
						<h2 id="video-configurations">
							<?php esc_html_e( 'Video Configurations', 'pmc-video-playlist-manager' ); ?>
						</h2>
					</div>
					<?php
					$page_links = paginate_links( array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'prev_text' => __( '&laquo;' ),
						'next_text' => __( '&raquo;' ),
						'total'     => absint( $total_page ),
						'current'   => absint( $current_page ),
					) );

					if ( $page_links ) {
						?>
						<div class="tablenav-pages">
							<span class="displaying-num">
								<?php /* translators: %s: Number of posts */ ?>
								<?php echo esc_html( sprintf( _n( '%s item', '%s items', absint( $post_count ), 'pmc-video-playlist-manager' ), number_format_i18n( absint( $post_count ) ) ) ); ?>
							</span>
							<?php echo wp_kses_post( $page_links ); ?>
						</div>
						<?php
					}
					?>
					<div class="alignright">
						<div>
							<a href="javascript:;" class="button" id="new-video-config" style="display: inline-block;">
								<?php esc_html_e( 'Add Video Configuration', 'pmc-video-playlist-manager' ); ?>
							</a>
						</div>
					</div>
				</div>

				<div id="provider-forms"></div>
				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<select name="action" id="bulk-action-selector-top">
							<option value="-1"><?php esc_html_e( 'Bulk Actions', 'pmc-video-playlist-manager' ); ?></option>
							<option value="trash"><?php esc_html_e( 'Move to Trash', 'pmc-video-playlist-manager' ); ?></option>
						</select>
						<input type="submit" id="action-delete" class="button action" value="Apply" disabled>
					</div>
				</div>
				<table class="wp-list-table widefat adm-list" cellspacing="0">
					<thead>
					<tr>
						<th scope="col" class="manage-column column-checkbox">
							<input type="checkbox" class="pvm-post-cb-all">
						</th>
						<th scope="col" class="manage-column column-key"><?php esc_html_e( 'Video Module Name', 'pmc-video-playlist-manager' ); ?></th>
						<th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'pmc-video-playlist-manager' ); ?></th>
						<th scope="col" class="manage-column column-playlist_name"><?php esc_html_e( 'Playlist Name', 'pmc-video-playlist-manager' ); ?></th>
						<th scope="col" class="manage-column column-page_target"><?php esc_html_e( 'Pages Targeted', 'pmc-video-playlist-manager' ); ?></th>
						<th scope="col" class="manage-column column-timeframe"><?php esc_html_e( 'Time Frame', 'pmc-video-playlist-manager' ); ?></th>
						<th scope="col" class="manage-column column-priority"><?php esc_html_e( 'Priority', 'pmc-video-playlist-manager' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					if ( $video_posts ) {
						foreach ( $video_posts as $i => $pvm ) {
							$data = $pvm->post_content;
							?>

							<tr class="pvm-row-post <?php echo ( $i % 2 ) ? 'alternate' : ''; ?>" data-id="<?php echo intval( $pvm->ID ); ?>">

								<td class="column-export-checkbox">
									<input class="pvm-post-cb" type="checkbox" name="pvm_post[]" value="<?php echo intval( $pvm->ID ); ?>">
								</td>

								<td class="column-key">
									<strong><?php echo esc_html( $pvm->post_title ); ?></strong>
									<div class="row-actions">
										<span class="edit"><a class="pvm-ajax-edit" href="javascript:;"><?php esc_html_e( 'Edit', 'pmc-video-playlist-manager' ); ?></a> | </span>
										<span class="delete"><a class="pvm-ajax-delete" href="javascript:;"><?php esc_html_e( 'Delete', 'pmc-video-playlist-manager' ); ?></a></span>
									</div>
								</td>

								<td class="column-status">
									<?php ( 'publish' === $pvm->post_status ) ? esc_html_e( 'Enabled', 'pmc-video-playlist-manager' ) : esc_html_e( 'Disabled', 'pmc-video-playlist-manager' ); ?>
								</td>

								<td class="column-pvm_playlist">
									<strong><?php echo esc_html( $data['playlist'] ); ?></strong>
								</td>

								<td class="column-pvm_location">

									<?php
									$targets = explode( '|', $pvm->post_excerpt );

									foreach ( $targets as $target ) {
										echo esc_html( $target ) . '<br>';
									}
									?>

								</td>

								<td class="column-timeframe">
									<?php
									if ( ! empty( $data['start'] ) && ! empty( $data['end'] ) ) {
										echo esc_html( $data['start'] . ' - ' . $data['end'] );
									} else {
										esc_html_e( 'Indefinitely', 'pmc-video-playlist-manager' );
									}
									?>
								</td>

								<td class="column-priority">
									<?php echo esc_html( $data['priority'] ); ?>
								</td>

							</tr>

							<?php
						}
					} else {
						?>
						<tr>
							<td colspan="5" class="no-results">
								<?php esc_html_e( 'No playlist have been defined.', 'pmc-video-playlist-manager' ); ?><br>
							</td>
						</tr>

					<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
