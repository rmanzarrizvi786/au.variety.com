<div class="get__magazine-container" style="background-color:<?php echo esc_attr( $background_color ); ?>">
	<?php PMC::render_template( sprintf( '%s/templates/frontend/sub-modules/magazine-cover.php', untrailingslashit( PMC_SUBSCRIPTION_BANNERS_ROOT ) ), [ 'module_details' => $module_details ], true ); ?>
	<div class="gtm-sub-container">
		<?php PMC::render_template( sprintf( '%s/templates/frontend/sub-modules/magazine-text.php', untrailingslashit( PMC_SUBSCRIPTION_BANNERS_ROOT ) ), [ 'module_details' => $module_details ], true ); ?>
		<?php PMC::render_template( sprintf( '%s/templates/frontend/sub-modules/magazine-button.php', untrailingslashit( PMC_SUBSCRIPTION_BANNERS_ROOT ) ), [ 'module_details' => $module_details ], true ); ?>
	</div>
</div>
