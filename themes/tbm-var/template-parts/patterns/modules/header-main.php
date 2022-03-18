<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="header-main // <?php echo esc_attr($header_sticky_classes ?? ''); ?>">
	<div class="lrv-a-wrapper <?php echo esc_attr($header_inner_classes ?? ''); ?>">
		<div class="header-sticky__menu lrv-u-flex lrv-u-align-items-center">
			<div class="<?php echo esc_attr($header_menu_icons_classes ?? ''); ?>">

				<div class="lrv-u-margin-r-050">
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-icon-button.php', $o_icon_button_menu, true); ?>
				</div>

				<div class="<?php echo esc_attr($expandable_search_wrapper_classes ?? ''); ?>">
					<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/expandable-search.php', $expandable_search, true); ?>
				</div>
			</div>

			<div class="<?php echo esc_attr($header_navigation_classes ?? ''); ?>">
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_top_nav, true); ?>
				<?php // \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/region-selector.php', $region_selector, true ); 
				?>
			</div>
		</div>

		<div class="tbm lrv-a-glue-parent">

			<?php if (!empty($header_main_show_special_icon)) { ?>
				<div class="header-main__special-icon // lrv-a-glue lrv-a-glue--l-0 lrv-u-height-100p u-transform-translateX-n100p lrv-u-flex lrv-u-align-items-center a-hidden@desktop-max <?php echo esc_attr($header_main_special_icon_classes ?? ''); ?>">
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon, true); ?>
				</div>
			<?php } ?>

			<div class="header__logo // <?php echo esc_attr($header_sticky_logo_classes ?? ''); ?>">
				<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/components/c-logo.php', $c_logo, true); ?>
			</div>
		</div>

		<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/login-actions.php', $login_actions, true); ?>

	</div>

	<div class="js-Header-extra lrv-a-wrapper lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-space-between lrv-u-padding-tb-025 a-hidden@tablet u-background-color-pale-sky">
		<div class="header-sticky__user-menu">
			<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/login-actions-mobile.php', $login_actions_mobile, true); ?>
		</div>
	</div>
</div>