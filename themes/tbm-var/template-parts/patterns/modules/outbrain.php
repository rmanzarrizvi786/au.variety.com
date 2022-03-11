<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="outbrain__wrapper // <?php echo esc_attr( $outbrain_wrapper_classes ?? '' ); ?>">
	<div class="outbrain // <?php echo esc_attr( $outbrain_classes ?? '' ); ?>">
		<div class="outbrain__header // <?php echo esc_attr( $outbrain_header_classes ?? '' ); ?>">
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>

			<div class="outbrain__logo // <?php echo esc_attr( $outbrain_logo_classes ?? '' ); ?>">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading_secondary, true ); ?>
				<?php \PMC::render_template( CHILD_THEME_PATH . '/assets/build/svg/' . ( $outbrain_svg ?? '' ) . '.svg', [], true ); ?>
			</div>
		</div>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list, true ); ?>
	</div>
</section>
