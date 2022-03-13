<div class="wrap">
	<h2>Newsletter Settings</h2>
	<?php if ( $sailthru_errors ) {
	echo "<ul class='error'>";
	foreach ( $sailthru_errors as $error ) {
		echo "<li>". esc_html( $error ) ."</li>";
	}
	echo "</ul>";
} else if ( $sailthru_success ) {
	echo '<div class="updated fade">' . esc_html( $sailthru_success ) . '</div>';
}

	?>
	<fieldset class="mmcnws_admin_box" name="fldst_nwsltr_settings">
		<form method="post" action="<?php menu_page_url( 'sailthru_newsletter_settings' );?>">
			<h3 class="title">Newsletter Thumbnail:</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="mmcnewsletter_thumb_width">Newsletter Thumbnail Width</label></th>
					<td>
						<input type="text" name="mmcnewsletter_thumb_width" id="mmcnewsletter_thumb_width"
							   value="<?php echo esc_attr( $sailthru_item['mmcnewsletter_thumb_width'] ); ?>" maxlength="3"
							   style="width:50px;"/> px
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mmcnewsletter_thumb_height">Newsletter Thumbnail Height</label></th>
					<td>
						<input type="text" name="mmcnewsletter_thumb_height" id="mmcnewsletter_thumb_height"
							   value="<?php echo esc_attr( $sailthru_item['mmcnewsletter_thumb_height'] ); ?>" maxlength="3"
							   style="width:50px;"/> px
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mmcnewsletter_thumb_src">Newsletter Thumbnail Source</label></th>
					<td>
						<select name="mmcnewsletter_thumb_src" id="mmcnewsletter_thumb_src">
							<?php pmc_newsletter_create_options( $mmcnws_thumb_src, $sailthru_item['mmcnewsletter_thumb_src'], $mmcnws_thumb_src_optgrp ); ?>
						</select>
					</td>
				</tr>
			</table>
			<br/>

			<h3 class="title">Newsletter Featured Post Image:</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="mmcnewsletter_feature_image_width">Featured Post Image Width</label>
					</th>
					<td>
						<input type="text" name="mmcnewsletter_feature_image_width"
							   id="mmcnewsletter_feature_image_width"
							   value="<?php echo esc_attr( $sailthru_item['mmcnewsletter_feature_image_width'] ); ?>" maxlength="3"
							   style="width:50px;"/> px
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mmcnewsletter_feature_image_height">Featured Post Image Height</label>
					</th>
					<td>
						<input type="text" name="mmcnewsletter_feature_image_height"
							   id="mmcnewsletter_feature_image_height"
							   value="<?php echo esc_attr( $sailthru_item['mmcnewsletter_feature_image_height'] ); ?>" maxlength="3"
							   style="width:50px;"/> px
					</td>
				</tr>
			</table>
			<br/>

			<p>

			<h3 class="title">Global Default Image:</h3>
			<input type="text" name="global_default_image" id="default_thumbnail_src"
				   value="<?php echo esc_url( $sailthru_item['global_default_image'] ); ?>"/>
			<input type="button" value="upload new image" id="upload_default_thumbnail_src">
			<br style="clear:both">
			<input type="hidden" name="update_mmcnewsletter_thumb" value="update"/>
			<input type="hidden" name="_mmcnws_settings_nonce" value="<?php echo esc_attr( $mmcnws_nonce ); ?>"/>
			<input class="button-primary" type="submit" name="btn_settings" id="btn_settings"
				   value=" Update Settings "/>
			</p>
			<p>

			<h3 class="title">Newsletter Senddefinition:</h3>
			<select name="pmc_newsletter_senddefinition" id="pmc_newsletter_senddefinition">
				<?php foreach ( $et_sendclassification as $value => $name ) {
					echo "<option value=\"" . esc_attr( $value ) . "\"";
					selected( $sailthru_item['pmc_newsletter_senddefinition'], $value );
					echo ">" . esc_html( $name ) . "</option>";
				} ?>
			</select>

			<h3 class="title">Alert Senddefinition:</h3>
			<select name="pmc_alert_senddefinition" id="pmc_alert_senddefinition">
				<?php foreach ( $et_sendclassification as $value => $name ) {
					echo "<option value=\"" . esc_attr( $value ) . "\"";
					selected( $sailthru_item['pmc_alert_senddefinition'], $value );
					echo ">" . esc_html( $name ) . "</option>";
				} ?>
			</select>
			</p>
			<h3 class="title">Endpoint API Token:</h3>
			<input name="pmc_newsletter_api_token" id="pmc_newsletter_api_token" value="<?php echo esc_attr( $sailthru_item['pmc_newsletter_api_token'] ); ?>"/>
			</p>
		</form>
	</fieldset>
</div>
