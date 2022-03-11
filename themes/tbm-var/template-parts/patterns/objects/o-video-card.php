<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div tabindex="0" class="o-video-card__link // <?php echo esc_attr($o_video_card_link_classes ?? ''); ?>" data-video-showcase-trigger="<?php echo esc_attr($o_video_card_link_showcase_trigger_data_attr ?? ''); ?>" data-video-showcase-type="<?php echo esc_attr($o_video_card_link_showcase_type_data_attr ?? ''); ?>" data-video-showcase-dek="<?php echo esc_attr($o_video_card_link_showcase_dek_data_attr ?? ''); ?>" data-video-showcase-title="<?php echo esc_attr($o_video_card_link_showcase_title_data_attr ?? ''); ?>" data-video-showcase-permalink="<?php echo esc_url($o_video_card_link_showcase_permalink_data_url ?? ''); ?>" data-video-showcase-time="<?php echo esc_attr($o_video_card_link_showcase_time_data_attr ?? ''); ?>">

	<article class="o-video-card <?php echo esc_attr($modifier_class ?? ''); ?> <?php echo esc_attr($o_video_card_classes ?? ''); ?>" data-video-showcase-autoplay="<?php echo esc_attr($o_video_card_link_showcase_autoplay_data_attr ?? ''); ?>" data-video-showcase-player>

		<?php if (!empty($o_video_card_crop_class)) { ?>
			<div class="<?php echo esc_attr($o_video_card_crop_class ?? ''); ?>" data-video-showcase-active-text="<?php echo esc_attr($o_video_card_crop_data_attr ?? ''); ?>">
			<?php } ?>

			<?php if (!empty($o_video_card_permalink_url)) { ?>
				<a href="<?php echo esc_url($o_video_card_permalink_url ?? ''); ?>" class="<?php echo esc_attr($o_video_card_permalink_classes ?? ''); ?>">
				<?php } ?>
				<img class="o-video-card__image <?php echo esc_attr($o_video_card_image_classes ?? ''); ?>" src="<?php echo esc_url($o_video_card_image_url ?? ''); ?>" alt="<?php echo esc_attr($o_video_card_alt_attr ?? ''); ?>">
				<?php if (!empty($o_video_card_permalink_url)) { ?>
				</a>
			<?php } ?>

			<?php if (!empty($o_video_card_is_player)) { ?>
				<iframe class="js-VideoShowcasePlayerIframe" hidden frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen" data-video-showcase-iframe></iframe>
				<div hidden id="jwplayerContainer" data-video-showcase-jwplayer></div>
				<div class="js-VideoShowcasePlayerOembed" hidden data-video-showcase-oembed></div>
			<?php } ?>

			<?php if (!empty($c_play_icon)) { ?>
				<?php if (!empty($o_video_card_permalink_url)) { ?>
					<a href="<?php echo esc_url($o_video_card_permalink_url ?? ''); ?>">
					<?php } ?>
					<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/components/c-play-badge.php', $c_play_icon, true); ?>
					<?php if (!empty($o_video_card_permalink_url)) { ?>
					</a>
				<?php } ?>
			<?php } ?>

			<?php if (!empty($o_video_card_crop_class)) { ?>
			</div>
		<?php } ?>

		<div class="o-video-card__meta // <?php echo esc_attr($o_video_card_meta_classes ?? ''); ?>">
			<?php if (!empty($o_indicator)) { ?>
				<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-indicator.php', $o_indicator, true); ?>
			<?php } ?>

			<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true); ?>

			<?php if (!empty($c_dek)) { ?>
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-dek.php', $c_dek, true); ?>
			<?php } ?>

			<?php if (!empty($c_span)) { ?>
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true); ?>
			<?php } ?>
		</div>

	</article>

</div>