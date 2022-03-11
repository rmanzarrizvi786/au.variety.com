<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php
/*
This is basically o-tease, stripped and customized because it needed an extra wrapper
*/
?>

<article class="o-tease <?php echo esc_attr( $o_tease_classes ?? '' ); ?>" <?php echo esc_attr( $o_tease_data_attributes ?? '' ); ?>>

	<?php if ( ! empty( $o_tease_url ) ) { ?>
		<a href="<?php echo esc_url( $o_tease_url ?? '' ); ?>" class="<?php echo esc_attr( $o_tease_link_classes ?? '' ); ?>">
	<?php } ?>
		<div class="o-tease__primary <?php echo esc_attr( $o_tease_primary_classes ?? '' ); ?>">
			<div class="o-tease__meta // <?php echo esc_attr( $o_tease_meta_classes ?? '' ); ?>">
				<?php if ( ! empty( $o_taxonomy_item ) ) { ?>
					<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-taxonomy-item.php', $o_taxonomy_item, true ); ?>
				<?php } ?>

				<?php if ( ! empty( $c_timestamp ) ) { ?>
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-timestamp.php', $c_timestamp, true ); ?>
				<?php } ?>
			</div>

			<?php if ( ! empty( $sponsored_homepage_river_ad_action ) ) { ?>
				<?php do_action( 'sponsored_homepage_river_ad_action' ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_title ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $c_dek ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-dek.php', $c_dek, true ); ?>
			<?php } ?>
		</div>

		<?php if ( ! empty( $c_lazy_image ) ) { ?>
			<div class="o-tease__secondary <?php echo esc_attr( $o_tease_secondary_classes ?? '' ); ?>">
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>

				<?php if ( ! empty( $is_video ) ) { ?>
					<?php if ( ! empty( $video_permalink_url ) ) { ?>
						<a href="<?php echo esc_url( $video_permalink_url ?? '' ); ?>" class="<?php echo esc_attr( $c_play_badge_wrapper_classes ?? '' ); ?>">
					<?php } ?>
						<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-play-badge.php', $c_play_badge, true ); ?>
					<?php if ( ! empty( $video_permalink_url ) ) { ?>
						</a>
					<?php } ?>
				<?php } ?>
			</div>
		<?php } ?>

	<?php if ( ! empty( $o_tease_url ) ) { ?>
		</a>
	<?php } ?>
</article>
