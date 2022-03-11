<?php
/**
 * Vcategory Add Term fields.
 *
 * Note that "vcategory" is also labelled as "Playlist."
 *
 * This extends the vcategory Add Term screen found at
 * wp-admin/edit-tags.php?taxonomy=vcategory
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

?>
<div class="form-field term-group vcat-image">
	<label for="vcat-image-id"><?php esc_html_e( 'Playlist Image', 'pmc-variety' ); ?></label>
	<input type="hidden" id="vcat-image-id" name="vcat-image-id" class="image-id" value="">
	<div id="vcat-image-wrapper" class="img-wrapper"></div>
	<p>
		<input type="button" class="button button-secondary btn-add" id="vcat-btn-add" value="<?php esc_html_e( 'Add Image', 'pmc-variety' ); ?>" />
		<input type="button" class="button button-secondary btn-remove hidden" id="vcat-btn-remove" value="<?php esc_html_e( 'Remove Image', 'pmc-variety' ); ?>" />
	</p>
	<?php wp_nonce_field( $nonce['action'], $nonce['name'] ); ?>
</div>
<style>
	.img-wrapper img {
		width: 300px;
		max-width: 90%;
		height: auto;
		border: 1px solid #ddd;
	}
</style>
