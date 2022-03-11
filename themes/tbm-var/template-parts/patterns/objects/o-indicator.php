<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<span class="o-indicator <?php echo esc_attr( $o_indicator_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_icon ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_span ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
	<?php } ?>
</span>
