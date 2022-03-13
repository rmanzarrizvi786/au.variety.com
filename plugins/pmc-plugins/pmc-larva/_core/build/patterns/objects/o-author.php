<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="o-author // <?php echo esc_attr( $o_author_classes ?? '' ); ?>">

	<span class="<?php echo esc_attr( $o_author_by_classes ?? '' ); ?>"><?php echo esc_html( $o_author_text ?? '' ); ?></span>

	<?php if ( ! empty( $c_span ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-span', $c_span, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_timestamp ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-timestamp', $c_timestamp, true ); ?>
	<?php } ?>

</div>
