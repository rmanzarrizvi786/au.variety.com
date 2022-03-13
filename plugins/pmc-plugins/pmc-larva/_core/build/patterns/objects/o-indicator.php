<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<span class="o-indicator <?php echo esc_attr( $o_indicator_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_icon ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-icon', $c_icon, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_span ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-span', $c_span, true ); ?>
	<?php } ?>
</span>
