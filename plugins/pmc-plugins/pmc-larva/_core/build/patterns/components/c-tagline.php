<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_tagline_text ) ) { ?>
	<p class="c-tagline <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_tagline_classes ?? '' ); ?>"><?php echo esc_html( $c_tagline_text ?? '' ); ?></p>
<?php } ?>

<?php if ( ! empty( $c_tagline_markup ) ) { ?>
	<div class="c-tagline <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_tagline_classes ?? '' ); ?>">
		<?php echo wp_kses_post( $c_tagline_markup ?? '' ); ?>
	</div>
<?php } ?>
