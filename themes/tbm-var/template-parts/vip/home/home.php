<?php

/**
 * Home Template.
 *
 * @package pmc-variety
 */


// One-off module customizations for the homepage.
$m_data                             = [];
$m_data['more_from_widget_classes'] = 'a-span3@tablet u-padding-r-4@tablet';
$m_data['o_tease_list_classes']     = 'lrv-a-unstyle-list u-margin-t-125@tablet u-padding-lr-125@mobile-max';

$u_data                            = [];
$u_data['upcoming_events_classes'] = 'u-background-image-slash@mobile-max u-border-t-6@mobile-max u-border-color-brand-secondary-50 u-padding-lr-175@mobile-max u-max-width-300@tablet u-margin-lr-n050@mobile-max';

$v_data                           = [];
$v_data['video_carousel_classes'] = 'u-border-t-6 u-border-b-6 u-border-color-brand-secondary-50 u-padding-b-175 u-padding-t-150@tablet u-padding-b-150@tablet u-background-image-slash@tablet u-margin-t-125@tablet';

?>

<div id="page">

	<section class="homepage lrv-a-wrapper u-padding-lr-050@mobile-max">

		<?php
		if (!pmc_subscription_user_has_entitlements(['Variety.VarietyVIP'])) {
			\PMC::render_template(
				sprintf('%s/template-parts/vip/home/top-stories-carousel.php', untrailingslashit(CHILD_THEME_PATH)),
				[],
				true
			);
		}
		?>

		<?php
		if (pmc_subscription_user_has_entitlements(['Variety.VarietyVIP'])) {
			\PMC::render_template(
				sprintf('%s/template-parts/vip/home/top-stories-vip.php', untrailingslashit(CHILD_THEME_PATH)),
				[],
				true
			);
		}
		?>

		<div class="lrv-a-wrapper lrv-a-grid lrv-a-cols4@tablet">
			<?php
			\PMC::render_template(
				sprintf('%s/template-parts/vip/home/latest-from.php', untrailingslashit(CHILD_THEME_PATH)),
				[],
				true
			);
			?>

			<?php
			if (is_active_sidebar('vip_home_featured_chart')) {
				dynamic_sidebar('vip_home_featured_chart');
			}
			?>

		</div>

		<?php
		\PMC::render_template(
			sprintf('%s/template-parts/vip/home/special-reports-carousel.php', untrailingslashit(CHILD_THEME_PATH)),
			[],
			true
		);
		?>

		<div class="lrv-a-wrapper lrv-a-grid lrv-a-cols4@tablet u-margin-t-2@tablet">

			<?php
			\PMC::render_template(
				sprintf('%s/template-parts/vip/module/more-from-vip.php', untrailingslashit(CHILD_THEME_PATH)),
				$m_data,
				true
			);
			?>

			<?php
			\PMC::render_template(
				sprintf('%s/template-parts/vip/home/upcoming-events.php', untrailingslashit(CHILD_THEME_PATH)),
				$u_data,
				true
			);
			?>

		</div>

		<?php
		\PMC::render_template(
			sprintf('%s/template-parts/vip/home/video-carousel.php', untrailingslashit(CHILD_THEME_PATH)),
			$v_data,
			true
		);
		?>

		<div class="lrv-a-wrapper">

			<?php
			\PMC::render_template(
				sprintf('%s/template-parts/vip/module/trending-topics.php', untrailingslashit(CHILD_THEME_PATH)),
				[],
				true
			);
			?>

		</div>

	</section>

</div>