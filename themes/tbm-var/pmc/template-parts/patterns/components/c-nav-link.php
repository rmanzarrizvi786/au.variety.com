<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<a class="c-nav-link <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_nav_link_classes ?? '' ); ?>" href="<?php echo esc_url( $c_nav_link_url ?? '' ); ?>"
	<?php if ( ! empty( $c_nav_link_aria_current_attr ) ) { ?>
		aria-current="<?php echo esc_attr( $c_nav_link_aria_current_attr ?? '' ); ?>"
	<?php } ?>
>
	<?php echo esc_html( $c_nav_link_text ?? '' ); ?>
	<?php if ( ! empty( $c_nav_link_screen_reader_text ) ) { ?>
	<span class="lrv-a-screen-reader-only"><?php echo esc_html( $c_nav_link_screen_reader_text ?? '' ); ?></span>
	<?php } ?>
</a>
