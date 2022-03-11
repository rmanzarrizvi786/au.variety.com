<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<footer class="footer-simplified // <?php echo esc_attr( $footer_simplified_classes ?? '' ); ?>">
	<div class="lrv-a-wrapper lrv-u-flex lrv-u-flex-direction-column\@mobile-max">
		<div class="lrv-u-flex lrv-u-flex-direction-column lrv-u-width-100p lrv-u-padding-tb-2 ">
			<?php if ( ! empty( $o_nav ) ) { ?>
			<div class="footer-simplified__nav // <?php echo esc_attr( $footer_nav_classes ?? '' ); ?>">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true ); ?>
			</div>
			<?php } ?>

			<div class="footer-simplified__meta // lrv-u-text-align-center">
				<?php if ( ! empty( $c_tagline_copyright ) ) { ?>
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline_copyright, true ); ?>
				<?php } ?>

			</div>
		</div>

	</div>
</footer>
