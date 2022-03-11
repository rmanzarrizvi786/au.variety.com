<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $c_heading_outer ) ) { ?>
	<div class="c-heading__outer <?php echo esc_attr( $c_heading_outer_classes ?? '' ); ?>">
<?php } ?>

<?php if ( ! empty( $c_heading_is_primary_heading ) ) { ?>
	<h1 id="<?php echo esc_attr( $c_heading_id_attr ?? '' ); ?>" class="c-heading larva <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_heading_classes ?? '' ); ?>">
<?php } else { ?>
	<h2 id="<?php echo esc_attr( $c_heading_id_attr ?? '' ); ?>" class="c-heading larva <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $c_heading_classes ?? '' ); ?>">
<?php } ?>

	<?php if ( ! empty( $c_heading_url ) ) { ?>
		<a href="<?php echo esc_url( $c_heading_url ?? '' ); ?>" class="<?php echo esc_attr( $c_heading_link_classes ?? '' ); ?>">
	<?php } ?>

		<?php echo esc_html( $c_heading_text ?? '' ); ?>

	<?php if ( ! empty( $c_heading_url ) ) { ?>
		</a>
	<?php } ?>

<?php if ( ! empty( $c_heading_is_primary_heading ) ) { ?>
	</h1>
<?php } else { ?>
	</h2>
<?php } ?>

<?php if ( ! empty( $c_heading_outer ) ) { ?>
	</div>
<?php } ?>
