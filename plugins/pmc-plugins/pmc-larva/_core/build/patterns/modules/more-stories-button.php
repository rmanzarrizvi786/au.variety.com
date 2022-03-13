<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<nav class="more-stories-button // lrv-u-flex lrv-u-justify-content-space-between <?php echo esc_attr( $more_stories_button_classes ?? '' ); ?>">
	<?php if ( ! empty( $c_button ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_button_prev ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button_prev, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_button_next ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button_next, true ); ?>
	<?php } ?>
</nav>
