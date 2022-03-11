<?php
/**
 * Figure (image) template.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

// Set the credit and the caption.
if ( ! empty( $image_id ) ) {
	$image_credit  = pmc_get_photo_credit( $image_id );
	$image_caption = get_post_field( 'post_excerpt', $image_id );
}
?>
<figure class="c-figure <?php echo esc_attr( $image_align ); ?>" itemprop="image" itemtype="http://schema.org/ImageObject">

	<span class="c-figure__img-wrap">
		<?php echo wp_kses_post( $image_element ); ?>
	</span>

	<?php if ( 'feature' === $header_type ) { ?>

		<figcaption class="c-figure__desc">

			<div class="c-figure__caption"><?php esc_html_e( 'FEATURE', 'pmc-variety' ); ?></div>

			<?php if ( ! empty( $image_credit ) ) : ?>
				<div class="c-figure__credit"><?php esc_html_e( 'CREDIT', 'pmc-variety' ) ?>: <?php echo esc_html( $image_credit ); ?></div>
			<?php endif; ?>

		</figcaption>

	<?php } elseif ( ! empty( $image_caption ) || ! empty( $image_credit ) ) { ?>

		<figcaption class="c-figure__desc">

			<?php if ( ! empty( $image_credit ) ) { ?>
				<div class="c-figure__credit"><?php esc_html_e( 'CREDIT', 'pmc-variety' ) ?>: <?php echo esc_html( $image_credit ); ?></div>
			<?php } ?>

		</figcaption>

	<?php } ?>

</figure>
