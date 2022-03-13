<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $o_icon_button_url ) ) { ?>
	<a href="<?php echo esc_url( $o_button_url ?? '' ); ?>" class="<?php echo esc_attr( $o_icon_button_classes ?? '' ); ?>">
<?php } else { ?>
	<button class="o-icon-button <?php echo esc_attr( $o_icon_button_classes ?? '' ); ?>">
<?php } ?>

	<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-icon', $c_icon, true ); ?>

	<?php if ( ! empty( $o_icon_button_screen_reader_text ) ) { ?>
		<span class="lrv-a-screen-reader-only"><?php echo esc_html( $o_icon_button_screen_reader_text ?? '' ); ?></span>
	<?php } ?>

	<?php if ( ! empty( $c_span ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-span', $c_span, true ); ?>
	<?php } ?>

<?php if ( ! empty( $o_icon_button_url ) ) { ?>
	</a>
<?php } else { ?>
	</button>
<?php } ?>
