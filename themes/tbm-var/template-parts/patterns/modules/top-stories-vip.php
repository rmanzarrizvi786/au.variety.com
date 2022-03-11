<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="top-stories-vip // <?php echo esc_attr( $top_stories_classes ?? '' ); ?>">
	<?php foreach ( $top_stories ?? [] as $item ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-top-story.php', $item, true ); ?>

		<?php if ( $item['is_primary'] ) { ?>
			<div class="top-stories__secondary // <?php echo esc_attr( $top_stories_secondary_classes ?? '' ); ?>">
		<?php } ?>
	<?php } ?>

	</div>

	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list, true ); ?>
</section>
