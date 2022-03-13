<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_dek_text ) ) { ?>
	<p class="c-dek <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_dek_classes ?? '' ); ?>"><?php echo esc_html( $c_dek_text ?? '' ); ?></p>
<?php } ?>

<?php if ( ! empty( $c_dek_markup ) ) { ?>
	<p class="c-dek <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_dek_classes ?? '' ); ?>">
		<?php echo wp_kses_post( $c_dek_markup ?? '' ); ?>
	</p>
<?php } ?>
