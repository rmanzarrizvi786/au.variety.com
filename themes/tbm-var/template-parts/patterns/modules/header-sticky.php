<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div style="width: 100%;" class="nav-network-wrap-2">
	<?php get_template_part('template-parts/header/nav-network'); ?>
</div>
<div class="header-sticky // <?php echo esc_attr($header_sticky_classes ?? ''); ?>">
	<?php if (!empty($is_vip_header)) { ?>
		<div class="js-Header">
			<div class="js-Header-contents lrv-u-background-color-white u-background-image-slash">
			<?php } else { ?>
				<div class="lrv-a-wrapper">
				<?php } ?>

				<div class="<?php echo esc_attr($header_sticky_inner_classes ?? ''); ?>">
					<div class="header-sticky__menu // <?php echo esc_attr($header_sticky_menu_classes ?? ''); ?>" <?php if (!empty($is_vip_header)) { ?> data-collapsible="collapsed" <?php } ?>>
						<?php if (!empty($is_vip_header)) { ?>
							<span data-collapsible-toggle="always-show">
							<?php } ?>
							<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-icon-button.php', $o_icon_button_menu, true); ?>
							<?php if (!empty($is_vip_header)) { ?>
							</span>
						<?php } ?>

						<?php // if ( ! empty( $is_vip_header ) ) { 
						?>
						<?php // \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/main-header-vip.php', $header_vip, true ); 
						?>
						<?php // } 
						?>
					</div>

					<div class="header-sticky__search <?php echo esc_attr($header_sticky_search_classes ?? ''); ?>">
						<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/expandable-search.php', $expandable_search, true); ?>
					</div>

					<div class="<?php echo esc_attr($header_sticky_logo_classes ?? ''); ?>">
						<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/components/c-logo.php', $c_logo, true); ?>

						<?php if (!empty($is_vip_header_h1)) { ?>
							<h1>
							<?php } ?>

							<?php if (!empty($o_icon_button_link)) { ?>
								<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-icon-button.php', $o_icon_button_link, true); ?>
							<?php } ?>

							<?php if (!empty($o_icon_button_backup)) { ?>
								<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-icon-button.php', $o_icon_button_backup, true); ?>
							<?php } ?>

							<?php if (!empty($is_vip_header_h1)) { ?>
							</h1>
						<?php } ?>
					</div>

					<?php if (!empty($is_vip_header)) { ?>
						<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/login-actions-vip.php', $login_actions, true); ?>
					<?php } else { ?>
						<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/login-actions.php', $login_actions, true); ?>
					<?php } ?>

				</div>

				<?php if (!empty($login_actions_mobile)) { ?>
					<div class="<?php echo esc_attr($header_sticky_secondary_classes ?? ''); ?>">
						<div class="header-sticky__user-menu">
							<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/login-actions-mobile.php', $login_actions_mobile, true); ?>
						</div>
					</div>
				<?php } ?>

				<?php if (!empty($o_nav)) { ?>
					<?php if (!empty($is_vip_header)) { ?>
						<div class="<?php echo esc_attr($header_sticky_secondary_classes ?? ''); ?>">
							<div class="header-sticky__user-menu">
								<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true); ?>
							</div>
						</div>
					<?php } ?>
				<?php } ?>

				<?php if (!empty($is_vip_header)) { ?>
					<?php if (!empty($header_vip_navbar)) { ?>
						<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/header-vip-navbar.php', $header_vip_navbar, true); ?>
					<?php } ?>
				</div>
			</div>
		<?php } else { ?>
		</div>
	<?php } ?>
</div>