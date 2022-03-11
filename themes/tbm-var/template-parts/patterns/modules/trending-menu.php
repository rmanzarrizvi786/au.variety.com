<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="trending-menu // <?php echo esc_attr( $trending_menu_classes ?? '' ); ?>">
	<div class="trending-menu__inner // <?php echo esc_attr( $trending_menu_inner_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_span ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
		<?php } ?>

		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true ); ?>
	</div>
</div>
