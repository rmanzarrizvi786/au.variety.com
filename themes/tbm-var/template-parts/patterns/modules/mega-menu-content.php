<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php 
/*
 Note: the mega-menu__parent-list class is hooked up to custom CSS and will eventually be removed. */
?>
<ul class="mega-menu-content // mega-menu__parent-list // lrv-a-unstyle-list lrv-a-grid a-cols5@tablet u-grid-gap-075 u-grid-gap-175@tablet u-background-color-geyser u-background-color-brand-accent-100-b@tablet u-padding-lr-3 lrv-u-padding-t-1" data-collapsible-group="">	
	
	<?php foreach ( $mega_menu_content_items ?? [] as $item ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/mega-menu-item.php', $item, true ); ?>
	<?php } ?>

</ul>

