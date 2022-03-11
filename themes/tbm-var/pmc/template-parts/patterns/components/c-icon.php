<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_icon_url ) ) { ?>
<a class="<?php echo esc_attr( $c_icon_link_classes ?? '' ); ?>" href="<?php echo esc_url( $c_icon_url ?? '' ); ?>"
	<?php if ( ! empty( $c_icon_rel_name ) ) { ?>
		rel="<?php echo esc_attr( $c_icon_rel_name ?? '' ); ?>"
	<?php } ?>
	<?php if ( ! empty( $c_icon_target_attr ) ) { ?>
		target="<?php echo esc_attr( $c_icon_target_attr ?? '' ); ?>"
	<?php } ?>
>
	<span class="lrv-a-screen-reader-only"><?php echo esc_html( $c_icon_link_screen_reader_text ?? '' ); ?></span>
<?php } ?>

<?php if ( ! empty( $c_icon_screen_reader_text ) ) { ?>
	<?php if ( ! empty( $c_icon_screen_reader_tag_text ) ) { ?>
	<<?php echo esc_html( $c_icon_screen_reader_tag_text ?? '' ); ?> class="lrv-a-screen-reader-only" title="<?php echo esc_attr( $c_icon_screen_reader_title_attr ?? '' ); ?>"><?php echo esc_html( $c_icon_screen_reader_text ?? '' ); ?></<?php echo esc_html( $c_icon_screen_reader_tag_text ?? '' ); ?>>
	<?php } else { ?>
	<span class="lrv-a-screen-reader-only" title="<?php echo esc_attr( $c_icon_screen_reader_title_attr ?? '' ); ?>"><?php echo esc_html( $c_icon_screen_reader_text ?? '' ); ?></span>
	<?php } ?>
<?php } ?>

<svg class="c-icon <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_icon_classes ?? '' ); ?>"
	<?php if ( ! empty( $c_icon_screen_reader_text ) ) { ?>
		aria-hidden="true"
	<?php } ?>
>
	<use xlink:href="#<?php echo esc_attr( $c_icon_name ?? '' ); ?>" />
	<?php if ( ! empty( $c_icon_secondary_name ) ) { ?>
		<use xlink:href="#<?php echo esc_attr( $c_icon_secondary_name ?? '' ); ?>" />
	<?php } ?>
</svg>

<?php if ( ! empty( $c_icon_url ) ) { ?>
</a>
<?php } ?>
