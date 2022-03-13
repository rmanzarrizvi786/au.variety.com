<div class="wrap pmc-export-container" id="pmc-export-posts">
	<div id="poststuff">

		<div id="postbox-container-1" class="postbox-container">

			<div class="postbox">

				<h3 class="hndle ui-sortable-handle"><span><?php echo esc_html( $title ); ?></span></h3>

				<div class="inside">
					<form id="pmc-export-form" action='javascript:return false;' method='POST'>

						<div class="filter-section">
							<div><label for="post_type"><?php esc_html_e( 'Post type', 'pmc-export' ); ?></label></div>
							<select name="post_type" id="post_type">
								<?php
								foreach ( $post_types as $key => $value ) {
									echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
								}
								?>
							</select>
						</div>
						<div class="filter-section">
							<div><label for="date_filter"><?php esc_html_e( 'Date filter', 'pmc-export' ); ?></label></div>
							<select name="date_filter" id="date_filter">
								<?php
								foreach ( $dates_filter as $key => $value ) {
									echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
								}
								?>
							</select>
						</div>
						<div class="filter-section">
							<div><label for="date_filter"><?php esc_html_e( 'Reporting Fields', 'pmc-export' ); ?></label></div>
							<div>
							<select data-placeholder="All fields by default.." name="reporting_fields_filter" id="reporting_fields_filter" multiple size="1">
								<?php

								$default_fields = ! empty( $default_fields ) ? $default_fields : [];

								foreach ( $reporting_fields_filter as $value ) {

									$selected = '';

									if ( in_array( $value, (array) $default_fields, true ) ) {
										$selected = 'selected';
									}

									printf(
										'<option %s value="%s">%s</option>',
										esc_attr( $selected ),
										esc_attr( $value ),
										esc_html( $value )
									);
								}
								?>
							</select>
							</div>

						</div>

						<div class="clear"></div>

						<div class="submit">
							<?php submit_button( __( 'Start Download', 'pmc-export' ) ); ?>

							<div id="progress-bar"></div>
							<div class="spin-loader"></div>
						</div>

						<div id="download-links"></div>

					</form>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
