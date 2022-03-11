<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="latest-from // <?php echo esc_attr( $latest_from_classes ?? '' ); ?>">
	<?php if ( ! empty( $o_more_from_heading ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>
	<?php } ?>

	<div class="latest-from__inner // <?php echo esc_attr( $latest_from_inner_classes ?? '' ); ?>">
		<div class="latest-from__primary // <?php echo esc_attr( $latest_from_primary_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease.php', $o_tease_primary, true ); ?>
		</div>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list, true ); ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
	</div>
</section>
