<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="js-vip_menu <?php echo esc_attr($vip_menu_classes ?? ''); ?>" data-collapsible-panel>
	<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/search-form.php', $search_form, true); ?>

	<div class="vip-menu__primary">
		<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav_primary, true); ?>
	</div>

	<div class="vip-menu__secondary">
		<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav_secondary, true); ?>
	</div>
</div>