<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<footer class="mega-menu-footer // a-hidden@mobile-max u-background-color-brand-accent-100-b u-color-brand-secondary-30">
	<div class="lrv-a-wrapper">

		<section class="lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-center u-padding-l-2@desktop lrv-u-padding-tb-075 u-flex-1 lrv-u-border-b-1 lrv-u-border-t-1 u-border-color-pale-sky-2">
			<h3 class="u-font-size-19 lrv-u-width-50p lrv-u-text-align-right"><?php echo esc_html($mega_menu_footer_alerts_text ?? ''); ?></h3>
			<div class="lrv-u-flex-grow-1 lrv-u-padding-l-1 lrv-u-width-50p">
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-email-capture-form.php', $o_email_capture_form, true); ?>
			</div>
		</section>

		<div class="lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-space-between u-padding-tb-175">
			<div class="lrv-u-flex lrv-u-align-items-center">
				<section class="lrv-u-flex u-flex-direction-column@desktop-max lrv-u-align-items-center u-margin-r-1@desktop">
					<h3 class="lrv-u-font-size-20 lrv-u-font-size-24@desktop lrv-u-margin-b-050 lrv-u-display-none"><?php echo esc_html($mega_menu_footer_follow_text ?? ''); ?></h3>

					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-social-list.php', $o_social_list, true); ?>
				</section>

				<div class="subscribe-link // lrv-u-font-weight-bold lrv-u-font-family-secondary lrv-u-font-size-12 u-letter-spacing-2">
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_subscribe_link, true); ?>
				</div>
			</div>

			<div class="lrv-u-flex lrv-u-align-items-center">
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav_tips, true); ?>
				<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/region-selector.php', $region_selector, true);
				?>
			</div>
		</div>

		<div class="lrv-a-grid a-cols1@desktop u-grid-gap-0@desktop-max lrv-u-border-t-1 u-border-color-pale-sky-2">
			<div class="lrv-u-padding-tb-075 lrv-u-overflow-auto">
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true); ?>
			</div>
			<div class="lrv-u-flex lrv-u-align-items-center u-justify-content-center lrv-u-padding-a-050 lrv-u-height-100p">
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon, true);
				?>
				<small class="lrv-u-padding-l-1 lrv-u-font-family-secondary u-color-brand-secondary-30 lrv-u-font-weight-normal lrv-u-font-size-14"><?php echo esc_html($mega_menu_footer_copyright_text ?? ''); ?></small>
			</div>
		</div>
	</div>
</footer>