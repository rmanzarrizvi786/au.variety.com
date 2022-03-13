<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="vlanding-video-showcase // <?php echo esc_attr( $vlanding_video_showcase_classes ?? '' ); ?>" data-video-showcase="">
	<div class="lrv-a-wrapper lrv-a-grid lrv-a-cols3@tablet">
		<div class="lrv-a-grid-item lrv-a-span2@tablet">
			<?php if ( ! empty( $vlanding_video_card_player ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/vlanding-video-card', $vlanding_video_card_player, true ); ?>
			<?php } ?>
		</div>
		<div class="lrv-a-grid-item lrv-u-margin-tb-auto lrv-u-margin-tb-auto">
			<?php if ( ! empty( $c_span ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-span', $c_span, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_title ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-title', $c_title, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_dek ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-dek', $c_dek, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $social_share ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/social-share', $social_share, true ); ?>
			<?php } ?>
		</div>
	</div>

	<?php if ( ! empty( $vlanding_video_showcase_video_cards ) ) { ?>

		<div class="lrv-a-wrapper lrv-u-padding-t-1">
			<?php if ( ! empty( $c_heading ) ) { ?>
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
			<?php } ?>

			<div class="vlanding-video-showcase__video-cards // lrv-u-overflow-hidden lrv-a-glue-parent">
				<ul class="lrv-a-unstyle-list lrv-a-scrollable-grid@desktop-max <?php echo esc_attr( $vlanding_video_showcase_video_cards_classes ?? '' ); ?>">
					<?php foreach ( $vlanding_video_showcase_video_cards ?? [] as $item ) { ?>
						<li class="<?php echo esc_attr( $vlanding_video_showcase_video_cards_item_classes ?? '' ); ?>">
							<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/vlanding-video-card', $item, true ); ?>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>

	<?php } ?>
</section>
