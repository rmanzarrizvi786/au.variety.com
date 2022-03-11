<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="subscribe-cta // <?php echo esc_attr( $subscribe_cta_classes ?? '' ); ?>">
	<div class="subscribe-cta__inner // <?php echo esc_attr( $subscribe_cta_inner_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_span ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_more_link ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_more_link_desktop ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link_desktop, true ); ?>
		<?php } ?>
	</div>
</section>
