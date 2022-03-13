<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<footer class="footer // <?php echo esc_attr( $footer_classes ?? '' ); ?>">
	<div class="lrv-a-wrapper">
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/footer-menus', $footer_menus, true ); ?>

		<?php if ( ! empty( $footer_social ) ) { ?>

			<div class="lrv-a-grid lrv-a-cols3@desktop lrv-u-border-t-1 lrv-u-border-color-grey lrv-u-padding-tb-1">
				<div class="lrv-u-border-r-1 lrv-u-border-color-grey lrv-u-padding-a-1 lrv-u-border-r-1@desktop">
					<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/footer-social', $footer_social, true ); ?>
				</div>
				<div class="lrv-u-border-r-1 lrv-u-border-color-grey lrv-u-padding-a-1 lrv-u-border-t-1 lrv-u-border-t-0@desktop lrv-u-border-r-1@desktop">
					<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/footer-tip', $footer_tip, true ); ?>
				</div>
				<div class="lrv-u-border-r-1 lrv-u-border-color-grey lrv-u-padding-a-1 lrv-u-border-t-1 lrv-u-border-t-0@desktop">
					<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/footer-newsletter', $footer_newsletter, true ); ?>
				</div>
			</div>

		<?php } ?>

	</div>
</footer>
