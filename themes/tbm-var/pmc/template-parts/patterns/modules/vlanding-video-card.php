<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $vlanding_video_card_permalink_url ) ) { ?>
	<?php if ( ! empty( $vlanding_video_card_use_button ) ) { ?>
	<button class="vlanding-video-card__link lrv-a-unstyle-button lrv-u-width-100p <?php echo esc_attr( $vlanding_video_card_link_classes ?? '' ); ?>"
		data-video-showcase-trigger="<?php echo esc_attr( $vlanding_video_card_link_showcase_trigger_data_attr ?? '' ); ?>"
		data-video-showcase-type="<?php echo esc_attr( $vlanding_video_card_link_showcase_type_data_attr ?? '' ); ?>"
		data-video-showcase-dek="<?php echo esc_attr( $vlanding_video_card_link_showcase_dek_data_attr ?? '' ); ?>"
		data-video-showcase-title="<?php echo esc_attr( $vlanding_video_card_link_showcase_title_data_attr ?? '' ); ?>"
		data-video-showcase-permalink="<?php echo esc_url( $vlanding_video_card_link_showcase_permalink_data_url ?? '' ); ?>"
		<?php if ( ! empty( $vlanding_video_card_link_aria_controls_attr ) ) { ?>
			aria-controls="<?php echo esc_attr( $vlanding_video_card_link_aria_controls_attr ?? '' ); ?>"
		<?php } ?>
	>
	<?php } else { ?>
	<a href="<?php echo esc_url( $vlanding_video_card_permalink_url ?? '' ); ?>" class="vlanding-video-card__link <?php echo esc_attr( $vlanding_video_card_link_classes ?? '' ); ?>" data-video-showcase-trigger="<?php echo esc_attr( $vlanding_video_card_link_showcase_trigger_data_attr ?? '' ); ?>" data-video-showcase-type="<?php echo esc_attr( $vlanding_video_card_link_showcase_type_data_attr ?? '' ); ?>" data-video-showcase-dek="<?php echo esc_attr( $vlanding_video_card_link_showcase_dek_data_attr ?? '' ); ?>" data-video-showcase-title="<?php echo esc_attr( $vlanding_video_card_link_showcase_title_data_attr ?? '' ); ?>" data-video-showcase-permalink="<?php echo esc_url( $vlanding_video_card_link_showcase_permalink_data_url ?? '' ); ?>">
	<?php } ?>
<?php } ?>

	<div class="vlanding-video-card <?php echo esc_attr( $vlanding_video_card_classes ?? '' ); ?>" data-video-showcase-player>

		<div class="<?php echo esc_attr( $vlanding_video_card_crop_class ?? '' ); ?>" data-video-showcase-active-text="<?php echo esc_attr( $vlanding_video_card_active_txt_attr ?? '' ); ?>">

			<?php if ( ! empty( $c_lazy_image ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $vlanding_video_card_is_player ) ) { ?>
				<iframe hidden id="youtubePlayerContainer" frameborder="0" data-video-showcase-iframe></iframe>
				<div hidden id="jwplayerContainer" data-video-showcase-jwplayer></div>
			<?php } ?>

		</div>

		<?php if ( ! empty( $c_span ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_title ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>
		<?php } ?>

	</div>

<?php if ( ! empty( $vlanding_video_card_permalink_url ) ) { ?>
	<?php if ( ! empty( $vlanding_video_card_use_button ) ) { ?>
	</button>
	<?php } else { ?>
	</a>
	<?php } ?>
<?php } ?>

<?php if ( ! empty( $vlanding_video_card_social_share_markup ) ) { ?>
	<div class="lrv-a-hidden" data-video-showcase-trigger-social-share>
		<?php echo wp_kses_post( $vlanding_video_card_social_share_markup ?? '' ); ?>
	</div>
<?php } ?>

