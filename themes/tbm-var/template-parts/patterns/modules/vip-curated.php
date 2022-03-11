<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="vip-curated // <?php echo esc_attr( $vip_curated_classes ?? '' ); ?>">
	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>

	<div class="vip-curated__stories // <?php echo esc_attr( $vip_curated_stories_classes ?? '' ); ?>">
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease.php', $o_tease_primary, true ); ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list, true ); ?>
	</div>
	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
</section>
