<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="o-player" data-video-showcase>
	<div tabindex="0"
	class="o-player__link // <?php echo esc_attr( $o_player_link_classes ?? '' ); ?>" data-video-showcase-trigger="<?php echo esc_attr( $o_player_trigger_data_attr ?? '' ); ?>" data-video-showcase-type="<?php echo esc_attr( $o_player_type_data_attr ?? '' ); ?>">
		<div class="o-player <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_player_classes ?? '' ); ?>" data-video-showcase-autoplay="<?php echo esc_attr( $o_player_autoplay_data_attr ?? '' ); ?>" data-video-showcase-player>
		<?php if ( ! empty( $o_player_crop_class ) ) { ?>
			<div class="<?php echo esc_attr( $o_player_crop_class ?? '' ); ?>">
		<?php } ?>

				<img class="o-player__image <?php echo esc_attr( $o_player_image_classes ?? '' ); ?>" src="<?php echo esc_url( $o_player_image_url ?? '' ); ?>" alt="<?php echo esc_attr( $o_player_alt_attr ?? '' ); ?>">
				<iframe hidden frameborder="0" data-video-showcase-iframe></iframe>
				<div hidden id="jwplayerContainer" data-video-showcase-jwplayer></div>
				<div hidden data-video-showcase-oembed></div>

				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/components/c-play-badge.php', $c_play_icon, true ); ?>

		<?php if ( ! empty( $o_player_crop_class ) ) { ?>
			</div>
		<?php } ?>
		</div>
	</div>
</div>
