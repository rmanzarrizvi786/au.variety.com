<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="top-stories // u-background-color-white@mobile-max u-box-shadow-menu@mobile-max <?php echo esc_attr($top_stories_classes ?? ''); ?>">
	<?php if (!empty($c_heading)) { ?>
		<div class="top-stories__heading a-hidden@tablet u-border-b-1@mobile-max u-border-color-brand-secondary-40@mobile-max">
			<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true); ?>
		</div>
	<?php } ?>

	<div class="top-stories__stories a-top-stories-grid@tablet // <?php echo esc_attr($top_stories_stories_classes ?? ''); ?>">
		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-story.php', $o_story_first, true); ?>
		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-story.php', $o_story_second, true); ?>
		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-story.php', $o_story_third, true); ?>
		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-story.php', $o_story_fourth, true); ?>
		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-story.php', $o_story_fifth, true); ?>

		<?php if (!empty($o_story_sixth)) { ?>
			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-story.php', $o_story_sixth, true); ?>
		<?php } ?>
	</div>

	<?php if (!empty($o_tease_ad)) { ?>
		<div class="lrv-u-padding-b-1">
			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease.php', $o_tease_ad, true); ?>
		</div>
	<?php } ?>

	<?php if (!empty($o_latest_news_link)) { ?>
		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/objects/o-latest-news-link.php', $o_latest_news_link, true); ?>
	<?php } ?>
</section>