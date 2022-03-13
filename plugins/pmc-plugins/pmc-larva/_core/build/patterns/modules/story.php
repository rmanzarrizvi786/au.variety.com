<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="story lrv-u-padding-tb-1 lrv-u-padding-tb-2@desktop // <?php echo esc_attr( $story_classes ?? '' ); ?>">
	<div class="<?php echo esc_attr( $story_grid_classes ?? '' ); ?>">

		<div class="<?php echo esc_attr( $story_grid_primary_classes ?? '' ); ?> // lrv-a-glue-parent u-padding-t-1@mobile-max lrv-u-flex lrv-u-flex-direction-column lrv-u-height-100p lrv-u-justify-content-start">
			<div class="lrv-a-glue-parent">
				<?php if ( ! empty( $c_lazy_image ) ) { ?>
					<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-lazy-image', $c_lazy_image, true ); ?>
				<?php } ?>

				<?php if ( ! empty( $o_indicator ) ) { ?>
					<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-indicator', $o_indicator, true ); ?>
				<?php } ?>
			</div>
		</div>

		<div class="<?php echo esc_attr( $story_grid_secondary_classes ?? '' ); ?> // lrv-u-flex lrv-u-flex-direction-column lrv-u-height-100p lrv-u-justify-content-center">
			<?php if ( ! empty( $c_title ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-title', $c_title, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_dek ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-dek', $c_dek, true ); ?>
			<?php } ?>

			<ul class="lrv-u-flex lrv-u-order-n1 lrv-a-unstyle-list lrv-a-space-children-horizontal lrv-a-space-children--050 lrv-u-margin-b-050 u-letter-spacing-012 // <?php echo esc_attr( $story_links_classes ?? '' ); ?>">

				<?php if ( ! empty( $c_link_top ) ) { ?>
					<li>
						<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-link', $c_link_top, true ); ?>
					</li>
				<?php } ?>

				<?php if ( ! empty( $c_span ) ) { ?>
					<li>
						<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-span', $c_span, true ); ?>
					</li>
				<?php } ?>
			</ul>

			<ul class="lrv-a-unstyle-list // <?php echo esc_attr( $story_nav_classes ?? '' ); ?> <?php echo esc_attr( $story_nav_layout_classes ?? '' ); ?>">
				<?php if ( ! empty( $c_button ) ) { ?>
					<li class="lrv-u-flex">
						<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
					</li>
				<?php } ?>

				<?php if ( ! empty( $c_tagline_author ) ) { ?>
					<li class="lrv-u-flex // <?php echo esc_attr( $c_tagline_author_wrapper_classes ?? '' ); ?>">
						By <?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-tagline', $c_tagline_author, true ); ?>
					</li>
				<?php } ?>

				<?php if ( ! empty( $c_timestamp ) ) { ?>
					<li>
						<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-timestamp', $c_timestamp, true ); ?>
					</li>
				<?php } ?>
			</ul>

			<?php if ( ! empty( $c_link_bottom ) ) { ?>
				<div class="lrv-u-flex lrv-u-order-100 lrv-u-margin-t-050 // <?php echo esc_attr( $c_link_bottom_wrapper_classes ?? '' ); ?>">
					<?php if ( ! empty( $c_link_bottom ) ) { ?>
						<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-link', $c_link_bottom, true ); ?>
					<?php } ?>
				</div>
			<?php } ?>

		</div>

	</div>

	<?php if ( ! empty( $story_arc_stories ) ) { ?>
		<div class="lrv-u-margin-t-1 lrv-u-margin-t-2@desktop">
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/story-arc-stories', $story_arc_stories, true ); ?>
		</div>
	<?php } ?>
</div>
