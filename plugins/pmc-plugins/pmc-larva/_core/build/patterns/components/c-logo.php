<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_logo_is_h1 ) ) { ?>
	<h1 class="lrv-u-flex">
<?php } ?>
	<a class="c-logo <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_logo_classes ?? '' ); ?>" href="<?php echo esc_url( $c_logo_url ?? '' ); ?>">
		<?php \PMC::render_template( \PMC\Larva\Config::get_instance()->get( 'brand_directory' ) . '/build/svg/' . ( $c_logo_svg ?? '' ) . '.svg', [], true ); ?>
		<span class="lrv-a-screen-reader-only"><?php echo esc_html( $c_logo_screen_reader_text ?? '' ); ?></span>
	</a>
<?php if ( ! empty( $c_logo_is_h1 ) ) { ?>
	</h1>
<?php } ?>
