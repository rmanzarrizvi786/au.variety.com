<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="profile-landing-header // <?php echo esc_attr( $profile_landing_header_classes ?? '' ); ?>">

	<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-lazy-image', $c_lazy_image, true ); ?>

	<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'modules/profile-body', $profile_body, true ); ?>

	<?php if ( ! empty( $c_button ) ) { ?>
		<div class="lrv-u-text-align-center lrv-u-margin-tb-2">
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-button', $c_button, true ); ?>
		</div>
	<?php } ?>
</div>
