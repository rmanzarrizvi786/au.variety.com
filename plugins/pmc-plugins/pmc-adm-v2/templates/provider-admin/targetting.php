<div class="adm-column-3">
	<fieldset class="adm-input adm-targeting_data">
		<legend>
			<strong><?php esc_html_e( 'Targeting Key/Values', 'pmc-plugins' ); ?></strong>
			<?php esc_html_e( ' (optional)', 'pmc-plugins' ); ?>
		</legend>

		<?php
		$targeting_data = PMC_Ads::get_instance()->get_ad_property( 'targeting_data', $ad, array(
			array(
				'key'   => '',
				'value' => ''
			)
		) );
		foreach ( $targeting_data as $i => $target_data ) {
			?>
			<div class="adm-target_data<?php if ( 0 === $i ) {
				echo ' primary-target_data';
			} ?>">
				<table>
					<tr>
						<td>
							<label for="<?php echo esc_attr( 'key-' . $i ); ?>">
								<?php esc_html_e( 'Key:', 'pmc-plugins' ); ?>
							</label>&nbsp;&nbsp;
							<br>
							<input
								type="text"
								id="<?php echo esc_attr( 'key-' . $i ); ?>"
								name="targeting_data[key][]"
								placeholder="position"
								value="<?php echo esc_attr( $target_data['key'] ); ?>">
						</td>
						<td>
							<label for="<?php echo esc_attr( 'value-' . $i ); ?>">
								<?php esc_html_e( 'Value: ', 'pmc-plugins' ); ?>
							</label>
							<br>
							<input
								type="text"
								id="<?php echo esc_attr( 'value-' . $i ); ?>"
								name="targeting_data[value][]"
								placeholder="bottom"
								value="<?php echo esc_attr( $target_data['value'] ); ?>">
						</td>
					</tr>
				</table>
				<button type="button" class="del-x"><?php esc_html_e( 'X', 'pmc-plugins' ); ?></button>
			</div>
			<?php
		}
		?>
		<span
			class="description"><?php esc_html_e( 'If GPT targeting value is ["bottom"] then you would enter bottom as the value here.  Multiple values are not supported at this time.', 'pmc-plugins' ); ?></span>
	</fieldset>

	<button type="button" class="adm-new-targeting_data button"><?php esc_html_e( 'New Targeting Key/Value', 'pmc-plugins' ); ?></button>
</div>