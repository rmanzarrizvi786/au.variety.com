<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<span class="c-span <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_span_classes ?? '' ); ?>">
<?php if ( ! empty( $c_span_url ) ) { ?>
	<a href="<?php echo esc_url( $c_span_url ?? '' ); ?>" class="c-span__link <?php echo esc_attr( $c_span_link_classes ?? '' ); ?>">
<?php } ?>

	<?php echo esc_html( $c_span_text ?? '' ); ?>

<?php if ( ! empty( $c_span_url ) ) { ?>
	</a>
<?php } ?>
</span>
