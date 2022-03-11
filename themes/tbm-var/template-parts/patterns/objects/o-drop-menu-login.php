<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>

<div class="o-drop-menu o-drop-menu-login <?php echo esc_attr($o_drop_menu_classes ?? ''); ?>" data-collapsible="collapsed" <?php echo esc_attr($o_drop_data_attr ?? ''); ?> <?php if (!empty($o_drop_data_attr)) { ?> <?php echo esc_attr($o_drop_data_attr ?? ''); ?> <?php } ?>>
	<div class="js-LoginStatus--hide-when-authenticated">
		<?php if (!is_user_logged_in()) { ?>
			<a class="o-drop-menu__toggle <?php echo esc_attr($o_drop_menu_toggle_classes ?? ''); ?>" href="<?php echo wp_login_url(); ?>">
				<?php if (!empty($c_span)) { ?>
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true); ?>
				<?php } ?>
			</a>
		<?php } ?>
	</div>
</div>

<?php
return;
?>
<div class="o-drop-menu o-drop-menu-login <?php echo esc_attr($o_drop_menu_classes ?? ''); ?>" data-collapsible="collapsed" <?php echo esc_attr($o_drop_data_attr ?? ''); ?> <?php if (!empty($o_drop_data_attr)) { ?> <?php echo esc_attr($o_drop_data_attr ?? ''); ?> <?php } ?>>
	<div class="js-LoginStatus--hide-when-authenticated">
		<a class="o-drop-menu__toggle <?php echo esc_attr($o_drop_menu_toggle_classes ?? ''); ?>" href="#" data-collapsible-toggle="always-show">
			<?php if (!empty($c_span)) { ?>
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true); ?>
			<?php } ?>

			<?php if (!empty($o_icon_button)) { ?>
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-icon-button.php', $o_icon_button, true); ?>
			<?php } ?>
		</a>
	</div>
	<div class="js-LoginStatus--show-when-authenticated">
		<a class="o-drop-menu__toggle <?php echo esc_attr($o_drop_menu_toggle_classes_logged_in ?? ''); ?>" href="#" data-collapsible-toggle="always-show">
			<?php if (!empty($c_span_logged_in)) { ?>
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_logged_in, true); ?>
			<?php } ?>
		</a>
	</div>

	<div class="o-drop-menu__list <?php echo esc_attr($o_drop_menu_list_classes ?? ''); ?>" data-collapsible-panel>

		<?php if (!empty($o_nav_not_logged_in_pp)) { ?>
			<div class="o_nav_not_logged_in_pp js-LoginStatus--hide-when-authenticated-pp">
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav_not_logged_in_pp, true); ?>
			</div>
		<?php } ?>

		<?php if ($c_span_user or c_tagline or o_nav) { ?>
			<div class="o_nav_logged_in_pp js-LoginStatus--show-when-authenticated-pp">
				<?php if (!empty($c_span_user)) { ?>
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_user, true); ?>
				<?php } ?>

				<?php if (!empty($c_tagline)) { ?>
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true); ?>
				<?php } ?>

				<?php if (!empty($o_nav)) { ?>
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true); ?>
				<?php } ?>
			</div>
		<?php } ?>

		<?php if (!empty($c_horizontal_rule_display)) { ?>
			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/components/c-horizontal-rule.php', $c_horizontal_rule, true); ?>
		<?php } ?>

		<?php if (!empty($o_nav_not_logged_in_vip)) { ?>
			<div class="o_nav_not_logged_in_vip js-LoginStatus--hide-when-authenticated-vip">
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav_not_logged_in_vip, true); ?>
			</div>
		<?php } ?>

		<?php if (!empty($o_nav_logged_in_vip)) { ?>
			<div class="o_nav_logged_in_vip js-LoginStatus--show-when-authenticated-vip">
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav_logged_in_vip, true); ?>
			</div>
		<?php } ?>

	</div>
</div>