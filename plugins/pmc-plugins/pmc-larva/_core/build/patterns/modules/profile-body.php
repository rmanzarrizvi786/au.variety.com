<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="profile-body // <?php echo esc_attr( $profile_body_classes ?? '' ); ?>">

	<?php if ( ! empty( $c_heading ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-heading', $c_heading, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $c_dek ) ) { ?>
		<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-dek', $c_dek, true ); ?>
	<?php } ?>

	<div class="profile-body__content // <?php echo esc_attr( $profile_body_content_classes ?? '' ); ?>">
		<?php echo wp_kses_post( $profile_body_content_markup ?? '' ); ?>
	</div>

</div>
