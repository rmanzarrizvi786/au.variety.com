<?php

/**
 * Archive Template
 *
 * @package pmc-variety
 */

$current_term = get_queried_object();

$menu_location = $current_term && isset($current_term->slug) ?  $current_term->slug . '-' . $current_term->taxonomy . '-menu' : '';

$menu_items = PMC\Core\Inc\Menu::get_instance()->get_menu_data($menu_location);

$is_paged = is_paged();

$sidebar = 'global-sidebar';

if (is_tax('vertical')) {
	$sidebar = 'vertical-sidebar';
}

?>

<div class="u-padding-t-1@mobile-max">
	<div class="lrv-u-margin-b-1@tablet">
		<?php

		\PMC::render_template(
			sprintf('%s/template-parts/archive/sub-header.php', untrailingslashit(CHILD_THEME_PATH)),
			['menu_items' => $menu_items],
			true
		);

		?>
	</div>
	<div class="lrv-a-wrapper lrv-u-margin-t-1@tablet lrv-u-margin-b-2">
		<div class="lrv-u-flex@tablet lrv-u-width-100p">
			<div class="lrv-a-space-children-vertical lrv-a-space-children--1 lrv-u-flex@tablet lrv-u-flex-direction-column">
				<?php

				\PMC::render_template(
					sprintf(
						'%s/template-parts/archive/top-stories-section-front.php',
						untrailingslashit(CHILD_THEME_PATH)
					),
					[
						'menu_items' => $menu_items,
						'is_paged'   => $is_paged,
					],
					true
				);

				\PMC::render_template(
					sprintf(
						'%s/template-parts/archive/widgets-section-front.php',
						untrailingslashit(CHILD_THEME_PATH)
					),
					[
						'menu_items' => $menu_items,
						'is_paged'   => $is_paged,
					],
					true
				);

				?>

				<?php get_template_part('template-parts/archive/latest-news-river'); ?>

			</div>

			<aside class="u-width-320@tablet lrv-u-flex-shrink-0 lrv-u-flex@tablet lrv-u-flex-direction-column lrv-a-space-children-vertical lrv-a-space-children--1 u-padding-l-1@tablet">
				<!-- <div class="a-hidden@mobile-max">
					<div class="lrv-u-padding-tb-1@mobile-max ">
						<section class="u-width-300 u-height-300 lrv-u-text-align-center lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-center">
							<div class="admz">
								<?php pmc_adm_render_ads('mrec'); ?>
							</div>
						</section>
					</div>
				</div> -->
				<?php
				if (is_active_sidebar($sidebar)) {
					dynamic_sidebar($sidebar);
				}
				?>
				<!-- <div class="a-hidden@mobile-max">
					<div class="lrv-u-padding-tb-1@mobile-max ">
						<section class="u-width-300 lrv-u-text-align-center lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-center">
							<div class="admz">
								<?php pmc_adm_render_ads('vrec'); ?>
							</div>
						</section>
					</div>
				</div> -->
			</aside>
		</div>
	</div>
</div>