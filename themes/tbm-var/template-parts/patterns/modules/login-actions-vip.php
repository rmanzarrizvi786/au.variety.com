<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="login-actions // <?php echo esc_attr( $loginactions_classes ?? '' ); ?> lrv-u-flex lrv-u-align-items-center lrv-u-margin-r-050">
	<div class="js-LoginStatus--show-when-authenticated">
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-drop-menu.php', $o_drop_menu, true ); ?>
	</div>
	<div class="js-LoginStatus--hide-when-authenticated">
		<div class="lrv-u-flex">
			<div class="a-hidden@mobile-max lrv-u-flex lrv-u-margin-r-1">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/cxense-widget.php', $cxense_header_subscribe_widget, true ); ?>
				<div class="a-subscription-banner lrv-a-hidden">
					<div id="cx-fly-out-vip">
					</div>
				</div>
			</div>
			<div class="<?php echo esc_attr( $header_login_wrapper_classes ?? '' ); ?>">
				<div class="a-hidden@mobile-max">
					<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/header-button.php', $header_login_button, true ); ?>
				</div>
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-login-icon.php', $o_login_icon, true ); ?>
			</div>
		</div>
	</div>
</div>
