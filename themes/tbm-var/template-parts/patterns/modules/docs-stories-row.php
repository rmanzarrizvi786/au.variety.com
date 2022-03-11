<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="stories-row // <?php echo esc_attr( $stories_row_classes ?? '' ); ?>">
	<div class="<?php echo esc_attr( $stories_row_wrapper_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_span ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
		<?php } ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-card.php', $large_story, true ); ?>

		<div class="lrv-a-grid lrv-a-cols4@tablet u-grid-gap-175 u-grid-gap-125@tablet lrv-u-margin-b-1">
			<?php foreach ( $stories_row_items ?? [] as $item ) { ?>
				<div class="stories-row__item // <?php echo esc_attr( $stories_row_item_classes ?? '' ); ?>">
					<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-card.php', $item, true ); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</section>
