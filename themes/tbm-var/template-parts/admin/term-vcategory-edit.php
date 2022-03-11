<?php
/**
 * Vcategory Edit Term additional fields.
 *
 * Note that "vcategory" is also labelled as "Playlist."
 *
 * This extends the vcategory Edit Term screen found at
 * wp-admin/term.php?taxonomy=vcategory&tag_ID={TERM_ID}
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

// Validate options.
$values = array(
	'playlist' => array(
		'img_id'  => ! empty( $options['playlist']['img_id'] ) ? $options['playlist']['img_id'] : '',
		'img_src' => ! empty( $options['playlist']['img_src'] ) ? $options['playlist']['img_src'] : '',
	),
	'sponsor'  => array(
		'text'    => ! empty( $options['sponsor']['text'] ) ? $options['sponsor']['text'] : '',
		'name'    => ! empty( $options['sponsor']['name'] ) ? $options['sponsor']['name'] : '',
		'link'    => ! empty( $options['sponsor']['link'] ) ? $options['sponsor']['link'] : '',
		'img_id'  => ! empty( $options['sponsor']['img_id'] ) ? $options['sponsor']['img_id'] : '',
		'img_src' => ! empty( $options['sponsor']['img_src'] ) ? $options['sponsor']['img_src'] : '',
	),
);
?>
<tr class="form-field term-group-wrap vcat-image">
	<th scope="row">
		<label for="vcat-image-id"><?php esc_html_e( 'Playlist Image', 'pmc-variety' ); ?></label>
	</th>
	<td>
		<input type="hidden" id="vcat-image-id" name="vcat-image-id" class="image-id" value="<?php echo esc_html( $values['playlist']['img_id'] ); ?>">
		<div id="vcat-image-wrapper" class="img-wrapper">
			<?php if ( ! empty( $values['playlist']['img_src'] ) ) : ?>
				<img src="<?php echo esc_url( $values['playlist']['img_src'] ); ?>" />
			<?php endif; ?>
		</div>
		<p>
			<input type="button" class="button button-secondary btn-add <?php echo esc_attr( empty( $values['playlist']['img_id'] ) ? '' : 'hidden' ); ?>" id="vcat-btn-add" value="<?php esc_html_e( 'Add Image', 'pmc-variety' ); ?>" />
			<input type="button" class="button button-secondary btn-remove <?php echo esc_attr( empty( $values['playlist']['img_id'] ) ? 'hidden' : '' ); ?>" id="vcat-btn-remove" value="<?php esc_html_e( 'Remove Image', 'pmc-variety' ); ?>" />
		</p>
	</td>
</tr>
<tr class="form-field term-group-wrap">
	<th scope="row">
		<label for="vcat-sponsored-text"><?php esc_html_e( 'Sponsor Text', 'pmc-variety' ); ?></label>
	</th>
	<td>
		<input type="text" id="vcat-sponsored-text" name="vcat-sponsored-text" placeholder="<?php echo esc_attr( __( 'powered by', 'pmc-variety' ) ); ?>" value="<?php echo esc_html( $values['sponsor']['text'] ); ?>" />
	</td>
</tr>
<tr class="form-field term-group-wrap">
	<th scope="row">
		<label for="vcat-sponsor-name"><?php esc_html_e( 'Sponsor Name', 'pmc-variety' ); ?></label>
	</th>
	<td>
		<input type="text" id="vcat-sponsor-name" name="vcat-sponsor-name" value="<?php echo esc_html( $values['sponsor']['name'] ); ?>" />
	</td>
</tr>
<tr class="form-field term-group-wrap">
	<th scope="row">
		<label for="vcat-sponsor-link"><?php esc_html_e( 'Sponsor Link', 'pmc-variety' ); ?></label>
	</th>
	<td>
		<input type="text" id="vcat-sponsor-link" name="vcat-sponsor-link" value="<?php echo esc_html( $values['sponsor']['link'] ); ?>" />
	</td>
</tr>
<tr class="form-field term-group-wrap vcat-logo">
	<th scope="row">
		<label for="vcat-logo-id"><?php esc_html_e( 'Sponsor Logo', 'pmc-variety' ); ?></label>
	</th>
	<td>
		<input type="hidden" id="vcat-logo-id" name="vcat-logo-id" class="image-id" value="<?php echo esc_html( $values['sponsor']['img_id'] ); ?>">
		<div id="vcat-logo-wrapper" class="img-wrapper">
			<?php if ( ! empty( $values['sponsor']['img_src'] ) ) : ?>
				<img src="<?php echo esc_url( $values['sponsor']['img_src'] ); ?>" />
			<?php endif; ?>
		</div>
		<p>
			<input type="button" class="button button-secondary btn-add <?php echo esc_attr( empty( $values['sponsor']['img_id'] ) ? '' : 'hidden' ); ?>" id="vcat-btn-add" value="<?php esc_html_e( 'Add Logo', 'pmc-variety' ); ?>" />
			<input type="button" class="button button-secondary btn-remove <?php echo esc_attr( empty( $values['sponsor']['img_id'] ) ? 'hidden' : '' ); ?>" id="vcat-btn-remove" value="<?php esc_html_e( 'Remove Logo', 'pmc-variety' ); ?>" />
		</p>
		<?php wp_nonce_field( $nonce['action'], $nonce['name'] ); ?>
	</td>
</tr>
<style>
	.img-wrapper img {
		width: 300px;
		max-width: 90%;
		height: auto;
		border: 1px solid #ddd;
	}
</style>
