<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="profile-landing-header // <?php echo esc_attr( $profile_landing_header_classes ?? '' ); ?>">

	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>

	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/modules/profile-body.php', $profile_body, true ); ?>

	<?php if ( ! empty( $c_button ) ) { ?>
		<div class="lrv-u-text-align-center lrv-u-margin-tb-2">
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-button.php', $c_button, true ); ?>
		</div>
	<?php } ?>
</div>
