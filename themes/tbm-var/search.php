<?php
get_header();

$sidebar = 'global-sidebar';
?>

<article id="page-search" class="lrv-a-wrapper">
	<div class="search-results">
		<div class="lrv-u-margin-tb-1">
			<div class="search_form">
				<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>" style="display: flex;">
					<input type="text" autocomplete="off" class="" placeholder="Search &hellip;" value="<?php echo get_search_query(); ?>" name="s" style="color: #000;">
					<input type="submit" class="" value="Search">
				</form>
			</div>
		</div>

		<div class="lrv-u-flex@tablet lrv-u-width-100p">
			<div class="lrv-a-space-children-vertical lrv-a-space-children--1 lrv-u-flex@tablet lrv-u-flex-direction-column">
				<?php get_template_part('template-parts/archive/latest-news-river'); ?>
			</div>
			<aside class="u-width-320@tablet lrv-u-flex-shrink-0 lrv-u-flex@tablet lrv-u-flex-direction-column lrv-a-space-children-vertical lrv-a-space-children--1 u-padding-l-1@tablet">
				<div class="a-hidden@mobile-max">
					<div class="lrv-u-padding-tb-1@mobile-max ">
						<section>
							<div class="admz">
								<?php pmc_adm_render_ads('mrec'); ?>
							</div>
						</section>
					</div>
				</div>
				<?php
				if (is_active_sidebar($sidebar)) {
					dynamic_sidebar($sidebar);
				}
				?>
				<div id="tbm-sticky-rail-ad" class="a-hidden@mobile-max">
					<div class="lrv-u-padding-tb-1@mobile-max ">
						<section>
							<div class="admz">
								<?php pmc_adm_render_ads('vrec'); ?>
							</div>
						</section>
					</div>
				</div>
			</aside>
		</div>
	</div>
</article>
<?php
get_footer();
