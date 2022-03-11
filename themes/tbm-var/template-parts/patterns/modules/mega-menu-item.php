<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php 
/*
 Note: the mega-menu__parent-list-item and mega-menu__child-list classes are hooked up to custom CSS and will eventually be removed. */
?>
<li class="mega-menu-item // mega-menu__parent-list-item // <?php echo esc_attr( $mega_menu_parent_list_item_classes ?? '' ); ?>" data-collapsible="collapsed">
	<div class="<?php echo esc_attr( $mega_menu_parent_list_item_inner_classes ?? '' ); ?>">
		<button class="<?php echo esc_attr( $mega_menu_toggle_button_classes ?? '' ); ?>"><span class="lrv-a-screen-reader-only">Expand the sub menu</span></button>
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link, true ); ?>
	</div>

	<ul class="mega-menu__child-list // <?php echo esc_attr( $mega_menu_children_list_classes ?? '' ); ?>" data-collapsible-panel="" data-collapsible-breakpoint="mobile-only">
		<?php foreach ( $mega_menu_item_children ?? [] as $item ) { ?>
			<li class="mega-menu__child-list-item // <?php echo esc_attr( $mega_menu_children_list_item_classes ?? '' ); ?>">

				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>
</li>
