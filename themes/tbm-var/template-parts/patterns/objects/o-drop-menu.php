<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="o-drop-menu <?php echo esc_attr( $o_drop_menu_classes ?? '' ); ?>" data-collapsible="collapsed" <?php echo esc_attr( $o_drop_data_attr ?? '' ); ?>
	<?php if ( ! empty( $o_drop_data_attr ) ) { ?>
		<?php echo esc_attr( $o_drop_data_attr ?? '' ); ?>
	<?php } ?>>
	<a class="o-drop-menu__toggle <?php echo esc_attr( $o_drop_menu_toggle_classes ?? '' ); ?>" href="#" data-collapsible-toggle="always-show">
		<?php if ( ! empty( $c_span ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_icon_button ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-icon-button.php', $o_icon_button, true ); ?>
		<?php } ?>
	</a>

	<div class="o-drop-menu__list <?php echo esc_attr( $o_drop_menu_list_classes ?? '' ); ?>" data-collapsible-panel>
		<?php if ( ! empty( $c_span_user ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_user, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $c_tagline ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
		<?php } ?>

		<?php if ( ! empty( $o_nav ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true ); ?>
		<?php } ?>
	</div>
</div>
