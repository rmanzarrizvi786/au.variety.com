<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="footer-menus // <?php echo esc_attr($footer_menu_classes ?? ''); ?>">
	<?php foreach ($o_navs ?? [] as $item) { ?>
		<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $item, true); ?>
	<?php } ?>
</div>