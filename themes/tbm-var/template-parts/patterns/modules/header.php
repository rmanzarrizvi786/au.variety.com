<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<header class="header // js-Header <?php echo esc_attr($header_classes ?? ''); ?>">
	<div class="js-Header-contents lrv-u-flex lrv-u-justify-content-space-between a-stacking-context a-stack-1">
		<div class="js-hide-when-sticky lrv-u-width-100p <?php echo esc_attr($header_contents_classes ?? ''); ?>">
			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/header-main.php', $header_main, true); ?>
		</div>

		<div class="js-show-when-sticky lrv-u-width-100p js-sticky-header-slidedown">
			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/header-sticky.php', $header_sticky, true); ?>
		</div>
		<div class="a-subscription-banner lrv-a-hidden">
			<div class="lrv-a-wrapper">
				<div id="cx-fly-out-variety"></div>
			</div>
		</div>
	</div>
</header>