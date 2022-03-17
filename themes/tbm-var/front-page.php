<?php

/**
 * The Front page for our theme.
 *
 * @package pmc-variety
 */

get_header();
?>

<section class="homepage">

	<?php
	if (is_active_sidebar('homepage-top')) {
		dynamic_sidebar('homepage-top');
	}

	?>
	<div style="margin-top: 1rem;">
		<?php pmc_adm_render_ads('incontent_1'); ?>
	</div>


	<div class="lrv-a-wrapper lrv-u-flex@tablet lrv-u-margin-t-1">

		<div class="lrv-u-width-100p">
			<?php
			get_template_part('template-parts/home/latest-news');
			?>
		</div>
		<div class="u-width-320@tablet lrv-u-flex-shrink-0 u-padding-l-1@tablet">
			<?php
			if (is_active_sidebar('homepage-sidebar')) {
				dynamic_sidebar('homepage-sidebar');
			}
			?>
		</div>
	</div>

	<?php

	\PMC::render_template(
		sprintf('%s/template-parts/widgets/breaking-news-alerts.php', untrailingslashit(CHILD_THEME_PATH)),
		[],
		true
	);
	?>

	<div style="margin-top: 1rem;">
		<?php pmc_adm_render_ads('incontent_2'); ?>
	</div>

	<?php
	if (is_active_sidebar('homepage-bottom')) {
		dynamic_sidebar('homepage-bottom');
	}
	?>

	<div style="margin-top: 1rem;">
		<?php
		pmc_adm_render_ads('incontent_3');
		?>
	</div>

</section>

<?php
get_footer();
