<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

if ( $notices ) {
	echo "<div class='notice notice-info'><ul>";
	foreach ( $notices as $notice ) {
		echo '<li>' . esc_html( $notice ) . '</li>';
	}
	echo '</ul></div>';
}

if ( is_array( $sailthru_errors ) && ! empty( $sailthru_errors ) ) {

	echo "<div class='error notice-info'><ul>";

	foreach ( $sailthru_errors as $sailthru_error ) {
		echo '<li>' . esc_html( $sailthru_error ) . '</li>';
	}

	echo '</ul></div>';
}
?>
<form method="post" action="<?php echo esc_url( menu_page_url( 'sailthru_fast_newsletter', false )) . $sailthru_edit_type ; ?>">
	<table id="mmc_breakingnews_edit_table">
		<tr>
			<td><label for="name">Name</label></td>
			<td>
				<input type="text" name="name" id="name"
					   value="<?php echo isset( $sailthru_edit_name ) ? stripslashes( esc_attr( $sailthru_edit_name ) ) : "" ?>"/>
			</td>
		</tr>
		<tr class="odd">
			<td class="label"><label for="dataextension">Data Extension</label></td>
			<td class="control">
				<select name="dataextension" id="dataextension">
					<?php foreach ( $sailthru_dataextension as $value => $name ) {
						echo "<option value=\"". esc_attr( $value ) . "\"";
						selected( $sailthru_item['dataextension'], $value );
						echo ">" . esc_html( $name ) . "</option>";
					}?>
				</select>
			</td>
		</tr>
		<tr class="odd">
			<td class="control">
				<?php
				$email_name = '';
				if ( ! empty( $sailthru_item['email_name'] ) ) {
					$email_name = $sailthru_item['email_name'];
				} ?>
				<input type="hidden" name="email_name" value="<?php echo esc_attr( $email_name ); ?>">
			</td>
		</tr>
		<tr>
			<td><label for="subject">Subject</label></td>
			<td>
				<input type="text" name="subject"
					   value="<?php echo isset( $sailthru_item ) && isset( $sailthru_item['subject'] ) ? stripslashes( esc_attr( $sailthru_item['subject'] ) ) : "" ?>"/>
			</td>
		</tr>
		<tr>
			<td><label for="content_builder">Content Builder</label></td>
			<td>
				<select name="content_builder" id="content_builder">
						<option value="yes" <?php echo ( ! empty( $sailthru_item['content_builder'] ) && 'yes' === $sailthru_item['content_builder'] ) ? 'selected' : ''; ?> >Yes</option>
						<option value="no" <?php echo ( empty( $sailthru_item['content_builder'] ) || 'yes' !== $sailthru_item['content_builder'] ) ? 'selected' : ''; ?> >No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="template">HTML Template</label>
				<label for="content_builder_template">HTML Template <br> <strong>( Content Builder )</strong></label>
			</td>
			<td>
				<select name="template" id="template">
					<?php foreach ( $sailthru_templates as $value => $name ) {
						echo "<option value=\"" . esc_attr( $value ) . "\"";
						selected( $sailthru_item['template'], $value );
						echo ">" . esc_html( $name ) . "</option>";
					} ?>
				</select>
				<select name="content_builder_template" id="content_builder_template">
					<?php
					foreach ( $content_builder_templates as $template_id => $name ) {
						printf(
							'<option value="%s" %s >%s</option>',
							esc_attr( $template_id ),
							selected( $sailthru_item['template'], $template_id ),
							esc_html( $name )
						);
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="tags">Tag Name(Exact)</label>
			</td>
			<td>
				<input type="text" name='post_tag_name' value="<?php if( isset( $sailthru_item['post_tag_name'] ) ) echo esc_attr( $sailthru_item['post_tag_name'] ); ?>" />
			</td>
		</tr>
		<tr>
			<?php
			if( isset( $sailthru_item['pmc_newsletter_alert_senddefinition'] ) ){
				$alert_classification = $sailthru_item['pmc_newsletter_alert_senddefinition'];
			}else{
				$alert_classification = 0; // default value for the send definition.
			}
			?>
			<td>Newsletter Alert Send Definition</td>
			<td><select name="pmc_newsletter_alert_senddefinition" id="pmc_newsletter_alert_senddefinition">
				<option value="0" <?php selected( $alert_classification, 0 ); ?> >Please Select a Senddefinition</option>
				<?php foreach ( $et_sendclassification as $value => $name ) {
				echo "<option value=\"" . esc_attr( $value ) . "\"";
				selected( $alert_classification, $value );
				echo ">" . esc_html( $name ) . "</option>";
			} ?>
			</select></td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="hidden" name="_mmcnws_addeditbna_nonce" value="<?php print( $mmcnws_nonce ); ?>"/>
				<input type="hidden" name="old_name" value="<?php echo isset( $sailthru_edit_name ) ? stripslashes( esc_attr( $sailthru_edit_name ) ) : "" ?>"/>
				<input type="submit" value="Submit"/>
			</td>
		</tr>

	</table>
</form>
