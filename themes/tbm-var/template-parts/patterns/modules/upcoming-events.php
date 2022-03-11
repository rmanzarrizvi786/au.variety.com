<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="upcoming-events // <?php echo esc_attr( $upcoming_events_classes ?? '' ); ?>">
	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_heading, true ); ?>

	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>

	<div class="upcoming-events__inner // <?php echo esc_attr( $upcoming_events_inner_classes ?? '' ); ?>">
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>

		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-dek.php', $c_dek, true ); ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
	</div>
</section>
