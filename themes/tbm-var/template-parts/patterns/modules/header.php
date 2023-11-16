<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div style="width: 100%;" class="nav-network-wrap-2">
	<?php get_template_part('template-parts/header/nav-network'); ?>
</div>

<div style="background-color: rgb(247, 247, 247);">
<header class="header // js-Header <?php echo esc_attr($header_classes ?? ''); ?>">
	<div class="js-Header-contents lrv-u-flex lrv-u-justify-content-space-between a-stacking-context a-stack-1">
		<div class="js-hide-when-sticky lrv-u-width-100p <?php echo esc_attr($header_contents_classes ?? ''); ?>">
			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/header-main.php', $header_main, true); ?>
		</div>

		<div class="js-show-when-sticky lrv-u-width-100p js-sticky-header-slidedown">
			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/header-sticky.php', $header_sticky, true); ?>
		</div>
	</div>
</header>