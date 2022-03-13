<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="o-select-nav js-SelectNav js-SelectNav-redirect lrv-a-glue-parent <?php echo esc_attr( $o_select_nav_classes ?? '' ); ?>">
	<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>

	<select class="o-select-nav__select js-SelectNav-select lrv-u-cursor-pointer lrv-a-glue lrv-a-glue--t-0 lrv-a-glue--l-0 lrv-u-width-100p lrv-u-height-100p lrv-u-opacity-0 <?php echo esc_attr( $o_select_nav_select_classes ?? '' ); ?>">
		<?php foreach ( $o_select_nav_options ?? [] as $item ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-select-option', $item, true ); ?>
		<?php } ?>
	</select>
</div>
