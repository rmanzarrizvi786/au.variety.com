<?php
/**
 * Template part prtin issue settings admin options.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<div id="variety-print-issue-setting" class="wrap">

	<?php \Variety\Plugins\Variety_Print_Issue\Print_Issue_Setting::nonce_field(); ?>

	<div class="icon32" id="icon-options-general"></div>

	<h2><?php esc_html_e( 'Print Syndication Setting', 'pmc-variety' ); ?></h2>

	<div class="variety-panel">
		<label>
			<?php esc_html_e( 'Schedule for future automate volume & issue number increment', 'pmc-variety' ); ?>
		</label>
		<div class="variety-hint">
			<?php esc_html_e( 'These schedules are used to automate the issue and volume numbering creation for print syndication on a future date moving forward.', 'pmc-variety' ); ?>
			<br/>
			<?php esc_html_e( 'Note: outdated information will be automatically removed, except for one locked record.', 'pmc-variety' ); ?>
		</div>
		<table class='variety-volume-schedule'>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date affected', 'pmc-variety' ); ?></th>
					<th><?php esc_html_e( 'Volume No.', 'pmc-variety' ); ?></th>
					<th><?php esc_html_e( 'Issue No.', 'pmc-variety' ); ?></th>
				</tr>
			</thead>
			<tbody id="variety-data-grid">
			</tbody>
			<tfoot>
				<tr>
					<td>
						<span style="z-index: 0;" id="variety-date-hint" class="variety-hint">
							<?php esc_html_e( 'YYYY-MM-DD', 'pmc-variety' ); ?>
						</span>
						<input style="z-index: 1" id="variety-input-date" class="variety-date">
					</td>
					<td>
						<input id="variety-input-volume" class="variety-volume">
					</td>
					<td>
						<input id="variety-input-issue" value="1" class="variety-issue">
					</td>
					<td>
						<input id="variety-btn-add" type="button" class="button" value="Add"/>
					</td>
				</tr>
			</tfoot>
		</table>

		<label>
			<?php esc_html_e( 'Showing alert message to following users', 'pmc-variety' ); ?>
		</label>
		<div>
			<textarea id="variety-input-user-list"></textarea>
			<div class="variety-hint">
				<?php esc_html_e( 'Enter user login separated by space, commas, or line break', 'pmc-variety' ); ?>
			</div>
		</div>
		<div style="clear: both"></div>
		<div class="variety-buttons">
			<input id="variety-btn-save" type="submit" class="button" value="Save"/>
		</div>
	</div>
	<div id="variety-setting-message" class="message updated variety-hide"></div>
</div>
