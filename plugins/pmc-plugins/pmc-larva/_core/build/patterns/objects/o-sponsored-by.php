<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="o-sponsored-by <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_sponsored_by_classes ?? '' ); ?>">
	<span class="<?php echo esc_attr( $o_sponsored_by_title_classes ?? '' ); ?>"><?php echo esc_html( $o_sponsored_by_text ?? '' ); ?></span>

	<?php if ( ! empty( $c_lazy_image ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-lazy-image', $c_lazy_image, true ); ?>
	<?php } ?>
</div>
