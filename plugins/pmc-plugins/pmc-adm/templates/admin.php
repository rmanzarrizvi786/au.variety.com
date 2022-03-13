<div class="acm-ui-wrapper wrap">
	<h2><?php esc_html_e( 'PMC Ad Manager', 'pmc-plugins' ); ?></h2>
</div>

<div class="wrap nosubsub">
	<div id="col-container">

		<div>
			<div class="col-wrap" style="padding-right: 0">
				<?php if ( current_user_can( 'pmc_manage_ads_cap' ) ) : ?>
					<div>
						<div class="wrap">
							<h3><?php esc_html_e( 'Import Ads','pmc-plugins' ); ?></h3>

							<div class="narrow">
								<?php echo wp_kses_post( PMC_Ads_Importer::get_instance()->import_message ); ?>
								<?php wp_import_upload_form( admin_url( 'tools.php?page=ad-manager&import=true' ) ); ?>
							</div>
						</div>
						<div class="submit">
							<?php
							$nonce = wp_create_nonce( PMC_Ads_Exporter::NONCE_KEY );

							$url = $_SERVER['REQUEST_URI'];
							$url = add_query_arg( 'action', 'pmc-ads-export', $url );
							$url = add_query_arg( '_wpnonce', $nonce, $url );
							?>
							<input id="pmc-ads-exporter" type="button" class="button-primary" value="Export Ads" data-url="<?php echo esc_url( $url ) ?>"/>
							<iframe id="pmc-ads-exporter-iframe" src="#" style="display: none"></iframe>
						</div>
					</div>
				<?php endif; ?>
				<div class="tablenav top">
					<div class="alignleft">
						<h2 id="ad-configurations">
							<?php esc_html_e( 'Ad Configurations', 'pmc-plugins' ); ?>
						</h2>
					</div>
					<?php
						$page_links = paginate_links( array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => __( '&laquo;' ),
							'next_text' => __( '&raquo;' ),
							'total'     => $total_page,
							'current'   => $current_page,
						) );
					?>
					<?php if ( $page_links ) : ?>
						<div class= "tablenav-pages">
							<span class="displaying-num">
								<?php echo esc_html( sprintf( _n( '%s item', '%s items', absint( $post_count ) ), number_format_i18n( absint( $post_count ) ) ) ); ?>
							</span>
							<?php echo wp_kses_post( $page_links ); ?>
						</div>
					<?php endif; ?>

					<div class="alignright">
						<?php if ( ! empty( $providers ) ) : ?>
							<div>
								<select id="provider">
									<?php foreach ( $providers as $key => $provider ) { ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( 'google-publisher', $key ); ?>>
											<?php echo esc_html( $provider->get_title() ); ?>
										</option>
									<?php } ?>
								</select>

								<button type="button" class="button" id="new-ad" style="display: inline-block;"><?php esc_html_e( 'Add Ad Configuration', 'pmc-plugins' ); ?></button>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div id="provider-forms"></div>
				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<select name="action" id="bulk-action-selector-top">
							<option value="-1"><?php esc_html_e( 'Bulk Actions', 'pmc-plugins' ); ?></option>
							<option value="trash"><?php esc_html_e( 'Move to Trash', 'pmc-plugins' ); ?></option>
						</select>
						<input type="submit" id="action-delete" class="button action" value="Apply" disabled>
					</div>
				</div>
				<table class="wp-list-table widefat adm-list" cellspacing="0">
					<thead>
					<tr>
						<th scope="col" class="manage-column column-checkbox">
							<input type="checkbox" class="ad-post-cb-all">
						</th>
						<th scope="col" class="manage-column column-key"><?php esc_html_e( 'Ad', 'pmc-plugins' ); ?></th>
						<th scope="col" class="manage-column column-ad_type"><?php esc_html_e( 'Ad Type', 'pmc-plugins' ); ?></th>
						<th scope="col" class="manage-column column-ad_location"><?php esc_html_e( 'Location', 'pmc-plugins' ); ?></th>
						<th scope="col" class="manage-column column-priority"><?php esc_html_e( 'Status', 'pmc-plugins' ); ?></th>
						<th scope="col" class="manage-column column-priority"><?php esc_html_e( 'Priority', 'pmc-plugins' ); ?></th>
						<th scope="col" class="manage-column column-conditions"><?php esc_html_e( 'Conditions', 'pmc-plugins' ); ?></th>
						<th scope="col" class="manage-column column-timeframe"><?php esc_html_e( 'Timeframe', 'pmc-plugins' ); ?></th>
						<th scope="col" class="manage-column column-lazy_load"><?php esc_html_e( 'Lazy Load', 'pmc-plugins' ); ?></th>
						<th scope="col" class="manage-column column-provider_type"><?php esc_html_e( 'Ad Provider', 'pmc-plugins' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php if ( $ads ) :
						foreach ( $ads as $i => $ad ) :
							$data = $ad->post_content;
							?>

							<tr class="adm-row-ad <?php echo ( $i % 2 ) ? 'alternate' : ''; ?>"
								data-id="<?php echo esc_attr( $ad->ID ); ?>"
								data-provider="<?php echo esc_attr( $data['provider'] ); ?>">
								<td class="column-export-checkbox">
									<input
										class="ad-post-cb"
										type="checkbox"
										name="ad_post[]"
										value="<?php echo intval( $ad->ID ) ?>">
								</td>
								<td class="column-key">
									<?php if ( ! empty( $data['ad-image'] ) ) : ?>
										<span class="media-icon image-icon">
											<img src="<?php echo esc_url( $data['ad-image'] ); ?>"/>
										</span>
									<?php endif; ?>
									<strong class="has-ad-media"><?php echo esc_html( $ad->post_title ); ?></strong>
									<div class="row-actions">
										<span class="edit">
											<a href="javascript:;" class="adm-ajax-edit"><?php esc_html_e( 'Edit', 'pmc-plugins' ); ?></a> | </span>
										<span class="delete">
											<a href="javascript:;" class="adm-ajax-delete"><?php esc_html_e( 'Delete', 'pmc-plugins' ); ?></a>
										</span>
									</div>
								</td>
								<td class="column-ad_type">
									<strong><?php echo esc_html( implode( ', ', (array) $data['device'] ) ); ?></strong><br>
									<?php echo esc_html( $data['width'] . 'x' . $data['height'] . ( ( isset( $data['slot-type'] ) ) ? '-' . $data['slot-type'] : '' ) ); ?>
								</td>
								<td class="column-ad_location">
									<?php echo esc_html( ( ! empty( $data['location'] ) ) ? ( ! empty( $manager->locations[ $data['location'] ]['title'] ) ? $manager->locations[ $data['location'] ]['title'] : $data['location'] ) : '' ); ?>
								</td>
								<td class="column-ad_status">
									<?php
										$status = ( ! empty( $data['status'] ) ) ? $data['status'] : __( 'Active', 'pmc-plugins' );
										echo esc_html( $status );
									?>
								</td>
								<td class="column-priority">
									<?php echo esc_html( $data['priority'] ); ?>
								</td>
								<td class="column-conditions">
									<?php

									if ( ! empty( $data['ad_conditions'] ) && is_array( $data['ad_conditions'] ) ) {
										echo wp_kses_post( PMC_Ad_Conditions::get_instance()->display( $data['ad_conditions'], $data ) );
									}

									?>
								</td>
								<td class="column-timeframe">
									<?php
									if ( ! empty( $data['start'] ) && ! empty( $data['end'] ) ) :
										echo esc_html( $data['start'] . ' - ' . $data['end'] );
									endif;
									?>
								</td>
								<td class="column-lazy_load">
									<?php if ( ! empty( $data['is_lazy_load'] ) && 'yes' === $data['is_lazy_load'] ) : ?>
										<?php esc_html_e( 'Y', 'pmc-plugins' ); ?>
									<?php endif; ?>
								</td>
								<td class="column-provider_type">
									<?php echo esc_html( ucwords( str_replace( '-', ' ', $data['provider'] ) ) ); ?>
								</td>
							</tr>

						<?php endforeach; ?>
					<?php else : ?>

						<tr>
							<td colspan="5" class="no-results">
								<?php esc_html_e( 'No ads have been defined.', 'pmc-plugins' ); ?>
								<br>
								<?php if ( empty( $providers ) ) :
									esc_html_e( 'A provider must be supplied before creating an ad.', 'pmc-plugins' );
								endif; ?>
							</td>
						</tr>

					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
