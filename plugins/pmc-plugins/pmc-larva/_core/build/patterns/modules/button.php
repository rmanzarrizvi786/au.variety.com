<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<a class="button larva // <?php echo esc_attr( $button_classes ?? '' ); ?> <?php echo esc_attr( $button_typography_class ?? '' ); ?> <?php echo esc_attr( $button_background_color_class ?? '' ); ?> <?php echo esc_attr( $button_color_class ?? '' ); ?> <?php echo esc_attr( $button_width_class ?? '' ); ?>"
	<?php if ( ! empty( $button_url ) ) { ?>
		href="<?php echo esc_url( $button_url ?? '' ); ?>"
	<?php } ?>
>
	<?php echo wp_kses_post( $button_markup ?? '' ); ?>
</a>
