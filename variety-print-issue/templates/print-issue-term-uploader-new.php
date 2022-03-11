<?php
/**
 * Template part for Print Issue term uploader - new terms.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<style>
	#print-issue-image-wrapper img {
		width: 300px;
		max-width: 90%;
		height: auto;
		border: 1px solid #ddd;
	}
</style>
<div class="form-field term-group">
	<label for="print-issues-image-id">
		<?php esc_html_e( 'Issue Cover Image', 'pmc-variety' ); ?>
	</label>
	<input type="hidden" id="print-issue-image-id" name="print-issue-image-id" value="">
	<div id="print-issue-image-wrapper"></div>
	<p>
		<input type="button" class="button button-secondary" id="print-issue-btn-add" value="<?php esc_html_e( 'Add Cover', 'pmc-variety' ); ?>" />
		<input type="button" class="button button-secondary" id="print-issue-btn-remove" value="<?php esc_html_e( 'Remove Cover', 'pmc-variety' ); ?>" />
	</p>
	<?php wp_nonce_field( $image_nonce_action, $image_nonce_name ); ?>
</div>
