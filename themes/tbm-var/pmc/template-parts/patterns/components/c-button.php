<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_button_url ) ) { ?>
	<a class="c-button larva <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_button_classes ?? '' ); ?>" href="<?php echo esc_url( $c_button_url ?? '' ); ?>" target="<?php echo esc_attr( $c_button_target_attr ?? '' ); ?>" rel="<?php echo esc_attr( $c_button_rel_attr ?? '' ); ?>" <?php do_action( 'pmc_do_render_custom_ga_tracking_attr', $c_button_ga_tracking ); ?>>
<?php } else { ?>
	<button class="c-button larva <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_button_classes ?? '' ); ?>" type="<?php echo esc_attr( $c_button_type_attr ?? '' ); ?>">
<?php } ?>

	<span class="c-button__inner <?php echo esc_attr( $c_button_inner_classes ?? '' ); ?>">
		<?php echo esc_html( $c_button_text ?? '' ); ?>
	</span>

	<?php if ( ! empty( $c_button_screen_reader_text ) ) { ?>
		<span class="lrv-a-screen-reader-only"><?php echo esc_html( $c_button_screen_reader_text ?? '' ); ?></span>
	<?php } ?>

<?php if ( ! empty( $c_button_url ) ) { ?>
	</a>
<?php } else { ?>
	</button>
<?php } ?>
