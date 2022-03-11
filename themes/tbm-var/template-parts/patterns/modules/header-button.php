<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<a id="<?php echo esc_attr( $header_button_id_attr ?? '' ); ?>" href="<?php echo esc_url( $header_button_url ?? '' ); ?>" class="header-button // lrv-u-display-inline-block lrv-a-unstyle-button lrv-u-text-align-center lrv-u-line-height-normal lrv-u-cursor-pointer lrv-u-padding-tb-025 lrv-u-padding-lr-050 <?php echo esc_attr( $header_button_classes ?? '' ); ?>">
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_main, true ); ?>
	<?php if ( ! empty( $c_span_secondary ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_secondary, true ); ?>
	<?php } ?>
</a>
