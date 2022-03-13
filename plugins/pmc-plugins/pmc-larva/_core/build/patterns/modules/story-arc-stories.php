<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="story-arc-stories <?php echo esc_attr( $story_arc_stories_classes ?? '' ); ?>">
	<div class="lrv-u-font-family-primary lrv-u-margin-b-050">
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-span', $c_span, true ); ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
	</div>

	<div class="lrv-u-overflow-hidden lrv-u-max-width-100vw lrv-u-padding-b-1">
		<div class="lrv-u-width-100p lrv-u-max-width-100p a-scrollable-grid@desktop-max lrv-a-grid lrv-a-cols3@desktop">
			<?php foreach ( $story_arc_stories ?? [] as $item ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-story-arc-item', $item, true ); ?>
			<?php } ?>
		</div>
	</div>
</section>
