<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_label_tag_text ) ) { ?>
<<?php echo esc_html( $c_label_tag_text ?? '' ); ?> id="<?php echo esc_attr( $c_label_id_attr ?? '' ); ?>" class="c-label <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_label_classes ?? '' ); ?>" <?php echo esc_attr( $c_label_data_attr ?? '' ); ?>>
<?php } else { ?>
<span id="<?php echo esc_attr( $c_label_id_attr ?? '' ); ?>" class="c-label <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_label_classes ?? '' ); ?>" <?php echo esc_attr( $c_label_data_attr ?? '' ); ?>>
<?php } ?>

<?php if ( ! empty( $c_label_url ) ) { ?>
	<a href="<?php echo esc_url( $c_label_url ?? '' ); ?>" class="c-label__link <?php echo esc_attr( $c_label_link_classes ?? '' ); ?>">
<?php } ?>
	<?php echo esc_html( $c_label_text ?? '' ); ?>
<?php if ( ! empty( $c_label_url ) ) { ?>
	</a>
<?php } ?>

<?php if ( ! empty( $c_label_tag_text ) ) { ?>
</<?php echo esc_html( $c_label_tag_text ?? '' ); ?>>
<?php } else { ?>
</span>
<?php } ?>
