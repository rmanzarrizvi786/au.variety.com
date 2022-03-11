<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="reviews__outer // <?php echo esc_attr( $reviews_outer_classes ?? '' ); ?>">
	<div class="reviews // <?php echo esc_attr( $reviews_classes ?? '' ); ?>">
		<div class="reviews__header // <?php echo esc_attr( $reviews_header_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>

			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true ); ?>
		</div>

		<div class="reviews__inner // <?php echo esc_attr( $reviews_inner_classes ?? '' ); ?>">
			<?php foreach ( $reviews_lists ?? [] as $item ) { ?>
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $item, true ); ?>
			<?php } ?>
		</div>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
	</div>
</section>
