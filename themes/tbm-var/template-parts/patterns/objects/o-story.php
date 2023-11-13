<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<article class="o-story lrv-u-flex u-box-shadow-menu@tablet u-border-b-1@mobile-max u-height-100p@tablet // <?php echo esc_attr($o_story_classes ?? ''); ?>">

	<?php if (!empty($c_span_secondary)) { ?>
		<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_secondary, true); ?>
	<?php } ?>

	<div class="o-story__primary lrv-u-flex lrv-u-flex-direction-column lrv-u-width-100p u-height-100p@tablet // <?php echo esc_attr($o_story_primary_classes ?? ''); ?>">
		<?php if (!empty($c_span)) { ?>
			<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true); ?>
		<?php } ?>

		<?php if (!empty($c_title)) { ?>
			<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true); ?>
		<?php } ?>

		<?php if (!empty($c_dek)) { ?>
			<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-dek.php', $c_dek, true); ?>
		<?php } ?>

		<div class="o-story__meta lrv-u-flex@tablet u-align-items-baseline  // <?php echo esc_attr($o_story_meta_classes ?? ''); ?>">
			<?php if (!empty($c_link)) { ?>
				<div class="lrv-u-margin-r-1@tablet u-margin-r-1@desktop">
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link, true); ?>
				</div>
			<?php } ?>

			<?php if (!empty($c_timestamp)) { ?>
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-timestamp.php', $c_timestamp, true); ?>
			<?php } ?>
		</div>
	</div>

	<?php if (!empty($c_lazy_image)) { ?>	
		<div class="o-story__secondary lrv-a-glue-parent // <?php echo esc_attr($o_story_secondary_classes ?? ''); ?>">
			<?php if((int) $c_lazy_image['post_id'] == (int) 9536) { ?>
				<?php $c_lazy_image['c_lazy_image_src_url'] = "https://images-r2.thebrag.com/var/uploads/2023/07/gary-vee.jpg"; ?>
			<?php } ?>
			<?php if (!empty($c_lazy_image)) { ?>
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true); ?>

				<?php if (!empty($video_permalink_url)) { ?>
					<a href="<?php echo esc_url($video_permalink_url ?? ''); ?>" class="lrv-a-glue lrv-a-glue--b-0 lrv-u-width-100p lrv-u-height-100p // <?php echo esc_attr($c_lazy_image_badge_classes ?? ''); ?>">
						<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/components/c-play-badge.php', $c_play_badge, true); ?>
					</a>
				<?php } ?>
			<?php } ?>
		</div>
	<?php } ?>

</article>