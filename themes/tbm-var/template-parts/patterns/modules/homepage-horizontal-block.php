<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="homepage-horizontal // <?php echo esc_attr( $homepage_horizontal_classes ?? '' ); ?>">
	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>

	<div class="homepage-horizontal__stories // <?php echo esc_attr( $homepage_horizontal_inner_classes ?? '' ); ?>">
		<div class="homepage-horizontal__primary // <?php echo esc_attr( $homepage_horizontal_primary_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease.php', $o_tease_primary, true ); ?>
		</div>

		<div class="homepage-horizontal__secondary // <?php echo esc_attr( $homepage_horizontal_secondary_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list, true ); ?>

			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list_bottom, true ); ?>
		</div>
	</div>
	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
</section>
