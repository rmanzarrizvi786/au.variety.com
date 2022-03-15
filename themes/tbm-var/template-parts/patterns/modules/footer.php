<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div id="cx-sticky-footer" class="a-sticky-footer"></div>
<footer class="footer // <?php echo esc_attr($footer_classes ?? ''); ?>">
	<div class="lrv-a-wrapper lrv-u-flex lrv-u-flex-direction-column\@mobile-max">
		<?php
		\PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true); ?>

		<div class="lrv-u-flex lrv-u-flex-direction-column lrv-u-width-100p">
			<div class="footer-menu">
				<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/footer-menus.php', $footer_menus, true); ?>
			</div>

			<div class="footer-meta // lrv-u-text-align-center">
				<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/components/c-logo.php', $c_logo, true); ?>

				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true); ?>

				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link_subscribe, true); ?>

				<?php // \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline_copyright, true); ?>

				<?php // \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline_tip, true); ?>
			</div>
		</div>

	</div>
</footer>