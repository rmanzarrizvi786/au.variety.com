<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<a class="c-link-logo // <?php echo esc_attr( $c_link_logo_classes ?? '' ); ?>" href="<?php echo esc_url( $c_link_logo_url ?? '' ); ?>"
	<?php if ( ! empty( $c_link_logo_target_attr ) ) { ?>
		target="<?php echo esc_attr( $c_link_logo_target_attr ?? '' ); ?>"
	<?php } ?>
	<?php if ( ! empty( $c_link_logo_calendar_attr ) ) { ?>
		data-start="<?php echo esc_attr( $data_start_attr ?? '' ); ?>"
		data-title="<?php echo esc_attr( $data_title_attr ?? '' ); ?>"
		data-location="<?php echo esc_attr( $data_location_attr ?? '' ); ?>"
	<?php } ?>
	>
	<span class="c-link-logo-text // <?php echo esc_html( $c_link_logo_text_classes ?? '' ); ?>" ><?php echo esc_html( $c_link_logo_text ?? '' ); ?></span>
	<?php if ( ! empty( $c_link_logo_svg ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/assets/build/svg/' . ( $c_link_logo_svg ?? '' ) . '.svg', [], true ); ?>
		<span class="lrv-a-screen-reader-only"><?php echo esc_html( $c_logo_screen_reader_text ?? '' ); ?></span>
	<?php } ?>
</a>
