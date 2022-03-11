<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="more-from-widget // <?php echo esc_attr( $more_from_widget_classes ?? '' ); ?>">
	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-from-heading.php', $o_more_from_heading, true ); ?>

	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-tease-list.php', $o_tease_list, true ); ?>

	<div class="lrv-a-wrapper lrv-u-flex lrv-u-justify-content-space-between lrv-u-padding-tb-1">
		<?php if ( ! empty( $more_from_widget_is_paged ) ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link_previous, true ); ?>
		<?php } ?>

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-more-link.php', $o_more_link, true ); ?>
	</div>


</div>
