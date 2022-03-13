<?php
$ad_conditions = PMC_Ads::get_instance()->get_ad_property( 'ad_conditions', $ad );
$logical_operator = PMC_Ads::get_instance()->get_ad_property( 'logical_operator', $ad );
?>
<div class="adm-column-3">

	<fieldset class="adm-input adm-conditions">
		<legend>
			<strong>
				<?php echo esc_html_e( 'Dynamic Ad Unit Format Conditions', 'pmc-plugins' ); ?>
			</strong>
			<?php esc_html_e( 'Time Gap (optional)', 'pmc-plugins' ); ?>
		</legend>

		<?php esc_html_e( 'Possible values for the is_country option are:', 'pmc-plugins' ); ?>
		<br>
		<?php esc_html_e( ' "US" (N. America), "RU" (Russia), or "GB" (Great Britain)', 'pmc-plugins' ); ?>
		<br>
		<input
			id="pmc-adm-condition-input"
			type="hidden"
			name="pmc_ad_condition_json"
			value="<?php echo esc_attr( wp_json_encode( $ad_conditions ) ); ?>"
		/>
		<div id="pmc-adm-condition-form"></div>
		<div id="pmc-adm-condition-display" class="<?php echo esc_attr( $logical_operator ); ?>"></div>

		<strong><?php esc_html_e( 'Logical operations type:', 'pmc-plugins' ); ?></strong>

		<select id="pmc-adm-condition-logical-operator" name="pmc-adm-condition-logical-operator">
			<option value="or" <?php selected( $logical_operator, 'or' ) ?>><?php esc_html_e( 'OR', 'pmc-plugins' ); ?></option>
			<option value="and" <?php selected( $logical_operator, 'and' ) ?>><?php esc_html_e( 'AND', 'pmc-plugins' ); ?></option>
		</select>

	</fieldset>
</div>
