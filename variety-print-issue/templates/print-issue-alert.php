<?php
/**
 * Template part for Print Issue Alert.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<div id='variety-print-issue-alert-overlay'></div>
<div id="variety-print-issue-alert">

	<form method="post">
	<?php \Variety\Plugins\Variety_Print_Issue\Print_Issue_Setting::nonce_field(); ?>
		<input id="variety-print-info-slug" type="hidden" name="print-info[slug]"
			   value="<?php echo esc_attr( $esc_print_slug ); ?>"/>
		<input id="variety-print-info-term-id" type="hidden" name="print-info[term_id]"
			   value="<?php echo esc_attr( $esc_term_id ); ?>"/>

		<div>
			<?php
			/* translators: Shows syndicated date. */
			echo esc_html( sprintf( __( 'Please confirm that the following details are correct for the print issue that will be syndicated %s evening.', 'pmc-variety' ), $syndicate_datestr ) );
			?>
		</div>

		<div id="variety-prompt-decide">
			<table>
				<tr>
					<th><?php esc_html_e( 'Volume:', 'pmc-variety' ); ?></th>
					<td><?php echo esc_html( $esc_volume ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Issue:', 'pmc-variety' ); ?></th>
					<td><?php echo esc_html( $esc_issue ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Date:', 'pmc-variety' ); ?></th>
					<td><?php echo esc_html( $datestr ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Display:', 'pmc-variety' ); ?></th>
					<td><?php echo esc_html( $esc_name_html ); ?></td>
				</tr>
			</table>

			<div>
				<?php esc_html_e( 'Are these details correct?', 'pmc-variety' ); ?>
			</div>
			<input id="variety-btn-yes" type="button" class="button button-primary" name="print-info[correct]"
				   value="<?php esc_attr_e( 'Yes', 'pmc-variety' ); ?> "/>
			<input id="variety-btn-no" type="button" class="button button-secondary" value="<?php esc_attr_e( 'No', 'pmc-variety' ); ?>"/>
		</div>

		<div id="variety-prompt-update" style="display: none">
			<table>
				<tr>
					<th><?php esc_html_e( 'Volume:', 'pmc-variety' ); ?></th>
					<td>
						<input id="variety-print-info-volume" type='text' name="print-info[volume]"
							   value="<?php echo esc_attr( $esc_volume ); ?>">
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Issue:', 'pmc-variety' ); ?></th>
					<td>
						<input id="variety-print-info-issue" type='text' name="print-info[issue]"
							   value="<?php echo esc_attr( $esc_issue ); ?>">
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Date:', 'pmc-variety' ); ?></th>
					<td>
						<input id="variety-print-info-date" type='text' name="print-info[date]"
							   value="<?php echo esc_attr( $esc_datestr ); ?>">
						<span class="variety-hint">
							<?php esc_html_e( '(YYYY-MM-DD)', 'pmc-variety' ); ?>
						</span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Display:', 'pmc-variety' ); ?></th>
					<td>
						<input id="variety-print-info-name" type='text' name="print-info[name]"
							   value="<?php echo esc_attr( $esc_name ); ?>">
						<span class="variety-hint">
							<?php esc_html_e( 'Blank to auto create: From the [Month] [Day], [Year] issue of Variety', 'pmc-variety' ); ?>
						</span>
					</td>
				</tr>
			</table>
			<input id="variety-btn-update" class="button button-primary" type="button" name="print-info[action]"
				   value="<?php esc_attr_e( 'Update', 'pmc-variety' ); ?>"/>
			<input id="variety-btn-cancel" class="button button-secondary" type="reset" value="<?php esc_attr_e( 'Cancel', 'pmc-variety' ); ?>"/>
		</div>
	</form>
</div>
