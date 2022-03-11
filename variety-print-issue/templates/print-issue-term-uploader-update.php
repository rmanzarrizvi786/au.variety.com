<?php
/**
 * Template part for Print Issue term uploader - update terms.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<tr class="form-field term-group-wrap">
	<th scope="row">
		<label for="print-issue-image-id"><?php esc_html_e( 'Issue Cover Image', 'pmc-variety' ); ?></label>
	</th>
	<td>
		<input type="hidden" id="print-issue-image-id" name="print-issue-image-id" value="<?php echo esc_html( $image_id ); ?>">
		<div id="print-issue-image-wrapper">
			<?php if ( ! empty( $img_src ) ) : ?>
				<img src="<?php echo esc_url( $img_src ); ?>" />
			<?php endif; ?>
		</div>
		<p>
			<input type="button" class="button button-secondary <?php echo esc_attr( empty( $image_id ) ? '' : 'hidden' ); ?>" id="print-issue-btn-add" value="<?php esc_html_e( 'Add Cover', 'pmc-variety' ); ?>" />
			<input type="button" class="button button-secondary <?php echo esc_attr( empty( $image_id ) ? 'hidden' : '' ); ?>" id="print-issue-btn-remove" value="<?php esc_html_e( 'Remove Cover', 'pmc-variety' ); ?>" />
		</p>
		<?php wp_nonce_field( $image_nonce_action, $image_nonce_name ); ?>
	</td>
</tr>
<style>
	#print-issue-image-wrapper img {
		width: 300px;
		max-width: 90%;
		height: auto;
		border: 1px solid #ddd;
	}
</style>
