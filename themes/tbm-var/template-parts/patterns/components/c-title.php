<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_title_tag_text ) ) { ?>
<<?php echo esc_html( $c_title_tag_text ?? '' ); ?> id="<?php echo esc_attr( $c_title_id_attr ?? '' ); ?>" class="c-title <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_title_classes ?? '' ); ?>">
<?php } else { ?>
<h3 id="<?php echo esc_attr( $c_title_id_attr ?? '' ); ?>" class="c-title <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_title_classes ?? '' ); ?>">
<?php } ?>

	<?php if ( ! empty( $c_title_above_url ) ) { ?>
		<a href="<?php echo esc_url( $c_title_above_url ?? '' ); ?>" class="<?php echo esc_attr( $c_title_above_link_classes ?? '' ); ?>">
			<?php echo esc_html( $c_title_above_text ?? '' ); ?>
		</a>
	<?php } ?>

	<?php if ( ! empty( $c_title_url ) ) { ?>
		<a href="<?php echo esc_url( $c_title_url ?? '' ); ?>" class="c-title__link <?php echo esc_attr( $c_title_link_classes ?? '' ); ?>" 
		<?php if ( ! empty( $c_title_link_attr ) ) { ?> 
		target="<?php echo esc_attr( $c_title_link_attr ?? '' ); ?>"
		<?php } ?>  <?php echo esc_attr( $c_title_link_data_attr ?? '' ); ?>>
	<?php } ?>

		<?php if ( ! empty( $c_title_before_text ) ) { ?>
			<span class="c-title__before <?php echo esc_attr( $c_title_before_classes ?? '' ); ?>"><?php echo esc_html( $c_title_before_text ?? '' ); ?></span>
		<?php } ?>

		<?php if ( ! empty( $c_title_markup ) ) { ?>
			<?php echo wp_kses_post( $c_title_markup ?? '' ); ?>
		<?php } else { ?>
			<?php echo esc_html( $c_title_text ?? '' ); ?>
		<?php } ?>

	<?php if ( ! empty( $c_title_url ) ) { ?>
		</a>
	<?php } ?>

<?php if ( ! empty( $c_title_tag_text ) ) { ?>
</<?php echo esc_html( $c_title_tag_text ?? '' ); ?>>
<?php } else { ?>
</h3>
<?php } ?>
