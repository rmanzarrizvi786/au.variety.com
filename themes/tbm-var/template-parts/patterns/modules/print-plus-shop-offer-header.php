<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<header class='print-plus-offer-header // <?php echo esc_attr( $print_plus_shop_offer_header_classes ?? '' ); ?> ' >
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading_primary, true ); ?>
	<?php if ( ! empty( $c_span_secondary ) ) { ?>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span_secondary, true ); ?>
	<?php } ?>
	<?php if ( ! empty( $o_tab_variety ) ) { ?>
	<div class='print-plus-shop-header-tabs // <?php echo esc_attr( $print_plus_shop_header_tabs_classes ?? '' ); ?>' >
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tab.php', $o_tab_variety, true ); ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tab.php', $o_tab_vip, true ); ?>
	</div>
	<div class='print-plus-shop-header-tabs--mobile // <?php echo esc_attr( $print_plus_shop_header_tabs_classes__mobile ?? '' ); ?>' >
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tab.php', $o_tab_variety__mobile, true ); ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tab.php', $o_tab_vip__mobile, true ); ?>
	</div>
	<?php } ?>
</header>
