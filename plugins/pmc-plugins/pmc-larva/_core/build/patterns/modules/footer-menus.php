<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="footer-menus // <?php echo esc_attr( $footer_menu_classes ?? '' ); ?>">
	<?php foreach ( $o_navs ?? [] as $item ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-nav', $item, true ); ?>
	<?php } ?>
</div>
