<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<figcaption class="c-figcaption <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_figcaption_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_figcaption_inner ) ) { ?>
		<div class="c-figcaption__inner <?php echo esc_attr( $c_figcaption_inner_classes ?? '' ); ?>">
	<?php } ?>

		<?php if ( ! empty( $c_figcaption_caption_markup ) ) { ?>
			<span class="<?php echo esc_attr( $c_figcaption_caption_classes ?? '' ); ?>"><?php echo wp_kses_post( $c_figcaption_caption_markup ?? '' ); ?></span>
		<?php } ?>
		<?php if ( ! empty( $c_figcaption_credit_text ) ) { ?>
			<cite class="<?php echo esc_attr( $c_figcaption_credit_classes ?? '' ); ?>"><?php echo esc_html( $c_figcaption_credit_text ?? '' ); ?></cite>
		<?php } ?>

	<?php if ( ! empty( $c_figcaption_inner ) ) { ?>
		</div>
	<?php } ?>
</figcaption>
