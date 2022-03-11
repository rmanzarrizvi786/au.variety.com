<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="login-actions // <?php echo esc_attr( $loginactions_classes ?? '' ); ?> lrv-u-flex lrv-u-align-items-center lrv-u-margin-r-050">
	<div class="js-LoginStatus--hide-when-authenticated">
		<div class="lrv-u-flex">
			<div class="lrv-u-flex">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/cxense-widget.php', $cxense_header_subscribe_widget, true ); ?>
			</div>
		</div>
	</div>

	<div class="<?php echo esc_attr( $header_login_wrapper_classes ?? '' ); ?>">
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-drop-menu-login.php', $o_drop_menu, true ); ?>
	</div>
</div>
